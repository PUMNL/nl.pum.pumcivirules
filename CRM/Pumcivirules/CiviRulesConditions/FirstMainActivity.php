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
    $caseData = $triggerData->getEntityData('Case');
    $activityData = $triggerData->getEntityData('Activity');
    CRM_Core_Error::debug('case data', $caseData);
    CRM_Core_Error::debug('activity data', $activityData);
    exit();
    $validCaseTypes = $this->setValidCaseTypes();


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