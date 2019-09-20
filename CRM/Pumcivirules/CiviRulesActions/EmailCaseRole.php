<?php
/**
 * Class for CiviRules Email Case (or Project) role PUM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 18 Oct 2016
 * @license AGPL-3.0
 */
class CRM_Pumcivirules_CiviRulesActions_EmailCaseRole extends CRM_Civirules_Action {

  private $_caseData = array();
  private $_availableCaseRoles = array();
  private $_selectedCaseRoles = array();
  private $_caseClientId = array();

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $this->_caseData = $triggerData->getEntityData('Case');
    if (!isset($this->_caseData['client_id'][1])) {
      $this->_caseData['client_id'] = civicrm_api3('Case', 'getvalue', array(
        'id' => $this->_caseData['case_id'],
        'return' => 'client_id'
      ));
    }
    $this->_caseClientId = (int) $this->_caseData['client_id'][1];
    $this->_availableCaseRoles = CRM_Pumcivirules_Utils::getAvailableCaseRoles();
    // processing only makes sense if we have case in the triggerData

    if (!empty($this->_caseData)) {

      $actionParams = $this->getActionParameters();
      $this->_selectedCaseRoles = array();

      // determine who to send email to
      $contactIdsToMail = array();
      $contactIdsToMail = $this->retrieveContactsFromCaseContacts() + $this->retrieveContactsFromOthers();

      foreach ($actionParams['case_role'] as $selectedCaseRoleId) {
        $params = array(
          'version' => 3,
          'sequential' => 1,
          'case_id' => $this->_caseData['id'],
          'relationship_type_id' => $this->_availableCaseRoles[$actionParams['case_role'][0]]['relationship_type_id'],
        );
        $result = civicrm_api('Relationship', 'get', $params);

        foreach($result['values'] as $key => $value){
          if(!empty($value['contact_id_b']) && !in_array($value['contact_id_b'],$contactIdsToMail)){
            $contactIdsToMail[] = $value['contact_id_b'];
          }
        }
      }

      //Prevent duplicate contacts
      $contactIdsToMail = array_unique($contactIdsToMail);

      foreach ($contactIdsToMail as $contactIdToMail) {
        $emailParams = array(
          'contact_id' => $contactIdToMail,
          'template_id' => $actionParams['mail_template'],
          'from_email' => $actionParams['from_email'],
          'from_name' => $actionParams['from_name'],
          'case_id' => $this->_caseData['id']
        );
        try {
          civicrm_api3('Email', 'send', $emailParams);
        } catch (CiviCRM_API3_Exception $ex) {}
      }
    }
  }

  /**
   * Method to retrieve contacts to mail from case contacts
   *
   * @return array
   */
  private function retrieveContactsFromCaseContacts() {
    $result = array();
    foreach ($this->_selectedCaseRoles as $selectedKey => $selectedData) {
      // loop through all case contacts and check if the role equals the selected
      if (isset($this->_caseData['contacts'])) {
        foreach ($this->_caseData['contacts'] as $caseContact) {
          if ($caseContact['role'] == $selectedData['title']) {
            if (isset($caseContact['email']) && !empty($caseContact['email'])) {
              $result[] = $caseContact['contact_id'];
              $this->_selectedCaseRoles[$selectedKey]['found'] = TRUE;
            }
          }
        }
      }
    }
    return $result;
  }

  /**
   * Method to find programma manager using the project of the case
   *
   * @return int
   */
  private function getProgrammeManagerId() {
    $projectId = CRM_Threepeas_BAO_PumCaseProject::getProjectIdWithCaseId($this->_caseData['id']);
    if ($projectId) {
      $pumProject = CRM_Threepeas_BAO_PumProject::getSingleProjectById($projectId);
      $programmeManagerId = (int) CRM_Threepeas_BAO_PumProgramme::getProgrammeManagerIdWithId($pumProject['programme_id']);
      return $programmeManagerId;
    } else {
      return 0;
    }
  }

  /**
   * Method to get project manager from project
   *
   * @return int
   */
  private function getProjectManagerId() {
    $projectId = CRM_Threepeas_BAO_PumCaseProject::getProjectIdWithCaseId($this->_caseData['id']);
    if ($projectId) {
      $pumProject = CRM_Threepeas_BAO_PumProject::getSingleProjectById($projectId);
      if (isset($pumProject['projectmanager_id']) && !empty($pumProject['projectmanager_id'])) {
        return $pumProject['projectmanager_id'];
      }
    }
    return 0;
  }

  /**
   * Method to find the contacts that have to be mailed
   *
   * @return array
   */
  private function retrieveContactsFromOthers() {
    $result = array();
    foreach ($this->_selectedCaseRoles as $selectedKey => $selectedData) {
      if (!$selectedData['found']) {
        switch ($selectedData['name_a_b']) {
          // anamon
          case 'Anamon':
            $foundId = CRM_Threepeas_BAO_PumCaseRelation::getAnamonId($this->_caseClientId);
            break;
          case 'Country Coordinator is':
            $foundId = CRM_Threepeas_BAO_PumCaseRelation::getCountryCoordinatorId($this->_caseClientId);
            break;
          case 'Expert':
            $foundId = CRM_Threepeas_BAO_PumCaseRelation::getCaseExpert($this->_caseData['case_id']);
            break;
          case 'Grant Coordinator':
            $foundId = CRM_Threepeas_BAO_PumCaseRelation::getGrantCoordinatorId($this->_caseClientId);
            break;
          case 'Project Officer for':
            $foundId = CRM_Threepeas_BAO_PumCaseRelation::getProjectOfficerId($this->_caseClientId);
            break;
          case 'Programme Manager':
            $foundId = $this->getProgrammeManagerId();
            break;
          case "Projectmanager":
            $foundId = $this->getProjectManagerId();
            break;
          case 'Representative is':
            $foundId = CRM_Threepeas_BAO_PumCaseRelation::getRepresentativeId($this->_caseClientId);
            break;
          case 'Sector Coordinator':
            $foundId = CRM_Threepeas_BAO_PumCaseRelation::getSectorCoordinatorId($this->_caseClientId);
            break;
        }
        if (!empty($foundId)) {
          $result[] = $foundId;
          $this->_selectedCaseRoles[$selectedKey]['found'] = TRUE;
        }
      }
    }
    return $result;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleActionId
   * @return bool|string
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirules/pum/emailcaserole', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $this->_availableCaseRoles = CRM_Pumcivirules_Utils::getAvailableCaseRoles();
    $return = "";
    $textParts = array();
    $params = $this->getActionParameters();
    if (isset($params['from_name']) && !empty($params['from_name'])) {
      $textParts[] = 'from name: '.$params['from_name'];
    }
    if (isset($params['from_email']) && !empty($params['from_email'])) {
      $textParts[] = 'from email address: '.$params['from_email'];
    }
    if (isset($params['case_role']) && !empty($params['case_role'])) {
      $caseRolesText = array();
      foreach ($params['case_role'] as $caseRoleId) {
        $caseRolesText[] = $this->_availableCaseRoles[$caseRoleId]['title'];
      }
      $textParts[] = 'to case roles: '.implode(', ', $caseRolesText);
    }
    if (isset($params['mail_template']) && !empty($params['mail_template'])) {
      $textParts[] = 'with template: '.$this->getTemplateName($params['mail_template']);
    }
    if (!empty($textParts)) {
      $return = 'Send Email to Case Role with ' . implode(', ', $textParts);
    }
    return $return;
  }

  /**
   * Method to get template title with Id
   *
   * @param $templateId
   * @return null|string
   */
  private function getTemplateName($templateId) {
    $templateTitle = NULL;
    $version = CRM_Core_BAO_Domain::version();
    if($version >= 4.4) {
      $messageTemplates = new CRM_Core_DAO_MessageTemplate();
    } else {
      $messageTemplates = new CRM_Core_DAO_MessageTemplates();
    }
    $messageTemplates->id = $templateId;
    $messageTemplates->is_active = true;
    if ($messageTemplates->find(TRUE)) {
      $templateTitle = $messageTemplates->msg_title;
    }
    return $templateTitle;
  }
}