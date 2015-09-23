<?php 
$ws_dim = Dimensions::findByCode('workspaces');
$row_cls = "";
$add_button_text = count($data_ws) > 0 ? lang('add new workspace') : lang('add your first workspace');
$no_objects_text = count($data_ws) > 0 ? '' : lang('you have no workspaces yet');
$ws_widget = Widgets::instance()->findById('workspaces');
$section = $ws_widget instanceof Widget && in_array($ws_widget->getDefaultSection(), array('left','right')) ? $ws_widget->getDefaultSection() : 'right';
?>

<div class="ws-widget widget">

	<div class="widget-header" onclick="og.dashExpand('<?php echo $genid?>');">
		<div class="widget-title"><?php echo $ws_dim->getName()?></div>
		<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander"></div>
	</div>
	
	<div class="widget-body" id="<?php echo $genid; ?>_widget_body" >
	
	<?php if (isset($data_ws) && $data_ws && count($data_ws) > 0) : ?>
		<div class="project-list">
		<?php foreach($data_ws as $ws):?>
			<div class="workspace-row-container <?php echo $row_cls ?>" id="workspace-<?php echo $ws->getId()?>">
				<a class="internalLink" href="javascript:void(0);" onclick="og.workspaces.onWorkspaceClick(<?php echo $ws->getId() ?>);">
					<img class="ico-color<?php echo $ws->getMemberColor() ?>" unselectable="on" src="s.gif"/>
					<?php echo $ws->getName() ?>
				</a>
				<?php
					$p = $ws->getParentMember();
					if ($p instanceof Member) {
						$crumb_members = array(
							$p->getId() => array('name' => $p->getName(), 'ot' => $p->getObjectTypeId(), 'c' => $p->getColor()),
						);
						$crumbOptions = json_encode(array($ws_dim->getId() => $crumb_members));
						if($crumbOptions == ""){
							$crumbOptions = "{}";
						}
						$crumbJs = " og.getEmptyCrumbHtml($crumbOptions, '.workspace-row-container' )";
					?>
				<span class="breadcrumb"></span>
				<script>
					var crumbHtml = <?php echo $crumbJs?>;
					$("#workspace-<?php echo $ws->getId()?> .breadcrumb").html(crumbHtml);
				</script>
				<?php } ?>
			</div>
			<div class="x-clear"></div>
		<?php endforeach;?>
		</div>
		
		<?php if (false && $total > 0) : ?>
			<div class="view-all-container">
				<a href="#" onclick="og.openLink(og.getUrl('member','init',{dim_id:'<?php echo $ws_dim->getId()?>', ot:'<?php echo $ws_ot_id?>'}), {caller:'workspaces'})"><?php echo lang("view all") ?></a>
			</div>
			<div class="clear"></div>
		<?php endif ;?>
		
		<div class="x-clear"></div>
		
	<?php endif; ?>
	
	<?php if (can_manage_dimension_members(logged_user())) : ?>
		<?php if (count($data_ws) > 0) : ?>
			<div class="separator"></div>
		<?php endif; ?>		
		<?php if ($no_objects_text != '') : ?><div class="no-obj-widget-msg"><?php echo $no_objects_text ?></div><?php endif; ?>
		<button title="<?php echo $add_button_text ?>" class="ws-more-details add-first-btn" style="float:<?php echo $section?>;">
			<img src="public/assets/themes/default/images/16x16/add.png"/>&nbsp;<?php echo $add_button_text ?>
		</button>
		<div class="clear"></div>
	<?php endif; ?>

	</div>
</div>


<script>
	$(function(){
		var parent_id = '<?php echo $parent instanceof Member ? $parent->getId() : 0?>';
		$(".ws-more-details").click(function(){
			og.openLink(og.getUrl('member','add'),{
				get: {
					'name': '',
					'dim_id': '<?php echo $ws_dim->getId()?>',
					'parent': parent_id
				}
			});
		});

		// og.eventManager.fireEvent('replace all empty breadcrumb', null);
	});
</script>
