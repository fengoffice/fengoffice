<?php
	require_javascript("og/modules/addContactForm.js");
	$currentDimension = current_dimension_id();
	if (!isset($contacts_for_combo)) $contacts_for_combo = null;
	/*
	  <form style="height:100%;background-color:white" action="<?php echo get_url("contact", "add_permissions_user", array("id" => $user->getId())) ?>" class="internalForm" onsubmit="javascript:og.ogPermPrepareSendData('<?php echo $genid ?>');return true;" method="POST">
</form>
	 */
?>

<div class="widget-persons widget">
	<?php 
		/*
		 * Title of the widget
		 */
	?>
	<div style="overflow: hidden;" class="widget-header" onclick="og.dashExpand('<?php echo $genid?>');">
		<div class="widget-title"><?php echo (isset($widget_title)) ? $widget_title : lang("contacts");?></div>
		<input name="mids" type="hidden" value="<?php echo isset($mids) ? $mids : "" ?>" />
		<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander"></div>
	</div>
	
	<div class="widget-body" id="<?php echo $genid; ?>_widget_body">
		<?php 
		/*
		 * This section display contacts (max displayed = $limit)
		 */
		?>
		<ul>
		<?php 
		$row_cls = "contact-row";
		$i = 0;
		foreach ($contacts as $person): ?>
				
				<li class="<?php echo $row_cls ?>" style="<?php echo ($i >= $persons_to_show ? "display:none;" : "") ?>">
				
					<div class="contact-picture contact-picture-container">
						<a href="<?php echo $person->getCardUrl() ?>" class="person" onclick="if (og.core_dimensions) og.core_dimensions.buildBeforeObjectViewAction(<?php echo $person->getId()?>, true);">
							<img src="<?php echo $person->getPictureUrl(); ?>" />
						</a>
					</div>
					
					<div class="contact-info">
						<a href="<?php echo $person->getCardUrl() ?>" class="person" onclick="if (og.core_dimensions) og.core_dimensions.buildBeforeObjectViewAction(<?php echo $person->getId()?>, true);"><?php echo clean($person->getObjectName()) ?></a>
						<div class="email"><?php echo $person->getEmailAddress(); ?></div> 
					</div>
					
					<div class="clear"></div>
				</li>
		<?php 
			$i++;
		endforeach;
		?>
		</ul>
		
		<?php if ($render_add) :
				$exe_pg = PermissionGroups::instance()->findOne(array('conditions' => "type='roles' AND parent_id>0 AND name='Executive'"));
		?>
			
			<div style="margin-top:4px; margin-left:10px; margin-right:10px;">
				
				<div style="float:left; width: 75%; margin-top: 10px;">
				<?php if (!isset($add_people_btn)) { ?>
				
					<button style="overflow: hidden; width:auto;" id="add-person-form-show" class="add-first-btn"
						onclick="og.openLink(og.getUrl('contact','add')); return false;">
						<img src="public/assets/themes/default/images/16x16/add.png"/>&nbsp;<?php echo lang('add contact');?>
					</button>
				
				<?php } ?>
				
				</div>
				
				<div style="float:right; margin-top: 20px;">
				<?php if ($total > $persons_to_show) : ?>
					<div style="text-align:right;"><a id='showlnk-contacts' href="#" 
						onclick="og.showHideWidgetMoreLink('.widget-persons .contact-row','-contacts',true);$('#<?php echo $genid?>view-all-link').show();">
							<?php echo lang("show more") ?>
					</a></div>
				<?php endif; ?>
				
				<?php if ($total > $limit) : ?>
					<div class="view-all-container" style="display:none;" id="<?php echo $genid?>view-all-link">
						<a href="#" onclick="og.openLink(og.getUrl('contact', 'init'), {caller:'contacts-panel'});">
							<?php echo lang('view all');?>
						</a>
					</div>
				<?php endif; ?>
				</div>
			
				<div class="clear"></div>

		</div>
		<?php endif;?>
		
	<div class="progress-mask"></div>
		
	</div>
</div>
