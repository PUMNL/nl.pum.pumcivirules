<?php

/**
 * Class for PUM CiviRules Utils functions
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 30 Sep 2016
 * @license AGPL-3.0
 */
class CRM_Pumcivirules_Utils {

  /**
   * Method to check if case is main activity
   *
   * @param $caseTypeId
   * @return bool
   */
  public static function isMainActivityCase($caseTypeId) {
    $caseTypeId = str_replace(CRM_Core_DAO::VALUE_SEPARATOR, "", $caseTypeId);
    $config = CRM_Threepeas_CaseRelationConfig::singleton();
    $validCaseTypes = $config->getExpertCaseTypes();
    if (in_array($caseTypeId, $validCaseTypes)) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Method to get all possible case (and project) roles for civirules action send email to case or project role
   * @return array
   */
  public static function getAvailableCaseRoles() {
    $result = array();
    // set array with name_a_b and name_b_a of relationship type
    $roles = array(
      array('name_a_b' => 'Anamon', 'name_b_a' => 'Anamon'),
      array('name_a_b' => 'Has authorised', 'name_b_a' => 'Authorised contact for'),
      array('name_a_b' => 'Business Coordinator', 'name_b_a' => 'Business Coordinator'),
      array('name_a_b' => 'Country Coordinator is', 'name_b_a' => 'Country Coordinator for'),
      array('name_a_b' => 'Counsellor', 'name_b_a' => 'Counsellor'),
      array('name_a_b' => 'Grant Coordinator', 'name_b_a' => 'Grant Coordinator'),
      array('name_a_b' => 'Expert', 'name_b_a' => 'Expert'),
      array('name_a_b' => 'Project Officer for','name_b_a' => 'Project Officer is'),
      array('name_a_b' => 'Programme Manager', 'name_b_a' => 'Programme Manager'),
      array('name_a_b' => 'Projectmanager', 'name_b_a' => 'Projectmanager'),
      array('name_a_b' => 'Representative is', 'name_b_a' => 'Representative'),
      array('name_a_b' => 'Sector Coordinator', 'name_b_a' => 'Sector Coordinator'));
    foreach ($roles as $roleNames) {
      try {
        $relationshipType = civicrm_api3('RelationshipType', 'getsingle', array(
          'name_a_b' => $roleNames['name_a_b'],
          'name_b_a' => $roleNames['name_b_a']
        ));
        $relationshipData = array(
          'relationship_type_id' => $relationshipType['id'],
          'label_b_a' => $relationshipType['label_b_a'],
          'name_a_b' => $roleNames['name_a_b'],
          'name_b_a' => $roleNames['name_b_a'],
          'title' => $relationshipType['label_b_a']
        );
      } catch (CiviCRM_API3_Exception $ex) {
        $relationshipData = array(
          'relationship_type_id' => 0,
          'label_b_a' => '',
          'name_a_b' => $roleNames['name_a_b'],
          'name_b_a' => $roleNames['name_b_a'],
          'title' => $roleNames['name_b_a']
        );
      }
      $result[] = $relationshipData;
    }
    return $result;
  }

  /**
   * Method to get the case id of the first main activity on a project
   *
   * @param int $projectId
   * @return int|bool
   * @access public
   * @static
   */
  public static function getFirstMainActivityCaseId($projectId) {
    $projectCases = CRM_Threepeas_BAO_PumProject::getCasesByProjectId($projectId);
    foreach ($projectCases as $projectCaseId => $projectCase) {
      if (self::isMainActivityCase($projectCase['case_type'])) {
        return $projectCaseId;
      }
    }
    return FALSE;
  }
}