<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Pumcivirules_CiviRulesActions_Drupal_BlockUserAccount extends CRM_Civirules_Action {

  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $drupal_uid = CRM_Pumcivirules_CiviRulesActions_Drupal_User::getDrupalUid($triggerData->getContactId());
    if ($drupal_uid) {
      CRM_Pumcivirules_CiviRulesActions_Drupal_User::blockUserAccount($drupal_uid);
    }
  }

  public function getExtraDataInputUrl($ruleActionId) {
    return '';
  }
}