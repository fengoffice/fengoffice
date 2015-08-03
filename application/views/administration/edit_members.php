<?php
	require_javascript('og/modules/memberListView.js'); 
	$genid = gen_id();
?>


<div class="adminProjects">
	<div class="coInputHeader">

	  <div class="coInputHeaderUpperRow">
		<div class="coInputTitle">
			<?php echo lang('dimensions') ?>
		</div>
	  </div>
	  <div class="clear"></div>
	</div>
	
	<div class="coInputMainBlock adminMainBlock">
  
<?php 
	if(is_array($dimensions) && count($dimensions) > 0) {
		foreach($dimensions as $dimension) { 
?>
			<fieldset><legend><span class="og-task-expander toggle_collapsed" style="padding-left:20px;" title="<?php echo lang('expand-collapse') ?>" id="<?php echo $genid?>expander<?php echo $dimension->getId()?>"
				onclick="og.editMembers.expandCollapseDim('<?php echo $genid?>dimension<?php echo $dimension->getId()?>', true);">
				<?php echo $dimension->getName() ?></span></legend>
				<div id="<?php echo $genid?>dimension<?php echo $dimension->getId()?>" style="display:none;">
<?php
			$dim_members = array_var($members, $dimension->getId());
			$alt = true;
			if (is_array($dim_members)) {
				foreach ($dim_members as $mem) {
					$alt = !$alt;
					$indent = 16 * $mem->getDepth();
					$ot = ObjectTypes::findById($mem->getObjectTypeId());
					$ot_name = $ot instanceof ObjectType ? lang($ot->getName()) : $mem->getName();
?>
						<div style="margin-left:<?php echo $indent?>px;width:<?php echo 800 - $indent?>px;" id="abm-members-item-container-<?php echo $mem->getId() ?>"
							class="<?php echo ($mem->getArchivedById() > 0 ? "member-item-archived" : "")?><?php echo ($alt ? " edit-mem-alt" : "")?>"
							onmouseover="og.editMembers.showHideOptions('<?php echo $genid?>actions<?php echo $mem->getId()?>', <?php echo $mem->getId()?>, true);" 
							onmouseout="og.editMembers.showHideOptions('<?php echo $genid?>actions<?php echo $mem->getId()?>', <?php echo $mem->getId()?>, false);">

							<table style="width:100%;"><tr><td style="width:425px;">
								<span class="coViewAction <?php echo $mem->getIconClass()?>">&nbsp;</span>
								<span class="abm-members-name"><?php echo $mem->getName() . ($mem->getArchivedById() > 0 ? " (".lang('archived').")" : "");?></span>
							</td><td>
								<span style="float:right;opacity:0.25;filter:alpha(opacity=25);font-weight:normal;" id="<?php echo $genid?>actions<?php echo $mem->getId()?>">
								<?php if (can_manage_dimension_members(logged_user())) : ?>
									<a href="<?php echo get_url('member', 'edit', array('id' => $mem->getId()))?>" class="db-ico ico-edit" style="padding:4px 10px 0 16px;"><?php echo lang('edit')?></a>
								<?php endif; ?>
								<?php if ($dimension->getDefinesPermissions() && can_manage_security(logged_user())) : ?>	
									<a href="<?php echo get_url('member', 'edit_permissions', array('id' => $mem->getId()))?>" class="db-ico ico-permissions" style="padding:4px 10px 0 16px;"><?php echo lang('permissions')?></a>
								<?php endif; ?>
								<?php if (can_manage_dimension_members(logged_user())) : ?>
									<a href="<?php echo "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . get_url('member', 'delete', array('id' => $mem->getId(), 'dont_reload' => true)) ."', {callback: function(success, data){if (success) Ext.get('abm-members-item-container-".$mem->getId()."').remove()}});"?>" 
										class="db-ico ico-delete" style="padding:4px 10px 0 16px;"><?php echo lang('delete')?></a>
									
									<a href="<?php echo "javascript:if(confirm('".lang('confirm archive member', $ot_name)."')) og.openLink('" . get_url('member', 'archive', array('id' => $mem->getId(), 'dont_back' => true)) ."', {callback: function(success, data){if (success) {Ext.get('archive-link-".$mem->getId()."').setDisplayed('none');Ext.get('unarchive-link-".$mem->getId()."').setDisplayed(''); Ext.get('abm-members-item-container-".$mem->getId()."').addClass('member-item-archived');}}});"?>" 
										class="db-ico ico-archive-obj" style="padding:4px 0 0 16px;<?php echo ($mem->getArchivedById()!=0 ? 'display:none;' : '')?>" id="archive-link-<?php echo $mem->getId() ?>"><?php echo lang('archive')?></a>
									
									<a href="<?php echo "javascript:if(confirm('".lang('confirm unarchive member', $ot_name)."')) og.openLink('" . get_url('member', 'unarchive', array('id' => $mem->getId(), 'dont_back' => true)) ."', {callback: function(success, data){if (success) {Ext.get('archive-link-".$mem->getId()."').setDisplayed('');Ext.get('unarchive-link-".$mem->getId()."').setDisplayed('none'); Ext.get('abm-members-item-container-".$mem->getId()."').removeClass('member-item-archived');}}});"?>" 
										class="db-ico ico-unarchive-obj" style="padding:4px 0 0 16px;<?php echo ($mem->getArchivedById()==0 ? 'display:none;' : '')?>" id="unarchive-link-<?php echo $mem->getId() ?>"><?php echo lang('unarchive')?></a>
									
								<?php endif; ?>
								</span>
							</td></tr></table>
						</div>
<?php			}
			} ?>
			<?php if (can_manage_dimension_members(logged_user())) : ?>
				<div style="margin-top:10px;"><a class="db-ico ico-add" style="padding:3px 0 0 20px;" href="<?php echo get_url('member', 'add', array("dim_id" => $dimension->getId()))?>">
					<?php echo lang('add member to this dimension')?>
				</a></div>
			<?php endif; ?>
				</div>
			</fieldset>
<?php
		}
	}
?>
  </div>
</div>
