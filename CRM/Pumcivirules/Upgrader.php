<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Pumcivirules_Upgrader extends CRM_Pumcivirules_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   */
  public function install() {
    $sql = 'SELECT COUNT(*) AS pumCaseProjectTrigger FROM civirule_trigger WHERE name = %1';
    $count = CRM_Core_DAO::singleValueQuery($sql,array(
      1 => array('new_pumcaseproject', 'String')));
    if ($count == 0) {
      $this->executeSqlFile('sql/insertPumCaseProjectTrigger.sql');
    }
  }
  // todo disable all rules that use FirstMainActivity or PumCaseProject
}
