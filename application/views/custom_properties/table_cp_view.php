<?php

$columnNames = explode(',', $cp->getValues());

// A column made up mostly of monetary amounts (a currency-code prefix like "USD 7.745.694",
// a plain number using thousands/decimal separators like "10.636.411" or "4.273.714,9", or a
// plain percentage like "50%"/"12.5%") reads better right-aligned. A bare small integer with
// no separator - e.g. a "Year" column - does NOT count as an amount here, so it's left as-is.
// The letter prefix requires a following space (real currency codes read "USD 100", not
// "USD100") so short codes like "Q1"/"A1" (quarter/period labels) aren't misread as amounts;
// it's also restricted to UPPERCASE so month/period labels like "Ene 2023" or "Sem 1" don't
// match either.
$amount_pattern = '/^[A-Z]{1,4}\.?\s+[\-+]?\d[\d.,]*\s*%?$'
	. '|^[\-+]?\d{1,3}(?:[.,]\d{3})+(?:[.,]\d+)?\s*%?$'
	. '|^[\-+]?\d+(?:[.,]\d+)?%$/';
$is_numeric_column = array();
foreach ($columnNames as $col_index => $colName) {
	$value_count = 0;
	$amount_like_count = 0;
	foreach ($rows as $row) {
		$cell = trim(array_var($row, $col_index, ''));
		if ($cell === '') continue;
		$value_count++;
		if (preg_match($amount_pattern, $cell)) {
			$amount_like_count++;
		}
	}
	// Tolerate a minority of non-matching placeholder cells (e.g. "SIN INFO"/"N/A" for a
	// missing amount) without losing the alignment for the rest of the column.
	$is_numeric_column[$col_index] = $value_count > 0 && ($amount_like_count / $value_count) >= 0.6;
}

?>
<div class="og-custom-properties og-add-custom-properties">
	<table class="table-cp">
		<thead>
			<tr>
<?php foreach ($columnNames as $col_index => $colName) { ?>
				<th class="<?php echo $is_numeric_column[$col_index] ? 'align-right' : '' ?>"><?php echo clean($colName) ?></th>
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
<?php 	foreach ($row as $col_index => $cell) { ?>
				<td class="<?php echo !empty($is_numeric_column[$col_index]) ? 'align-right' : '' ?>"><?php echo clean($cell) ?></td>
<?php 	} ?>
			</tr>
<?php } ?>
		</tbody>
	</table>
</div>

