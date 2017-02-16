<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Pumcivirules_CiviRulesActions_SetRctRoleOnCase extends CRM_Civirules_Action {

  public function getExtraDataInputUrl($ruleActionId) {
    return '';
  }

  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $case = $triggerData->getEntityData('Case');
    if (!$case) {
      return;
    }
    foreach($case['client_id'] as $client_id) {
      $rctMembers = $this->getRctMemberForContact($client_id);
      if (count($rctMembers) >= 1) {
        $rctMemberContactId = reset($rctMembers);
        $rctRelationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array('return' => 'id', 'name_a_b' => 'Recruitment Team Member'));
        try {
          // Check whether there is already an active RCT role on the case. If so do not change
          // anything.
          $currentRctRelationShipId = civicrm_api3('Relationship', 'getvalue', array(
            'case_id' => $case['id'],
            'relationship_type_id' => $rctRelationshipTypeId,
            'contact_id_a' => $client_id,
            'is_active' => '1',
            'return' => 'id',
          ));
        } catch (Exception $e) {
          // Do Nothing
          $currentRctRelationShipId = false;
        }

        if (empty($currentRctRelationShipId)) {
          $today = new DateTime();
          civicrm_api3('Relationship', 'create', array(
            'case_id' => $case['id'],
            'relationship_type_id' => $rctRelationshipTypeId,
            'contact_id_a' => $client_id,
            'contact_id_b' => $rctMemberContactId,
            'start_date' => $today->format('Ymd'),
            'is_active' => '1',
          ));
        }
        return;
      }
    }
  }

  protected function getRctMemberForContact($contact_id) {
    $rctMembers = array();
    $params = array(
      'contact_id' => $contact_id,
      'is_active' => 1,
      'role_value' => 'Expert',
      'is_main' => 1,
    );
    $contact_segments = civicrm_api3('ContactSegment', 'Get', $params);
    foreach ($contact_segments['values'] as $contact_segment_id => $contact_segment) {
      $segment = civicrm_api3('Segment', 'Getsingle', array('id' => $contact_segment['segment_id']));
      if (empty($segment['parent_id'])) {
        try {
          $rctMembers[] = civicrm_api3('ContactSegment', 'Getvalue', array(
            'is_active' => 1,
            'role_value' => 'Recruitment Team Member',
            'segment_id' => $segment['id'],
            'return' => 'contact_id'));
        } catch (CiviCRM_API3_Exception $ex) {}
      }
    }
    return $rctMembers;
  }

  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    $entities = $trigger->getProvidedEntities();
    if (isset($entities['Case'])) {
      return TRUE;
    }
    return FALSE;
  }


}