<?php
require_javascript("og/modules/addFileForm.js");
if ($file->isNew()) {
	$submit_url = get_url('files', 'add_file');
} else if (isset($checkin) && $checkin) {
	$submit_url = $file->getCheckinUrl();
} else {
	$submit_url = $file->getEditUrl();
}

$genid = gen_id();
$enableUpload = $file->isNew();// || (isset($checkin) && $checkin) || ($file->getCheckedOutById() == 0) || ($file->getCheckedOutById() != 0 && logged_user()->isAdministrator()) || ($file->getCheckedOutById() == logged_user()->getId());

// on submit functions
if (array_var($_REQUEST, 'modal')) {
	if ($enableUpload) {
		$on_submit = " if (og.fileCheckSubmit('".$genid."')) { og.submit_modal_form('".$genid."addfile'); } return false;";
	} else {
		$on_submit = "og.submit_modal_form('".$genid."addfile'); return false;";
	}
} else {
	$on_submit = "return true;";
	if ($enableUpload) {
		$on_submit = "return og.fileCheckSubmit('".$genid."');";
	}
}



$object = $file;
$comments_required = config_option('file_revision_comments_required');

$has_custom_properties = CustomProperties::countAllCustomPropertiesByObjectType($object->getObjectTypeId()) > 0;
$categories = array();
Hook::fire('object_edit_categories', $object, $categories);
?>
<form onsubmit="<?php echo $on_submit ?>" class="internalForm" id="<?php echo $genid ?>addfile" name="<?php echo $genid ?>addfile" action="<?php echo $submit_url ?>"  method="post">
	<input id="<?php echo $genid ?>hfFileIsNew" type="hidden" value="<?php echo $file->isNew()?>">
	<input id="<?php echo $genid ?>hfAddFileAddType" name='file[add_type]' type="hidden" value="regular">
	<input id="<?php echo $genid ?>hfFileId" name='file[file_id]' type="hidden" value="<?php echo array_var($file_data, 'file_id') ?>">
	<input id="<?php echo $genid ?>hfType" name='file[type]' type="hidden" value="<?php echo $file->isNew() ? "" : $file->getType() ?>">
	<input name="file[upload_id]" type="hidden" value="<?php echo $genid ?>" />

<div class="file">

