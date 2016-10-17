INSERT INTO civirule_trigger (name, label, object_name, op, class_name, created_date, created_user_id)
VALUES('new_pumcaseproject', 'PUM Case is linked to Project', 'PumCaseProject', 'create',
       'CRM_Pumcivirules_CiviRulesPostTrigger_PumCaseProject',  CURDATE(), 1);
