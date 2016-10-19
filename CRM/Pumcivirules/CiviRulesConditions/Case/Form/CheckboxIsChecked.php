<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Pumcivirules_CiviRulesConditions_Case_Form_CheckboxIsChecked extends CRM_CivirulesConditions_Form_Form {

  /**
   * Build the form.
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $custom_groups = civicrm_api3('CustomGroup', 'get', array('extends' => 'Case'));
    $fields = array();
    $optionsPerField = array();
    $allOptions = array();
    foreach($custom_groups['values'] as $custom_group) {
      $custom_fields = civicrm_api3('CustomField', 'get', array(
        'custom_group_id' => $custom_group['id'],
        'data_type' => 'String',
        'html_type' => 'CheckBox'
      ));
      foreach($custom_fields['values'] as $custom_field) {
        if (empty($custom_field['option_group_id'])) {
          continue;
        }
        $option_values = civicrm_api3('OptionValue', 'get', array(
          'option_group_id' => $custom_field['option_group_id'],
        ));
        if (count($option_values['values'])) {
          foreach($option_values['values'] as $option_value) {
            $optionsPerField[$custom_field['id']][] = $option_value;
            $allOptions[$option_value['id']] = $option_value['label'];
          }
          $fields[$custom_field['id']] = $custom_group['title'] . ': '.$custom_field['label'];
        }
      }
    }

    $this->add('select','custom_field_id', ts('Field'), $fields, true);
    $this->add('select','custom_field_value', ts('Checkbox'), $allOptions, true);
    $this->assign('options', json_encode($optionsPerField));

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
    $data = unserialize($this->ruleCondition->condition_params);
    if (!empty($data['custom_field_id'])) {
      $defaultValues['custom_field_id'] = $data['custom_field_id'];
    }
    if (!empty($data['custom_field_value_id'])) {
      $defaultValues['custom_field_value'] = $data['custom_field_value_id'];
    }
    return $defaultValues;
  }


  /**
   * Process form data after submitting
   */
  public function postProcess() {
    $value = civicrm_api3('OptionValue', 'getvalue', array(
      'id' => $this->_submitValues['custom_field_value'],
      'return' => 'value',
    ));
    $data['custom_field_id'] = $this->_submitValues['custom_field_id'];
    $data['custom_field_value'] = $value;
    $data['custom_field_value_id'] = $this->_submitValues['custom_field_value'];
    //var_dump($data); exit();
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }

}