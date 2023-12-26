<?php
require_javascript("og/DateField.js");
require_javascript("og/ReportingFunctions.js");
require_javascript('og/modules/doubleListSelCtrl.js');
require_javascript('og/CSVCombo.js');
$genid = gen_id();

$ignore_context = isset( $report_data['ignore_context']) ? $report_data['ignore_context'] : false;
$context_div_display = $ignore_context ? "display:none;" : "";

?>
<form style='height: 100%; background-color: white' class="internalForm report" action="<?php echo get_url('reporting','edit_default_report', array('id' => $object->getId())) ?>" method="post" name="Form">
	
    <div class="coInputHeader">

        <div class="coInputHeaderUpperRow">
            <div class="coInputTitle">
                <?php echo lang('edit report') ?>
            </div>
        </div>

        <div>
            <div class="coInputName">
            <?php echo text_field('report[name]', array_var($report_data, 'name'), array('id' => $genid . 'reportFormName', 'class' => 'title', 'required'=> '', 'placeholder' => lang('type name here'))); ?>
            </div>       
            <div class="coInputButtons">
                <?php echo submit_button((isset($id) ? lang('save changes') : lang('add report')),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
            </div>
            <div class="clear"></div>
        </div>

    </div>

    <div class="coInputMainBlock">

        <div class="dataBlock">
            <?php
            echo label_tag(lang('description'), $genid . 'reportFormDescription', false);
            echo text_field('report[description]', array_var($report_data, 'description'), array('id' => $genid . 'reportFormDescription', 'tabindex' => '2', 'class' => 'title'));
            ?>
        </div>

        <div class="clear"></div>
        <?php
            $params = array('object' => $object,'genid' => $genid);
            $categoryHtml = '';
            Hook::fire('show_category_selector', $params, $categoryHtml);
            echo $categoryHtml;
        ?>

        <div class="dataBlock">
            <span style="margin-left:30px;">
		        <?php echo checkbox_field("report[ignore_context]", $ignore_context, array('id' => $genid.'ignore_context',
			        'onchange' => 'document.getElementById("'.$genid.'add_report_select_context_div").style.display = (this.checked ? "none" : "")')); ?>
		        <label class="checkbox" for="<?php echo $genid.'ignore_context'?>"><?php echo lang('show always')?></label>
	        </span>
	        <div class="clear"></div>
	    </div>
	    <div class="dataBlock" id="<?php echo $genid ?>add_report_select_context_div" style="margin:0;<?php echo $context_div_display ?>">
	        <div> 
		        <?php
		        $listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$object->manager()->getObjectTypeId().')');
		        if ($object->isNew()) {
			        render_member_selectors($object->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners, 'object' => $object), null, null, false);
		        } else {
			        render_member_selectors($object->manager()->getObjectTypeId(), $genid, $object->getMemberIds(), array('listeners' => $listeners, 'object' => $object), null, null, false);
		        }?>
	            </div>
	        <div class="clear"></div>
	    </div>
    </div>

</form>
