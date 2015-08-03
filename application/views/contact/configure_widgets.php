<?php 
$genid = gen_id();
if (!isset($default_configuration)) $default_configuration = false;
$form_url = $default_configuration ? get_url('config', 'configure_widgets_default_submit') : get_url("contact", "configure_widgets_submit");
$title = $default_configuration ? lang('default dashboard options') : lang('dashboard options');
?>
<div id="<?php echo $genid ?>adminContainer" class="adminGroups" style="height:100%;background-color:white">
<form method="post" action="<?php echo $form_url; ?>">
<div class="coInputHeader">
  <div>
	<div class="coInputName">
		<div class="coInputTitle">
			<?php echo $title ?>
		</div>
	</div>
	<div class="coInputButtons">
		<?php echo submit_button(lang('save'), 's', array('style' => 'margin-top:0px;')) ?>
	</div>
	<div class="clear"></div>
  </div>
</div>

<div class="page widget-manager adminMainBlock">
	<div>
		<table style="border: 1px solid #D7E5F5;" id="<?php echo $genid?>top-widget-table">
			<tr><th colspan="3" style="text-align:center;border-bottom:1px solid #ADCCF0;"><?php echo lang('top')?></th></tr>
			<tr>
				<th><?php echo lang("title")?></th>
				<th style="text-align:right;"><?php echo lang("sec order")?></th>
				<th style="text-align:center;"><?php echo lang("section")?></th>
			</tr>
			<?php $altRow = 'altRow';
			foreach ( $widgets_info as $widget ) : 
				if ($widget['section'] != 'top') continue;
				$altRow = $altRow == '' ? 'altRow' : '';
			?>
			<tr class="<?php echo ($widget['section'] != 'none'?'enabled':'disabled')." $altRow"?>">
				<td><span style="padding:1px 0 3px 18px;" class="db-ico <?php echo $widget['icon']?>"></span><?php echo lang($widget['title'])?>
				<?php if (is_array(array_var($widget, 'options')) && count(array_var($widget, 'options')) > 0) {
						foreach ($widget['options'] as $option) {
				?><div style="padding-left:30px;padding-top:5px;" class="option"><?php 
					echo '- '.lang('widget_'.$option['widget'].'_'.$option['option']) . ": ";
					echo render_widget_option_input($option);
				?></div>
				<?php 	}
					} ?>
				</td>
				<td style="text-align:right;vertical-align:top;">
					<input type="number" name="widgets[<?php echo $widget['name']?>][order]" value="<?php echo $widget['order']?>" style="width:60px;text-align:right;"></input>
				</td>
				<td style="text-align:center;vertical-align:top;">
					<select name='widgets[<?php echo $widget['name']?>][section]' onchange="og.move_widget_table_row(this, '<?php echo $genid?>');">
						<option value="top" <?php echo $widget['section'] == 'top' ? 'selected=selected':''?>><?php echo lang('top')?></option>
						<option value="left" <?php echo $widget['section'] == 'left' ? 'selected=selected':''?>><?php echo lang('left')?></option>
						<option value="right" <?php echo $widget['section'] == 'right' ? 'selected=selected':''?>><?php echo lang('right')?></option>
						<option value="none" <?php echo $widget['section'] == 'none' ? 'selected=selected':''?>><?php echo lang('none')?></option>
					</select>
				</td>
			</tr>
			<?php endforeach;?>
		</table>
	</div><div class="left-section">
		<table style="border: 1px solid #D7E5F5;" id="<?php echo $genid?>left-widget-table">
			<tr><th colspan="3" style="text-align:center;border-bottom:1px solid #ADCCF0;"><?php echo lang('left')?></th></tr>
			<tr>
				<th><?php echo lang("title")?></th>
				<th style="text-align:right;"><?php echo lang("sec order")?></th>
				<th style="text-align:center;"><?php echo lang("section")?></th>
			</tr>
			<?php $altRow = 'altRow';
			foreach ( $widgets_info as $widget ) : 
				if ($widget['section'] != 'left') continue;
				$altRow = $altRow == '' ? 'altRow' : '';
			?>
			<tr class="<?php echo ($widget['section'] != 'none'?'enabled':'disabled')." $altRow"?>">
				<td><span style="padding:1px 0 3px 18px;" class="db-ico <?php echo $widget['icon']?>"></span><?php echo lang($widget['title'])?>
				<?php if (is_array(array_var($widget, 'options')) && count(array_var($widget, 'options')) > 0) {
						foreach ($widget['options'] as $option) {
				?><div style="padding-left:30px;padding-top:5px;" class="option"><?php 
					echo '- '.lang('widget_'.$option['widget'].'_'.$option['option']) . ": ";
					echo render_widget_option_input($option);
				?></div>
				<?php 	}
					} ?>
				</td>
				<td style="text-align:right;vertical-align:top;">
					<input type="number" name="widgets[<?php echo $widget['name']?>][order]" value="<?php echo $widget['order']?>" style="width:60px;text-align:right;"></input>
				</td>
				<td style="text-align:center;vertical-align:top;">
					<select name='widgets[<?php echo $widget['name']?>][section]' onchange="og.move_widget_table_row(this, '<?php echo $genid?>');">
						<option value="top" <?php echo $widget['section'] == 'top' ? 'selected=selected':''?>><?php echo lang('top')?></option>
						<option value="left" <?php echo $widget['section'] == 'left' ? 'selected=selected':''?>><?php echo lang('left')?></option>
						<option value="right" <?php echo $widget['section'] == 'right' ? 'selected=selected':''?>><?php echo lang('right')?></option>
						<option value="none" <?php echo $widget['section'] == 'none' ? 'selected=selected':''?>><?php echo lang('none')?></option>
					</select>
				</td>
			</tr>
			<?php endforeach;?>
		</table>
	</div><div class="right-section">
		<table style="border: 1px solid #D7E5F5;" id="<?php echo $genid?>right-widget-table">
			<tr><th colspan="3" style="text-align:center;border-bottom:1px solid #ADCCF0;"><?php echo lang('right')?></th></tr>
			<tr>
				<th><?php echo lang("title")?></th>
				<th style="text-align:right;"><?php echo lang("sec order")?></th>
				<th style="text-align:center;"><?php echo lang("section")?></th>
			</tr>
			<?php $altRow = 'altRow';
			foreach ( $widgets_info as $widget ) : 
				if ($widget['section'] != 'right') continue;
				$altRow = $altRow == '' ? 'altRow' : '';
			?>
			<tr class="<?php echo ($widget['section'] != 'none'?'enabled':'disabled')." $altRow"?>">
				<td><span style="padding:1px 0 3px 18px;" class="db-ico <?php echo $widget['icon']?>"></span><?php echo lang($widget['title'])?>
				<?php if (is_array(array_var($widget, 'options')) && count(array_var($widget, 'options')) > 0) {
						foreach ($widget['options'] as $option) {
				?><div style="padding-left:30px;padding-top:5px;" class="option"><?php 
					echo '- '.lang('widget_'.$option['widget'].'_'.$option['option']) . ": ";
					echo render_widget_option_input($option);
				?></div>
				<?php 	}
					} ?>
				</td>
				<td style="text-align:right;vertical-align:top;">
					<input type="number" name="widgets[<?php echo $widget['name']?>][order]" value="<?php echo $widget['order']?>" style="width:60px;text-align:right;"></input>
				</td>
				<td style="text-align:center;vertical-align:top;">
					<select name='widgets[<?php echo $widget['name']?>][section]' onchange="og.move_widget_table_row(this, '<?php echo $genid?>');">
						<option value="top" <?php echo $widget['section'] == 'top' ? 'selected=selected':''?>><?php echo lang('top')?></option>
						<option value="left" <?php echo $widget['section'] == 'left' ? 'selected=selected':''?>><?php echo lang('left')?></option>
						<option value="right" <?php echo $widget['section'] == 'right' ? 'selected=selected':''?>><?php echo lang('right')?></option>
						<option value="none" <?php echo $widget['section'] == 'none' ? 'selected=selected':''?>><?php echo lang('none')?></option>
					</select>
				</td>
			</tr>
			<?php endforeach;?>
		</table>
	</div><div class="clear"></div><div>
		<table style="border: 1px solid #D7E5F5;" id="<?php echo $genid?>none-widget-table">
			<tr><th colspan="3" style="text-align:center;border-bottom:1px solid #ADCCF0;"><?php echo trim(str_replace('--','',lang('none')))?></th></tr>
			<tr>
				<th><?php echo lang("title")?></th>
				<th style="text-align:right;"><?php echo lang("sec order")?></th>
				<th style="text-align:center;"><?php echo lang("section")?></th>
			</tr>
			<?php $altRow = 'altRow';
			foreach ( $widgets_info as $widget ) : 
				if ($widget['section'] != 'none') continue;
				$altRow = $altRow == '' ? 'altRow' : '';
			?>
			<tr class="<?php echo ($widget['section'] != 'none'?'enabled':'disabled')." $altRow"?>">
				<td><span style="padding:1px 0 3px 18px;" class="db-ico <?php echo $widget['icon']?>"></span><?php echo lang($widget['title'])?>
				<?php if (is_array(array_var($widget, 'options')) && count(array_var($widget, 'options')) > 0) {
						foreach ($widget['options'] as $option) {
				?><div style="padding-left:30px;padding-top:5px;" class="option"><?php 
					echo '- '.lang('widget_'.$option['widget'].'_'.$option['option']) . ": ";
					echo render_widget_option_input($option);
				?></div>
				<?php 	}
					} ?>
				</td>
				<td style="text-align:right;vertical-align:top;">
					<input type="number" name="widgets[<?php echo $widget['name']?>][order]" value="<?php echo $widget['order']?>" style="width:60px;text-align:right;"></input>
				</td>
				<td style="text-align:center;vertical-align:top;">
					<select name='widgets[<?php echo $widget['name']?>][section]' onchange="og.move_widget_table_row(this, '<?php echo $genid?>');">
						<option value="top" <?php echo $widget['section'] == 'top' ? 'selected=selected':''?>><?php echo lang('top')?></option>
						<option value="left" <?php echo $widget['section'] == 'left' ? 'selected=selected':''?>><?php echo lang('left')?></option>
						<option value="right" <?php echo $widget['section'] == 'right' ? 'selected=selected':''?>><?php echo lang('right')?></option>
						<option value="none" <?php echo $widget['section'] == 'none' ? 'selected=selected':''?>><?php echo lang('none')?></option>
					</select>
				</td>
			</tr>
			<?php endforeach;?>
		</table>
	</div>
		<?php echo submit_button(lang('save'), null, array('class' => 'blue'))?>
