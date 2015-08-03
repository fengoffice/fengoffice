<?php
require_javascript('og/modules/memberListView.js'); 
if (!isset($genid)) $genid = gen_id();
if (!isset($actual_associations)) $actual_associations = array();

function order_members($members) {
	$tmp_array = array();
	foreach ($members as $mem) {
		if (!isset($tmp_array[$mem->getDepth()])) $tmp_array[$mem->getDepth()] = array();
		$tmp_array[$mem->getDepth()][$mem->getId()] = array('m' => $mem, 'sub' => array());
	}
	
	$level = max(array_keys($tmp_array));
	while ($level > 1) {
		foreach ($tmp_array[$level] as $mid => &$minfo) {
			$tmp_array[$level-1][$minfo['m']->getParentMemberId()]['sub'][$mid] = $minfo;
		}
		$level--;
	}
	
	$ordered_members = array();
	foreach (array_shift($tmp_array) as $id => $info) {
		$ordered_members[] = $info['m'];
		flatten_members_array($info['sub'], $ordered_members);
	}
	
	return $ordered_members;
}

function flatten_members_array($members, &$result) {
	foreach ($members as $info) {
		$result[] = $info['m'];
		flatten_members_array($info['sub'], $result);
	}
}


if(is_array($dimensions) && count($dimensions) > 0) {
	foreach($dimensions as $dimension) {
	  $d_id = $dimension->getId();
?>
	<fieldset><legend><span class="og-task-expander toggle_collapsed" style="padding-left:20px;" title="<?php echo lang('expand-collapse') ?>" id="<?php echo $genid?>expander<?php echo $d_id?>"
		onclick="og.editMembers.expandCollapseDim('<?php echo $genid?>dimension<?php echo $d_id?>', true);">
		<?php echo $dimension->getName() . ($req_dimensions[$d_id] ? "&nbsp;<span class='label_required'>*</span>" : '')?></span></legend>
		<div id="<?php echo $genid?>dimension<?php echo $d_id?>" style="display:none;" class="adminMainBlock">
		
<?php foreach ($associations[$d_id] as $assoc) { ?>
			<table style="width:100%;margin-top:10px;" id="<?php echo $genid . $d_id . "_" . $assoc['ot']?>">
				<tr>
					<th><?php echo lang($assoc['ot_name']) . ($assoc['required'] ? "&nbsp;*" : "") ?></th>
					<th style="text-align:right;"><?php echo lang('associate')?></th>
				</tr>
<?php
		$row_cls = 'altRow';
		$assoc_members = array_var($assoc, 'members');
		
		$check_onclick = array_var($assoc, 'multi') ? "" : "og.dimProperties.uncheckOtherMembers(this, '".$genid . $d_id . "_" . $assoc['ot']."');";
		$check_onclick .= "og.dimProperties.disableChildProperties('".$genid."', ".$d_id.");";
		
		if (is_array($assoc_members)) {
			
			$ordered_members = order_members($assoc_members);
			
			foreach ($ordered_members as $mem) {
				if (!$mem instanceof Member) continue;
				$parent = $mem->getParentMemberId();
				$m_id = $mem->getId();
				$row_cls = $row_cls == '' ? ' altRow' : '';
				$padding = 20 * $mem->getDepth();
?>
				<tr class="add-member-table-row<?php echo $row_cls?>">
					<td>
						<span id="<?php echo $genid?>name_<?php echo $d_id?>_<?php echo $m_id?>" style="padding-left:<?php echo $padding?>px;">
							<?php echo $mem->getName();?>
						</span>
					</td>
					<td style="text-align:right;padding-right:20px;">
						<input type="checkbox" style="width:16px;" name="associated_members[<?php echo $m_id?>]" 
							id="<?php echo $genid?>associated_members_<?php echo $d_id?>_<?php echo $m_id?>" 
							value="<?php echo array_var($assoc, 'id')?>" <?php echo (isset($actual_associations[$m_id]) ? 'checked' : '')?>
							onclick="<?php echo $check_onclick ?>" />
					</td>
				</tr>
<?php		}
		} ?>
			</table>
<?php } ?>
		
		<?php if (!array_var($restricted_dimensions, $d_id)) { ?>
			<div style="margin-top:10px;"><a class="internalLink db-ico ico-add" style="padding:3px 0 0 20px;" href="#" 
				onclick="og.openLink(og.getUrl('member','add',{dim_id:<?php echo $d_id?>, prop_genid: '<?php echo $genid?>'}), {caller:'new member'});">
				<?php echo lang('add member to this dimension')?>
			</a></div>
		<?php } ?>
		</div>
	</fieldset>
	<input type="hidden" name="save_properties" value="1" />
<?php
	}
}/* else { ?>
	<div class="desc"><?php echo lang('no associations can be set for this type of member in this dimension')?></div>
<?php }*/
?>