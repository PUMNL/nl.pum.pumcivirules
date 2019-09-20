<?php
class CRM_Pumcivirules_CiviRulesActions_ChangePrimaryMail extends CRM_Civirules_Action {

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

  public static function getEmailAddresses($contact_id) {
    $params_email_addresses = array(
      'version' => 3,
      'sequential' => 1,
      'contact_id' => $contact_id,
    );
    $email_addresses = civicrm_api('Email', 'get', $params_email_addresses);

    $return_email = array();
    if(is_array($email_addresses['values'])){
      foreach($email_addresses['values'] as $key => $value) {
        $return_email[$value['id']] = $value['email'];
      }
    }
    return $return_email;
  }

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $action_params = $this->getActionParameters();
    $params['id'] = $triggerData->getContactId();
    $email_addresses = $this->getEmailAddresses($params['id']);
    $primary_email_set == 0;

    foreach($email_addresses as $email_id => $email_address) {
      if(strpos($email_address, '@pum.nl') > 0){
        $params = array(
          'version' => 3,
          'sequential' => 1,
          'id' => $email_id,
        );
        $result = civicrm_api('Email', 'delete', $params);
      } else {
        $params = array(
          'version' => 3,
          'sequential' => 1,
          'contact_id' => $params['id']
        );
        if($primary_email_set == 0) {
          $params['is_primary'] = 0;
          $primary_email_set = 1;
        }
        $result = civicrm_api('Email', 'update', $params);
      }
    }
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

    $label = ts('Delete @pum.nl address and change primary email to first found private email address');
    return $label;
  }
}