<?php
$genid = gen_id();
?>

<div class="system-modules-container">
	
	<div class="title">
		<div class="titletext"><?php echo lang('modules and dimensions')?></div>
		<button title="<?php echo lang('close')?>" style="float:left; margin: -10px 0 0 10px;" class="add-first-btn" onclick="og.save_system_modules_changes(this)">
			<img src="public/assets/themes/default/images/layout/close16.png">&nbsp;<?php echo lang('close')?>
		</button>
		<div class="clear"></div>
	</div>
	
	<div class="system-modules-section">
		<h1><?php echo lang('modules')?></h1>
		<div class="description"><?php echo lang('edit system modules description')?></div>
		<div class="section-content section1">
			<ul id="sortable-modules"><?php
		$number = 1;
		foreach ($modules as $module) {?>
				<li class="module sortable<?php echo $module['enabled'] ? ' active' : ''?>" id="<?php echo $module['id']?>">
					<div class="module-order"><?php echo $number?></div>
					<div class="coViewIconImage <?php echo $module['ico']?>" style=""></div>
			    	<div class="module-name"><?php echo $module['name'] ?></div>
			    	<div class="module-enabled-div big-checkbox greencheck">
						<?php echo checkbox_field($module['id'].'_enabled', $module['enabled'], array('onchange' => 'og.enable_disable_system_module(this);', 'id' => $genid.$module['id']."_checkbox"))?>
						<label for="big-checkbox greencheck"></label>
					</div>
				</li>
		<?php
			$number++; 
		} ?>
			</ul>
			<ul>
		<?php
		foreach ($other_modules as $module) {?>
				<li class="module not-sortable<?php echo $module['enabled'] ? ' active' : ''?>" id="<?php echo $module['id']?>">
					<div class="coViewIconImage <?php echo $module['ico']?>" style=""></div>
			    	<div class="module-name"><?php echo $module['name'] ?></div>
			    	<div class="module-enabled-div big-checkbox greencheck">
						<?php echo checkbox_field($module['id'].'_enabled', $module['enabled'], array('onchange' => 'og.enable_disable_plugin(this);', 'id' => $genid.$module['id']."_checkbox"))?>
						<label for="big-checkbox greencheck"></label>
					</div>
				</li><?php
		} ?>
			</ul>
			<ul>
		<?php
		foreach ($disabled_modules as $module) {?>
				<li class="module disabled" id="<?php echo $module['id']?>">
				  <a href="<?php echo array_var($module, 'link', 'http://www.fengoffice.com/web/pricing_sky.php');?>" target="_blank" class="disabled_link"><div style="height:72px;">
					<div class="coViewIconImage <?php echo $module['ico']?>" style=""></div>
			    	<div class="module-name"><?php echo $module['name'] ?></div>
			    	<div class="module-enabled-div big-checkbox greencheck">
			    		<?php echo checkbox_field($module['id'].'_enabled', false, array('disabled' => 'disabled'))?>
						<label for="big-checkbox greencheck"></label>
					</div>
				  </div></a>
				</li><?php
		} ?>
			</ul>
			<?php
		?></div>
		<div class="clear"></div>
		
	</div>
	

	
	<div class="system-modules-section">
		<h1><?php echo lang('dimensions')?></h1>
		<div class="description"><?php echo lang('edit dimensions description')?></div>
		<div class="section-content section2" id="dimensions-container">
			<ul id="sortable-dimensions">
		<?php
		$number = 1;
		foreach ($active_dimensions as $dim) {?>
				<li class="dimension module<?php echo in_array($dim['id'], $user_dimension_ids) ? ' active' : ''?>" id="<?php echo $dim['id']?>">
					<div class="dimension-order"><?php echo $number?></div>
					<div class="coViewIconImage <?php echo $dim['ico']?>" style=""></div>
			    	<div class="module-name"><?php echo $dim['name'] ?></div>
			    	<div class="module-enabled-div big-checkbox greencheck">
			    		<?php echo checkbox_field($dim['id'].'_enabled', in_array($dim['id'], $user_dimension_ids), array('onchange' => 'og.enable_disable_dimension(this);', 'id' => $genid.$dim['id']."_dim_checkbox"))?>
						<label for="big-checkbox greencheck"></label>
			    	</div>
				</li><?php
			$number++;
		} ?>
			</ul>
			<ul>
		<?php
		foreach ($other_dimensions as $dim) {?>
				<li class="dimension module disabled"><a href="http://www.fengoffice.com/web/pricing_sky.php" target="_blank">
					<div class="coViewIconImage <?php echo $dim['ico']?>" style=""></div>
			    	<div class="module-name"><?php echo $dim['name'] ?></div>
			    	<div class="module-enabled-div big-checkbox greencheck">
			    		<?php echo checkbox_field($dim['name'].'_enabled', false, array('disabled' => 'disabled'))?>
						<label for="big-checkbox greencheck"></label>
			    	</div>
				</a></li><?php
		} ?>
			</ul>
		</div>
	</div>
