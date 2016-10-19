<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-custom_field">
    <div class="crm-section">
        <div class="label">{$form.custom_field_id.label}</div>
        <div class="content">{$form.custom_field_id.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.custom_field_value.label}</div>
        <div class="content">{$form.custom_field_value.html}</div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

<script type="text/javascript">
{literal}
var optionsPerField = {/literal}{$options}{literal};

cj(function() {
    cj("select#custom_field_id").on('change', function() {
        var custom_field_id = cj("select#custom_field_id").val();
        cj('select#custom_field_value option').remove();
        for(var i=0; i < optionsPerField[custom_field_id].length; i++) {
            cj('select#custom_field_value').append('<option value="'+optionsPerField[custom_field_id][i].id+'">'+optionsPerField[custom_field_id][i].label+'</option>');
        }
    });

    cj("select#custom_field_id").trigger('change');
});

{/literal}
</script>