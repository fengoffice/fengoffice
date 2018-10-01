<?php
	require_javascript('og/modules/doubleListSelCtrl.js');

	if (!isset($for_task)) $for_task = false;
	
	// build columm list
	$list = array();

	$options = array();
	foreach ($allowed_columns as $acol) {
		$selected = false;
        $order = false;
        if (is_array($columns)) {
		    if (in_array($acol['id'],$columns)){
                $selected = true;
                $order = array_search($acol['id'], $columns);
            }
        }

			$list[] = array(
				'id' => $acol['id'],
				'text' => $acol['name'],
				'selected' => $selected,
                'order' => $order
			);

		if(!isset($order_by) || $order_by == '') $order_by = 'updated_on';
		if (!str_starts_with($acol['id'], "dim_") && !str_starts_with($acol['id'], "dimassoc_") && $acol['id']!='time' && $acol['id']!='billing' && $acol['type']!='calculated') {
			$options[] = option_tag($acol['name'], $acol['id'], $acol['id'] == $order_by ? array('selected' => 'selected') : null);
		}
	}
	
	// Render Order By combos
	if (!$for_task){
		echo label_tag(lang('order by'), $genid . 'reportFormOrderBy', true, array('id' => 'orderByLbl'));
		echo select_box('report[order_by]', $options, array('id' => 'report[order_by]', 'style' => 'width:200px;'));
		$asc = option_tag(lang('ascending'), 'asc', $order_by_asc ? array('selected' => 'selected') : null);
		$desc = option_tag(lang('descending'), 'desc', !$order_by_asc ? array('selected' => 'selected') : null);
		echo select_box('report[order_by_asc]', array($asc, $desc), array('id' => 'report[order_by_asc]', 'style' => 'width:120px;')); 
		echo "<br /><br />";
	}
	// Render Column lists
	echo label_tag(lang('columns to print'), 'columns');
	echo doubleListSelect("columns", $list, array('id' => $genid."columns", 'size' => 20), $option_groups);
	
	echo '<span class="desc">' . lang('columns to print desc') . '</span>'; 
?>