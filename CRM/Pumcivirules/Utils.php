<?php

/**
 * Class for PUM CiviRules Utils functions
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 30 Sep 2016
 * @license AGPL-3.0
 */
class CRM_Pumcivirules_Utils {
  public static function isMainActivityCase($caseTypeId)
  {
    $config = CRM_Threepeas_CaseRelationConfig::singleton();
    $validCaseTypes = $config->getExpertCaseTypes();
    if (in_array($caseTypeId, $validCaseTypes)) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
}