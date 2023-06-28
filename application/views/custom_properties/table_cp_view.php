<?php

$columnNames = explode(',', $cp->getValues());

?>
<div class="og-custom-properties og-add-custom-properties">
	<table class="table-cp">
		<thead>
			<tr>
<?php foreach ($columnNames as $colName) { ?>
				<th><?php echo $colName ?></th>
<?php } ?>
			</tr>
		</thead>
		<tbody>
<?php 
	  $row_cls = 'altRow';
	  foreach ($rows as $row) {
	  	while (count($row) < count($columnNames)) $row[]='';
	  	$row_cls = $row_cls == '' ? 'altRow' : '';
?>
			<tr class="<?php echo $row_cls ?>">
<?php 	foreach ($row as $cell) { ?>
				<td><?php echo $cell ?></th>
<?php 	} ?>
			</tr>
<?php } ?>
		</tbody>
	</table>
</div>
	
