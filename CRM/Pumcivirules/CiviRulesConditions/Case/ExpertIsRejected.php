<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Pumcivirules_CiviRulesConditions_Case_ExpertIsRejected extends CRM_Civirules_Condition {

  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    if ($this->isValidCaseTypeAndIsClientOfCase($triggerData)) {
      $case = $triggerData->getEntityData('Case');
      $originalData = $triggerData->getOriginalData();

      $rejectExpertCustomGroupId = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'Assessment_Expert_Application', 'return' => 'id'));
      $rejectExpertCustomField = civicrm_api3('CustomField', 'getsingle', array('name' => 'Reject_Expert_Application', 'custom_group_id' => $rejectExpertCustomGroupId));
      $customFieldKey = 'custom_'.$rejectExpertCustomField['id'];
      if (isset($case[$customFieldKey]) && empty($originalData[$customFieldKey])) {
        if (empty($originalData[$customFieldKey]) && !empty($case[$customFieldKey])) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Checks whether the tirgger data is valid for additional processing.
   *
   * This function checks whether the trigger data contains an original
   * value so we could detect whether a custom field is changed.
   * And whether the case is of type expert application and the trigger is on
   * the client of the case.
   *
   * @param \CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   */
  protected function isValidCaseTypeAndIsClientOfCase(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    if (!$triggerData instanceof CRM_Civirules_TriggerData_Edit) {
      // The trigger data does not contain the original value
      // So we can not do a comparison between old and new value
      // and therefor could determine whether the custom value is changed.
      return false;
    }
    $case = $triggerData->getEntityData('Case');
    if (!$case) {
      return false;
    }

    $relationship = $triggerData->getEntityData('Relationship');
    if (!empty($relationship)) {
      // Case trigger has a relationship filled in meaning that the trigger is triggered
      // on a case role.
      // When relationship is empty it means the case trigger is triggered on the client
      // of the case.
      return false;
    }

    try {
      $caseTypeOptionGroupId = civicrm_api3('OptionGroup', 'getvalue', array('name' => 'case_type', 'return' => 'id'));
      $expertApplicationCaseTypeId = civicrm_api3('OptionValue', 'getvalue', array('name' => 'Expertapplication', 'return' => 'value', 'option_group_id' => $caseTypeOptionGroupId));
      if ($case['case_type_id'] == $expertApplicationCaseTypeId) {
        return true;
      }
    } catch (Exception $e) {
      // Do nothing.
    }
    return false;
  }

  public function getExtraDataInputUrl($ruleConditionId) {
    return '';
  }

  /**
   * This function validates whether this condition works with the selected trigger.
   *
   * This function could be overriden in child classes to provide additional validation
   * whether a condition is possible in the current setup. E.g. we could have a condition
   * which works on contribution or on contributionRecur then this function could do
   * this kind of validation and return false/true
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    // This condition only works with the trigger case custom data changed.
    if ($trigger instanceof CRM_CivirulesPostTrigger_CaseCustomDataChanged) {
      return true;
    }
    return false;
  }

}