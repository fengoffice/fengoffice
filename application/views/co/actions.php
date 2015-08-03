<!-- Actions Panel -->

<table class="view-actions">
	<col width=12/><col width=216/><col width=12/>
	<tr>
		<td class="coViewHeader coViewSmallHeader" style="border:1px solid #ccc;" colspan=2 rowspan=2><div class="coViewPropertiesHeader"><?php echo lang("actions") ?></div></td>
		<td class="coViewTopRight"></td>
	</tr>
		
	<tr><td class="coViewRight" rowspan=2></td></tr>
	
	<tr>
		<td class="coViewBody" style="border:1px solid #ccc;" colspan=2> <?php
		if (count(PageActions::instance()->getActions()) > 0 ) { ?>
			<div id="actionsDialog1"> <?php
				$pactions = PageActions::instance()->getActions();
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
			
			$count = count($pactions);
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
		 PageActions::clearActions(); ?>
		</td>
	</tr>
	<tr>
		<td class="coViewBottomLeft" style="width:12px;">&nbsp;</td>
		<td class="coViewBottom" style="width:216px;"></td>
		<td class="coViewBottomRight" style="width:12px;">&nbsp;&nbsp;</td>
	</tr>
</table>