</div>
<script>
	// button handlers
	og.save_system_modules_changes = function(btn) {
		var enabled_dimensions = [];
		var inputs = $("#dimensions-container .module-enabled-div input:checkbox");
		for (var x=0; x<inputs.length; x++) {
			var id = inputs[x].name.substring(0, inputs[x].name.indexOf('_enabled'));
			if (inputs[x].checked) {
				enabled_dimensions.push(id);
			}
		}
		
		if (enabled_dimensions.length == 0) {
			
			og.err(lang('at least one dimension must be selected'));
			return;
			
		} else {
			
			og.openLink(og.getUrl('more', 'set_getting_started_step', {'step': 2}), {
				callback: function(success, data) {
					if (og.must_reload_system_modules || og.must_reload_dimensions) {
						window.location.href='<?php echo ROOT_URL ?>';
					} else {
						og.goback(btn);
					}
				}
			});
		}
	}

	og.cancel_system_modules_changes = function(btn) {
		if (og.must_reload_system_modules) {
			og.ordered_modules = og.original_ordered_modules;
			og.update_system_module_order();
	
			og.openLink(og.getUrl('more', 'enable_disable_system_modules', {modules: Ext.util.JSON.encode(og.original_module_status)}));
		}
		if (og.must_reload_dimensions) {
			og.ordered_dimensions = og.original_ordered_dimensions;
			og.update_dimensions_order();
			
			var status_array = {};
			for (var x=0; x<og.original_dimension_status.length; x++) {
				status_array[og.original_dimension_status[x]] = 1;
			}
			og.openLink(og.getUrl('more', 'enable_disable_dimensions', {dims: Ext.util.JSON.encode(status_array)}));
		}
		og.goback(btn);
	}

	// plugin activation
	og.enable_disable_plugin = function(checkbox) {
		var plugin_name = checkbox.name.substring(0, checkbox.name.indexOf('_enabled'));
		og.openLink(og.getUrl('more', 'enable_disable_plugin', {plugin: plugin_name, enabled: checkbox.checked ? 1 : 0}), {
			hideLoading: true,
			callback: function(success, data) {
				if (data.ok) {
					og.must_reload_system_modules = true;
					if (checkbox.checked) {
						$(checkbox).closest('.module').addClass('active');
					} else {
						$(checkbox).closest('.module').removeClass('active');
					}
				}
			}
		});
	}

	// system modules
	og.enable_disable_system_module = function(checkbox) {
		var id = checkbox.name.substring(0, checkbox.name.indexOf('_enabled'));
		og.openLink(og.getUrl('more', 'enable_disable_system_module', {module: id, enabled: checkbox.checked ? 1 : 0}), {
			hideLoading: true,
			callback: function(success, data) {
				if (data.ok) {
					og.must_reload_system_modules = true;
					if (checkbox.checked) {
						$(checkbox).closest('.module').addClass('active');
					} else {
						$(checkbox).closest('.module').removeClass('active');
					}
				}
			}
		});
	}

	og.ordered_modules = [];
	og.order_modules = function() {
		og.ordered_modules = [];
		var module_list = $("#sortable-modules .module");
		for (var i=0; i<module_list.length; i++) {
			og.ordered_modules.push(module_list[i].id);
			$("#"+module_list[i].id + " .module-order").html(i+1);
		}
	}

	og.update_system_module_order = function() {
		og.openLink(og.getUrl('more', 'update_system_module_order', {modules: Ext.util.JSON.encode(og.ordered_modules)}), {
			hideLoading: true,
			callback: function(success, data) {
				if (data.ok) {
					og.must_reload_system_modules = true;
				}
			}
		});
	}

	// dimensions
	og.enable_disable_dimension = function(checkbox) {
		var dimensions = {};
		var inputs = $("#dimensions-container .module-enabled-div input:checkbox");
		for (var x=0; x<inputs.length; x++) {
			var id = inputs[x].name.substring(0, inputs[x].name.indexOf('_enabled'));
			dimensions[id] = inputs[x].checked ? 1 : 0;
		}
		og.openLink(og.getUrl('more', 'enable_disable_dimensions', {dims: Ext.util.JSON.encode(dimensions)}), {
			hideLoading: true,
			callback: function(success, data) {
				if (data.ok) {
					og.must_reload_dimensions = true;
					if (checkbox.checked) {
						$(checkbox).closest('.module').addClass('active');
					} else {
						$(checkbox).closest('.module').removeClass('active');
					}
				}
			}
		});
	}

	og.ordered_dimensions = [];
	og.order_dimensions = function() {
		og.ordered_dimensions = [];
		var dim_list = $("#sortable-dimensions .module");
		for (var i=0; i<dim_list.length; i++) {
			og.ordered_dimensions.push(dim_list[i].id);
			$("#"+dim_list[i].id + " .dimension-order").html(i+1);
		}
	}

	og.update_dimensions_order = function() {
		og.openLink(og.getUrl('more', 'update_dimension_order', {dims: Ext.util.JSON.encode(og.ordered_dimensions)}), {
			hideLoading: true,
			callback: function(success, data) {
				if (data.ok) {
					og.must_reload_dimensions = true;
				}
			}
		});
	}

	// init
	$(function() {
		og.must_reload_system_modules = false;
		og.must_reload_dimensions = false;
		
		og.order_modules();
		og.original_ordered_modules = og.ordered_modules;
		og.original_dimension_status = Ext.util.JSON.decode('<?php echo json_encode(config_option('enabled_dimensions'))?>');

		og.module_hints = {};
		og.original_module_status = {};
		<?php foreach ($modules as $module) { ?>
			og.original_module_status['<?php echo $module['id']?>'] = <?php echo $module['enabled'] ? '1' : '0'?>;
			og.module_hints['<?php echo $module['id']?>'] = '<?php echo $module['hint']?>';
		<?php } ?>
		
		<?php foreach ($disabled_modules as $module) { ?>
			og.module_hints['<?php echo $module['id']?>'] = '<?php echo $module['hint']?>';
		<?php } ?>
		<?php foreach ($other_modules as $module) { ?>
			og.module_hints['<?php echo $module['id']?>'] = '<?php echo $module['hint']?>';
		<?php } ?>

		og.dimension_hints = {};
		<?php foreach ($active_dimensions as $dim) { ?>
			og.dimension_hints['<?php echo $dim['id']?>'] = '<?php echo $dim['hint']?>';
		<?php } ?>
		<?php foreach ($other_dimensions as $dim) { ?>
			og.dimension_hints['<?php echo $dim['id']?>'] = '<?php echo $dim['hint']?>';
		<?php } ?>

		og.order_dimensions();
		og.original_ordered_dimensions = og.ordered_dimensions;

		og.dragging_object_ids = {};
		
		$( "#sortable-modules" ).sortable({
			start: function(event, object) {
				og.dragging_object_ids[object.item[0].id] = true;
			},
			stop: function(event, object) {
				og.order_modules();
				og.update_system_module_order();
			}
		});
		$( "#sortable-modules" ).disableSelection();

		$( "#sortable-dimensions" ).sortable({
			start: function(event, object) {
				og.dragging_object_ids['dim_' + object.item[0].id] = true;
			},
			stop: function(event, object) {
				og.order_dimensions();
				og.update_dimensions_order();
			}
		});
		$( "#sortable-dimensions" ).disableSelection();

		$(".system-modules-container").parent().css('backgroundColor', 'white');

		<?php foreach ($modules as $module) { ?>
			$(".system-modules-section #<?php echo $module['id']?>").popover({
				content: og.module_hints['<?php echo $module['id']?>'],
				delay: { show: "100", hide: "200" },
				trigger: 'hover'
			});
		<?php } ?>
		<?php foreach ($disabled_modules as $module) { ?>
			$(".system-modules-section #<?php echo $module['id']?>").popover({
				content: og.module_hints['<?php echo $module['id']?>'],
				delay: { show: "100", hide: "200" },
				trigger: 'hover'
			});
		<?php } ?>
		<?php foreach ($other_modules as $module) {
				if (!is_array($dim) || !isset($dim['id'])) continue; 
		?>
			$(".system-modules-section #<?php echo $module['id']?>").popover({
				content: og.module_hints['<?php echo $module['id']?>'],
				delay: { show: "100", hide: "200" },
				trigger: 'hover'
			});
		<?php } ?>

		<?php foreach ($active_dimensions as $dim) {
				if (!is_array($dim) || !isset($dim['id'])) continue; 
		?>
			$(".system-modules-section #dimensions-container #<?php echo $dim['id']?>").popover({
				content: og.dimension_hints['<?php echo $dim['id']?>'],
				delay: { show: "100", hide: "200" },
				trigger: 'hover'
			});
		<?php } ?>
		<?php foreach ($other_dimensions as $dim) {
				if (!is_array($dim) || !isset($dim['id'])) continue; 
		?>
			$(".system-modules-section #dimensions-container #<?php echo $dim['id']?>").popover({
				content: og.dimension_hints['<?php echo $dim['id']?>'],
				delay: { show: "100", hide: "200" },
				trigger: 'hover'
			});
		<?php } ?>

		$(".system-modules-section .module.sortable").click(function() {
			var checkbox = $("#<?php echo $genid?>" + this.id + "_checkbox");
			if (checkbox.length > 0 && !og.dragging_object_ids[this.id]) {
				checkbox.attr('checked', checkbox.attr('checked') ? null : 'checked');
				og.enable_disable_system_module(checkbox[0]);
			} else {
				og.dragging_object_ids[this.id] = false;
			}
		});

		$(".system-modules-section .module.not-sortable").click(function() {
			var checkbox = $("#<?php echo $genid?>" + this.id + "_checkbox");
			if (checkbox.length > 0 && !og.dragging_object_ids[this.id]) {
				checkbox.attr('checked', checkbox.attr('checked') ? null : 'checked');
				og.enable_disable_plugin(checkbox[0]);
			} else {
				og.dragging_object_ids[this.id] = false;
			}
		});


		$(".system-modules-section .dimension.module").click(function() {
			var checkbox = $("#<?php echo $genid?>" + this.id + "_dim_checkbox");
			if (checkbox.length > 0 && !og.dragging_object_ids['dim_' + this.id]) {
				checkbox.attr('checked', checkbox.attr('checked') ? null : 'checked');
				og.enable_disable_dimension(checkbox[0]);
			} else {
				og.dragging_object_ids['dim_' + this.id] = false;
			}
		});
		
	});
</script>