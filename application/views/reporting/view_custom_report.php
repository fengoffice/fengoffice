<?php
	if (!isset($genid)) $genid = gen_id();
	
	if ($description != '') echo clean($description) . '<br/>';
	$conditionHtml = '';
	
	if (count($conditions) > 0) {
		foreach ($conditions as $condition) {
			if($condition->getCustomPropertyId() > 0){
				$cp = CustomProperties::getCustomProperty($condition->getCustomPropertyId());
				$name = clean($cp->getName());
				$paramName = $condition->getId()."_".$cp->getName();
				$coltype = $cp->getOgType();
			}else{
				$name = lang('field ' . $model . ' ' . $condition->getFieldName());
					
				$coltype = array_key_exists($condition->getFieldName(), $types)? $types[$condition->getFieldName()]:'';
				$paramName = $condition->getFieldName();
				if (str_starts_with($coltype, 'DATE') && !$condition->getIsParametrizable()) {
					$cond_value = DateTimeValueLib::dateFromFormatAndString('m/d/Y', $condition->getValue())->format(user_config_option('date_format'));
					$condition->setValue($cond_value);
				}
			}
			$paramValue = isset($parameters[$paramName]) ? $parameters[$paramName] : '';
			$value = $condition->getIsParametrizable() ? clean($paramValue) : clean($condition->getValue());
			
			eval('$managerInstance = ' . $model . "::instance();");
			$externalCols = $managerInstance->getExternalColumns();
			if(in_array($condition->getFieldName(), $externalCols)){
				$value = clean(Reports::instance()->getExternalColumnValue($condition->getFieldName(), $value, $managerInstance));
			}
			if ($value != '') {
				$conditionHtml .= '- ' . $name . ' ' . ($condition->getCondition() != '%' ? $condition->getCondition() : lang('ends with') ) . ' ' . format_value_to_print($condition->getFieldName(), $value, $coltype, '', '"', user_config_option('date_format')) . '<br/>';
			}
		}
	}
	?>
	
<div id="pdfOptions" style="display:none;">
	<span class="bold"><?php echo lang('report pdf options') ?></span><hr/>
	<?php echo lang('report pdf page layout') ?>:
	<select name="pdfPageLayout">
		<option value="P" selected><?php echo lang('report pdf vertical') ?></option>
		<option value="L"><?php echo lang('report pdf landscape') ?></option>
	</select>&nbsp;&nbsp;
	<?php echo lang('report font size') ?>:
	<select name="pdfFontSize">
		<option value="8">8</option>
		<option value="9">9</option>
		<option value="10">10</option>
		<option value="11">11</option>
		<option value="12" selected>12</option>
		<option value="13">13</option>
		<option value="14">14</option>
		<option value="15">15</option>
		<option value="16">16</option>
	</select>
	<input type="submit" name="exportPDF" value="<?php echo lang('export') ?>" onclick="document.getElementById('form<?php echo $genid ?>').target = '_download';" style="width: 120px; background-color: #ddd; margin-top: 2px; border-color: #aaa; border-radius: 5px;"/>
	<hr/>
</div>

<?php if ($conditionHtml != '') : ?>
<div style="float:left;">
	<div class="bold"><?php echo lang('conditions')?>:</div>
	<p style="padding-left:10px"><?php echo $conditionHtml; ?></p>
</div>
<?php endif; ?>

<?php if (count(active_context_members(false)) > 0) : ?>
<div style="margin-bottom: 10px; padding-bottom: 5px; float:left;<?php echo ($conditionHtml != '' ? "margin-left:35px;padding-left:35px;border-left:1px dotted #aaa;" : "")?>">
	<h5><?php echo lang('showing information for')?>:</h5>
	<ul>
	<?php
		$context = active_context();
		foreach ($context as $selection) :
			if ($selection instanceof Member) : ?>
				<li><span class="coViewAction <?php echo $selection->getIconClass()?>"><?php echo $selection->getName()?></span></li>	
	<?php 	endif;
		endforeach;
	?>
	</ul>
</div>
<?php endif; ?>

<div class="clear"></div>


<?php if (!isset($id)) $id= ''; ?>
<input type="hidden" name="id" value="<?php echo $id ?>" />
<input type="hidden" name="order_by" value="<?php echo $order_by ?>" />
<input type="hidden" name="order_by_asc" value="<?php echo $order_by_asc ?>" />
<table>
<tr>
<?php foreach($columns as $col) {
	$sorted = false;
	$asc = false;
	
	if($col != '' && $col != lang('located_under') && array_var($db_columns, $col) == $order_by) {
		$sorted = true;
		$asc = $order_by_asc;
	}	?>
	<td style="padding-right:10px;border-bottom:1px solid #666" class="bold">
<?php if($to_print){
		echo clean($col);
	  }else if($col != ''){
	  	$allow_link = true;
	  	if ($model == 'Timeslots' && in_array(array_var($db_columns, $col), ProjectTasks::instance()->getColumns())) {
	  		$allow_link = false;
	  	}
	  	$echo_link = $allow_link && !(is_numeric(array_var($db_columns, $col)) || str_starts_with(array_var($db_columns, $col), "dim_") || array_var($db_columns, $col) == 'time' || array_var($db_columns, $col) == 'billing'); 
	  	?>
		<a href="<?php echo $echo_link ? get_url('reporting', 'view_custom_report', array('id' => $id, 'replace' => true, 'order_by' => array_var($db_columns,$col), 'order_by_asc' => $asc ? 0 : 1)).$parameterURL : "#" ?>" <?php echo ($echo_link ? "" : 'style="cursor:default;"') ?>>
			<?php echo clean($col) ?>
		</a>
<?php }
	  if(!$to_print && $sorted){ ?>
		<span class="db-ico ico-<?php echo $asc ? 'asc' : 'desc' ?>" style="padding:2px 0 0 18px;">&nbsp;</span>
<?php } ?>
	</td>
<?php }?>
</tr>
<?php
	$isAlt = true; 
	foreach($rows as $row) {
		$isAlt = !$isAlt;
		$i = 0; 
?>
	<tr<?php echo ($isAlt ? ' style="background-color:#F4F8F9"' : "");?>>
		<?php
		foreach ($columns as $k => $col) {
			if ($k == "0") $k = 'link';
			if ($k == 'object_type_id') continue;
			
			$value = (is_numeric($k) ? array_var($row, $col) : array_var($row, $k));
			
			$db_col = (is_numeric($k) ? array_var($db_columns, $col) : $k);
			?>
			<td style="padding-right:10px;">
			<?php 
			$val_type = ($k == 'link' ? '' : array_var($types, $k));
			$date_format = is_numeric(array_var($db_columns, $k)) ? "Y-m-d" : user_config_option('date_format');
			
			echo ($val_type == 'DATETIME') ? $value : format_value_to_print($db_col, $value, $val_type, array_var($row, 'object_type_id'), '', $date_format);
			?>
			</td>
		<?php
			$i++;
		} ?>
	</tr>
<?php } ?>
</table>

<div style="margin-top: 10px;">
<?php if (isset($pagination)) echo $pagination ?>
</div>
<?php
	if (isset($pdf_export) && $pdf_export) {
		$html = ob_get_clean();
		file_put_contents($html_filename, $html);
	}
?>