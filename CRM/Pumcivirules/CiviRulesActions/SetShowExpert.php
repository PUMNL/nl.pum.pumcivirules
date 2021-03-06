<?php
/**
 * Class for PUM Specific CiviRules Action to Set the Show Expert to Customer
 * field on the Case Custom Group visibility
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 6 Dec 2016
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Pumcivirules_CiviRulesActions_SetShowExpert extends CRM_Civirules_Action {

  private static $_visibilityTableName;
  private static $_showExpertColumnName;

  /**
   * Returns the custom group table name for visibility
   * @return array
   * @throws Exception when error from api
   */
  private static function getVisibilityTableName() {
    if (!self::$_visibilityTableName) {
      $name = 'visibility_of_main_activity';
      try {
        self::$_visibilityTableName = civicrm_api3('CustomGroup', 'getvalue', array(
          'name' => $name,
          'extends' => 'Case',
          'return' => 'table_name'
        ));
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not find a custom group in '.__METHOD__
          .'with the name '.$name.' which extends Case (error from API Custom Group Getvalue: '
          .$ex->getMessage().', contact your system administrator');
      }
    }
    return self::$_visibilityTableName;
  }

  /**
   * Returns the custom field column name for show expert to customer
   * @return array
   * @throws Exception when error from api
   */
  private static function getShowExpertColumnName() {
    if (!self::$_showExpertColumnName) {
      $name = 'show_proposed_expert_to_customer';
      try {
        self::$_showExpertColumnName = civicrm_api3('CustomField', 'getvalue', array(
          'name' => $name,
          'custom_group_id' => 'visibility_of_main_activity',
          'return' => 'column_name'
        ));
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not find a custom field in '.__METHOD__
          .'with the name '.$name.' (error from API Custom Field Getvalue: '
          .$ex->getMessage().', contact your system administrator');
      }
    }
    return self::$_showExpertColumnName;
  }

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $caseData = $triggerData->getEntityData('Case');
    // makes no sense to do something if no case data
    if (!empty($caseData)) {
      if (isset($caseData['id']) && !empty($caseData['id'])) {
        $caseId = $caseData['id'];
      } else {
        if (isset($caseData['case_id'])) {
          $caseId = $caseData['case_id'];
        }
      }
      // makes no sense to do something if there is no case_id
      if (isset($caseId) && !empty($caseId)) {
        $actionParams = $this->getActionParameters();
        if (isset($actionParams['show_expert'])) {
          $this->setCustomData($caseId, $actionParams['show_expert']);
        }
      }
    }
  }

  /**
   * Method to update or insert the custom data
   *
   * @param int $caseId
   * @param int $showExpert
   */
  private function setCustomData($caseId, $showExpert) {
    $table = self::getVisibilityTableName();
    $column = self::getShowExpertColumnName();
    $count = CRM_Core_DAO::singleValueQuery(
      "SELECT COUNT(*) FROM ".$table." WHERE entity_id = %1",
      array(1 => array($caseId, "Integer")));
    if ($count == 0) {
      $sql = "INSERT INTO ".$table." (entity_id, ".$column.") VALUES(%1, %2)";
    } else {
      $sql = "UPDATE ".$table." SET ".$column." = %2 WHERE entity_id = %1";
    }
    $sqlParams = array(
      1 => array($caseId, 'Integer'),
      2 => array($showExpert, 'Integer'));
    CRM_Core_DAO::executeQuery($sql, $sqlParams);
  }

  /**
   * Method to link to form
   *
   * @param int $ruleActionId
   * @return string
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/setshowexpert', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $actionParams = $this->getActionParameters();
    if ($actionParams['show_expert'] == 1) {
      $text = ' switch ON';
    } else {
      $text = ' switch OFF';
    }
    return $text;
  }
}