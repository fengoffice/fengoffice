<?php
set_page_title(lang('file extension prevention uploading'));
?>
<div class="adminConfiguration" style="height:100%;background-color:white">
    
    <div class="coInputHeader">
	  <div>
		<div class="coInputName">
			<div class="coInputTitle">
		        <?php echo lang('file extension prevention uploading') ?>
			</div>
		</div>
		<div class="clear"></div>
	  </div>
	</div>
    <div class="adminMainBlock">
        <div class="page tab-manager">
            <form method = "POST" action="<?php echo get_url("administration", "documents_allow_submit") ?>" >
                <table>
                    <tr>
                        <th><?php echo lang("file extension") ?></th>
                        <th><?php echo lang("allow") ?></th>
                    </tr>
                    <?php $isAlt = true; foreach ($file_types as $file_type) : $isAlt = !$isAlt; ?>
                        <tr class="<?php echo $isAlt? 'altRow' : ''?>">
                            <td>
                                <?php echo $file_type->getExtension() ?>
                                <input type="hidden" value="<?php echo $file_type->getId()?>" name="file_types[<?php echo $file_type->getId()?>][id]"/>
                            </td>
                            <td>
                                <input 
                                    type="checkbox" 
                                    name="file_types[<?php echo $file_type->getId() ?>][allow]" 
                                    <?php echo ( $file_type->getIsAllow() ) ? "checked='checked'" : "" ?>/>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <input class="submit" type="submit" value="Save changes"></input>
            </form>
        </div>
    </div>
</div>



