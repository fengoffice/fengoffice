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
?>
<style>
    /*  Remove the scroll bar only to reports view page */
    #reporting-panel .x-panel-body.x-panel-body-noheader{
        overflow: hidden !important;
    }
</style>
<script>


    // Change the selector if needed
    var $table = $('.custom-report.scroll'),
        $bodyCells = $table.find('tbody tr.report-data:first').children(),
        colWidth;

    if ($bodyCells.length == 0) {
        $bodyCells = $table.find('tbody tr.list-totals-row:first').children()
    }
    
    
    // Adjust the width of thead cells when window resizes
    $(window).resize(function() {
        // Get the tbody columns width array
        colWidth = $bodyCells.map(function() {
            return $(this).width();
        }).get();

        // Set the width of thead columns
        $table.find('thead tr').children().each(function(i, v) {
            $(v).width(colWidth[i]);
            $(v).css('max-width',colWidth[i]);
        });
    }).resize();
    
    
    
    
   /* dynamic height to table report */
    var totalHeightHtml = $( document ).outerHeight();
    var totalHeaderHeight = $( ' #header' ).outerHeight();
    var totalMenuHeight = $( '#ext-gen245' ).outerHeight();
    var totalSubHeaderHeight = $( '.coViewHeader' ).outerHeight();
    var totalHeaderReport  = $('.report thead').outerHeight();
    var total = totalHeightHtml - (totalHeaderHeight + totalMenuHeight + totalSubHeaderHeight + totalHeaderReport + 160); // 150 is for paddings and margins of elements.
    $( '.report tbody' ).height(total);
    
    

    

    
</script>
