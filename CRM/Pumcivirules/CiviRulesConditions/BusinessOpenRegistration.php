<?php
/**
 * Class for PUM CiviRules Condition Business Link is Open for Registration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 7 Dec 2016
 * @license AGPL-3.0
 */

class CRM_Pumcivirules_CiviRulesConditions_BusinessOpenRegistration extends CRM_Civirules_Condition {

  /**
   * Method to determine if the condition is valid
   * Condition is valid if the case of the type Business is Open for Registration (custom field)
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $caseData = $triggerData->getEntityData('Case');
    // makes no sense to do something if there is no case
    if (!empty($caseData)) {
      return $this->isOpenForRegistration($caseData);
    }
    return FALSE;
  }
  /**
   * Method to determine if case is open for registration
   *
   * @param array $caseData
   * @return bool
   */
  private function isOpenForRegistration($caseData) {
    // return false if not Business case type
    try {
      $businessCaseTypeId = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'case_type',
        'name' => 'Business',
        'return' => 'value'));
      if ($caseData['case_type_id'] != $businessCaseTypeId) {
        return FALSE;
      }
      // retrieve custom field id
      try {
        $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
          'custom_group_id' => 'Business_Data',
          'name' => 'Open_for_Registration',
          'return' => 'id'
        ));
        $index = "custom_" . $customFieldId;
        if (isset($caseData[$index])) {
          return $caseData[$index];
        }
      } catch (CiviCRM_API3_Exception $ex) {}
    } catch (CiviCRM_API3_Exception $ex) {}
    return FALSE;  }

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleConditionId
   * @return bool|string
   * @access public
   * @abstract
   */
  public function getExtraDataInputUrl($ruleConditionId) {
    return FALSE;
  }

  /**
   * Overridden parent method checks if condition works with trigger when condition is added
   * For First Main Activity it does not make sense if case is not one of the provided entities of the trigger
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    $providedEntities = $trigger->getProvidedEntities();
    foreach ($providedEntities as $entityName => $entityData) {
      if (strtolower($entityName) == 'case') {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Returns an array with required entity names
   *
   * @return array
   * @access public
   */
  public function requiredEntities() {
    return array('Case');
  }
}