<div class="coInputHeader">

  <div class="coInputHeaderUpperRow" id="<?php echo $genid?>_title_label">
	<div class="coInputTitle" style="margin-top: 2px;">
		<?php echo $file->isNew() ? lang('upload file') : (isset($checkin) ? lang('checkin file') : lang('edit file properties')) ?>
	</div>
  </div>
  
  <div>
	<div class="coInputName">
	<?php if ($enableUpload) {
		if ($file->isNew()) {?>
			<div id="<?php echo $genid ?>selectFileControlDiv">
				<label class="checkbox" style="display:none;">
		    	<?php echo radio_field($genid.'_rg', true, array('id' => $genid.'fileRadio', 'onchange' => 'og.addDocumentTypeChanged(0, "'.$genid.'")', 'value' => '0'))?>
		    	<?php echo lang('file') ?>
		    	</label>
		    	<label class="checkbox" style="display:none;">
		    	<?php echo radio_field($genid.'_rg', false, array('id' => $genid.'weblinkRadio', 'onchange' => 'og.addDocumentTypeChanged(1, "'.$genid.'")', 'value' => '1'))?>
		    	<?php echo lang('weblink') ?>
		    	</label>
		        <div id="<?php echo $genid ?>fileUploadDiv">
				<?php //echo label_tag(lang('file'), $genid . 'fileFormFile', true) ?>
				<?php 
					Hook::fire('render_upload_control', array(
						"genid" => $genid,
						"attributes" => array(
							"id" => $genid . "fileFormFile",
							"class" => "title",
							"size" => "88",
								//"onchange" => "javascript:og.updateFileName('" . $genid .  "', this.value);"
						)
					), $ret);
				?>
				</div>
		    	<div id="<?php echo $genid ?>weblinkDiv" style="display:none;">
		        <?php echo label_tag(lang('weblink'), 'file[url]', true, array('id' => $genid.'weblinkLbl', 'type' => 'text')) ?>
		    	<?php echo text_field('file[url]', '', array('id' => $genid.'url', 'style' => 'width:500px;', "onchange" => "javascript:og.updateFileName('" . $genid .  "', this.value);")) ?>
		    	</div>
			</div>
		<?php } ?>
	<?php } else {
			if ($file->getType() == ProjectFiles::TYPE_WEBLINK) {
				echo text_field('file[url]',$file->getUrl(), array("id" => $genid .'fileFormUrl', 'class' => 'title'));
			} else {
				$fname = $file->getObjectName();
				$extension = "";
				if (!$file->isNew()) {
					$dot_pos = strrpos($fname, '.');
					if ($dot_pos !== false) {
						$extension = substr($fname, $dot_pos + 1);
						$fname = substr($fname, 0, $dot_pos);
					}
				}
				echo text_field('file[name]', $fname, array("id" => $genid .'fileFormFilename', 'class' => 'title'));
				echo input_field('file[extension]', $extension, array("id" => $genid .'fileFormExtension', 'type' => 'hidden'));
			}
		  }
	?>
	</div>
	
	<div class="coInputButtons" <?php if (!$enableUpload || !$file->isNew() ) { echo 'style="float:right;"'; } ?>>
		<?php echo submit_button($file->isNew() ? lang('add file') : (isset($checkin) ? lang('checkin file') : lang('save changes')),'s',array('style'=>'margin-top:0px;margin-left:2px;','id' => $genid.'add_file_submit1')) ?>
	</div>
	<div class="clear"></div>
	
	<?php if ($file->isNew()) { ?>
	<div id="<?php echo $genid ?>addFileFilename" style="<?php echo $file->isNew()? 'display:none' : '' ?>">
		<div class="coInputHeaderUpperRow" id="<?php echo $genid?>_chfilename_label">
			<div class="coInputTitle" style="margin-top: -1px;"><?php echo lang('filename') ?></div>
		</div>
		<div class="coInputName">
	      	<?php
	      		//echo label_tag(lang('new filename'), $genid .'fileFormFilename');     
	        	echo text_field('file[name]',$file->getFilename(), array("id" => $genid .'fileFormFilename', 'class' => 'title', 'style' => 'width:500px;',
	        	'onchange' => ($file->getType() == ProjectFiles::TYPE_DOCUMENT? 'javascript:og.checkFileName(\'' . $genid .  '\')' : '')));
	        
	    		if ($file->getType() == ProjectFiles::TYPE_WEBLINK) {
	        		echo label_tag(lang('new weblink'), $genid .'fileFormFilename');
	        		echo text_field('file[url]',$file->getUrl(), array("id" => $genid .'fileFormUrl', 'class' => 'title'));
	        	}
	        ?>
	    </div>
	    <div class="clear"></div>
	</div>
	<?php } ?>
	
	<?php if ($file->isNew() && $enableUpload) {?>
	<p><?php echo lang('upload file desc', format_filesize(get_max_upload_size())) ?></p>
	<?php } ?>
  </div>
</div>


<div class="coInputMainBlock">
	<div id="<?php echo $genid?>tabs" class="edit-form-tabs">
	
		<ul id="<?php echo $genid?>tab_titles">
		
			<li><a href="#<?php echo $genid?>file_data"><?php echo lang('file details') ?></a></li>
			
			<?php if ($has_custom_properties || config_option('use_object_properties')) { ?>
			<li><a href="#<?php echo $genid?>add_custom_properties_div"><?php echo lang('custom properties') ?></a></li>
			<?php } ?>
			
			<li><a href="#<?php echo $genid?>add_subscribers_div"><?php echo lang('object subscribers') ?></a></li>
			
			<?php if($object->isNew() || $object->canLinkObject(logged_user())) { ?>
			<li><a href="#<?php echo $genid?>add_linked_objects_div"><?php echo lang('linked objects') ?></a></li>
			<?php } ?>
			
			<?php foreach ($categories as $category) { ?>
			<li><a href="#<?php echo $genid . $category['name'] ?>"><?php echo $category['name'] ?></a></li>
			<?php } ?>
		</ul>
		
		<div id="<?php echo $genid ?>file_data" class="form-tab">
		
		<div id="<?php echo $genid ?>multipleFile" style="display: none;">
			<div id="<?php echo $genid ?>multipleFileNames" ></div>
		</div>
