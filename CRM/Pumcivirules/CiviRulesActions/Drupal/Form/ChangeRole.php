<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Pumcivirules_CiviRulesActions_Drupal_Form_ChangeRole extends CRM_CivirulesActions_Form_Form {

  /**
   * Build the form.
   */
  public function buildQuickForm() {
    $roles = user_roles(TRUE);
    unset($roles[2]); //Authenticated user. Unset this one as each user gets this role automaticly.
    $this->add('hidden', 'rule_action_id');

    $rolesToRemove = $this->addElement('advmultiselect', 'roles_to_remove', ts('Roles to remove'), $roles, array(
      'size' => 5,
      'style' => 'width:250px',
      'class' => 'advmultiselect',
    ));

    $rolesToRemove->setButtonAttributes('add', array('value' => ts('Add >>')));
    $rolesToRemove->setButtonAttributes('remove', array('value' => ts('<< Remove')));

    $rolesToAdd = $this->addElement('advmultiselect', 'roles_to_add', ts('Roles to add'), $roles, array(
      'size' => 5,
      'style' => 'width:250px',
      'class' => 'advmultiselect',
    ));

    $rolesToAdd->setButtonAttributes('add', array('value' => ts('Add >>')));
    $rolesToAdd->setButtonAttributes('remove', array('value' => ts('<< Remove')));

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
    $defaultValues['roles_to_remove'] = $data['roles_to_remove'];
    $defaultValues['roles_to_add'] = $data['roles_to_add'];
    return $defaultValues;
  }


  /**
   * Process form data after submitting
   */
  public function postProcess() {
    $data['roles_to_remove'] = $this->_submitValues['roles_to_remove'];
    $data['roles_to_add'] = $this->_submitValues['roles_to_add'];
    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();
    parent::postProcess();
  }

}