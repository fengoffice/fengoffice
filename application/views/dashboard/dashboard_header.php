<?php

    // Get active members
    $active_members = array();
    $context = active_context();
    if (is_array($context)) {
        foreach ($context as $selection) {
            if ($selection instanceof Member) $active_members[] = $selection;
        }
    }

    $members = array();
    if (count($active_members) == 1) {
            // Populate $member, $member_type, $member_name
            $member = $active_members[0];
            $member_type = $member->getTypeNameToShow();
            $member_name = clean($member->getName());
    } else if (count($active_members) > 1) {
        // Populate $members array
        foreach($active_members as $act_member){
            $members[] = array($act_member->getTypeNameToShow(), clean($act_member->getName()));
        }
    }

    // Build $members_table, that includes member type and member name, 
    if(isset($members)) {
        $members_table = '';
        foreach($members as $memb){
            $members_table .= '<tr class="dashboard-header-table-row"><td class="dashboard-header-td dashboard-header-td--member-type">' . $memb[0] . '</td><td class="dashboard-header-td">' . $memb[1] . '</td></tr>';
        }
    }

    // Setup edit button onclick function
    if (isset($member)) {
        $edit_onclick = "og.render_modal_form('', {c:'member', a:'edit', params: {id:".$member->getId()."}});";
        Hook::fire('active_context_widget_edit_link', $member, $edit_onclick);
    }
?>


<?php if (isset($member) && count($active_members) == 1) : ?>
    <!--
    Render Dashboard header with:
        - Member type
        - Member name
        - 'View as a list' button
        - 'Edit' button
        - 'Close' button
    -->
    <div class="dashboard-header">
        
        <div class="dashboard-title">
            <div class="dashboard-title--member-type"><?php echo $member_type ?></div>
            <div><?php echo $member_name ?></div>
        </div>
        
        <?php 
            $buttons_html = "";
            Hook::fire("more_active_context_widget_buttons", array("member" => $member), $buttons_html);
            if ($buttons_html) {
                echo $buttons_html;
            }
        ?>

        <div class="dashboard-header--buttons">
            <div class="dashActions" style="float: right;">
                <?php $actions = array(); 
                    Hook::fire('additional_dashboard_actions', null, $actions);
                    foreach ($actions as $action) {
                        $href = array_var($action, 'href', '#');
                        $target_str = array_var($action, 'target') ? 'target="'.array_var($action, 'target').'"' : '';
                        
                        echo '<a href="'.$href.'" '.$target_str.' onclick="'. $action['onclick'] .'" class="dashAction '. $action['class'] .'">'. $action['name'] .'</a>';
                    }
                ?>
                <a class="internalLink dashAction link-ico ico-grid" href="#" onclick="og.switchToOverview(); return false;"><?php echo lang('view as list') ?></a>
                
            </div>

            <div>
                <button class="btn" style="margin-left:10px;" onclick="event.stopPropagation(); <?php echo $edit_onclick ?>" title="<?php echo lang('edit')?>">
                    <img alt="" style="height: 15px" src="public/assets/themes/default/images/icons-feng-3/16x16/edit_color.png">
                    <?php echo lang('edit')?>
                </button>
            </div>
            <div>
                <button class="btn" style="margin-left:10px;" onclick="og.Breadcrumbs.resetSelection();">
                    <img alt="" style="height: 15px" src="public/assets/themes/default/images/layout/close16.png">
                    <?php echo lang('close') ?>
                </button>
            </div>
        </div>
    </div>

<?php elseif (isset($members) && count($active_members) > 1): ?>
    <!--
    Render Dashboard header with:
        - List of filters, that displays member type and member name of each filter
        - 'View as a list' button
        - 'Remove filters' button
    -->
    <div class="dashboard-header-filters">
        <div class="dashboard-header-filters--content">
            <p>Viewing information with the following filters: </p>
            <table class="dashboard-header-table">
                <?php echo $members_table ?>
            </table>
        </div>
        <div class="dashboard-header-filters--buttons">
            <div>
                <button class="btn dashboard-header-filters--button" onclick="og.Breadcrumbs.resetSelection();" title="<?php echo lang('close')?>">
                    <?php echo lang('remove filters') ?>
                    <img alt="" style="height: 15px" src="public/assets/themes/default/images/layout/close16.png">
                </button>
            </div>
            <div class="dashActions">
                <?php $actions = array(); 
                    Hook::fire('additional_dashboard_actions', null, $actions);
                    foreach ($actions as $action) {
                        $href = array_var($action, 'href', '#');
                        $target_str = array_var($action, 'target') ? 'target="'.array_var($action, 'target').'"' : '';
                        
                        echo '<a href="'.$href.'" '.$target_str.' onclick="'. $action['onclick'] .'" class="dashAction '. $action['class'] .'">'. $action['name'] .'</a>';
                    }
                ?>
                <a class="internalLink dashAction link-ico ico-grid" href="#" onclick="og.switchToOverview(); return false;"><?php echo lang('view as list') ?></a>	
            </div>
        </div>
    </div>
    
<?php endif; ?>