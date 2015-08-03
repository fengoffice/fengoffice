<?php


Env::useLibrary('openflashchart');

function render_pie_chart($options) {
	$values = array();
	$colours  = array();
	if  ( is_array($options['data']) ) {
		foreach ($options['data'] as $data ) {
		//	$values[]  = new OFC_Charts_Pie_Value($data['value'], $data['text']) ;
		 	$value  = new OFC_Charts_Pie_Value($data['value'], $data['text']) ;
		 	$value->label = $data['text'];
			$values[]  = $value ;
			$colours[] = $data['color'] ;
		}
	}
	
	
	$chart = new OFC_Chart();
	
	$chart->set_bg_colour(array_var($options, 'background-color', '#FFFFFF'));
	$chart->set_title(new OFC_Elements_Title(array_var($options, 'title', "")));
	
	$pie = new OFC_Charts_Pie();
	
	if (array_var($options, 'start_angle')) $pie->set_start_angle( array_var($options, 'start_angle') );
	$pie->tip = array_var($options, 'tip') ? array_var($options, 'tip') : "#val#/#total#<br>#percent#";
	$pie->values = $values ;
	$pie->colours = $colours ;
	$pie->alpha = array_var($options, 'alpha') ? array_var($options, 'alpha') : 0.7;
	$pie->set_animate(array_var($options, 'animate', true));
    $chart->add_element( $pie );

	
	
	$chart->x_axis = null;
	$filename = 'tmp/' . gen_id().'.json';
	file_put_contents(ROOT . "/$filename", $chart->toPrettyString());
	open_flash_chart_object(
		array_var($options, 'width'), 
		array_var($options, 'height'), 
		ROOT_URL . "/$filename", gen_id()
	);
	
}


function render_chart($options = array()) {
	$genid = array_var($options, 'genid', gen_id());
	$title = array_var($options, 'title', '');
	$width = array_var($options, 'width', 700);
	$height = array_var($options, 'height', 500);
	$type = array_var($options, 'type', 'line');
	$x_range = array_var($options, 'x_range', array());
	$y_range = array_var($options, 'y_range', array());
	$x_labels = array_var($options, 'x_labels', array());
	$y_axis_right = array_var($options, 'y_axis_right');
	$y_values = array_var($options, 'data');
	$shapes = array_var($options, 'shapes', array());
	$label_step = array_var($options, 'label_step', 7);
	
	$title = new OFC_Elements_Title($title);
	
	$max = 0;
	$chart_values = array();
	foreach ($y_values as $y_values_array) {
		$data_object = create_chart_data_object($type, $y_values_array);
		
		$values_data = array_var($y_values_array, 'values', array());
		$max = count($values_data) > $max ? count($values_data) : $max;
		
		$chart_values[] = $data_object;
	}
	
	$x_range_start = array_var($x_range, 'start', 0);
	$x_range_end = array_var($x_range, 'end', 10) - $x_range_start > $max ? $max + $x_range_start - 1 : array_var($x_range, 'end', 10);

	$labels = array();
	$coef = floor(count($x_labels) / $label_step);
	if ($coef > 0) {
		$k = 0;
		foreach ($x_labels as $label) {
			$labels[] = ($k % $coef == 0) ? $label : "";
			$k++;
		}
	} else {
		$labels = $x_labels;
	}
	
	$x_axis = new OFC_Elements_Axis_X();
	$x_axis->set_colours(array_var($options, 'x_axis_color', '#87C4FA'), array_var($options, 'x_grid_color', '#D4E8FA'));
	if (array_var($x_range, 'step')) $x_axis->set_range($x_range_start, $x_range_end, array_var($x_range, 'step', 1));
	$x_axis->set_labels_from_array($labels);
	
	$y_axis = new OFC_Elements_Axis_Y();
	$y_axis->set_colours(array_var($options, 'y_axis_color', '#87C4FA'), array_var($options, 'y_grid_color', '#D4E8FA'));
	if (array_var($y_range, 'step')) $y_axis->set_range(array_var($y_range, 'start', 0), array_var($y_range, 'end', 10), array_var($y_range, 'step', 1));

	$chart = new OFC_Chart();
	$chart->set_title($title);
	foreach ($chart_values as $cv) {
		$chart->add_element($cv);
	}

	$chart->set_x_axis($x_axis);
	$chart->set_y_axis($y_axis);
	$chart->set_bg_colour(array_var($options, 'back_color', '#FFFFFF'));
	if ($y_axis_right) $chart->set_y_axis_right($y_axis);
	
	foreach ($shapes as $s) {
		$shape = new shape(array_var($s, 'color', '#FA6900'));
		$points = array_var($s, 'points', array());
		foreach ($points as $p) {
			$shape->append_value( new shape_point($p['x'], $p['y']));
		}
		if (array_var($s, 'text')) $shape->set_text(array_var($s, 'text'));
		if (array_var($s, 'alpha')) $shape->set_alpha(array_var($s, 'alpha'));
		$chart->add_element($shape);
	}
	
	$filename = 'tmp/' . gen_id().'.json';
	file_put_contents(ROOT . "/$filename", $chart->toPrettyString());
	
	open_flash_chart_object($width, $height, ROOT_URL . "/$filename", $genid);
//	unlink(ROOT . "/$filename");
}

