<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Pumcivirules_CiviRulesActions_Drupal_Form_CreateUserAccount extends CRM_CivirulesActions_Form_Form {

  /**
   * Build the form.
   */
  public function buildQuickForm() {
    $roles = user_roles(TRUE);
    unset($roles[2]); //Authenticated user. Unset this one as each user gets this role automaticly.
    $this->add('hidden', 'rule_action_id');

    $multiGroup = $this->addElement('advmultiselect', 'roles', ts('Roles'), $roles, array(
      'size' => 5,
      'style' => 'width:250px',
      'class' => 'advmultiselect',
    ));

    $multiGroup->setButtonAttributes('add', array('value' => ts('Add >>')));
    $multiGroup->setButtonAttributes('remove', array('value' => ts('<< Remove')));

    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      )));
  }


  /**
   * Set default values.
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $data = unserialize($this->ruleAction->action_params);
    $defaultValues['roles'] = $data['roles'];
    return $defaultValues;
  }


  /**
   * Process form data after submitting
   */
  public function postProcess() {
    $data['roles'] = $this->_submitValues['roles'];
    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();
    parent::postProcess();
  }

}