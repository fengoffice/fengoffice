<?php
$timeslot = $object;
if (!Plugins::instance()->isActivePlugin('income')) {
	return;
}

// Check if we're in fallback rendering context
$is_fallback_rendering = isset($is_hook_rendering) && $is_hook_rendering === false;

// Get billing information
$sel_status = $timeslot->getColumnValue('invoicing_status');

if (!isset($object_id)) {
	$object_id = $timeslot instanceof Timeslot ? $timeslot->getRelObjectId() : 0;
}
if (!isset($pre_selected_member_ids)) {
	$pre_selected_member_ids = $timeslot instanceof Timeslot ? $timeslot->getMemberIds() : array();
}

Hook::fire('get_initial_invoicing_status_for_timeslot', array('timeslot' => $timeslot, 'task_id' => $object_id, 'selected_member_ids' => $pre_selected_member_ids), $sel_status);

if ($sel_status == '') $sel_status = 'pending';

// Check if the related task or any parent in hierarchy is a fixed fee task
$is_task_fixed_fee = false;
$task = $timeslot->getRelObject();
if ($task instanceof ProjectTask && Plugins::instance()->isActivePlugin('advanced_billing')) {
	Env::useHelper('additional_tasks_columns_functions', 'advanced_billing');

	// Check the task itself first
	$is_task_fixed_fee = $task->getColumnValue('is_fixed_fee');

	// If task itself is not fixed fee, check if any parent is fixed fee using existing helper
	if (!$is_task_fixed_fee) {
		$is_task_fixed_fee = has_parent_with_fixed_fee($task);
	}
}

// Get the current value of is_fixed_fee and is_billable_work
$timeslot_is_fixed_fee = $is_task_fixed_fee ? 1 : 0;
$timeslot_is_billable_work = $timeslot->getColumnValue('is_billable_work');
if ($timeslot_is_billable_work === null) {
	$timeslot_is_billable_work = 1; // default to billable work
}

// Common input elements (avoid duplication)
ob_start();
?>

<div id="<?php echo $genid?>billing_status_container">
	<input type="hidden" name="timeslot[invoice_id]" value="<?php echo $timeslot->getColumnValue('invoice_id')?>" />
	<input type="hidden" name="timeslot[invoicing_status]" value="<?php echo $sel_status ?>" id="<?php echo $genid?>invoicing_status"/>
	<input type="hidden" name="timeslot[is_fixed_fee]" value="<?php echo $timeslot_is_fixed_fee ? '1' : '0' ?>" id="<?php echo $genid?>is_fixed_fee"/>

	<?php if ($sel_status != 'invoiced') { ?>

	<div id="<?php echo $genid?>is_billable_container" style="<?php echo $is_task_fixed_fee ? 'display:none;' : '' ?>">
		<div class="billing-inputs">
			<?php echo yes_no_widget('timeslot[billable]', $genid."is_billable", $sel_status=='pending', lang('yes'), lang('no'), null, array(
					'onchange' => "og.income.on_timeslot_is_billable_change(this.value, '$genid');",
			)); ?>
		</div>
	</div>

	<div id="<?php echo $genid?>is_billable_work_container" style="<?php echo $is_task_fixed_fee ? '' : 'display:none;' ?>">
		<div class="billing-inputs">
			<?php echo yes_no_widget('timeslot[is_billable_work]', $genid."is_billable_work", $timeslot_is_billable_work, lang('yes'), lang('no'), null, array(
					'onchange' => "og.income.on_timeslot_is_billable_work_change(this.value, '$genid');",
			)); ?>
			<span class="invoicing-status_fixed-fee-work" style="margin-left: 10px;"><?php echo lang('fixed fee work') ?></span>
		</div>
	</div>

	<?php } ?>
</div>

<script>
og.income.on_timeslot_is_billable_change = function(radio_val, genid) {
	radio_val = parseInt(radio_val);
	if (radio_val > 0) {
		var inv_status = 'pending';
	} else {
		var inv_status = 'non_billable';
	}
	$("#"+genid+"invoicing_status").val(inv_status);

	if (og.advanced_billing) {
		og.advanced_billing.applyCurrentBillingCategoryAmounts('both');
	}
}

og.income.on_timeslot_is_billable_work_change = function(radio_val, genid) {
	// This function handles the is_billable_work field change
	// No special logic needed for now, just track the value
}

og.income.toggle_billable_fields = function(is_task_fixed_fee, genid) {
	if (is_task_fixed_fee) {
		$("#"+genid+"is_billable_container").hide();
		$("#"+genid+"is_billable_work_container").show();
		$("#"+genid+"is_fixed_fee").val('1');
		// Set is_billable to non-billable when task is fixed fee
		$("#"+genid+"is_billableNo").click();
	} else {
		$("#"+genid+"is_billable_container").show();
		$("#"+genid+"is_billable_work_container").hide();
		$("#"+genid+"is_fixed_fee").val('0');
		// Set is_billable using current is_billable_work value
		if ($("#"+genid+"is_billable_workYes").is(':checked')) {
			$("#"+genid+"is_billableYes").click();
		} else {
			$("#"+genid+"is_billableNo").click();
		}
	}
}

og.disableTimeslotsHourlyFixed = function(){
	$('#addTimeslotHourlyBilling').prop('disabled', true);
	$('#addTimeslotFixedBilling').prop('disabled', true);
}

$(function() {
	og.income.ts_original_status = '<?php echo $sel_status?>';

	og.income.on_timeslot_is_billable_change(<?php echo $sel_status=='pending' ? '1' : '0'; ?>, '<?php echo $genid?>');
});
</script>

<?php
$input_elements = ob_get_clean();

if ($is_fallback_rendering) {
    // Fallback rendering: render with label and input wrappers for use with form-group structure
?>
    <label><?php echo lang('billing status') ?></label>
    <?php echo $input_elements; ?>
<?php
} else {
    // Hook-based rendering: only render the input content (label is already provided by hook)
    echo $input_elements;
}
?>