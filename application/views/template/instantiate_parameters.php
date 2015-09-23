<?php
$genid = gen_id();
// on submit functions
if (array_var($_REQUEST, 'modal')) {
	$on_submit = "og.submit_modal_form('".$genid."template_params', og.reload_active_tab); return false;";
} else {
	$on_submit = "return true;";
}
?>
<form onsubmit="<?php echo $on_submit?>" id="<?php echo $genid?>template_params" class="internalForm" action="<?php echo get_url('template', 'instantiate_parameters', array('id' => $id, 'back' => '1')) ?>" method="post">

<div class="template">
<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
		<div class="coInputTitle"><?php echo lang('template parameters')?></div>
		<div class="desc"><?php echo lang('template parameters description')?></div>
	</div>
	<div class="clear"></div>
</div>
<div class="coInputMainBlock">
	
	<div>
		<table style="width:100%;"><tbody>
		<?php foreach($parameters as $parameter) {
				$default_value = array_var($parameter, 'default_value');
				$dont_render_this_param = false;
				Hook::fire('before_instantiating_template_param_def_value', array('param' => $parameter), $dont_render_this_param);
				if ($dont_render_this_param) {
					continue;
				} else {
					$default_value = str_replace(array('{{email_body}}', '{{email_subject}}'), '', $default_value);
				}
		?>
			<tr style='height:30px;'>
				<td style="padding:3px 10px 0 10px;"><b><?php echo $parameter['name']; ?></b></td>
				<td align="left">
					<?php if($parameter['type'] == 'string'){ ?>
						<input id="parameterValues[<?php echo $parameter['name'] ?>]" name="parameterValues[<?php echo $parameter['name'] ?>]" class="title" value="<?php echo $default_value?>"/>
					<?php }else if($parameter['type'] == 'date'){ ?>
						<?php echo pick_date_widget2('parameterValues['.$parameter['name'].']')?>
					<?php }else{ ?>
						<select name="<?php echo 'parameterValues['.$parameter['name'].']'; ?>">
						<?php
							$context = active_context();
							if (isset($member_id) && $member_id > 0) {
								// filter by context passed by parameter
								$additional_member = Members::findById($member_id);
								if ($additional_member instanceof Member) {
									$context = array($additional_member);
								}
							}
							if (array_var($_REQUEST, 'from_email')) {
								$from_email = MailContents::findById(array_var($_REQUEST, 'from_email'));
								if ($from_email instanceof MailContent) {
									$context = $from_email->getMembers();
								}
							}
							$companies  = allowed_users_to_assign($context);
							foreach ($companies as $c) {
								if (config_option('can_assign_tasks_to_companies')) { ?>
								<option value="<?php echo $c['id']; ?>"> <?php echo $c['name']; ?></option>
								
							<?php }
								$users = $c['users'];
								if ( count($users) ) {
									foreach ($users as $usr) {?>																
										<option value="<?php echo $usr['id'] ?>"> <?php echo $usr['name'] ?></option>
										
								<?php }
								}
							}	
							 
						?>
						</select>
					<?php } ?>
				</td>
			</tr>
		<?php }//foreach ?>
		</tbody></table>
		<?php if (isset($member_id) && $member_id > 0) {
			?><input type="hidden" name="additional_member_ids" value="<?php echo $member_id?>"><?php 
		} ?>
	</div>
	<br/>
	<div>
	<?php echo submit_button(lang('instantiate'),'s',	array('style'=>'margin-top:0px', 'tabindex' => '3')) ?>
	</div>	
</div>
</div>
</form>