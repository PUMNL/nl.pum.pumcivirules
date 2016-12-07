<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pumcivirules_CiviRulesActions_Form_EmailCaseRole extends CRM_Core_Form {

  protected $_ruleActionId = false;
  protected $_ruleAction;
  protected $_action;

  /**
   * Overridden parent method to do pre-form building processing
   *
   * @throws Exception when action or rule action not found
   * @access public
   */
  public function preProcess() {
    $this->_ruleActionId = CRM_Utils_Request::retrieve('rule_action_id', 'Integer');
    $this->_ruleAction = new CRM_Civirules_BAO_RuleAction();
    $this->_action = new CRM_Civirules_BAO_Action();
    $this->_ruleAction->id = $this->_ruleActionId;
    if ($this->_ruleAction->find(true)) {
      $this->_action->id = $this->_ruleAction->action_id;
      if (!$this->_action->find(true)) {
        throw new Exception('CiviRules Could not find action with id '.$this->_ruleAction->action_id);
      }
    } else {
      throw new Exception('CiviRules Could not find rule action with id '.$this->_ruleActionId);
    }

    parent::preProcess();
  }

  /**
   * Method to get templates
   *
   * @return array
   * @access protected
   */
  protected function getMessageTemplates() {
    $return = array('' => ts('-- please select --'));
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_msg_template` WHERE `is_active` = 1 AND `workflow_id` IS NULL");
    while($dao->fetch()) {
      $return[$dao->id] = $dao->msg_title;
    }
    asort($return);
    return $return;
  }
  /**
   * Method to get case roles
   */
  private function getCaseRoles() {
    $caseRoles = array();
    $availableRoles = CRM_Pumcivirules_Utils::getAvailableCaseRoles();
    foreach ($availableRoles as $roleKey => $roleValue) {
      $caseRoles[$roleKey] = $roleValue['title'];
    }
    asort($caseRoles);
    return $caseRoles;
  }

  /**
   * Overridden parent method to build the form
   */
  function buildQuickForm() {
    $this->setFormTitle();
    $this->add('hidden', 'rule_action_id');
    $this->add('text', 'from_name', ts('From name for Email'), true);
    $this->add('text', 'from_email', ts('From Emailadress'), true);
    $this->add('select', 'mail_template', ts('Message template'), $this->getMessageTemplates(), true);
    $this->add('select', 'case_role', ts('Case (or Project) Role(s) to Email'), $this->getCaseRoles(), true,
      array('id' => 'case_role', 'multiple' => 'multiple','class' => 'crm-select2'));

    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $contactOne = civicrm_api3('Contact', 'getsingle', array('id' => 1));
    $data = array();
    $defaultValues = array();
    $defaultValues['rule_action_id'] = $this->_ruleActionId;
    if (!empty($this->_ruleAction->action_params)) {
      $data = unserialize($this->_ruleAction->action_params);
    }
    if (!empty($data['from_name'])) {
      $defaultValues['from_name'] = $data['from_name'];
    } else {
      if (isset($contactOne['display_name'])) {
        $defaultValues['from_name'] = $contactOne['display_name'];
      }
    }
    if (!empty($data['from_email'])) {
      $defaultValues['from_email'] = $data['from_email'];
    } else {
      if (isset($contactOne['email'])) {
        $defaultValues['from_email'] = $contactOne['email'];
      }
    }
    if (!empty($data['mail_template'])) {
      $defaultValues['mail_template'] = $data['mail_template'];
    }
    if (!empty($data['case_role'])) {
      $defaultValues['case_role'] = $data['case_role'];
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submitting
   *
   * @access public
   */
  public function postProcess() {
    $data['mail_template'] = $this->_submitValues['mail_template'];
    $data['case_role'] = $this->_submitValues['case_role'];
    $data['from_name'] = $this->_submitValues['from_name'];
    $data['from_email'] = $this->_submitValues['from_email'];
    $ruleAction = new CRM_Civirules_BAO_RuleAction();
    $ruleAction->id = $this->_ruleActionId;
    $ruleAction->action_params = serialize($data);
    $ruleAction->save();

    $session = CRM_Core_Session::singleton();
    $session->setStatus('Action '.$this->_action->label.' parameters updated to CiviRule '
      .CRM_Civirules_BAO_Rule::getRuleLabelWithId($this->_ruleAction->rule_id),
      'Action parameters updated', 'success');

    $redirectUrl = CRM_Utils_System::url('civicrm/civirule/form/rule', 'action=update&id='.$this->_ruleAction->rule_id, TRUE);
    CRM_Utils_System::redirect($redirectUrl);
  }

  /**
   * Method to set the form title
   *
   * @access protected
   */
  protected function setFormTitle() {
    $title = 'PUM CiviRules Edit Send Email to Case Role Action parameters';
    $this->assign('ruleActionHeader', 'Edit action '.$this->_action->label.' of CiviRule '
      .CRM_Civirules_BAO_Rule::getRuleLabelWithId($this->_ruleAction->rule_id));
    CRM_Utils_System::setTitle($title);
  }
}
