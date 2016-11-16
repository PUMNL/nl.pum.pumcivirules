<?php
/**
 * Class for PUM CiviRules Condition Is First Main Activity of Project
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 30 Sep 2016
 * @license AGPL-3.0
 */

class CRM_Pumcivirules_CiviRulesConditions_FirstMainActivity extends CRM_Civirules_Condition {

  /**
   * Method to determine if the condition is valid
   * Condition is valid if this is the first case in the project of the type Advice, Business, Remote
   * Coaching or Seminar after the Projectintake case
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $projectData = $triggerData->getEntityData('PumProject');
    $caseData = $triggerData->getEntityData('Case');
    if (isset($caseData['case_type_id']) && CRM_Pumcivirules_Utils::isMainActivityCase($caseData['case_type_id'])) {
      if (empty($projectData)) {
        $projectId = CRM_Threepeas_BAO_PumCaseProject::getProjectIdWithCaseId($caseData['id']);
      } else {
        $projectId = $projectData['id'];
      }
      $projectCases = CRM_Threepeas_BAO_PumProject::getCasesByProjectId($projectId);
      foreach ($projectCases as $projectCase) {
        if ($projectCase['case_id'] == $caseData['id'] && CRM_Pumcivirules_Utils::isMainActivityCase($projectCase['case_type'])) {
          return TRUE;
        } elseif ($projectCase['case_id'] != $caseData['id'] && CRM_Pumcivirules_Utils::isMainActivityCase($projectCase['case_type'])) {
          return FALSE;
        }
      }
    }
    return FALSE;
  }

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