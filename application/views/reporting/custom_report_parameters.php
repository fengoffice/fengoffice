<?php
	$genid = gen_id();
	$tiCount = 0;
	$firstId = '';
?>

<form style='height:100%;background-color:white' class="internalForm" action="<?php echo get_url('reporting', 'view_custom_report', array('id' => $id)) ?>" method="post">

<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
		<div class="coInputTitle"><?php echo $title ?></div>
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">

	<div style="width:600px;padding-bottom:20px"><?php echo $description ?></div>

	<table class="custom-report-params">
		<tr>
			<td><span class="bold"><?php echo lang('field') ?></span></td>
			<td style="text-align:center;"><span class="bold"><?php echo lang('condition') ?></span></td>
			<td><span class="bold"><?php echo lang('value') ?></span></td>
			<td><span class="bold"><?php echo lang('ignore this condition') ?></span></td>
		</tr>
		
		<?php
		$ot = ObjectTypes::findById($model);
		$model = $ot->getHandlerClass();
		foreach($conditions as $condition){
			if($condition->getCustomPropertyId() > 0){
				$cp = null;
				if ($ot instanceof ObjectType && in_array($ot->getType(), array('dimension_group'))) {
					if (Plugins::instance()->isActivePlugin('member_custom_properties')) {
						$cp = MemberCustomProperties::getCustomProperty($condition->getCustomPropertyId());
					}
				} else {
					$cp = CustomProperties::getCustomProperty($condition->getCustomPropertyId());
				}
				if (!$cp) continue;
				$name = $cp->getName();

			} else {
				$name = Localization::instance()->lang('field ' . $model . ' ' . $condition->getFieldName());
				if (!$name) {
					$name = lang('field Objects ' . $condition->getFieldName());
				}
				Hook::fire('custom_report_param_name_render', array('field' => $condition, 'report' => null, 'report_ot' => $ot), $name);
			}
			$tiCount ++;
			?>
			<tr style='height:30px;'>
			<?php
				$condId = $genid . 'rpcond' . $condition->getId();
				if ($firstId == '') $firstId = $condId;
			?>
				<td><span class="bold"><?php echo $name ?>&nbsp;</span></td>
				<td style="text-align:center;"><?php
					if ($condition->getCondition() == '%') {
						echo lang('ends with');
					} else {
						$cond_label = Localization::instance()->lang($condition->getCondition());
						if (!$cond_label) $cond_label = $condition->getCondition();

						Hook::fire('custom_report_param_condition_render', array('field' => $condition, 'report' => null, 'report_ot' => $ot), $cond_label);

						echo $cond_label;
					}
				?>&nbsp;</td>
			<?php if(isset($cp)){ ?>
				<td align='left'>
					<?php if($cp->getType() == 'text' || $cp->getType() == 'numeric'){ ?>
						<input type="text" id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId()."_".clean($cp->getName()) ?>]" tabindex=<?php echo $tiCount?>/>
					<?php }else if($cp->getType() == 'boolean'){  ?>
						<select id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId()."_".clean($cp->getName()) ?>]" tabindex=<?php echo $tiCount?>>
							<option value="0" ></option>
							<option value="1" > <?php echo lang('yes') ?>  </option>
							<option value="-1" > <?php echo lang('no') ?> </option>
						</select>
					<?php }else if($cp->getType() == 'list'){  ?>
						<select id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId()."_".clean($cp->getName()) ?>]" tabindex=<?php echo $tiCount?>>
							<option value=""> <?php echo lang('none') ?>  </option>
						<?php foreach(explode(',', $cp->getValues()) as $value){ ?>
							<option value="<?php echo $value ?>"> <?php echo $value ?>  </option>
						<?php }//foreach ?>
						</select>
					<?php }else if($cp->getType() == 'date' || $cp->getType() == 'datetime'){  ?>
						<?php echo pick_date_widget2("params[".$condition->getId()."_".clean($cp->getName())."]",$genid,$tiCount)?>
					<?php }?>
				</td>

				<td align='center' style="padding-top: 5px;"><?php
					echo checkbox_field('disabled_params['.$condition->getId().']', null, array('onchange' => 'og.on_custom_report_param_disable(this);'));
				?></td>

			<?php }else{ ?>
				<td align='left'>
				<?php
						$model_instance = new $model();
						$col_type = $model_instance->getCOColumnType($condition->getFieldName());

						if(in_array($condition->getFieldName(), array_keys($external_fields))){
				?>

				<div id="<?php echo $genid.$condition->getId(); ?>external_field_combo_container" style="float:left;"></div>

				<script>
					var external_fields_values = [];
				    <?php foreach($external_fields[$condition->getFieldName()] as $value){  ?>
				    		external_fields_values.push(['<?php echo $value['id'] ?>', '<?php echo clean(escape_character($value['name'],"'",true)) ?>']);
				    <?php } ?>

				    	var external_fields_store = new Ext.data.SimpleStore({
    		        		fields: ["id", "name"],
    		        		data: external_fields_values
    					});

				    	var tsContactCombo = new Ext.form.ComboBox({
				    		renderTo:'<?php echo $genid.$condition->getId(); ?>external_field_combo_container',
				    		name: "params[<?php echo $condition->getId(); ?>]",
				    		id: '<?php echo $genid.$condition->getId(); ?>external_field_combo',
				    		value: '0',
				    		store: external_fields_store,
				    		mode: 'local',
				            cls: 'assigned-to-combo',
				            triggerAction: 'all',
				            selectOnFocus:true,
				            width: 244,
				            listWidth: 244,
				            listClass: 'assigned-to-combo-list',
				            displayField    : 'name',
				            valueField        : 'id',
				            hiddenName : "params[<?php echo $condition->getId(); ?>]",
				            emptyText: '',
				            valueNotFoundText: ''
				    	});
				</script>



				<?php
						} else {
							if ($condition->getFieldName() == 'is_user') {
								$options = array(option_tag(lang('yes'), 1), option_tag(lang('no'), 0));
								echo select_box("params[".$condition->getId()."]", $options);
							} else  if ($col_type == DATA_TYPE_DATE || $col_type == DATA_TYPE_DATETIME) {
								echo pick_date_widget2("params[".$condition->getId()."]");
							} else {

							    if($ot->getName() == 'task' && $condition->getFieldName() == 'status'){
							    ?>
                                    <select id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId()."_".clean($condition->getFieldName()) ?>]">
            							<option value="0" > <?php echo lang('pending') ?>  </option>
            							<option value="1" > <?php echo lang('completed') ?> </option>
            						</select>
							    <?php
							    }else{
									$already_rendered = false;
									Hook::fire('custom_report_param_render', array('field' => $condition, 'report' => null, 'report_ot' => $ot), $already_rendered);

									if (!$already_rendered) {
								?>
										<input type="text" id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId() ?>]" />
								<?php
								    }
							     }
							}
						}
				?>
				</td>

				<td align='center' style="padding-top: 5px;"><?php
					echo checkbox_field('disabled_params['.$condition->getId().']', null, array('onchange' => 'og.on_custom_report_param_disable(this);'));
				?></td>
			</tr>
		<?php
			}
			unset($cp);
		} //foreach ?>
	</table>

<?php echo submit_button(lang('generate report'),'s',array('tabindex' => $tiCount + 1))?>
</div>

<style>
table.custom-report-params td {
	padding: 3px 10px;
}
table.custom-report-params .single-tree.member-chooser-container td {
	padding-left: 0px;
}
table.custom-report-params tr.disabled {
	color: #666;
	font-style: italic;
	background: #efefef;
}
</style>

<script>
og.on_custom_report_param_disable = function(checkbox) {
	if ($(checkbox).attr('checked')) {
		$(checkbox).closest('tr').addClass('disabled');
	} else {
		$(checkbox).closest('tr').removeClass('disabled');
	}
}

var firstCond = Ext.getDom('<?php echo $firstId ?>');
if (firstCond)
	firstCond.focus();
</script>

</form>
