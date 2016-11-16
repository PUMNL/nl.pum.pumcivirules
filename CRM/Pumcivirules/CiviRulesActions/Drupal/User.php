<?php
/**
 * This is a util class which could be used for actions on the drupal account.
 * E.g. create user account and set a role; or remove certain role from a drupal account.
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_Pumcivirules_CiviRulesActions_Drupal_User {

  /**
   * Returns the User ID of the drupal account.
   * Returns false when no drupal user is found.
   *
   * @param $contact_id
   * @return int|false
   */
  public static function getDrupalUid($contact_id) {
    try {
      $domain_id = CRM_Core_Config::domainID();
      $uf = civicrm_api3('UFMatch', 'getsingle', array('contact_id' => $contact_id, 'domain_id' => $domain_id));
      return $uf['uf_id'];
    } catch (Exception $e) {
      //do nothing
    }
    return false;
  }

  /**
   * Assign roles to a drupal user.
   *
   * @param $uid
   * @param $roles
   */
  public static function assignRolesToUser($uid, $roles) {
    $user = user_load($uid);
    $role_names = user_roles(TRUE);
    $user_roles = $user->roles;
    $changes = 0;
    foreach($roles as $rid) {
      if (!isset($user_roles[$rid])) {
        // The role is not set. Set it.
        $user_roles[$rid] = $role_names[$rid];
        $changes++;
      }
    }
    user_save($user, array('roles' => $user_roles));
  }

  public static function blockUserAccount($uid) {
    $users = entity_load('user', array($uid));

    foreach ($users as $uid => $user) {
      $user->status = 0;
      user_save($user);
    }
  }

  /**
   * Unset roles from a drupal user.
   *
   * @param $uid
   * @param $roles
   */
  public static function unsetRolesFromUser($uid, $roles) {
    $user = user_load($uid);
    $user_roles = $user->roles;
    $changes = 0;
    foreach($roles as $rid) {
      if (isset($user_roles[$rid])) {
        // The role is not set. Set it.
        unset($user_roles[$rid]);
        $changes++;
      }
    }
    user_save($user, array('roles' => $user_roles));
  }

  /**
   * Create a drupal user account for the selected contact.
   * An e-mail is send to the user to notify the user of its new account.
   *
   * @param $contact_id
   * @return false|int
   * @throws \Exception
   */
  public static function createUser($contact_id) {
    $drupal_uid = self::getDrupalUid($contact_id);
    if ($drupal_uid !== false) {
      return $drupal_uid;
    }

    //create user in drupal
    //user the form api to create the user
    $form_state = form_state_defaults();
    try {
      $email = civicrm_api3('Email', 'getsingle', array('contact_id' => $contact_id, 'is_primary' => '1'));
    } catch (Exception $e) {
      Throw new Exception('Could not find an e-mail address for the contact.');
    }

    $name = $email['email'];
    $form_state['input'] = array(
      'name' => $name,
      'mail' => $email['email'],
      'op' => 'Create new account',
      'notify' => true,
    );

    $pass = self::randomPassword();
    $form_state['input']['pass'] = array('pass1'=>$pass,'pass2'=>$pass);

    $form_state['rebuild'] = FALSE;
    $form_state['programmed'] = TRUE;
    $form_state['complete form'] = FALSE;
    $form_state['method'] = 'post';
    $form_state['build_info']['args'] = array();
    /*
    * if we want to submit this form more than once in a process (e.g. create more than one user)
    * we must force it to validate each time for this form. Otherwise it will not validate
    * subsequent submissions and the manner in which the password is passed in will be invalid
    * */
    $form_state['must_validate'] = TRUE;

    $config = CRM_Core_Config::singleton();
    $config->inCiviCRM = TRUE;

    /*
     * We have created a duplicate of the drupal user_register_form function
     * just to create a default form so that we could set that an administrator
     * has created the account, rather the really role the user has
     */
    $form = self::getUserRegisterForm($form_state);

    //process the form with standard drupal functionality
    $form_state['process_input'] = 1;
    $form_state['submitted'] = 1;
    $form['#array_parents'] = array();
    $form['#tree'] = FALSE;
    drupal_process_form('user_register_form', $form, $form_state);

    $config->inCiviCRM = FALSE;

    if (form_get_errors()) {
      throw new Exception('Could not create drupal user account');
    }
    $drupal_uid = $form_state['user']->uid;

    $ufmatch             = new CRM_Core_DAO_UFMatch();
    $ufmatch->domain_id  = CRM_Core_Config::domainID();
    $ufmatch->uf_id      = $drupal_uid;
    $ufmatch->contact_id = $contact_id;
    $ufmatch->uf_name    = $name;

    if (!$ufmatch->find(TRUE)) {
      $ufmatch->save();
    }

    return $drupal_uid;
  }

  /**
   * Get the user registration form array so we could use it to register a user.
   *
   * @param $form_state
   * @return mixed
   */
  private static function getUserRegisterForm(&$form_state) {
    $form = array();

    // Pass access information to the submit handler. Running an access check
    // inside the submit function interferes with form processing and breaks
    // hook_form_alter().
    $form['administer_users'] = array(
      '#type' => 'value',
      '#value' => 1,
    );

    $form['#user'] = drupal_anonymous_user();
    $form['#user_category'] = 'register';

    // Start with the default user account fields.
    user_account_form($form, $form_state);

    // Attach field widgets, and hide the ones where the 'user_register_form'
    // setting is not on.
    $langcode = entity_language('user', $form['#user']);
    field_attach_form('user', $form['#user'], $form, $form_state, $langcode);
    foreach (field_info_instances('user', 'user') as $field_name => $instance) {
      if (empty($instance['settings']['user_register_form'])) {
        $form[$field_name]['#access'] = FALSE;
      }
    }

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Create new account'),
    );

    $form['#validate'][] = 'user_register_validate';
    // Add the final user registration form submit handler.
    $form['#submit'][] = 'user_register_submit';

    return $form;
  }

  /**
   * Generates an random password.
   *
   * @return string
   */
  private static function randomPassword() {
    //from http://stackoverflow.com/a/6101969
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
      $n = rand(0, $alphaLength);
      $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
  }

}