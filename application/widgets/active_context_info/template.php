<style>
.widget-active-context-info .widget-body tr.cp-info td {
	padding: 2px 6px;
}
</style>
<div class="widget-active-context-info widget">

	<div class="widget-header" onclick="og.dashExpand('<?php echo $genid?>');">
		<div class="widget-title"><?php echo $member->getName() . ' - ' . lang('information')?></div>
		<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander"></div>
	</div>


    <?php
    $edit_onclick = "og.render_modal_form('', {c:'member', a:'edit', params: {id:".$member->getId()."}});";
    $add_attendee = "og.render_modal_form('', {c:'event_ticket', a:'add_multiple'});";
    Hook::fire('active_context_widget_edit_link', $member, $edit_onclick);

    $img_url = '';
    $customer = Customers::instance()->findById($member->getObjectId());
    if ($customer instanceof Customer) {
        $contact = $customer->getContact();
        if ($contact instanceof Contact) {
            $img_url = $contact->getPictureUrl('medium');
        }
    }
    
    $member_object_type = ObjectTypes::findById($member->getObjectTypeId());

    ?>
    <div class="widget-body" id="<?php echo $genid; ?>_widget_body">
        <div style="float:right;">
       		<?php if(Plugins::instance()->isActivePlugin("event_tickets") 
       				&& $member_object_type instanceof ObjectType 
       				&& $member_object_type->getName() == 'managed_event') {
       		?>
            <button class="btn" onclick="<?php echo $add_attendee ?>" title="<?php echo lang('new event registration')?>">
                <img alt="" style="height: 15px" src="public/assets/themes/default/images/icons-feng-3/16x16/new_color.png">
                <?php echo lang('new event registration')?>
            </button>
            <?php } ?>

            <button class="btn" style="margin-left:10px;" onclick="<?php echo $edit_onclick ?>" title="<?php echo lang('edit')?>">
                <img alt="" style="height: 15px" src="public/assets/themes/default/images/icons-feng-3/16x16/edit_color.png">
                <?php echo lang('edit')?>
            </button>
            <button class="btn" style="margin-left:10px;" onclick="og.goToParent('<?php echo $member->getDimension()->getCode() ?>','<?php echo $member->getDimensionId() ?>')" title="<?php echo lang('close')?>">
                <img alt="" style="height: 15px" src="public/assets/themes/default/images/layout/close16.png">
                <?php echo lang('close') ?>
            </button>
        </div>
        <?php if($img_url != ''){?>
        <table style="width: calc(100% - 200px);">
            <tr>
                <td style="width: 50px;"><img src="<?php echo  $img_url?>" alt="" style=" height: 60px;"/></td>
                <td style="vertical-align: middle; font-size: 25px; padding-left: 5px;"><?php echo $member->getName() ?></td>
            </tr>
        </table>
        <?php } ?>
        <table><?php

		echo $prop_html . $assoc_mem_html;

	?></table>
        <?php echo $cp_html; ?>
    </div>
</div>

<script>
    og.goToParent = function(dimensionCode,dimensionId){
        var tree = Ext.getCmp("dimension-panel-"+dimensionId);
        var root_node = tree.getRootNode();
        og.memberTreeExternalClick(dimensionCode, root_node.id);
    }


</script>
