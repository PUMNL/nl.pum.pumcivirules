<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_Pumcivirules_CiviRulesConditions_Case_CheckboxIsChecked extends CRM_Civirules_Condition {

  private static $customFields = array();

  private $conditionParams = array();

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   * @access public
   */
  public function setRuleConditionData($ruleCondition) {
    parent::setRuleConditionData($ruleCondition);
    $this->conditionParams = array();
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->conditionParams = unserialize($this->ruleCondition['condition_params']);
    }
  }

  protected static function getCustomField($custom_field_id) {
    if (!isset(self::$customFields[$custom_field_id])) {

      self::$customFields[$custom_field_id] = civicrm_api3('CustomField', 'getsingle', array('id' => $custom_field_id, 'name' => 'accepted'));
    }
    return self::$customFields[$custom_field_id];
  }

  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/condition/case/checkboxischecked', 'rule_condition_id='.$ruleConditionId);
  }

  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    if ($triggerData instanceof CRM_Civirules_TriggerData_Edit) {
      $case = $triggerData->getEntityData('Case');
      $originalData = $triggerData->getOriginalData();
      $custom_field_id = $this->conditionParams['custom_field_id'];
      $custom_field_key = 'custom_' . $custom_field_id;
      $custom_field_value = $this->conditionParams['custom_field_value'];
      $custom_field_value_string = CRM_Core_DAO::VALUE_SEPARATOR . $custom_field_value . CRM_Core_DAO::VALUE_SEPARATOR;
      $original_value = null;

      // Check whether the value is present in the original data.
      $does_original_value_contains_the_value = false;
      if (isset($originalData[$custom_field_key])) {
        $original_value = $originalData[$custom_field_key];
      }
      if ($original_value && is_array($original_value) && isset($original_value[$custom_field_value])) {
        $does_original_value_contains_the_value = true;
      } elseif ($original_value && (is_string($original_value) && stristr($original_value, $custom_field_value_string))) {
        $does_original_value_contains_the_value = true;
      }

      $new_value = null;
      $does_new_value_contains_the_value = false;
      if (isset($case[$custom_field_key])) {
        $new_value = $case[$custom_field_key];
      }
      if ($new_value && is_array($new_value) && isset($new_value[$custom_field_value])) {
        $does_new_value_contains_the_value = true;
      } elseif ($new_value && (is_string($new_value) && stristr($new_value, $custom_field_value_string))) {
        $does_new_value_contains_the_value = true;
      }

      if (!$does_original_value_contains_the_value && $does_new_value_contains_the_value) {
        return TRUE;
      }
    }
    return false;
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $custom_field_id = $this->conditionParams['custom_field_id'];
    $custom_field_value = $this->conditionParams['custom_field_value'];
    $custom_field = civicrm_api3('CustomField', 'getsingle', array('id' => $custom_field_id));
    $custom_group = civicrm_api3('CustomGroup', 'getsingle', array('id' => $custom_field['custom_group_id']));
    $option_value = civicrm_api3('OptionValue', 'getsingle', array('option_group_id' => $custom_field['option_group_id'], 'value' => $custom_field_value));

    $field = $custom_group['title'].': '.$custom_field['label'];
    $value = $option_value['value'];
    return ts('Checkbox %1 from field %2', array(1=>$value, 2=>$field));
  }


}