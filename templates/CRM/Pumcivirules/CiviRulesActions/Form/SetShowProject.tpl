<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-pumcivirule-rule_action-block-set_show_project">
  <div class="crm-section from_name-section">
    <div class="label">
      <label for="show_project">{$form.show_project.label}</label>
    </div>
    <div class="content" id="show_project_block">
      {$form.show_project.html}
    </div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>