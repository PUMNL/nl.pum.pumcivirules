<?php

/**
 * Trigger when an Activity linked to a Case changes.
 *
 * Notice: this class extends from CRM_CivirulesPostTrigger_Activity
 * (trigger on Activity change). By doing this, we reuse all the
 * Activity triggering logic, while still filtering for Case-related
 * activities.
 */
class CRM_Pumcivirules_CiviRulesPostTrigger_PumCaseProject extends CRM_Civirules_Trigger_Post {

  /**
   * Override getTriggerDataFromPost() so that we can append the Case and Project
   * entity to the trigger data.
   */
  protected function getTriggerDataFromPost($op, $objectName, $objectId, $objectRef) {
    $triggerData = parent::getTriggerDataFromPost($op, $objectName,
                                                  $objectId, $objectRef);
    $case = new CRM_Case_BAO_Case();
    if ($objectRef instanceof CRM_Activity_DAO_Activity && $objectRef->case_id) {
      $case->id = $objectRef->case_id;
    } else {
      // Get the CaseActivity record.
      $caseActivity = new CRM_Case_DAO_CaseActivity();
      $caseActivity->activity_id = $objectId;
      if ($caseActivity->find(TRUE)) {
        // Now load the case.
        $case->id = $caseActivity->case_id;
      }
    }

    if ($case->id && $case->find(TRUE)) {
      $data = array();
      CRM_Core_DAO::storeValues($case, $data);
      $triggerData->setEntityData('Case', $data);
    }
    return $triggerData;
  }


  /**
   * Trigger a rule for this trigger
   *
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   */
  public function triggerTrigger($op, $objectName, $objectId, $objectRef) {
    if ($op == 'create') {
      $t = $this->getTriggerDataFromPost($op, $objectName, $objectId, $objectRef);
      $triggerData = clone $t;
      // get and setcase and projectdata
      if (isset($objectRef->project_id)) {
        $projectData = CRM_Threepeas_BAO_PumProject::getValues(array('id' => $objectRef->project_id));
        $triggerData->setEntityData('PumProject', $projectData[$objectRef->project_id]);
      }
      if (isset($objectRef->case_id)) {
        $caseData = civicrm_api3('Case', 'getsingle', array('id' => $objectRef->case_id));
        $triggerData->setEntityData('Case', $caseData);
        $triggerData->setContactId($caseData['client_id'][1]);
      }
      if ($caseData && $projectData) {
        CRM_Civirules_Engine::triggerRule($this, $triggerData);
      }
    }
  }

  protected function isCaseActivity($op, $objectName, $objectId, $objectRef) {
    if ($objectName != 'Activity') {
      return false;
    }
    if (isset($objectRef->case_id) && !empty($objectRef->case_id)) {
      return true;
    } elseif (CRM_Case_BAO_Case::isCaseActivity($objectId)) {
      return true;
    }
    return false;
  }


  /**
   * Returns additional entities provided in this trigger.
   *
   * @return array of CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function getAdditionalEntities() {
    $entities = parent::getAdditionalEntities();
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('Case', 'Case', 'CRM_Case_DAO_Case' , 'Case');
    return $entities;
  }
}
