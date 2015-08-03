<?php 
$current_member = current_member();
$member_id = 0;
if($current_member instanceof Member){
   $member_id = $current_member->getId();
}
?>
<div class="widget-activity widget dashTableActivity">

	<div class="widget-header dashHeader">
            <div style="overflow:hidden; float: left; width: 90%; text-overflow: ellipsis; white-space: nowrap;" class="widget-title" onclick="og.dashExpand('<?php echo $genid?>');"><?php echo (isset($widget_title)) ? $widget_title : lang("activity");?></div>                
			<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander" onclick="og.dashExpand('<?php echo $genid?>');"></div>
            <div style="z-index:1; width:16px; float: right; margin-right: 5px; margin-top: 1px;" id="<?php echo $genid; ?>configFilters" onclick="og.quickForm({ type: 'configFilter', genid: '<?php echo $genid; ?>', members:'<?php echo $member_id ?>'});">
            	<img src="public/assets/themes/default/images/16x16/administration.png"/>
            </div>
	</div>
	
	<div class="widget-body" id="<?php echo $genid; ?>_widget_body">
		<ul>
            <table style="width:100%;" cellpadding="0" cellspacing="0" id="dashTableActivity">
			<?php 
            $c = 0;
            foreach ($acts['data'] as $k => $activity):
            $c++;
            $user = $acts['created_by'][$k];
            if ($activity instanceof Contact && $activity->isUser() || (isset($member_deleted) && $member_deleted)) {
            	$crumbOptions = "{}";
            } else {
            	if ($activity instanceof Member){
            		$crumbOptions = "{}";
            	} else if ($activity instanceof ContentDataObject) {
					$crumbOptions = json_encode($activity->getMembersToDisplayPath(null,false,false));
            	} else {
            		continue;
            	}
			}
            $crumbJs = " og.getEmptyCrumbHtml($crumbOptions,'.activity-breadcrumb-container') ";
            $class = "";
            $style = "";
            if($c > $total){
            	$class = "noViewActivity";
            	$style = "style='display:none'";
            }
			?>
				<tr class="activity-row <?php echo $class?>" id="<?php echo "activity-".$c?>" <?php echo $style?>>
					<td style="width:50px;">
						<div style="float: left; margin-top:10px; min-width:40px;">
					<?php if ($user instanceof Contact) : ?>
							<img src="<?php echo $user->getPictureUrl() ?>" style="max-width:40px;max-height:40px"/>
					<?php endif; ?>
						</div>
					</td><td>
						<div style="float: left; margin-top:10px; margin-left: 10px;width: 100%;" class="activity-info">
							<div><?php echo $acts['act_data'][$k] ?><div class="activity-breadcrumb-container" style="margin-top:4px;"><span class="breadcrumb"></div></span></div>
							<script>
                        	
                        	var crumbHtml  =  <?php echo $crumbJs?>;
                        	$("#activity-<?php echo $c?> .breadcrumb").html(crumbHtml);
                        	</script>
						</div>
					</td><td style="min-width:100px;vertical-align:bottom;">
						<div class="desc date-container"><?php echo $acts['date'][$k] ?></div>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if (count($acts['data']) > $total) :?>
				<tr>
					<td colspan="3" align="right" style="padding:20px 0 5px; width: 20px; color: #003562;">
						<span onclick="og.hideActivity('<?php echo $genid?>')" id="hidelnk<?php echo $genid?>" style="cursor:pointer; display:none;" title="<?php echo lang('hide') ?>"><?php echo lang('hide') ?></span>
						<span onclick="og.showActivity('<?php echo $genid?>')" id="showlnk<?php echo $genid?>" style="cursor:pointer;" title="<?php echo lang('view more') ?>"><?php echo lang('view more') ?></span>
					</td>
				</tr>
			<?php endif;?>
			</table>
		</ul>
		<div class="x-clear"></div>
		<div class="progress-mask"></div>
	</div>	
</div>
<script>
	og.showActivity = function(id) {
		og.showHide('hidelnk' + id);
		og.showHide('showlnk' + id);
                
        $(".activity-row").each(function() {
        	if($("#" + this.id).hasClass("noViewActivity")){
            	$("#" + this.id).show("slow");
            }
        });
	}
        
    og.hideActivity = function(id) {
		og.showHide('hidelnk' + id);
		og.showHide('showlnk' + id);
	                
	    $(".activity-row").each(function() {
		    if($("#" + this.id).hasClass("noViewActivity")){
		    	$("#" + this.id).hide("slow");
		    }
	    });
	}
    $(function() {
    	// og.eventManager.fireEvent('replace all empty breadcrumb', null);
    });   
</script>