</div>
</form>
</div>
<script>
og.radio_options = [];
og.on_widget_radio_option_change = function(input) {
	og.radio_options[input.name] = input.value;
}
og.select_options = [];
og.on_widget_select_option_change = function(select) {
	og.select_options[select.name] = select.value;
}

og.move_widget_table_row = function(sel, genid) {
	var section = sel[sel.selectedIndex].value;
	var table = document.getElementById(genid + section+'-widget-table');

	var tr = sel.parentNode.parentNode;

	// get options to modify
	var radio_options = [];
	var radios = $(tr).find('.option input:radio');
	for (var i=0; i<radios.length; i++) {
		var el = document.getElementById(radios[i].id);
		radio_options.push(el);
	}
	var select_options = [];
	var selects = $(tr).find('.option select');
	for (var i=0; i<selects.length; i++) {
		select_options.push(selects[i].name);
	}
	

	// put html in the other table
	$("#"+table.id+" tr:last").after('<tr>'+tr.innerHTML+'</tr>');

	var new_tr = table.rows[table.rows.length-1];
	new_tr.deleteCell(2);
	if (table.rows.length % 2 == 0) new_tr.style.backgroundColor = '#F4F8F9';
	var c = new_tr.insertCell(2);
	c.style.textAlign = 'center';
	c.style.verticalAlign = 'top';
	c.appendChild(sel);

	tr.parentNode.deleteRow(tr.rowIndex);
		
	// restore option values
	for (var i=0; i<radio_options.length; i++) {
		var radios = document.getElementsByName(radio_options[i].name);
		for (var j=0; j<radios.length; j++) {
			if (og.radio_options[radios[j].name] == 1) {
				if (radio_options[i].id.indexOf(']Yes') >= 0) document.getElementById(radio_options[i].id).setAttribute('checked', 'checked');
				else document.getElementById(radio_options[i].id).removeAttribute('checked');
			} else if (og.radio_options[radios[j].name]) {
				if (radio_options[i].id.indexOf(']Yes') >= 0) document.getElementById(radio_options[i].id).removeAttribute('checked');
				else document.getElementById(radio_options[i].id).setAttribute('checked', 'checked');
			}

		}
	}
	for (var i=0; i<select_options.length; i++) {
		var selects = document.getElementsByName(select_options[i]);
		for (var j=0; j<selects.length; j++) {
			if (!og.select_options[selects[i].name]) continue;
			selects[i].value = og.select_options[selects[i].name];
		}
	}
	
	return true;
}
</script>