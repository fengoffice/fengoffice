<?php
	$dimensions_info = array();
	
	$dimensions = Dimensions::findAll();
	foreach ($dimensions as $dimension) {
		if (in_array($dimension->getCode(), array('feng_users', 'feng_persons'))) continue;
		if (!isset($dimensions_info[$dimension->getName()])) {
			$dimensions_info[$dimension->getName()] = array('id' => $dimension->getId(), 'members' => array());
		}
	}
	
	$members = $object->getMembers();
	foreach ($members as $member) {
		/* @var $member Member */
		$dimension = $member->getDimension();
		if (in_array($dimension->getCode(), array('feng_users', 'feng_persons'))) continue;
		
		$obj_is_user = $object instanceof Contact && $object->isUser();
		
		if ($dimension->getDefinesPermissions() && !$obj_is_user && !can_read(logged_user(), array($member), $object->getObjectTypeId())) continue;
		
		if (!isset($dimensions_info[$dimension->getName()])) {
			$dimensions_info[$dimension->getName()] = array('members' => array(), 'icon' => $member->getIconClass());
		}
		if (!isset($dimensions_info[$dimension->getName()]['icon'])) {
			$dimensions_info[$dimension->getName()]['icon'] = $member->getIconClass();
		}
		$parents = array_reverse($member->getAllParentMembersInHierarchy(true));
		foreach ($parents as $p) {
			$dimensions_info[$dimension->getName()]['members'][$p->getId()] = array('p' => $p->getParentMemberId(), 'name' => $p->getName(), 'ot' => $p->getObjectTypeId(), 'color' => $p->getMemberColor());
		}
	}
	
	foreach ($dimensions_info as &$dim_info) {
		if (!isset($dim_info['icon'])) {
			$dots = DimensionObjectTypes::findAll(array('conditions' => 'dimension_id = '.$dim_info['id']));
			if (count($dots) > 0) {
				$ot = ObjectTypes::findById($dots[0]->getObjectTypeId());
				if ($ot instanceof ObjectType) $dim_info['icon'] = $ot->getIconClass();
			}
		}
	}
	
	$breadcrumb_member_count = user_config_option('breadcrumb_member_count');
	if (!$breadcrumb_member_count) $breadcrumb_member_count = 5;
	
	$width_style = ($object instanceof ProjectTask || $object instanceof TemplateTask) ? "width:50%;" : "";
	
	if (count($dimensions_info) > 0) {
		ksort($dimensions_info, SORT_STRING);
?>
<div class="commentsTitle"><?php echo lang('related to')?></div>
	<div style="padding-bottom: 10px;">
	<div style="<?php echo $width_style?> float: left;">
<?php
		foreach ($dimensions_info as $dname => $dinfo) {
			$dim_name = $dname;
			Hook::fire("edit_dimension_name", array('dimension' => $dinfo['id']), $dim_name);
			
			?><div class="member-path-dim-block">
				<span class="dname coViewAction <?php echo array_var($dinfo, 'icon')?>"><?php echo $dim_name?>:&nbsp;</span>
		<?php
			$breadcrumb_count = 1;
			if (count($dinfo['members']) == 0) {
				echo '<span class="desc">' . lang('not related') . '</span>';
			} else {
				$first = true;
				foreach ($dinfo['members'] as $mid => $minfo) {
					
					$color_cls = ' og-wsname-color-' . $minfo['color'];
					
					if (!$first) {
						$breadcrumb_count++;
						if ($breadcrumb_count > $breadcrumb_member_count) {
							break;
						}
						if ($minfo['p'] == 0) echo "&nbsp;&#45;&nbsp;"; // " - " same level member separator
						else echo ($minfo['color'] >= 0 ? "" : "/"); // submember separator
					}
					
					echo '<span class="mname'.$color_cls.'">'; ?>
					
					<a href="#" onclick="if (og.additional_on_dimension_object_click[<?php echo $minfo['ot']?>]) 
						eval(og.additional_on_dimension_object_click[<?php echo $minfo['ot']?>].replace('<parameters>', <?php echo $mid ?>))">
					<?php echo $minfo['name']?>
					</a>
					
					<?php
					echo '</span>';
					
					$first = false;
				}
			}
			if ($breadcrumb_member_count < count($dinfo['members'])) {				
				echo '<span class="desc">&nbsp;' . lang('and xx more', count($dinfo['members']) - $breadcrumb_member_count) . '</span>';
			}
		?></div><?php
		}
		
	?></div>
	<?php 
		if($object instanceof ProjectTask || $object instanceof TemplateTask) {
		?><div style="width:50%; float: left; "><?php 
			
			$task_list = $object;
			//milestone
			if (isset($milestone)){
				echo $milestone;
			}
			
			//parent
			if (isset($parentInf)){
				echo $parentInf;
			}
				
		}
		?></div><?php 
		?>
	
	
	</div>
	<div class="clear"></div>
		
	
	
	
	<?php
	}