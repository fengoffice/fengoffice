<?php
	$params_url = isset($parametersURL) ? $parametersURL : "";
	$report = Reports::getReport($id);
	
	echo report_table_html($results, $report, $params_url,$to_print);
	
	$pagination = array_var($results, 'pagination');
?>

<div style="margin-top: 4px;">
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

    $().ready(function() {

        // Change the selector if needed
        var $table = $('.custom-report.scroll'),
            $bodyCells = $table.find('tbody tr.report-data:first').children(),
            colWidth;

        if ($bodyCells.length == 0) {
            $bodyCells = $table.find('tbody tr.list-totals-row:first').children()
        }


        // Adjust the width of thead cells when window resizes
        $(window).resize(function() {
        	// if total width < screen width => expand the cells
            // first: get the form width
            var total_width = $("#form<?php echo $genid ?>").outerWidth();
            // remove container margin
            total_width -= 35;
            
            // get the current td width
            var all_cols_width = $("#form<?php echo $genid ?> .coViewBody .custom-report thead tr.custom-report-table-heading").outerWidth();
            if (all_cols_width < total_width) {

				// check if we have to consider the icon container td
				var non_data_cells_count = 0;
				var ico_cell_width = 0;
				$bodyCells.each(function(i, v) {
					if ($(v).children('a.link-ico').length > 0) {
						non_data_cells_count = 1;
						ico_cell_width = 30;
					}
				});
                
                // each cell's width
				var width = Math.floor((total_width - ico_cell_width) / ($bodyCells.length - non_data_cells_count));
				// remove cell paddings
				width -= 20;
				
				$bodyCells.each(function(i, v) {
					// only expand the data cells, not the icon container one if present
					if (non_data_cells_count == 0 || i > 0) {
						$(v).width(width + 'px');
						$(v).css('min-width', width + 'px');
					}
				});
            }
            // ------
             
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
        var totalHeight = $("#form<?php echo $genid ?>").parent().height();
        var totalHeaderReport = $("#form<?php echo $genid ?> .coViewHeader").outerHeight();
        var reportHeadersHeight = $("#form<?php echo $genid ?> .coViewBody .custom-report thead").outerHeight();
        var paginationHeight = $("#form<?php echo $genid ?> .pagination-div").outerHeight() | 0;

        var fixedSubtract = <?php echo ($pagination ? (!isset($results['group_by_criterias']) ? '28' : '10') : '0') ?>;
        
        
		var tbodyWidth = $(".report.custom-report tbody").width();
		var formWidth = $("#form<?php echo $genid ?>").width();
		if (tbodyWidth > formWidth) { // space for scrollbar needed
			fixedSubtract += 28;
		}
		
        
        var total = totalHeight - totalHeaderReport - reportHeadersHeight - paginationHeight - fixedSubtract;
        
        $("#form<?php echo $genid ?> tbody").height(total);
    });

    

    
</script>
