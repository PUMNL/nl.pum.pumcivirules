<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pumcivirules_CiviRulesActions_Form_SetShowProject extends CRM_Core_Form {

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
   * Overridden parent method to build the form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_action_id');
    $this->add('select', 'show_project', ts('Show Project to Expert'), array('No', 'Yes'), true);

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
    $defaultValues = parent::setDefaultValues();
    $defaultValues['rule_action_id'] = $this->_ruleActionId;
    if (!empty($this->_ruleAction->action_params)) {
      $data = unserialize($this->_ruleAction->action_params);
    }
    if (isset($data['show_project'])) {
      $defaultValues['show_project'] = $data['show_project'];
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submitting
   *
   * @access public
   */
  public function postProcess() {
    $data['show_project'] = $this->_submitValues['show_project'];
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
}
