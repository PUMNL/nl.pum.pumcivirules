<?php
/**
 * Class for PUM CiviRules Condition Is First Travel Case of Project
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 30 Sep 2016
 * @license AGPL-3.0
 */

class CRM_Pumcivirules_CiviRulesConditions_FirstTravelCase extends CRM_Civirules_Condition {

  /**
   * Method to determine if the condition is valid
   * Condition is valid if this is the first case Travel Case on the first Main Activity in the project
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $caseData = $triggerData->getEntityData('Case');
    if (isset($caseData['id'])) {
      $parentCaseId = CRM_Travelcase_Utils_GetParentCaseId::getParentCaseId($caseData['id']);
      if ($parentCaseId) {
        $projectId = CRM_Threepeas_BAO_PumCaseProject::getProjectIdWithCaseId($parentCaseId);
        if ($projectId) {
          $firstCaseId = CRM_Pumcivirules_Utils::getFirstMainActivityCaseId($projectId);
          if ($firstCaseId) {
            if ($parentCaseId == $firstCaseId) {
              if ($this->isFirstTravelCaseOnParentCase($parentCaseId) == TRUE) {
                return TRUE;
              }
            }
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Method to check if this is the first travel case on the parent
   *
   * @param $parentCaseId
   * @return bool
   */
  private function isFirstTravelCaseOnParentCase($parentCaseId) {
    $travelParentTableName = civicrm_api3('CustomGroup', 'getvalue', array(
      'name' => 'travel_parent',
      'return' => 'table_name'
    ));
    $sql = 'SELECT COUNT(*) FROM ' . $travelParentTableName . ' tp JOIN civicrm_case c ON tp.entity_id=c.id
      JOIN civicrm_case_contact cc ON c.id=cc.case_id WHERE tp.case_id = %1 AND c.is_deleted = %2';
    $countTravelCases = CRM_Core_DAO::singleValueQuery($sql, array(
      1 => array($parentCaseId, 'Integer'),
      2 => array(0, 'Integer')
    ));
    if ($countTravelCases == 0) {
      return TRUE;
    } else {
      return FALSE;
    }
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
   * Returns an array with required entity names
   *
   * @return array
   * @access public
   */
  public function requiredEntities() {
    return array('Case');
  }
}