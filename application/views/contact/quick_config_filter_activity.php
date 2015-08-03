
<form action="<?php echo get_url('contact', 'quick_config_filter_activity') ?>" method="post" >
	<h2 class="quick-form-title"><?php echo lang("config activity") ?></h2>
        <input type="hidden" value="<?php echo $members?>" name="filter[members]"/>
        <input type="hidden" value="<?php echo $id?>" name="filter[id]"/>
        <input type="hidden" value="0" name="filter[apply_everywhere]" id="everywhere"/>
        <div class="field">
        	<div style="float: left;">
	        	<div style="float: left; width: 120px">
		        	<label for="filter-timeslot" ><?php echo lang("view timeslots")?>:</label>
		        </div>
		        <div style="float: right; margin: 5px;">
		        	<input type="checkbox" name="filter[timeslot]" value="1" <?php echo $timeslot?>/>
		        </div>
        	</div>
        	<div style="float: left; width: 120px; clear: both;">
	        	<label for="filter-show" >
	        		<?php echo lang("recent activities to show display")?>
                                <input type="text" id="filter-show" name="filter[show]" style="width: 17px;" value="<?php echo $show?>"/>
                                <?php echo lang("recent activities to show lines")?>
                        </label>
        	</div>
	        
<!--            <label for="filter-dimension" ><?php echo lang("entry to see the dimension")?>:</label>
            <input class="yes_no" type="radio" name="filter[dimension]" value="1" <?php // echo $checked_dimension_yes?>/>
            <label class="yes_no"><?php echo lang("yes")?></label>
            <input class="yes_no" type="radio" name="filter[dimension]" value="0" <?php // echo $checked_dimension_no?>/>            
            <label class="yes_no"><?php echo lang("no")?></label>-->
            
            <!--<label for="filter-timeslot" ><?php echo lang("view timeslots")?>:</label>
            <input class="yes_no" type="radio" name="filter[timeslot]" value="1" <?php // echo $checked_timeslot_yes?>/>
            <label class="yes_no"><?php echo lang("yes")?></label>
            <input class="yes_no" type="radio" name="filter[timeslot]" value="0" <?php // echo $checked_timeslot_no?>/>
            <label class="yes_no"><?php echo lang("no")?></label>-->
            
            
            
<!--            <label for="filter-view-dowloads" ><?php echo lang("views and downloads")?>:</label>
            <input class="yes_no" type="radio" name="filter[view_downloads]" value="1" <?php // echo $checked_view_downloads_yes?>/>
            <label class="yes_no"><?php echo lang("yes")?></label>
            <input class="yes_no" type="radio" name="filter[view_downloads]" value="0" <?php // echo $checked_view_downloads_no?>/>
            <label class="yes_no"><?php echo lang("no")?></label>-->
        </div>
        <div class="action" style="float: left; width: 290px;">
            <div style="float: left;">
                <input type="button" class="submit" value="<?php echo lang("apply everywhere")?>" id="apply_everywhere"/>
            </div>
            <div style="float: right;">
                <input type="submit" class="submit" value="<?php echo lang("apply for this",$dim_name)?>"/>
            </div>
        </div>
</form>

<script>
	$( function() {
		// To make ajax submit:
		og.captureLinks("quick-form");
                $("#apply_everywhere").click(function() {
                        $("#everywhere").val('1');
			$("#quick-form form").submit();
		}); 
        // After sumbmit hide form 
		$("#quick-form form").submit(function(a){
			$("#quick-form").slideUp();
		});
	});
</script>