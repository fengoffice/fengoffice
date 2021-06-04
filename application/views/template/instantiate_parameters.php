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
		<div class="coInputTitle"><?php echo lang('template parameters').': '.$template->getObjectName() ?></div>
		<div class="desc"><?php echo lang('template parameters description')?></div>
		<div class="desc"><?php echo $template->getDescription()?></div>
	</div>
	<div class="clear"></div>
</div>
<div class="coInputMainBlock">
	<?php if (!isset($parameters) || count($parameters) == 0) { ?>
	<input name="parameterValues[dummy]" value="" type="hidden"/>
	<?php } ?>
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
				<td style="padding:3px 10px 0 10px;"><span class="bold"><?php
					$parameter_name = $parameter['name'];
					Hook::fire('template_param_instantiation_name', array('param' => $parameter, 'template' => $template), $parameter_name);
					echo $parameter_name;
					
					$parameter_js_key = str_replace("'", "", $parameter['name']);
					
				?></span></td>
				<td align="left">
					<?php if($parameter['type'] == 'string'){ ?>
						<input id="parameterValues[<?php echo $parameter_js_key; ?>]" name="parameterValues[<?php echo $parameter['name'] ?>]" class="title" value="<?php echo $default_value?>"/>
					<?php } else if ($parameter['type'] == 'date'){
							echo pick_date_widget2('parameterValues['.$parameter_js_key.']');
							
						  } else if($parameter['type'] == 'user') { ?>
						<select name="<?php echo 'parameterValues['.$parameter_js_key.']'; ?>">
						<?php
							$context = active_context();
							if (isset($member_id) && $member_id > 0) {
								// filter by context passed by parameter
								$additional_member = Members::findById($member_id);
								if ($additional_member instanceof Member) {
									$context = array($additional_member);
								}
							}

							$companies  = allowed_users_to_assign($context);
							?>
							<option value="0"><?php echo lang('none') ?></option>
							<?php
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
					<?php } else {
								$null = null;
								Hook::fire('render_param_to_instantiate', array('param' => $parameter), $null);
						  }
					?>
				</td>
			</tr>
		<?php }//foreach ?>
		</tbody></table>
		<input type="hidden" name="additional_member_ids" value='<?php echo json_encode($additional_member_ids)?>'>
		<input type="hidden" name="linked_objects" value='<?php echo json_encode($linked_objects)?>'>
		<?php
		if (isset($from_email)) {
			?><input type="hidden" name="from_email_id" value="<?php echo array_var($_REQUEST, 'from_email')?>"><?php 
		}
		?>
	</div>
	<br/>
	<div>
	<?php echo submit_button(lang('instantiate'),'s',	array('style'=>'margin-top:0px', 'tabindex' => '3')) ?>
	</div>	
</div>
</div>
</form>