function create_chart_data_object($type, $y_values_array) {
	switch ($type) {
		case 'line': $data_object = new OFC_Charts_Line();
			if (array_var($y_values_array, 'color')) $data_object->set_colour(array_var($y_values_array, 'color'));
			if (array_var($y_values_array, 'dot_size')) $data_object->set_dot_size(array_var($y_values_array, 'dot_size'));
			if (array_var($y_values_array, 'halo_size')) $data_object->set_halo_size(array_var($y_values_array, 'halo_size'));
			if (array_var($y_values_array, 'text')) $data_object->set_key(array_var($y_values_array, 'text', ''), array_var($y_values_array, 'text_size', 10));
			if (array_var($y_values_array, 'width')) $data_object->set_width(array_var($y_values_array, 'width'));
			
			$values_data = array_var($y_values_array, 'values', array());
			break;
		case 'bar': $data_object = new OFC_Charts_Bar_3d();
			if (array_var($y_values_array, 'color')) $data_object->set_colour(array_var($y_values_array, 'color'));
			if (array_var($y_values_array, 'tooltip')) $data_object->set_tooltip(array_var($y_values_array, 'tooltip'));
			if (array_var($y_values_array, 'text')) $data_object->set_key(array_var($y_values_array, 'text', ''), array_var($y_values_array, 'text_size', 10));
			if (array_var($y_values_array, 'alpha')) $data_object->set_alpha(array_var($y_values_array, 'alpha'));
			
			$values_data = array_var($y_values_array, 'values', array());
			break;
		case 'bar-stack': $data_object = new OFC_Charts_Bar_Stack();
			if (array_var($y_values_array, 'color')) $data_object->set_colour(array_var($y_values_array, 'color'));
			if (array_var($y_values_array, 'colors')) $data_object->{'colours'} = array_var($y_values_array, 'colors');
			if (array_var($y_values_array, 'keys')) $data_object->{'keys'} = array_var($y_values_array, 'keys');
			if (array_var($y_values_array, 'tooltip')) $data_object->set_tooltip(array_var($y_values_array, 'tooltip'));
			if (array_var($y_values_array, 'text')) $data_object->set_key(array_var($y_values_array, 'text', ''), array_var($y_values_array, 'text_size', 10));
			if (array_var($y_values_array, 'alpha')) $data_object->set_alpha(array_var($y_values_array, 'alpha'));
			
			$data = array_var($y_values_array, 'values', array());
			foreach ($data as $d) {
				$data_object->append_stack($d);
			}			
			
			break;
		default: continue;
	}
	
	if (isset($values_data)) $data_object->set_values($values_data);
	
	return $data_object;
}