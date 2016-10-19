<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-create-user-account">
    <div class="crm-section roles_to_remove-multiple">
        <div class="label">{$form.roles_to_remove.label}</div>
        <div class="content">{$form.roles_to_remove.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section roles_to_add-multiple">
        <div class="label">{$form.roles_to_add.label}</div>
        <div class="content">{$form.roles_to_add.html}</div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>