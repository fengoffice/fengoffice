<?php 
	$genid = gen_id();
	$formId = "$genid-save-dimension-options";
	$action = get_url('administration', 'dimension_options_submit');
?>
<div class="dimensionOptionsConfiguration">


<div class="adminMainBlock">
	<div class="coInputHeader">
		<table><tr><td>
		  <div class="coInputHeaderUpperRow">
			<div class="coInputTitle">
				<?php echo lang('dimension options') ?>
			</div>
			<div class="desc" style="margin-top:10px;"><?php echo lang('dimension options desc'); ?></div>
		  </div>
		</td><td>
		  <div class="coInputButtons"><?php echo submit_button(lang('save changes'), null, array('style' => 'margin:0;' , 'id' => $genid.'_top_submit')) ?></div>
		</td></tr></table>
	
		<div class="clear"></div>
	</div>
	
	<div class="coInputMainBlock adminMainBlock">
		<form method="post" enctype="multipart/form-data" id="<?php echo $formId ?>" action="<?php echo $action?>">
		
		  <div class="section">
		
			<h1><?php echo lang('dimension names') ?></h1>
			<div class="description desc"><?php echo lang('dimension names desc')?></div>
			<table class="bordered">
				<tr><th><?php echo lang('default name')?></th><th><?php echo lang('custom name')?></th></tr>
				<?php 
				$isAlt = true;
				foreach ($custom_dimension_names as $dimension_id => $custom_dimension_name) {
					$isAlt = !$isAlt;
					$dimension = Dimensions::getDimensionById($dimension_id);
					if (!$dimension instanceof Dimension || !$dimension->getIsManageable()) continue;
					
					$default_name = lang($dimension->getCode());
					Hook::fire("edit_dimension_name", array('dimension' => $dimension), $default_name);
				?>
				<tr class="<?php echo ($isAlt ? 'altRow ' : '') ?>">
				
					<td><?php echo $default_name ?></td>
					
					<td><?php echo text_field('custom_names['.$dimension_id.']', $custom_dimension_name, array('class' => 'long')) ?></td>
					
				</tr>
				<?php 
				}
				?>
			</table>
		  </div>
		  
		  <div class="section">
		  
			<h1><?php echo lang('enable or disable dimension types') ?></h1>
			<div class="description desc"><?php echo lang('enable or disable dimension types desc')?></div>
			<table class="bordered">
				<tr><th><?php echo lang('dimension')?></th><th><?php echo lang('type')?></th><th class="center"><?php echo lang('status')?></th></tr>
				<?php 
				$last_dim = 0;
				$isAlt = true;
				foreach ($dimension_ots as $dimension_ot) {
					$dim_changed = $last_dim != $dimension_ot->getDimensionId();
					if ($dim_changed) {
						$isAlt = !$isAlt;
					}
					$dimension = Dimensions::getDimensionById($dimension_ot->getDimensionId());
					if (!$dimension instanceof Dimension || !$dimension->getIsManageable()) continue;
					
					$dimension_name = $dimension->getName();
					
					$ot = ObjectTypes::findById($dimension_ot->getObjectTypeId());
					if (!$ot instanceof ObjectType || in_array($ot->getName(), array('customer_folder', 'project_folder'))) continue;
					$ot_name = lang($ot->getName());
				?>
				<tr class="<?php echo ($isAlt ? 'altRow ' : '') . ($dim_changed ? 'bordered-top' : '') ?>">
				
					<td><?php echo $dim_changed ? $dimension_name : ""?></td>
					
					<td><span class="link-ico <?php echo $ot->getIconClass()?>"><?php echo $ot_name ?></span></td>
					
					<td class="center"><?php
						echo yes_no_widget('enabled_dots['.$dimension_ot->getDimensionId().']['.$dimension_ot->getObjectTypeId().'][enabled]', $genid, $dimension_ot->getEnabled(), lang('enabled'), lang('disabled'));
					?></td>
				</tr>
				<?php
					$last_dim = $dimension_ot->getDimensionId();
				}
				?>
			</table>
		  </div>
			<?php echo submit_button(lang('save changes')) ?>
		</form>
	</div>
</div>
<script>
$(function(){
	$(".dimensionOptionsConfiguration .coInputMainBlock table").css('min-width', '500px').css('margin', '15px 0');
	$("#<?php echo $genid?>_top_submit").click(function(){
		$("#<?php echo $formId?>").submit();
	});
});
</script>