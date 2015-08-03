<div style="border: 2px solid #818283; width: 800px; font-family: Verdana, Arial, sans-serif; font-size: 12px; margin: 10px;">
    <div>
        <div>
            <div style="width: 50px; float: left; margin: 10px">
                <?php 
                if (isset($attachments['logo']) && is_array($attachments['logo']) || file_exists(ROOT . '/tmp/logo_empresa.png')){
                ?>
<!--                    <img src="cid:<?php echo $attachments['logo']['cid']?>"/>-->
                    <img style="max-height: 50px;max-width: 70px;" src="<?php echo ROOT_URL . '/tmp/logo_empresa.png' ?>"/>
                <?php 
                    unset($attachments['logo']);
                }else{  
                ?>
                    <img src="<?php echo ROOT_URL . '/public/assets/themes/default/images/layout/logo_1.png' ?>"/>
                <?php } ?>  
            </div> 
            <div style="width: 700px; float: left; font-size: 13px;  margin-top: 10px">
                <span style="width: 100%; float: inherit;">
                    <?php echo $description_title ?>
                </span>
                <span style="width: 100%; float: inherit; padding-top: 6px; margin-bottom: 5px;">
                    <a href="<?php echo $task_assigned->getViewUrl() ?>" target="_blank" style="font-size: 16px;">
                        <?php echo $title ?>
                    </a>
                </span>
            </div>
        </div>
    </div>
    <!-- DESCRIPTION -->
    <?php if (isset($description) && $description != ""){?>
    <div style="clear: both; border-top: 2px solid #818283; font-family: Verdana, Arial, sans-serif; font-size: 14px; width: 100%; min-height: 200px;">
        <div style="padding: 14px;">
            <?php echo $description ?>
        </div>            
    </div>   
    <?php }?>
    <!-- END DESCRIPTION -->
    <div style="padding: 14px; clear: both; border-top: 2px solid #818283; font-family: Verdana, Arial, sans-serif; font-size: 12px; background-color: #BBB">
        <!-- ASSIGNED TO, BY AND PRIORITY -->
        <div>
        <!-- ASSIGNED TO AND BY -->
        <?php if ((isset($asigned) && $asigned != "") || (isset($by) && $by != "")){?>
            <span style="width: 100%; line-height: 20px; display: block;">
            <?php if (isset($asigned) && $asigned != "") {?>
                <?php echo lang('assigned to')?>: <b><?php echo $asigned ?></b> 
            <?php }?>
            <?php if (isset($by) && $by != "") {?>
                <?php echo lang('by'); ?>: <b><?php echo $by ?></b> 
            <?php }?>                
            </span>
        <?php }?>
        <!-- END ASSIGNED TO AND BY -->
        
        <!-- PRIORITY -->
        <?php 
            if (isset($priority) && $priority != "") {
                $prority = $priority[0];
                if($priority[1] == "white"){
                    $back_color = "font-size: 12px;";
                }elseif($priority[1] == "#DAE3F0"){
                    $back_color = "font-size: 90%; padding:1px 5px; color: black; background-color: " . $priority[1];
                }else{
                    $back_color = "font-size: 90%; padding:1px 5px; color: white; background-color: " . $priority[1];
                }    
        ?>
                <span style="width: 100%; line-height: 20px; display: block;">
                    <?php echo lang('priority')?>: <span style="font-family : Verdana,Arial,sans-serif; <?php echo $back_color?>"><?php echo $prority ?></span>
                </span>
        <?php }?>
        <!-- END PRIORITY -->
        </div>
        <!-- END ASSIGNED TO, BY AND PRIORITY -->
        <div>
            <span style="width: 100%; line-height: 20px; display: block;">
                <!-- CONTEXTS -->
                <?php 
                    foreach ($contexts as $dimension => $context) { 
                        if($dimension == "customer_project" || $dimension == "customers"){
                            foreach ($context as $obj => $cont){
                                echo lang($obj). ": ";
                                foreach ($cont as $c){
                                    echo $c;
                                } 
                                echo "&nbsp;";
                            }
                        }else{
                            echo lang($dimension). ": ";
                            foreach ($context as $c){
                                echo $c;
                            }
                            echo "&nbsp;";
                        }                        
                    }
                ?>
                <!-- CONTEXTS -->
            </span>
        </div>
        <div>
            <!-- DUE DATE AND START DATE -->
            <?php if ((isset($due_date) && $due_date != "") || (isset($start_date) && $start_date != "")){?>
            <span style="width: 100%; line-height: 20px; display: block;">
                <!-- DUE DATE -->
                <?php if (isset($due_date) && $due_date != "") {?>
                    <?php echo lang('due date')?>: <b><?php echo $due_date ?></b> 
                <?php }?>
                <!-- END DUE DATE -->
                
                <!-- START DATE -->
                <?php if (isset($start_date) && $start_date != "") {?>
                    <?php echo lang('start date')?>: <b><?php echo $start_date ?></b> 
                <?php }?>
                <!-- END START DATE -->
            </span>
            <?php }?>
            <!-- END DUE DATE AND START DATE -->
        </div>
        <div>
            <!-- SUBSCRIBERS -->
            <?php if ((isset($subscribers) && $subscribers != "")){?>
            <span style="width: 100%; line-height: 20px; display: block;">
                <?php echo lang('subscribers')?>: <?php echo $subscribers ?>
            </span>
            <?php }?>
            <!-- END SUBSCRIBERS -->
        </div>
    </div>
</div>
<div style="clear: both; color: #818283; font-style: italic; padding-left: 24px; font-family: Verdana, Arial, sans-serif; font-size: 12px;">
    <?php echo lang('system notification email'); ?><br>
    <a href="<?php echo ROOT_URL; ?>" target="_blank" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><?php echo ROOT_URL; ?></a>
</div>
