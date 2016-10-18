<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Pumcivirules_CiviRulesActions_Drupal_CreateUserAccount extends CRM_Civirules_Action {

  /**
   * Process the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $action_parameters = $this->getActionParameters();
    $drupal_roles = $action_parameters['roles'];

    $drupal_uid = CRM_Pumcivirules_CiviRulesActions_Drupal_User::createUser($triggerData->getContactId());
    if ($drupal_uid) {
      //activate user
      $user = user_load($drupal_uid);
      $user->status = 1; //activate user
      user_save($user);

      //assign role sto drupal user
      CRM_Pumcivirules_CiviRulesActions_Drupal_User::assignRolesToUser($drupal_uid, $drupal_roles);
    }
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleActionId
   * @return bool|string
   * $access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/drupal/createuser', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $action_parameters = $this->getActionParameters();
    $drupal_roles = $action_parameters['roles'];
    $role_names = user_roles(TRUE);
    $roles = array();
    foreach($drupal_roles as $rid) {
      $roles[] = $role_names[$rid];
    }
    if (count($roles)) {
      return ts('With roles: %1', array(1 => implode(", ", $roles)));
    }
    return '';
  }

}