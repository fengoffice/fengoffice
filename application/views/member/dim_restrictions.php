<?php
require_javascript('og/modules/memberListView.js'); 
if (!isset($genid)) $genid = gen_id();

if (!isset($orderable_dimensions_otypes)) $orderable_dimensions_otypes = array();
if (!isset($restrictions)) $restrictions = array();

if(is_array($dimensions) && count($dimensions) > 0) {
	foreach($dimensions as $dimension) {
		$d_id = $dimension->getId();
?>
	<fieldset><legend><span class="og-task-expander toggle_collapsed" style="padding-left:20px;" title="<?php echo lang('expand-collapse') ?>" id="<?php echo $genid?>expander<?php echo $d_id?>"
		onclick="og.editMembers.expandCollapseDim('<?php echo $genid?>dimension<?php echo $d_id?>', true);">
		<?php echo $dimension->getName() ?></span></legend>
		<div id="<?php echo $genid?>dimension<?php echo $d_id?>" style="display:none;" class="adminMainBlock">
			<table style="width:100%;">
				<tr>
					<th><?php echo lang('member')?></th>
					<th style="text-align:center;"><?php echo lang('restricted')?></th>
					<th style="text-align:center;"><?php echo lang('ordering')?></th>
				</tr>
<?php
		$row_cls = 'altRow';
		$dim_members = array_var($members, $d_id);
		if (is_array($dim_members)) {
			foreach ($dim_members as $mem) {
				$parent = $mem->getParentMemberId();
				$m_id = $mem->getId();
				$indent = 16 * $mem->getDepth();
				$row_cls = $row_cls == '' ? ' altRow' : '';
?>
				<tr class="add-member-table-row<?php echo $row_cls?>">
					<td>
						<span style="margin-left:<?php echo $indent?>px;width:<?php echo 500 - $indent?>px;" id="<?php echo $genid?>name_<?php echo $d_id?>_<?php echo $m_id?>">
							<?php echo $mem->getName();?>
						</span>
					</td>
					<td style="text-align:center;width:90px;">
						<input type="checkbox" style="width:16px;" name="restricted_members[<?php echo $d_id?>][<?php echo $m_id?>][restricted]" 
							id="<?php echo $genid?>restricted_members_<?php echo $d_id?>_<?php echo $m_id?>" 
							value="<?php echo (isset($restrictions[$m_id]) ? '1' : '')?>" <?php echo (isset($restrictions[$m_id]) ? 'checked' : '')?>
							onclick="og.dimRestrictions.showHide('<?php echo $genid?>order_controls_<?php echo $d_id?>_<?php echo $m_id;?>', this.checked);
								og.dimRestrictions.enableDisableChilds('<?php echo $genid?>', '<?php echo $d_id?>', '<?php echo $m_id;?>', this.checked);
								if (this.checked) og.dimRestrictions.addMemberToOrderList('<?php echo $genid?>', <?php echo $d_id?>, <?php echo $m_id;?>, <?php echo $parent;?>);
								else og.dimRestrictions.delMemberOfOrderList('<?php echo $genid?>', <?php echo $d_id?>, <?php echo $m_id;?>, <?php echo $parent;?>);" />
					</td>
					<td style="padding-left:16px;width:160px;">
<?php 			if (in_array($d_id."_".$mem->getObjectTypeId(), $orderable_dimensions_otypes)) { ?>
						<span id="<?php echo $genid?>order_controls_<?php echo $d_id?>_<?php echo $m_id;?>" <?php echo (isset($restrictions[$m_id]) ? '' : 'style="display:none;"')?>>
						
							<span onclick="og.dimRestrictions.move('<?php echo $genid;?>', '<?php echo $d_id;?>', '<?php echo $m_id;?>', <?php echo $parent;?>, -1);" 
								class="clico ico-up transparent" style="padding:2px 0 0 15px;" title="<?php echo lang('move up')?>">&nbsp;</span>
							
							<span onclick="og.dimRestrictions.move('<?php echo $genid;?>', '<?php echo $d_id;?>', '<?php echo $m_id;?>', <?php echo $parent;?>, 1);" 
								class="clico ico-down transparent" style="padding:2px 0 0 15px;" title="<?php echo lang('move down')?>">&nbsp;</span>
								
							<span id="<?php echo $genid?>order_<?php echo $d_id?>_<?php echo $m_id;?>" style="padding-left:<?php echo 40 + $indent?>px;" title="<?php echo lang('current order')?>">
								<?php echo (isset($restrictions[$m_id]) ? $restrictions[$m_id] : '0')?>
							</span>
						
						</span>
						
						<input type="hidden" name="restricted_members[<?php echo $d_id?>][<?php echo $m_id;?>][order_num]" id="<?php echo $genid?>order_num_<?php echo $d_id?>_<?php echo $m_id;?>" 
							value="<?php echo (isset($restrictions[$m_id]) ? $restrictions[$m_id] : '0')?>" />
<?php 			} else { ?>
						<span id="<?php echo $genid?>order_controls_<?php echo $d_id?>_<?php echo $m_id;?> class="desc" style="display:none;"><?php echo lang('not orderable')?></span>
<?php 			} ?>
					</td>
				</tr>
<?php		} 
		} ?>
			</table>
			
			<div style="margin-top:10px;"><a class="internalLink db-ico ico-add" style="padding:3px 0 0 20px;" href="#" 
				onclick="og.openLink(og.getUrl('member','add',{dim_id:<?php echo $d_id?>, rest_genid: '<?php echo $genid?>'}), {caller:'new member'});">
				<?php echo lang('add member to this dimension')?>
			</a></div>
		</div>
	</fieldset>
<?php
	}
} else { ?>
	<div class="desc"><?php echo lang('no restrictions can be defined for this type of member in this dimension')?></div>
<?php }
?>
<script>

</script>