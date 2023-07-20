<?php $genid = gen_id();?>
<style>
.dimension-members-container {
	background-color: white;
	padding: 10px;
}
.dimension-members-container .title {
	font-weight: normal;
	font-size: 200%;
	margin-top: 20px;
	margin-left: 20px;
}

.dimension-members-container .title .titletext {
	float: left;
	min-width: 500px;
}

.dimension-members-container .dimension-members-section {
	border-top: 1px solid #ccc;
	margin: 20px 10px 30px;
	padding: 0 10px; 
}
.dimension-members-container .dimension-members-section h1 {
	font-weight: bold;
	font-size: 150%;
	margin: 10px 0;
}
.dimension-members-container .dimension-members-section div.description {
	font-weight: bold;
	margin: 5px 0 10px;
}
.dimension-members-container .dimension-members-section .section-content {
	margin-top: 20px;
	border: 1px solid #ddd;
}
.dimension-members-container .dimension-members-section .section-content .member-row {
	height: 18px;
	width: 100%;
	padding: 5px 0;
}
.dimension-members-container .dimension-members-section .section-content .member-row.alt {
	background-color: #ECEFF7;
}
.dimension-members-container .dimension-members-section .section-content .member-row .member-name {
	padding-left:20px;
	padding-top: 2px;
	height: 18px;
	width: 400px;
	margin-left: 5px;
	float: left;
	white-space: nowrap;
	text-overflow: ellipsis;
	overflow: hidden;
}
.dimension-members-container .dimension-members-section .section-content .member-row .member-log {
	float: left;
}
.dimension-members-container .dimension-members-section .section-content .member-row .member-actions {
	float: left;
	width: 150px;
	margin-top: 3px;
}
.dimension-members-container .dimension-members-section .section-content .member-row .member-actions .db-ico {
	padding: 5px 0 2px 20px;
}

.dimension-members-container .dimension-members-section .pagination-container {
	float: left;
	padding-top: 18px;
	width: 180px;
}
.dimension-members-container .dimension-members-section .orderby-container {
	float: left;
	padding-top: 18px;
}
.dimension-members-container .dimension-members-section .itemsxpage-container {
	float: left;
	padding-top: 18px;
	margin-left: 20px;
}
.dimension-members-container .dimension-members-section .button-container {
	float: left;
}
</style>

<div class="dimension-members-container">
	
	<div class="title">
		<div class="titletext"><?php echo $dimension_name ?></div>
		<button title="<?php echo lang('close')?>" style="float:left; margin: -15px 0 0 0px;" class="add-first-btn" onclick="og.goback(this)">
			<img src="public/assets/themes/default/images/layout/close16.png" style="margin-bottom:-1px;">&nbsp;<?php echo lang('close')?>
		</button>
		<div class="clear"></div>
	</div>
	
	<div class="dimension-members-section">
		<div class="button-container">
		<?php // add buttons
		$margin_left = 0;
		foreach ($member_types as $type) {
			$info = null;
			Hook::fire('add_member_by_type_info', array('dim_code' => $dimension->getCode(), 'mem_type' => $type->getId()), $info);
			if (!is_array($info)) continue;
			?>
			<button title="<?php echo $info['name']?>" style="float:left; margin: 10px 0 0 <?php echo $margin_left?>;" class="add-first-btn" onclick="og.openLink('<?php echo  $info['url']?>');">
				<img src="public/assets/themes/default/images/16x16/add.png">&nbsp;<?php echo $info['name']?>
			</button>
		<?php
			$margin_left = '20px'; 
		}
		?>
		</div>
		<div class="clear"></div>
		
		<div class="section-content section1">
<?php
		$is_alt = false;
		foreach ($members as $member) {/* @var $member Member */
			if(!is_null($log_data[$member->getId()]['created_by_id'])){
				$created_by = Contacts::findById($log_data[$member->getId()]['created_by_id']);
				$created_by_name = $created_by instanceof Contact ? clean($created_by->getObjectName()) : lang('n/a');
				$created_on = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $log_data[$member->getId()]['created_on']);
			}
		?>
			<div class="member-row <?php echo ($is_alt ? "alt" : "")?>">
				<div class="member-name db-ico <?php echo $member->getIconClass()?>" id="member-<?php echo $member->getId()?>"><?php 
					echo $member->getName();
					
					$p = $member->getParentMember();
					if ($p instanceof Member) {
						$crumb_members = array(
							$p->getId() => array('name' => $p->getName(), 'ot' => $p->getObjectTypeId(), 'c' => $p->getColor()),
						);
						$crumbOptions = json_encode(array($member->getDimensionId() => $crumb_members));						
					?>
					<span class="breadcrumb"></span>
					<script>
					var crumbHtml = "<div class='breadcrumb-container' style='display: inline-block;min-width: 250px;'>";
					crumbHtml += og.getEmptyCrumbHtml(<?php echo $crumbOptions?>, '.breadcrumb-container', og.breadcrumbs_skipped_dimensions);
					crumbHtml += "</div>";

					$("#member-<?php echo $member->getId()?> .breadcrumb").html(crumbHtml);
					</script>
			<?php 	} ?>
				</div>
			<?php $edit_url = get_url('member', 'edit', array('id' => $member->getId()));
				if ($member->getObjectId() > 0) {
					if ($obj = Objects::findObject($member->getObjectId())) {
						if ($obj->getEditUrl()) $edit_url = $obj->getEditUrl();
					}
				}
			?>
				<div class="member-actions"><a class="db-ico ico-edit" href="<?php echo $edit_url?>"><?php echo lang('edit')?></a></div>
		  <?php if(!is_null($log_data[$member->getId()]['created_by_id'])){ ?>
				<div class="desc member-log"><?php echo lang('created by on', ($created_by instanceof Contact ? $created_by->getCardUrl() : ''), $created_by_name, format_date($created_on))?></div>
				<?php } ?>
				<div class="clear"></div>
			</div>
		<?php 
			$is_alt = !$is_alt;
		}
