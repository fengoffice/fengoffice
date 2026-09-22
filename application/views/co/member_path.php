<?php
	$dimensions_info = array();
	$hidden_dim_ids = array();
	
	$enabled_dimensions = config_option('enabled_dimensions');
	$dimensions = Dimensions::instance()->findAll(array('conditions' => 'id IN ('.implode(',',$enabled_dimensions).') '));
	foreach ($dimensions as $dimension) {
		if (in_array($dimension->getCode(), array('feng_users', 'feng_persons'))) continue;
		
		$hook_return = null;
		Hook::fire("hidden_breadcrumbs", array('ot_id' => $object->getObjectTypeId(), 'dim_id' => $dimension->getId()), $hook_return);
		if (!is_null($hook_return) && array_var($hook_return, 'hidden')) {
			$hidden_dim_ids[] = $dimension->getId();
			continue;
		}
		
		if (!isset($dimensions_info[$dimension->getName()])) {
			$dimensions_info[$dimension->getName()] = array(
				'id' => $dimension->getId(),
				'members' => array(),
				'total_count' => 0,
				'displayed_count' => 0
			);
		}
	}
	
	$breadcrumb_member_count = user_config_option('breadcrumb_member_count');
	if (!$breadcrumb_member_count) $breadcrumb_member_count = 3;

	// Get members with limit
	$dimensions_info = $object->getMembersWithLimit($breadcrumb_member_count);

	// Apply filters and permissions to the dimensions_info
	foreach ($dimensions_info as $dim_name => &$dim_info) {
		$dimension = Dimensions::instance()->findById($dim_info['id']);
		if (!$dimension) continue;

		$dim_info['code'] = $dimension->getCode();

		// Apply existing filters
		if (in_array($dimension->getCode(), array('feng_users', 'feng_persons')) || !in_array($dimension->getId(), $enabled_dimensions) 
				 || in_array($dimension->getId(), $hidden_dim_ids)) {
			unset($dimensions_info[$dim_name]);
			continue;
		}

		// Check if dimension selector is hidden
		$hidden_dim_selector = false;
		if (Plugins::instance()->isActivePlugin('advanced_core')) {
			$hidden_dim_selector = DimensionContentObjectOptions::getOptionValue($dimension->getId(), $object->getObjectTypeId(), 'hide_member_selector_in_forms');
		}
		if ($hidden_dim_selector) {
			unset($dimensions_info[$dim_name]);
			continue;
		}

		// Set icon if not already set
		if (!isset($dim_info['icon'])) {
			// Try to get icon from displayed members
			foreach ($dim_info['members'] as $member_data) {
				$member = Members::instance()->findById($member_data['id']);
				if ($member) {
					$dim_info['icon'] = $member->getIconClass();
					break;
				}
			}
		}

		if (count($dim_info['members']) > 0) {
			$has_member_related = true;
		}
	}

	foreach ($dimensions_info as &$dim_info) {
		if (!isset($dim_info['icon'])) {
			$dots = DimensionObjectTypes::instance()->findAll(array('conditions' => 'dimension_id = '.$dim_info['id']));
			if (count($dots) > 0) {
				$ot = ObjectTypes::instance()->findById($dots[0]->getObjectTypeId());
				if ($ot instanceof ObjectType) $dim_info['icon'] = $ot->getIconClass();
			}
		}
	}
	
	$width_style = "width:100%;";// ($object instanceof ProjectTask || $object instanceof TemplateTask) ? "width:100%;" : "width:100%;";
	
	if (count($dimensions_info) > 0) {
		ksort($dimensions_info, SORT_STRING);
?>

<?php if (isset($has_member_related)){ ?>
<div class="clear"></div>
<div class="commentsTitle"><?php echo lang('related to')?></div>
<?php } ?>
	<div style="padding-bottom: 10px;">
	<div style="<?php echo $width_style?> float: left; overflow: hidden;" class="object-view-member-path-container">
	
		<table style="width:100%;">
<?php
		$gid = gen_id();
		$member_path = $object->getMembersIdsToDisplayPath();
        foreach ($dimensions_info as $dname => $dinfo) {
		    if (count($dinfo['members']) > 0){
		        
    			$dim_name = $dname;
    			Hook::fire("edit_dimension_name", array('dimension' => $dinfo['id']), $dim_name);
    			?>
    			<tr class="member-path-dim-block">
    				<td style="width: 200px; height:25px;">
    					<div class="dname coViewAction <?php echo array_var($dinfo, 'icon')?>">
							<?php echo $dim_name; ?>
							&nbsp;
							<a href="javascript:void(0)"
								onclick="showAllMembersModal('<?php echo $dinfo['id']?>', '<?php echo addslashes($dim_name)?>', <?php echo $dinfo['total_count']?>, '<?php echo addslashes($dinfo['code'] ?? '')?>')">
								(<?php echo $dinfo['total_count']?>)
							</a>
						</div>
    				</td>
    				<td>
    			<?php 
    			if (array_var($member_path, $dinfo['id'])) {
    				$dim_mem_path = array($dinfo['id'] => array_var($member_path, $dinfo['id']));
    				foreach ($dim_mem_path as $otid => &$otpath) {
    					if (isset($otpath['is_assoc_dim'])) unset($otpath['is_assoc_dim']);
    				}
    		    ?>
    					<div class='breadcrumb-container' style='max-width:800px; width:100%;' id="<?php echo $gid?>-breadcrumb-container-<?php echo $dinfo['id']?>">
    						<script>
    						
    							var dim_mem_path = '<?php echo json_encode($dim_mem_path)?>';
    							var mpath = null;
    							if (dim_mem_path){
    								mpath = Ext.util.JSON.decode(dim_mem_path);
    							}
    							var mem_path = "";			
    							if (mpath){
    								mem_path = og.getEmptyCrumbHtml(mpath, '.breadcrumb-container', null, null, null, true);
    							}
    							$("#<?php echo $gid?>-breadcrumb-container-<?php echo $dinfo['id']?>").html(mem_path);
    						
    						</script>
    					</div>
    				</td>
    			</tr>
    		<?php
    			}
    			
    		//	$ret=null; Hook::fire('object_view_member_path_dims', $object, $ret);
		    }
		}
		?>
		</table>
		<?php
		
	?></div>
	
	
	</div>
	<div class="clear"></div>
		
	
	<script>
	var memberPathObjectId = <?php echo $object->getId(); ?>;

	$(function() {
		// set max breadcrumb width
		<?php foreach ($dimensions_info as $dname => $dinfo) { ?>
			$("#<?php echo $gid?>-breadcrumb-container-<?php echo $dinfo['id']?>").css('max-width', ($("#<?php echo $gid?>-breadcrumb-container-<?php echo $dinfo['id']?>").parent().width()-10)+'px');
		<?php } ?>
		// draw breadcrumbs
		og.eventManager.fireEvent('replace all empty breadcrumb', null);
	});

	function showAllMembersModal(dimensionId, dimensionName, totalCount, dimensionCode) {
		var treeId = Ext.id() + '-tree';

		// Build modal DOM
		var wrap = document.createElement('div');
		wrap.className = 'member-tree-modal-wrap';
		wrap.innerHTML =
			'<div class="member-tree-modal-header">' +
				'<span class="member-tree-modal-title">' + memberTreeHtmlEncode(dimensionName) + '</span>' +
				'<span class="member-tree-modal-count">(' + totalCount + ')</span>' +
			'</div>' +
			'<div id="' + treeId + '" class="member-tree-modal-body">' +
				'<div class="member-tree-loading">' + lang('loading') + '...</div>' +
			'</div>';

		$.modal(wrap, {
			escClose:     true,
			overlayClose: true,
			minWidth:     520,
			closeHTML:    '<a class="modal-close simplemodal-close"><i class="icon-circle-x"></i></a>',
			position:     [80, null]
		});

		// Fetch tree data via AJAX
		og.openLink(og.getUrl('object', 'get_object_members_tree', {
			object_id:    memberPathObjectId,
			dimension_id: dimensionId
		}), {
			hideLoading: true,
			silent:      true,
			callback: function(success, data) {
				var $tree = $('#' + treeId);
				if (success && data && data.members_tree) {
					var html = renderMembersTree(data.members_tree, dimensionCode);
					$tree.html(html || '');
				}
				// Close modal when the user navigates to a member
				$tree.on('click', 'a.member-tree-link', function() {
					setTimeout($.modal.close, 100);
				});
			}
		});
	}

	// Safe HTML encoder – avoids Ext.util.Format.htmlEncode's falsy-passthrough quirk
	function memberTreeHtmlEncode(str) {
		return String(str == null ? '' : str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	// Mirrors the ObjectBreadcrumbs.js lookup so every object type
	function getMemberOnclick(node, dimensionCode) {
		var ot = node.object_type_id;
		if (ot && og.additional_on_dimension_object_click && og.additional_on_dimension_object_click[ot]) {
			return og.additional_on_dimension_object_click[ot].replace('<parameters>', node.id) + ' return false;';
		}
		return "og.memberTreeExternalClick('" + dimensionCode + "', " + node.id + "); return false;";
	}

	function renderMembersTree(nodes, dimensionCode) {
		if (!nodes || nodes.length === 0) return '';
		var html = '<ul class="member-tree-list">';
		for (var i = 0; i < nodes.length; i++) {
			var node = nodes[i];
			var hasChildren = node.children && node.children.length > 0;
			var childrenId = 'mtc-' + node.id;
			var label = memberTreeHtmlEncode(node.name);
			var toggleHtml, linkHtml;

			// Toggle arrow for parents, spacer for leaves
			if (hasChildren) {
				toggleHtml = '<span class="member-tree-toggle" ' +
					'onclick="memberTreeToggle(this,\'' + childrenId + '\')" ' +
					'title="' + lang('expand') + '">&#9658;</span>';
			} else {
				toggleHtml = '<span class="member-tree-spacer"></span>';
			}

			var colorDot = node.color >= 0
				? '<span class="member-color-dot og-wsname-color-' + node.color + '"></span>'
				: '';
			var cssClass = 'member-tree-link ' + (node.is_direct ? 'direct' : 'ancestor');
			linkHtml = colorDot +
				'<a class="' + cssClass + '" href="#" ' +
				'onclick="' + getMemberOnclick(node, dimensionCode) + '">' +
				label + '</a>';

			html += '<li class="member-tree-list-item">' + toggleHtml + linkHtml;

			if (hasChildren) {
				html += '<div class="member-tree-children collapsed" id="' + childrenId + '">';
				html += renderMembersTree(node.children, dimensionCode);
				html += '</div>';
			}

			html += '</li>';
		}
		html += '</ul>';
		return html;
	}

	function memberTreeToggle(toggleEl, childrenId) {
		var $toggle = $(toggleEl);
		var $children = $('#' + childrenId);
		var expanding = $children.hasClass('collapsed');
		$children.toggleClass('collapsed', !expanding);
		$toggle.toggleClass('expanded', expanding);
		$toggle.attr('title', expanding ? lang('collapse') : lang('expand'));
	}
	</script>
	<?php
	}