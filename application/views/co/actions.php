<!-- Actions Panel -->

<table class="view-actions">
	<col width=12/><col width=216/><col width=12/>
	<tr>
		<td class="coViewHeader coViewSmallHeader" style="border:1px solid #ccc;" colspan=2 rowspan=2><div class="coViewPropertiesHeader"><?php echo lang("actions") ?></div></td>
		<td class="coViewTopRight"></td>
	</tr>
		
	<tr><td class="coViewRight" rowspan=2></td></tr>
	
	<tr>
		<td id="<?php echo $genid ?>_obj_actions" class="coViewBody" style="border:1px solid #ccc;" colspan=2> <?php

		$page_actions = PageActions::instance();
		$pactions = $page_actions->getActions();
		
		if (count($pactions) > 0 ) { ?>
			<div id="actionsDialog1"> <?php
				//$pactions = PageActions::instance()->getActions();
				$shown = 0;
				foreach ($pactions as $action) {
					if ($action->isCommon) {
				 		//if it is a common action sets the style display:block
				 		if ($action->getTarget() != '') { ?>
	   				    	<a id="<?php $atrib = $action->getAttributes(); echo array_var($atrib,'id'); ?>" style="display:block" class="coViewAction <?php echo $action->getName()?>" href="<?php echo $action->getURL()?>" target="<?php echo $action->getTarget()?>"> <?php echo $action->getTitle(); ?></a>
				 		<?php } else { ?>
							<a id="<?php $atrib = $action->getAttributes(); echo array_var($atrib,'id'); ?>" style="display:block" class="<?php $attribs = $action->getAttributes(); echo isset($attribs["download"]) ? '':'internalLink' ?> coViewAction <?php echo $action->getName()?>" href="<?php echo $action->getURL()?>"> <?php echo $action->getTitle(); ?></a>
						<?php }
				 		$shown++;
					} //if
				}//foreach ?>
			</div> <?php
			
			foreach ($pactions as $action) {
				if (!$action->isCommon) {			 		
			 		if ($action->getTarget() != '') { ?>
						<a style="display:block" class="coViewAction <?php echo $action->getName()?>" href="<?php echo $action->getURL()?>" target="<?php echo $action->getTarget()?>"> <?php echo $action->getTitle() ?></a>
					<?php } else { ?>
						<a style="display:block" class="<?php $attribs = $action->getAttributes(); echo isset($attribs["download"]) ? '':'internalLink' ?> coViewAction <?php echo $action->getName()?>" href="<?php echo $action->getURL()?>"> <?php echo $action->getTitle() ?></a>
					<?php }
			    	$shown++;
				}
			} // foreach			
		 }
		 $page_actions->clearActions();
		 //PageActions::clearActions();
		 ?>
		</td>
	</tr>
	<tr>
		<td class="coViewBottomLeft" style="width:12px;">&nbsp;</td>
		<td class="coViewBottom" style="width:216px;"></td>
		<td class="coViewBottomRight" style="width:12px;">&nbsp;&nbsp;</td>
	</tr>
</table>

<script>

	var obj_actions = $('#<?php echo $genid ?>_obj_actions a');

	for (i = 0; i < obj_actions.length; ++i) {
		$(obj_actions[i]).attr('id', 'obj_action_ref_'+i+'_<?php echo $genid ?>');
		$(obj_actions[i]).click(function(e) {
			if ($(this).attr("disabled") == "disabled") {
				return false;
			}
			$(this).attr("disabled", "disabled");

			var obj_link_id = $(this).attr('id');
			setTimeout(function() {
				$('#'+obj_link_id).attr("disabled", false);
			}, 2000);
		});
	}
</script>