<?php if ($enableUpload) { ?>

	<?php if($file->isNew()) { //----------------------------------------------------ADD   ?>

		<div class="content">
			
			<div id="<?php echo $genid ?>addFileFilenameCheck" style="display: none">
				<h2><?php echo lang("checking filename") ?></h2>
			</div>
			<div id="<?php echo $genid ?>addFileUploadingFile" style="display: none">
				<h2><?php echo lang("uploading file") ?></h2>
			</div>

			<div id="<?php echo $genid ?>addFileFilenameExists" style="display: none">
				<h2><?php echo lang("duplicate filename")?></h2>
				<p><?php echo lang("filename exists") ?></p>
				<div style="padding-top: 10px">
				<table>
					<tr>
						<td style="height: 20px; padding-right: 4px">
							<?php echo radio_field('file[upload_option]',true, array("id" => $genid . 'radioAddFileUploadAnyway', "value" => -1)) ?>
						</td>
						<td>
							<?php echo lang('upload anyway')?>
						</td>
					</tr>
				</table>
				<table id="<?php echo $genid ?>upload-table">
				</table>
				</div>
			</div>
		</div>
		<?php if ($comments_required) { ?>
		<div class="dataBlock">
			<?php echo label_tag(lang('revision comment'), $genid.'fileFormRevisionComment', $comments_required) ?>
			<?php echo textarea_field('file[revision_comment]', array_var($file_data, 'revision_comment', lang('initial versions')), array('id' => $genid.'fileFormRevisionComment', 'class' => 'long')) ?>
		</div>
		<?php } else { ?>
			<?php echo input_field('file[revision_comment]', array_var($file_data, 'revision_comment', lang('initial versions')), array('type' => 'hidden', 'id' => $genid.'fileFormRevisionComment')) ?>
		<?php } ?>
		<input type='hidden' id ="<?php echo $genid ?>RevisionCommentsRequired" value="<?php echo $comments_required? '1':'0'?>"/>

	<?php }  else {//----------------------------------------------------------------EDIT?>

		<div class="content">
			<?php 
			if($file->getType() == ProjectFiles::TYPE_DOCUMENT){
				if (!isset($checkin)) { ?>
				<div class="header dataBlock">
					<input type="checkbox" name="file[update_file]" <?php echo array_var($file_data, 'update_file') ? 'checked="checked"': ''?> id="<?php echo $genid?>fileFormUpdateFile" onclick="App.modules.addFileForm.updateFileClick('<?php echo $genid?>')"/>
					<?php echo label_tag(lang('update file'), $genid .'fileFormUpdateFile', false, array('class' => 'checkbox'), '') ?>
				</div>
				<div class="clear"></div>
				<div id="<?php echo $genid ?>updateFileDescription">
					<p><?php echo lang('replace file description') ?></p>
				</div>
			<?php } // if ?>
			<div id="<?php echo $genid ?>updateFileForm" class="dataBlock" style="<?php echo isset($checkin) ? '': 'display:none' ?>">
				<p class="dataBlock">
					<label><?php echo lang('existing file') ?>:</label>
					<a target="_blank" href="<?php echo $file->getDownloadUrl() ?>" id="extension_old"><?php echo clean($file->getFilename()) ?></a>
					| <?php echo format_filesize($file->getFilesize()) ?>
				</p>
				<p id="warning_extension_file"></p>
				<div id="<?php echo $genid ?>selectFileControlDiv" class="dataBlock">
					<?php echo label_tag(lang('new file'), $genid.'fileFormFile', true) ?>
					<?php
						Hook::fire('render_upload_control', array(
							"genid" => $genid,
							"attributes" => array(
								"id" => $genid . "fileFormFile",
								"size" => 88,
								"style" => 'width:530px',
							)
						), $ret);
					?>
				</div>
				<div id="<?php echo $genid ?>revisionControls" class="dataBlock">
					<div>
						<input type="checkbox" name="file[version_file_change]" <?php echo array_var($file_data, 'version_file_change') ? 'checked="checked"': ''?> id="<?php echo $genid?>fileFormVersionChange" />
						<?php echo label_tag(lang('version file change'), $genid.'fileFormVersionChange', false, array('class' => 'checkbox'), '') ?>
					</div>
					<div id="<?php echo $genid ?>fileFormRevisionCommentBlock">
						<?php echo label_tag(lang('revision comment'), $genid.'fileFormRevisionComment', $comments_required) ?>
						<?php echo textarea_field('file[revision_comment]', array_var($file_data, 'revision_comment'), array('class' => 'long', 'id' => $genid.'fileFormRevisionComment')) ?>
						<input type='hidden' id ="<?php echo $genid ?>RevisionCommentsRequired" value="<?php echo $comments_required? '1':'0'?>"/>
					</div>
				</div>
			</div>
			<?php } ?>
			<?php if (!isset($checkin) && $file->getType() == ProjectFiles::TYPE_DOCUMENT) {?>
				<script>
					App.modules.addFileForm.updateFileClick('<?php echo $genid ?>');
					App.modules.addFileForm.versionFileChangeClick('<?php echo $genid ?>');
				</script>
			<?php } // if ?>
			<div class="clear"></div>
		</div>
	<?php } // if type add / edit ?>
<?php } // if enableupload ?>

		<div id="<?php echo $genid ?>add_file_select_context_div" >
			<?php
			$listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$object->manager()->getObjectTypeId().')'); 
			if ($file->isNew()) {
				render_member_selectors($file->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners), null, null, false);
			} else {
				render_member_selectors($file->manager()->getObjectTypeId(), $genid, $file->getMemberIds(), array('listeners' => $listeners), null, null, false);
			} ?>
			<?php if (!$file->isNew()) {?>
				<div id="<?php echo $genid ?>addFileFilenameCheck" style="display: none">
					<h2><?php echo lang("checking filename") ?></h2>
				</div>
				<div id="<?php echo $genid ?>addFileUploadingFile" style="display: none">
					<h2><?php echo lang("uploading file") ?></h2>
				</div>
				<div id="<?php echo $genid ?>addFileFilenameExists" style="display: none">
					<h2><?php echo lang("duplicate filename")?></h2>
					<?php echo lang("filename exists edit") ?>
				</div>
			<?php } // if ?>
			<div class="clear"></div>
		</div>
		
		<?php if ($file->getType() == ProjectFiles::TYPE_WEBLINK) { ?>
		<div class="dataBlock">
			<input type="checkbox" name="file[version_file_change]" <?php echo array_var($file_data, 'version_file_change') ? 'checked="checked"': ''?> id="<?php echo $genid?>fileFormVersionChange" />
			<?php echo label_tag(lang('version file change'), $genid.'fileFormVersionChange', false, array('class' => 'checkbox'), '') ?>
		</div>
		<div id="<?php echo $genid ?>fileFormRevisionCommentBlock" class="dataBlock">
			<?php echo label_tag(lang('revision comment'), $genid.'fileFormRevisionComment', $comments_required) ?>
			<?php echo textarea_field('file[revision_comment]', array_var($file_data, 'revision_comment'), array('class' => 'long', 'id' => $genid.'fileFormRevisionComment')) ?>
			<input type='hidden' id ="<?php echo $genid ?>RevisionCommentsRequired" value="<?php echo $comments_required? '1':'0'?>"/>
		</div>
		<?php } ?>
		<div class="clear"></div>

		<div class="dataBlock">
			<?php echo label_tag(lang('description')) ?>
			<?php echo textarea_field('file[description]', array_var($file_data, 'description'), array('rows' => '5', 'style' => 'width: 500px;' , 'id' => $genid.'fileFormDescription')) ?>
		</div>
		<div class="clear"></div>
	</div>


	

	<?php if ($has_custom_properties || config_option('use_object_properties')) { ?>
	<div id="<?php echo $genid ?>add_custom_properties_div" class="form-tab">
			<?php echo render_object_custom_properties($object, false) ?>
      		<?php echo render_add_custom_properties($object); ?>
		</fieldset>
	</div>
	<?php } ?>

		<div id="<?php echo $genid ?>add_subscribers_div" class="form-tab">
		
			<?php $subscriber_ids = array();
				if (!$object->isNew()) {
					$subscriber_ids = $object->getSubscriberIds();
				} else {
					$subscriber_ids[] = logged_user()->getId();
				}
			?><input type="hidden" id="<?php echo $genid ?>subscribers_ids_hidden" value="<?php echo implode(',',$subscriber_ids)?>"/>
			<div id="<?php echo $genid ?>add_subscribers_content">
				<?php //echo render_add_subscribers($object, $genid); ?>
			</div>
			
			<div id="<?php echo $genid ?>configuration_content">
				<input type="checkbox" name="file[attach_to_notification]" id="<?php echo $genid?>eventAttachNotification" style="margin: 3px; float: left;" 
					<?php echo array_var($file_data, 'attach_to_notification', user_config_option('attach_to_notification')) ? 'checked="checked"': ''?>/>
				<label for="<?php echo $genid ?>eventAttachNotification" class="checkbox"><?php echo lang('attach to notification') ?></label>
				<?php if (ContactConfigOptions::getByName('notify_myself_too') instanceof ContactConfigOption) : ?>
							
				<input type="checkbox" name="file[notify_myself_too]" id="<?php echo $genid?>notifyMyselfToo" style="margin: 3px 3px 3px 15px; float: left;"
					<?php echo user_config_option('notify_myself_too') ? 'checked="checked"': ''?>/>
				<label for="<?php echo $genid ?>notifyMyselfToo" class="checkbox"><?php echo lang('notify myself too') ?></label>
				<div class="clear"></div>
				<?php endif; ?>
			</div>
			
			<div style="width: 400px; align: center; text-align: left;">
				<table>
					<tr>
						<td colspan="2" style="vertical-align:middle;" >
							<label><?php echo lang('subject for email notification')?></label>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="vertical-align:middle; height: 22px;">
							<label><?php echo radio_field('file[default_subject_sel]',true,array('value' => 'default')) ."&nbsp;". lang('use default subject')?></label>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="vertical-align:middle; height: 22px;"><?php 
							$sel = false;
							if(array_var($file_data, 'default_subject') != ""){
							    $sel = true;
							}										
							echo radio_field('file[default_subject_sel]',$sel,array('value' => 'subject'));
							echo "&nbsp;" . text_field('file[default_subject_text]',array_var($file_data, 'default_subject'), array('style'=>'width:300px', 'placeholder' => lang('enter a custom subject')));
						?></td>
					</tr>
				</table>
			</div>
		
		</div>

	<?php if($object->isNew() || $object->canLinkObject(logged_user())) { ?>
		<div id="<?php echo $genid ?>add_linked_objects_div" class="form-tab">
			<?php echo render_object_link_form($object) ?>
		</div>
	<?php } // if ?>

	<?php foreach ($categories as $category) { ?>
	<div id="<?php echo $genid . $category['name'] ?>" class="form-tab">
		<?php echo $category['content'] ?>
	</div>
	<?php } ?>

	
