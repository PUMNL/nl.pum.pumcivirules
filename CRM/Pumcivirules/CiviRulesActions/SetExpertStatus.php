<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Pumcivirules_CiviRulesActions_SetExpertStatus extends CRM_Civirules_Action {

  private static $expertStatusCustomField;

  private static $expertStatusOptions;

  /**
   * Returns the custom field 'expert_status'
   * @return array
   */
  public static function getExpertStatusCustomField() {
    if (!self::$expertStatusCustomField) {
      $custom_group_id = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'expert_data', 'return' => 'id'));
      self::$expertStatusCustomField = civicrm_api3('CustomField', 'getsingle', array('name' => 'expert_status', 'custom_group_id' => $custom_group_id));
    }
    return self::$expertStatusCustomField;
  }

  public static function getExpertStatusOptions() {
    if (!self::$expertStatusOptions) {
      $field = self::getExpertStatusCustomField();
      $option_values = civicrm_api3('OptionValue', 'get', array('option_group_id' => $field['option_group_id']));
      self::$expertStatusOptions = array();
      foreach($option_values['values'] as $option_value) {
        self::$expertStatusOptions[$option_value['value']] = $option_value['label'];
      }
    }
    return self::$expertStatusOptions;
  }

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $field = self::getExpertStatusCustomField();
    $action_params = $this->getActionParameters();
    $params['id'] = $triggerData->getContactId();
    $params['custom_'.$field['id']] = $action_params['expert_status'];

    civicrm_api3('Contact', 'create', $params);
  }

  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/contact/expertstatus', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $params = $this->getActionParameters();
    $statusses = self::getExpertStatusOptions();
    $status = $statusses[$params['expert_status']];
    $label = ts('Set expert status to %1', array(1=>$status));
    return $label;
  }


}