<div style="padding:10px">
<table id="dashTableActivity" style="width:100%;min-width:500px;">
<?php
 
	$genid = gen_id();
	$c = 0;

	$ws = active_project();
	
	if ($ws instanceof Project ) {
		$sub_wss = $ws->getSubWorkspacesSorted(true, logged_user());
	} else {
		$sub_wss = array();
		$parent = 0;
		$all_ws = logged_user()->getWorkspaces(true);
		if (!is_array($all_ws)) $all_ws = array();
		
		$wsset = array();
		foreach ($all_ws as $w) {
			$wsset[$w->getId()] = true;
		}
		foreach ($all_ws as $w) {
			$tempParent = $w->getParentId();
			$x = $w;
			while ($x instanceof Project && !isset($wsset[$tempParent])) {
				$tempParent = $x->getParentId();
				$ant = $x;
				$x = $x->getParentWorkspace();
			}
			if (!$x instanceof Project) {
				$tempParent = 0;
				$sub_wss[] = $w;
			}
		}
	}

	
	$sub_wss_csv = array();
	foreach ($sub_wss as $sub_ws) $sub_wss_csv[] = $sub_ws->getId();
	
	$linked_object_actions = array();
	
	//FIXME $activities = ApplicationLogs::getLastActivities($ws, active_tag(), user_config_option('activity widget elements'));
	$groups = array();
	$first = null;
		
	foreach ($activities as $act) {
		$user = Users::findById($act->getCreatedById());
		$object = Objects::findObject($act->getRelObjectId());
		if (!$user || !$object) continue;
/*		if ($user && $object && $act->getAction() != 'login' && $act->getAction() != 'logout' 
			&& !can_access($user, $object, ACCESS_LEVEL_READ)) continue;
*/
		$avatar_url = $user->getPictureUrl();
		$date = $act->getCreatedOn() instanceof DateTimeValue ? friendly_date($act->getCreatedOn()) : lang('n/a');
		
		$dontshow = false;
		$tmp_id = '';
		if ($act->getAction() == ApplicationLogs::ACTION_LINK || $act->getAction() == ApplicationLogs::ACTION_UNLINK) {
			$tmp_id = $act->getRelObjectManager().":".$act->getRelObjectId();
			foreach ($linked_object_actions as $loa) {
				if ($loa['action'] == $act->getAction() && ($loa['source'] == $tmp_id && $loa['dest'] == $act->getLogData() || $loa['source'] == $act->getLogData() && $loa['dest'] == $tmp_id)) {
					$dontshow = true;
					break;
				}
			}
		}
		if ($dontshow) continue; // to prevent showing the linked objects two times
		$linked_object_actions[] = array('action' => $act->getAction(), 'source' => $tmp_id, 'dest' => $act->getLogData());
		
		$activity_data = $act->getActivityData();
		
		$act_data = array('avatar' => $avatar_url, 'date' => $date, 'act_data' => $activity_data);
		
		if ($act->getRelObjectManager() != 'Comments') {
			$obj_wss = WorkspaceObjects::getWorkspacesByObject($act->getRelObjectManager(), $act->getRelObjectId());
		} else {
			$obj_wss = WorkspaceObjects::getWorkspacesByObject(get_class($object->getObject()->manager()), $object->getObject()->getId());
		}
		
		$object_ws = null;
		$break = false;
		foreach ($obj_wss as $obj_ws) {
			if (in_array($obj_ws->getId(), $sub_wss_csv)) {
				$object_ws = $obj_ws;
				$break = true;
			} else {
				$parent = $obj_ws->getParentWorkspace();
				while ($parent) {
					if (in_array($parent, $sub_wss)) {
						$object_ws = $parent;
						$break = true;
						break;
					}
					$parent = $parent->getParentWorkspace();
				}
			}
			if ($break) break;
		}
		
		if ($object_ws) {
			$group_id = $object_ws->getId();
			$group_name = '<a href="#" onclick="Ext.getCmp(\'workspace-panel\').select('. $group_id .')"><img src="'. image_url('16x16/wscolors/color') . $object_ws->getColor() . '.png" />&nbsp;' . $object_ws->getName() .'</a>';
		} else {
			$group_id = 0;
			$group_name = 'nosubgroup';
		}
		
		if ($group_id == 0) {
			if ($first == null) $first = array('name' => $group_name, 'type' => 'ws', 'activities' => array($act_data));
			else $first['activities'][] = $act_data;
		} else {
			if (isset($groups[$group_id])) {
				$groups[$group_id]['activities'][] = $act_data;
			} else {
				$groups[$group_id] = array('name' => $group_name, 'type' => 'ws', 'activities' => array($act_data));
			}
		}
	}
	if ($first) array_unshift($groups, $first);
	
	
	foreach ($groups as $id => $gr) {
		if ($gr['name'] != 'nosubgroup') {
			?><tr><td colspan="1" class="groupTitle" style="padding:20px 0 5px;">
				<?php echo $gr['name'] ?>
			</td><td align="center" class="groupTitle" style="padding:20px 0 5px; width: 20px;" onclick="og.showHideActivityGroup('<?php echo $genid + $id?>')">
				<span id="hidelnk<?php echo $genid + $id?>" style="cursor:pointer;" title="<?php echo lang('hide') ?>">-</span>
				<span id="showlnk<?php echo $genid + $id?>" style="cursor:pointer; display:none;" title="<?php echo lang('show') ?>">+</span>
			</td></tr>
			
			<?php
		}
		?><tr ><td colspan="2"><div id="<?php echo $genid + $id?>" style="width: 100%">
			<table style="width:100%;" cellpadding="0" cellspacing="0"><?php
		foreach ($gr['activities'] as $act_data) {
			$c++;
		?>
		<tr class="<?php echo $c % 2 == 1? '':'dashAltRow';?>">
		<td style="width:32px"><img class="avatar" src="<?php echo $act_data['avatar'] ?>" /></td>
		<td style="padding-left:10px">
			<table cellpadding="0" cellspacing="0" style="width:100%;"><tr><td style="height: 17px;">
				<div><?php echo $act_data['act_data'] ?></div>
			</td></tr><tr><td style="padding-bottom:3px;">
				<div class="desc"><?php echo $act_data['date'] ?></div>
			</td></tr></table>
		</td>
		</tr>
	<?php } // foreach?>
		</table></div></td></tr>
<?php } // foreach?>
</table>
</div>
<script>
	og.showHideActivityGroup = function(id) {
		og.showHide(id);
		og.showHide('hidelnk' + id);
		og.showHide('showlnk' + id);
	}
	if (document.getElementById('dashTableActivity').offsetWidth < 500) {
		document.getElementById('dashTableActivity').style.width = '500px';
	}
</script>