<div style="border: 2px solid #818283; width: 800px; font-family: Verdana, Arial, sans-serif; font-size: 12px; margin: 10px;">
    <div>
        <div>
            <div style="width: 50px; float: left; margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px">
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
            <div style="width: 700px; float: left; font-size: 13px; margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px">
                <span style="width: 100%; float: inherit;">
                    <?php echo $description_title ?>
                </span>
                <span style="width: 100%; float: inherit; padding-top: 6px; margin-bottom: 5px;">
                    <a href="<?php echo $object->getViewUrl() ?>" target="_blank" style="font-size: 16px;">
                        <?php echo $title ?>
                    </a>
                </span>
            </div>
        </div>
    </div>
    
    <!-- DESCRIPTION, ATTACHMENTS, LINKS, REVISION COMMENT AND DATA EVENTS-->
    <?php if ((isset($description) && $description != "") || (isset($attachments) && count($attachments) > 0) || (isset($links) && count($links) > 0) || (isset($revision_comment) && $revision_comment != "") || (isset($start) && $start != "")){?>
    <div style="clear: both; border-top: 2px solid #818283; font-family: Verdana, Arial, sans-serif; font-size: 14px; width: 100%; min-height: 200px;">
        <div style="padding: 14px;">
            <!-- DATA EVENTS -->
            
                <!-- DATE -->
                <?php if (isset($start) && $start != "") {?>
                <span style="width: 100%; line-height: 20px; display: block;">
                    <?php echo lang('date')?>: <b><?php echo $start ?></b> 
                </span>
                <?php }?>
                <!-- END DATE -->
                
                <!-- TIME -->
                <?php if (isset($time) && $time != "") {?>
                <span style="width: 100%; line-height: 20px; display: block;">
                    <?php echo lang('time')?>: <b><?php echo $time ?></b> 
                </span>
                <?php }?>
                <!-- END TIME -->
                
                <!-- DURATION -->
                <?php if (isset($duration) && $duration != "") {?>
                <span style="width: 100%; line-height: 20px; display: block;">
                    <?php echo lang('CAL_DURATION')?>: <b><?php echo $duration ?></b> 
                </span>
                <span style="width: 100%; line-height: 20px; display: block;">&nbsp;</span>
                <?php }?>
                <!-- END DURATION -->
                
            <!-- END DATA EVENTS -->
            
            <!-- DESCRIPTION -->
            <?php if (isset($description) && $description != "") {?>
                <?php if($object->getObjectTypeId() == 14){?>
                <b><?php echo lang('comment') ?>:</b>
                <?php }?>
                <?php echo $description ?>
            <?php }?>
            <!-- END DESCRIPTION -->
            
            <!-- LINKS -->
            <?php 
                if (isset($links) && is_array($links) && count($links) > 0) {?>
                <span style="width: 100%; line-height: 20px; display: block;">&nbsp;</span>
                <span style="width: 100%; line-height: 20px; display: block; font-size: 16px;">
                    <span style="font-family: Verdana, Arial, sans-serif;">
                <?php   echo lang('attendance')." ";
                        foreach ($links as $link) {
                            echo '<img src="'.$link['img'].'"/>';
                            echo '<a href="'.$link['url'].'" target="_blank">'.$link['text'].'</a> ';                        
                        }
                ?>
                    </span>
                </span>
            <?php }?>
            <!-- END LINKS -->
            
            <!-- DOCUMENT IMAGE -->
            <?php 
                if (isset($attachments) && is_array($attachments)){
                    foreach ($attachments as $att) {
                        if (array_var($att, 'disposition') != 'inline') continue;
            ?>
                    <span style="width: 100%; line-height: 20px; display: block; font-size: 16px;">
                        <img src="cid:<?php echo array_var($att, 'cid')?>" alt="" />
                    </span>
            <?php   } 
                }  
            ?>
            <!-- END DOCUMENT IMAGE -->
            
            <!-- DOCUMENT REVISION -->
            <?php if (isset($revision_comment) && $revision_comment != "") {?>
                <span style="width: 100%; line-height: 20px; display: block;">&nbsp;</span>
                <span style="width: 100%; line-height: 20px; display: block;">
                    <?php echo lang('revision comment')?>: <?php echo $revision_comment ?> 
                </span>
            <?php }?>
            <!-- END DOCUMENT REVISION -->
        </div>            
    </div>   
    <?php }?>
    <!-- END DESCRIPTION, ATTACHMENTS, LINKS, REVISION COMMENT AND DATA EVENTS -->
    
    <!-- FOOTER -->
    <div style="padding: 14px; clear: both; border-top: 2px solid #818283; font-family: Verdana, Arial, sans-serif; font-size: 12px; background-color: #BBB">
        <?php if($object->getObjectTypeId() == 14){?>
            <a href="<?php echo $object->getViewUrl() ?>" target="_blank" style="font-size: 16px;">
                <?php echo lang('view object and comments',$object_comment_name) ?>
            </a>
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
        <?php }else{?>        
        <!-- EVENT INVITATIONS -->
        <?php if (isset($guests) && $guests != "") {?>
        <div style="line-height:20px;clear:both;">
        	<div style="width:35%;line-height:20px;float:left;">
        		<b><?php echo lang('invitations')?></b>
        	</div>
        	<div style="line-height:20px;float:left;">
        		<b><?php echo lang('participate')?></b>
        	</div>
        </div>
        <?php echo $guests ?>
        <div style="clear:both">&nbsp;</div>
        <?php }?> 
        <!-- END EVENT INVITATIONS -->
        
        <!-- ASSIGNED TO, BY AND PRIORITY -->
        <div>
        <!-- ASSIGNED TO AND BY -->
        <?php if ((isset($asigned) && $asigned != "") || (isset($by) && $by != "")){?>
            <span style="width: 100%; line-height: 20px; display: block;">
            <?php if (isset($asigned) && $asigned != "") {?>
                <?php echo lang('assigned to')?>: <b><?php echo $asigned ?></b> 
            <?php }?>
            <?php if (isset($by) && $by != "") {?>
                <?php echo $object->getObjectTypeId() == 6 ? lang('author') : lang('by'); ?>: <b><?php echo $by ?></b> 
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
                	$first = true;
                    foreach ($contexts as $dimension => $context) {
                    	if ($first) {
                    		$first = false;
                    	} else {
                    		echo '<br />';
                    	}
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
                            $f = true;
                            foreach ($context as $c){
                                echo ($f ? "" : " - ") . $c;
                                $f = false;
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
        
        <?php }?>
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
    <!-- END FOOTER -->
</div>
<div style="clear: both; color: #818283; font-style: italic; padding-left: 24px; font-family: Verdana, Arial, sans-serif; font-size: 12px;">
    <?php echo lang('system notification email'); ?><br>
    <a href="<?php echo ROOT_URL; ?>" target="_blank" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><?php echo ROOT_URL; ?></a>
</div>
