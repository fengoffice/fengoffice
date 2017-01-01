<?php
	if (!isset($genid)) $genid = gen_id();
	
	if ($description != '') echo clean($description) . '<br/>';
	
	custom_report_info_blocks(array('id' => $id, 'results' => $results, 'parameters' => $parameters));
?>


<?php if (!isset($id)) $id= ''; ?>
<input type="hidden" name="id" value="<?php echo $id ?>" />
<input type="hidden" name="order_by" value="<?php echo $order_by ?>" />
<input type="hidden" name="order_by_asc" value="<?php echo $order_by_asc ?>" />

<?php 
	$params_url = isset($parametersURL) ? $parametersURL : "";
	$report = Reports::getReport($id);
	
	Env::useHelper('reporting');
	echo report_table_html($results, $report, $params_url);
	
	$pagination = array_var($results, 'pagination');
?>

<div style="margin-top: 10px;">
</div>
<?php
	if ($pagination) echo $pagination;

	if (isset($save_html_in_file) && $save_html_in_file) {
		$html = ob_get_clean();
		file_put_contents($html_filename, $html);
	}

