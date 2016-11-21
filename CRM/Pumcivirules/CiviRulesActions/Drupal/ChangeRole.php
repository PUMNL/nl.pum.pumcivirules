<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Pumcivirules_CiviRulesActions_Drupal_ChangeRole extends CRM_Civirules_Action {

  /**
   * Process the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $action_parameters = $this->getActionParameters();
    $drupal_roles_to_remove = $action_parameters['roles_to_remove'];
    $drupal_roles_to_add = $action_parameters['roles_to_add'];

    $drupal_uid = CRM_Pumcivirules_CiviRulesActions_Drupal_User::getDrupalUid($triggerData->getContactId());
    if ($drupal_uid) {
      CRM_Pumcivirules_CiviRulesActions_Drupal_User::unsetRolesFromUser($drupal_uid, $drupal_roles_to_remove);
      CRM_Pumcivirules_CiviRulesActions_Drupal_User::assignRolesToUser($drupal_uid, $drupal_roles_to_add);
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
    return CRM_Utils_System::url('civicrm/civirule/form/action/drupal/changerole', 'rule_action_id='.$ruleActionId);
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
    $drupal_roles_to_remove = $action_parameters['roles_to_remove'];
    $drupal_roles_to_add = $action_parameters['roles_to_add'];
    $role_names = user_roles(TRUE);
    $roles_to_remove = array();
    if ($drupal_roles_to_remove && is_array($drupal_roles_to_remove)) {
      foreach ($drupal_roles_to_remove as $rid) {
        $roles_to_remove[] = $role_names[$rid];
      }
    }
    $roles_to_add = array();
    if ($drupal_roles_to_add && is_array($drupal_roles_to_add)) {
      foreach ($drupal_roles_to_add as $rid) {
        $roles_to_add[] = $role_names[$rid];
      }
    }
    return ts('Roles to remove: %1 and roles to add %2', array(1 => implode(", ", $roles_to_remove), 2 => implode(", ", $roles_to_add)));
  }

}