<?php
class CRM_Pumcivirules_CiviRulesActions_SetExpertStatusEndDate extends CRM_Civirules_Action {

  private static $expertStatusEndDateCustomField;

  public function getExtraDataInputUrl($ruleActionId) {
    return '';
  }

  /**
   * Returns the custom field 'expert_status_end_date'
   * @return array
   */
  public static function getexpertStatusEndDateCustomField() {
    if (!self::$expertStatusEndDateCustomField) {
      $custom_group_id = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'expert_data', 'return' => 'id'));
      self::$expertStatusEndDateCustomField = civicrm_api3('CustomField', 'getsingle', array('name' => 'expert_status_end_date', 'custom_group_id' => $custom_group_id));
    }
    return self::$expertStatusEndDateCustomField;
  }

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $field = self::getexpertStatusEndDateCustomField();
    $action_params = $this->getActionParameters();
    $params['id'] = $triggerData->getContactId();
    $params['custom_'.$field['id']] = date('d-m-Y');

    civicrm_api3('Contact', 'create', $params);
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

    $status = $statusses[$params['expert_status_end_date']];
    $label = ts('Set expert status end date to date of action');
    return $label;
  }


}