?>
		</div>
		
		<div class="clear"></div>
		
		
	<?php
		$prev_page = $page - 1;
		$next_page = $page + 1;
		$last_page = floor($total_items / $items_x_page) + 1;
	?>
		<div class="pagination-container">
			<?php if ($page > 1) { ?>
			<span onclick="og.call_dimension_list_members(1);"><span class="x-tbar-page-first" style="padding-left:16px; cursor:pointer;"></span></span>
			<?php } else { ?>
			<span class="x-item-disabled"><span class="x-tbar-page-first" style="padding-left:16px"/></span></span>
			<?php }?>
			
			<?php if ($prev_page > 0) { ?>
			<span onclick="og.call_dimension_list_members(<?php echo $prev_page?>);"><span class="x-tbar-page-prev" style="padding-left:16px; cursor:pointer;"></span></span>
			<?php } else { ?>
			<span class="x-item-disabled"><span class="x-tbar-page-prev" style="padding-left:16px"/></span></span>
			<?php } ?>
			
			<?php echo lang('page')?>&nbsp;<input id="<?php echo $genid?>_actual_page_input" type="text" style="width:15px;" value="<?php echo $page ?>" /><?php echo " " .strtolower(lang('of'))." $last_page"?>
			
			<?php if ($next_page <= $last_page) { ?>
			<span onclick="og.call_dimension_list_members(<?php echo $next_page?>);"><span class="x-tbar-page-next" style="padding-left:16px; cursor:pointer;"></span></span>
			<?php } else { ?>
			<span class="x-item-disabled"><span class="x-tbar-page-next" style="padding-left:16px"/></span></span>
			<?php } ?>
			
			<?php if ($last_page > $page) { ?>
			<span onclick="og.call_dimension_list_members(<?php echo $last_page?>);"><span class="x-tbar-page-last" style="padding-left:16px; cursor:pointer;"></span></span>
			<?php } else { ?>
			<span class="x-item-disabled"><span class="x-tbar-page-last" style="padding-left:16px"/></span></span>
			<?php } ?>
			
		</div>
		
		<div class="orderby-container">
			<?php echo lang('order by')?>&nbsp;
			<select id="<?php echo $genid?>_order_by" onchange="og.call_dimension_list_members(1);">
				<option value="name" <?php echo ($order_by == 'name') ? 'selected="selected"' : ''?>><?php echo lang('name')?></option>
				<option value="created_on" <?php echo ($order_by == 'created_on') ? 'selected="selected"' : ''?>><?php echo lang('field Objects created_on')?></option>
			</select>
			<select id="<?php echo $genid?>_order_by_dir" onchange="og.call_dimension_list_members(1);">
				<option value="ASC" <?php echo ($order_by_dir == 'ASC') ? 'selected="selected"' : ''?>><?php echo lang('ascending')?></option>
				<option value="DESC" <?php echo ($order_by_dir == 'DESC') ? 'selected="selected"' : ''?>><?php echo lang('descending')?></option>
			</select>
		</div>
		
		<div class="itemsxpage-container">
			<?php echo lang('items x page')?>&nbsp;<input id="<?php echo $genid?>_items_x_page" type="text" style="width:15px;" value="<?php echo $items_x_page ?>" />
			<span class="desc">&nbsp;<?php echo lang('total').": $total_items"?></span>
		</div>
		
		<div class="clear"></div>
		
		
	
		
	</div>
</div>
<script>
var genid = '<?php echo $genid?>';
var dim_id = '<?php echo $dimension->getId()?>';
var actual_page = '<?php echo $page?>';
var items_x_page = '<?php echo $items_x_page?>';

og.call_dimension_list_members = function(page) {
	if (!page) page = actual_page;
	var order = $("#"+genid+"_order_by").val();
	var order_dir = $("#"+genid+"_order_by_dir").val();
	og.openLink(og.getUrl('dimension', 'list_members', {dim: dim_id, page: page, order: order, order_dir: order_dir, items_x_page:items_x_page}));
}

$(function() {
	$(".dimension-members-container").parent().css('backgroundColor', 'white');

	$("#<?php echo $genid?>_actual_page_input").keypress(function(e){
		if(e.keyCode == 13){
			og.call_dimension_list_members($(this).val());
 		}
	});
	$("#<?php echo $genid?>_actual_page_input").blur(function(e){
		og.call_dimension_list_members($(this).val());
	});
	
	$("#<?php echo $genid?>_items_x_page").keypress(function(e){
		if(e.keyCode == 13){
			items_x_page = $(this).val();
			og.call_dimension_list_members(1);
 		}
	});
	$("#<?php echo $genid?>_items_x_page").blur(function(e){
		items_x_page = $(this).val();
		og.call_dimension_list_members(1);
	});

	og.eventManager.fireEvent('replace all empty breadcrumb', null);
});
</script>