<?php

return array (
  0 =>
    array (
      'name' => 'PUM:PumciviRules:CiviRulesAction.CreateUserAccount',
      'entity' => 'CiviRuleAction',
      'params' =>
        array (
          'version' => 3,
          'name' => 'pum_drupal_create_user_account',
          'label' => 'Create Drupal User Account',
          'class_name' => 'CRM_Pumcivirules_CiviRulesActions_Drupal_CreateUserAccount',
          'is_active' => 1
        ),
    ),
  1 =>
    array (
      'name' => 'PUM:PumciviRules:CiviRulesAction.ChangeRole',
      'entity' => 'CiviRuleAction',
      'params' =>
        array (
          'version' => 3,
          'name' => 'pum_drupal_change_role',
          'label' => 'Add/Remove Drupal Role',
          'class_name' => 'CRM_Pumcivirules_CiviRulesActions_Drupal_ChangeRole',
          'is_active' => 1
        ),
    ),
  2 =>
    array (
      'name' => 'PUM:PumciviRules:CiviRulesAction.BlockUserAccount',
      'entity' => 'CiviRuleAction',
      'params' =>
        array (
          'version' => 3,
          'name' => 'pum_drupal_block_user',
          'label' => 'Block user account',
          'class_name' => 'CRM_Pumcivirules_CiviRulesActions_Drupal_BlockUserAccount',
          'is_active' => 1
        ),
    ),
);