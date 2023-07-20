<?php

if (!function_exists('render_table_rows_custom_property_field')) {
  function render_table_rows_custom_property_field($custom_property, $configs, $columnNames, &$rows) {
	$html = '';
	$rows = 0;
	$default_value = $configs['default_value'];
	$name = $configs['name'];
	$genid = $configs['genid'];
	$cell_width = (600 / count($columnNames)) . "px";

	if (is_array($default_value) && count($default_value) > 0) {
		foreach ($default_value as $val) {
			$html .= render_table_row_custom_property_field($name, $rows, $val->getValue(), $cell_width, $columnNames);
			$rows++;
		}
	}else{
		$html .= render_table_row_custom_property_field($name, $rows, $default_value, $cell_width, $columnNames);
		$rows++;
	}

	if ($rows == 0) {
		// create first empty row
		$html .= '<script>if (!Ext.isIE) document.getElementById("'.$configs['genid'].'-add-row-'.$custom_property->getId().'").onclick();</script>';
	}

	return $html;
  }
}

if (!function_exists('render_table_row_custom_property_field')) {
  function render_table_row_custom_property_field($name, $row_number, $value, $cell_width, $column_names) {
	$html = '';

	$html .= '<tr>';
	$col = 0;
	if ($value) {
		$values = str_replace("\|", "%%_PIPE_%%", $value);
		$exploded = explode("|", $values);
		while (count($exploded) < count($column_names)) $exploded[]='';
	} else {
		$exploded = array();
		foreach ($column_names as $col_name) {
			$exploded[] = '';
		}
	}
	foreach ($exploded as $v) {
		$v = str_replace("%%_PIPE_%%", "|", $v);
		$html .= '<td><input class="value" style="width: '.$cell_width.';min-width:120px;" name="'.$name."[$row_number][$col]". '" value="'. clean($v) .'" /></td>';
		$col++;
	}
	$html .= '<td><div class="ico ico-delete" style="width: 20px;height: 20px;cursor: pointer;margin-left: 2px;margin-top: 1px;"
				onclick="og.removeTableCustomPropertyRow(this.parentNode.parentNode);return false;">&nbsp;</div></td>';
	$html .= '</tr>';

	return $html;
  }
}

$columnNames = explode(',', $custom_property->getValues());
$default_value = $configs['default_value'];
$name = $configs['name'];
$genid = $configs['genid'];

$is_member_cp = '0';
$ot = ObjectTypes::findById($custom_property->getObjectTypeId());
if ($ot instanceof ObjectType && $ot->getType() == 'dimension_group') {
	$is_member_cp = '1';
}

$container_id = $genid . "_container_cp_" . $custom_property->getId();

$cell_width = (600 / count($columnNames)) . "px";
?>
<div class="og-add-custom-properties" id="<?php echo $container_id ?>">
	<table>
		<thead>
			<tr>
<?php
foreach ($columnNames as $colName) {
?>
				<th style="width:<?php echo $cell_width ?>;"><?php echo $colName ?></th>
<?php
}
?>
				<th class="actions"></th>
			</tr>
		</thead>
		<tbody>
<?php 
$row_count = 0;
$html = render_table_rows_custom_property_field($custom_property, $configs, $columnNames, $row_count);
echo $html;
?>
		</tbody>
	</table>
	<a href="#" id="<?php echo $genid?>-add-row-<?php echo $custom_property->getId()?>" class="link-ico ico-add" 
		onclick="og.addTableCustomPropertyRow(this.parentNode, true, null, <?php echo count($columnNames)?>, null, <?php echo $custom_property->getId()?>, <?php echo $is_member_cp?>);return false;"><?php echo lang("add new row") ?></a>
</div>
<div class="clear"></div>

<script>
if (!og.table_cps_last_row_id) og.table_cps_last_row_id = {};
og.table_cps_last_row_id[<?php echo $custom_property->getId()?>] = <?php echo $row_count ?>;

<?php if (array_var($configs,'disabled')) { ?>
	$("#<?php echo $container_id ?> input").attr("disabled", "disabled");
	$("#<?php echo $container_id ?> a.ico-delete").remove();
<?php } ?>
</script>