</div>
	
<?php if (!array_var($_REQUEST, 'modal')) { ?>
	<div id="<?php echo $genid ?>fileSubmitButton" style="display: inline">
		<input type="hidden" name="upload_id" value="<?php echo $genid ?>" />
		<?php
			if (!$file->isNew()) { //Edit file
				if (isset($checkin) && $checkin) {
					echo submit_button(lang('checkin file'),'s',array("id" => $genid.'add_file_submit2'));
				} else {
					echo submit_button(lang('save changes'),'s',array("id" => $genid.'add_file_submit2'));
				}
			} else { //New file
				echo submit_button(lang('add file'),'s',array("id" => $genid.'add_file_submit2'));
			}
		?>
	</div>
<?php } ?>
</div>
</form>

<script>
	
	var ctl = Ext.get('<?php echo $genid ?>fileFormFile');
	if (ctl) ctl.focus();
        
        $(document).ready(function() {
            $('#<?php echo $genid ?>fileFormFile').change(function () {
                var extension = this.value.split('.');
                var ext_old_html = $('#extension_old').html();
                var extension_old = ext_old_html ? ext_old_html.split('.') : "";
                if(extension_old[1] != extension[1]){
                    var html = "<strong style='color:#FF0000'><?php echo lang('warning file extension type') ?></strong>";                
                    $('#warning_extension_file').html(html);
                }else{
                    $('#warning_extension_file').html("");
                }
            })
			
			//check if support input type=file/multiple
         	function supportMultiple() {
				var el = document.createElement("input");
				return ("multiple" in el);
			}

			var is_new = <?php echo $file->isNew() ? '1' : '0'; ?>;

			if (is_new) {
				if(supportMultiple()) {
	            	$('#<?php echo $genid ?>addfile').attr("action", "<?php echo get_url('files', 'add_multiple_files') ?>");
	            	$('#<?php echo $genid ?>fileFormFile').attr("name", "file_file[]");
	            	$('#<?php echo $genid ?>fileFormFile').attr("multiple", "multiple");
	            }else{
					og.msg(lang("upload multiple files"), lang("for upload multiple files upgrade your browser"), 6, "err");
				}
			} 

			$('#<?php echo $genid ?>fileFormFile').change(function (){
				if(is_new && supportMultiple()) {
			        // if select more than one file upload all files as new files
				    if(this.files.length > 1){
				    	$('#<?php echo $genid ?>multipleFileNames').empty();
				    	$('#<?php echo $genid ?>multipleFile').show();
				    	$('#<?php echo $genid ?>addFileFilenameExists').hide();
				    	$('#<?php echo $genid ?>addFileFilename').hide();
				    	var $ul = $("<ul>");
				    	for (var i = 0; i < this.files.length; ++i) {
				          var name = this.files.item(i).name;
				          var $li = $("<li>");
				          $li.text(name);
				          $ul.append($li);
				        }
				    	$('#<?php echo $genid ?>multipleFileNames').append($ul);
					}else{
						// if select one file check the file name
						$('#<?php echo $genid ?>multipleFile').hide();
						og.updateFileName("<?php echo $genid ?>", this.files.item(0).name);
					}
			    }else{
				   og.updateFileName("<?php echo $genid ?>", this.value);
				}

				og.resize_modal_form();
	        });
        });
        
        $("#<?php echo $genid?>tabs").tabs();

        setTimeout(function() {
        	<?php if ($file->isNew()) { ?>
            var w = $(".simplemodal-data .coInputHeader .coInputName input.title").width();
        	$(".simplemodal-data .coInputHeader .coInputName input.title").css('width', (2*w)+'px');
        	$(".simplemodal-data .coInputHeader .coInputName").css('width', (2*w + 10)+'px');
        	
        	<?php } ?>

        	var wl1 = $("#<?php echo $genid?>_title_label .coInputTitle").width();
        	var wl2 = $("#<?php echo $genid?>_chfilename_label .coInputTitle").width();
        	var wl3 = wl1 > wl2 ? wl1 : wl2;
        	$("#<?php echo $genid?>_title_label .coInputTitle").css('width', wl3+'px');
        	$("#<?php echo $genid?>_chfilename_label .coInputTitle").css('width', wl3+'px');
        	
        	$(".simplemodal-data .coInputHeader .coInputName").css('white-space', 'nowrap');

        }, 500);
</script>