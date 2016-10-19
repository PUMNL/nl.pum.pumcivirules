<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-pumcivirule-rule_action-block-mail_case_role">
  <div class="crm-section from_name-section">
    <div class="label">
      <label for="from_name">{$form.from_name.label}</label>
    </div>
    <div class="content" id="from_name_block">
      {$form.from_name.html}
    </div>
    <div class="clear"></div>
  </div>
  <div class="crm-section from_name-section">
    <div class="label">
      <label for="from_email">{$form.from_email.label}</label>
    </div>
    <div class="content" id="from_email-block">
      {$form.from_email.html}
    </div>
    <div class="clear"></div>
  </div>
  <div class="crm-section mail_case_role-section">
    <div class="label">
      <label for="case_role-select">{$form.case_role.label}</label>
    </div>
    <div class="content crm-select-container" id="case_role_block">
      {$form.case_role.html}
    </div>
    <div class="clear"></div>
  </div>
  <div class="crm-section mail_template-section">
    <div class="label">
      <label for="mail_template-select">{$form.mail_template.label}</label>
    </div>
    <div class="content crm-select-container" id="mail-template_role_block">
      {$form.mail_template.html}
    </div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>