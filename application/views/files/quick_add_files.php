<?php

require_javascript("og/modules/addFileForm.js");
require_javascript('og/modules/addMessageForm.js');

$submit_url = get_url('files', 'quick_add_files');

$enableUpload = $file->isNew();
if (!isset($genid)) $genid = gen_id();
$object = $file;

?>
<div class="outter-quick-add-file">
<div class="inner-quick-add-file">
<form class="internalForm" style="height: 100%;" id="<?php echo $genid ?>quickaddfile" name="<?php echo $genid ?>quickaddfile" action="<?php echo $submit_url ?>" method="post">
	
	<input id="<?php echo $genid ?>hfFileIsNew" type="hidden" value="<?php echo $file->isNew()?>" />
	<input id="<?php echo $genid ?>hfAddFileAddType" name='file[add_type]' type="hidden" value="regular" />
	<input id="<?php echo $genid ?>hfType" name='file[type]' type="hidden" value="" />
	<input id="genidhidden" name="file[upload_id]" type="hidden" value="<?php echo $genid ?>" />
	<input id="<?php echo $genid ?>object_id" name="file[object_id]" value="<?php echo $object_id ?>" type="hidden" />
	<input id="<?php echo $genid ?>new_rev_file_id" name="file[new_rev_file_id]" value="<?php echo $new_rev_file_id;?>" type="hidden" />
	
	<input id="<?php echo $genid ?>no_msg" name="no_msg" value="" type="hidden" />
	<input id="<?php echo $genid ?>temp_id" name="temp_id" value="" type="hidden" />        
	
	<input id="<?php echo $genid ?>member_ids" name="file[member_ids]" value="" type="hidden" />
	
	<input id="<?php echo $genid ?>subscribers" name="subscribers[user_<?php echo logged_user()->getId()?>]" value="checked" type="hidden" />
	<input id="<?php echo $genid ?>composing_mail" name="file[composing_mail]" value="<?php echo (isset($composing_mail) && $composing_mail ? '1':'0')?>" type="hidden" />
	

<?php if ($file->isNew()) {?>
		<div id="<?php echo $genid ?>selectFileControlDiv">
		<?php if ($new_rev_file_id == "") { ?>
			<?php echo radio_field($genid.'_rg', true, array('id' => $genid.'fileRadio', 'onchange' => 'og.addDocumentTypeChanged(0, "'.$genid.'")', 'value' => '0')) . ' ' . lang('file')?>
	    	<?php echo radio_field($genid.'_rg', false, array('id' => $genid.'weblinkRadio', 'onchange' => 'og.addDocumentTypeChanged(1, "'.$genid.'")', 'value' => '1')) . ' ' . lang('weblink')?>
	    <?php } ?>
	        <div id="<?php echo $genid ?>fileUploadDiv" style="margin-top:5px;">
			<?php //echo label_tag(lang('file'), $genid . 'fileFormFile', true) ?>
			<h2><?php echo lang('file');?>: <span class="label_required">*</span></h2>
			<?php 
				Hook::fire('render_upload_control', array(
					"genid" => $genid,
					"attributes" => array(
						"id" => $genid . "fileFormFile",
						"class" => "title",
						"size" => "50",
						"tabindex" => "10"
						//"onchange" => "javascript:og.updateFileName('" . $genid .  "', this.value);"
					)
				), $ret);
			?>
			<p><?php echo lang('upload file desc', format_filesize(get_max_upload_size())) ?></p>
			</div>
	    	<div id="<?php echo $genid ?>weblinkDiv" style="margin-top:5px; display:none;">
	        	<?php //echo label_tag(lang('weblink'), 'file[url]', true, array('id' => $genid.'weblinkLbl', 'type' => 'text')) ?>
	        	<h2><?php echo lang('weblink');?>: <span class="label_required">*</span></h2>
	    		<?php echo text_field('file[url]', '', array('id' => $genid.'url', 'style' => 'width:500px;', "onchange" => "javascript:og.updateFileName('" . $genid .  "', this.value);")) ?>
	    	</div>
		</div>
	<?php } ?>
	<div id="<?php echo $genid ?>addFileFilename" style="<?php echo $file->isNew()? 'display:none' : '' ?>">
      	<?php echo label_tag(lang('new filename'), $genid .'fileFormFilename') ?>
        <?php echo text_field('file[name]',$file->getFilename(), array("id" => $genid .'fileFormFilename', 'tabindex' => '20', 'class' => 'title', 
        	'onchange' => ($file->getType() == ProjectFiles::TYPE_DOCUMENT? 'javascript:og.checkFileName(\'' . $genid .  '\')' : ''))) ?>
        
    	<?php if ($file->getType() == ProjectFiles::TYPE_WEBLINK){?>
        <?php echo label_tag(lang('new weblink'), $genid .'fileFormFilename') ?>
        <?php echo text_field('file[url]',$file->getUrl(), array("id" => $genid .'fileFormUrl', 'class' => 'title', 'tabindex' => '21')) ?>   
        <?php } //else ?>
		<br/>
    </div>

	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
		


	<?php if($file->isNew()) { //----------------------------------------------------ADD   ?>

		<div class="content">
			<fieldset id="<?php echo $genid ?>multipleFile" style="display: none;">
				<legend><?php echo lang("files") ?></legend>
				<div id="<?php echo $genid ?>multipleFileNames" ></div>
			</fieldset>
			<div id="<?php echo $genid ?>addFileFilenameCheck" style="display: none">
				<h2><?php echo lang("checking filename") ?></h2>
			</div>
			<div id="<?php echo $genid ?>addFileingFile" style="display: none">
				<h2><?php echo lang("ing file") ?></h2>
			</div>

			<div id="<?php echo $genid ?>addFileFilenameExists" style="display: none">
				<h2><?php echo lang("duplicate filename")?></h2>
				<p><?php echo lang("filename exists") ?></p>
				<div style="padding-top: 10px">
				<table>
					<tr>
						<td style="height: 20px; padding-right: 4px">
							<?php echo radio_field('file[upload_option]',true, array("id" => $genid . 'radioAddFileAnyway', "value" => -1, 'tabindex' => '30')) ?>
						</td><td>
							<?php echo lang('upload anyway')?>
						</td>
					</tr>
				</table>
				<table id="<?php echo $genid ?>upload-table">
				</table>
				</div>
			</div>
		</div>
		
	<?php } ?>


<?php if (!$file->isNew()) {?>
	<div id="<?php echo $genid ?>addFileFilenameCheck" style="display: none">
		<h2><?php echo lang("checking filename") ?></h2>
	</div>
	<div id="<?php echo $genid ?>addFileingFile" style="display: none">
		<h2><?php echo lang("ing file") ?></h2>
	</div>
	<div id="<?php echo $genid ?>addFileFilenameExists" style="display: none">
		<h2><?php echo lang("duplicate filename")?></h2>
		<?php echo lang("filename exists edit") ?>
	</div>
<?php } // if ?>

<div id="<?php echo $genid ?>addFileRevisionComments" style="margin-top:10px;">
	<h2><?php echo lang('revision comment')?>:</h2>
	<?php echo textarea_field('file[revision_comment]', '', array('class' => 'long'))?>
</div>

<script>
var not_use_multiple = <?php echo $new_rev_file_id > 0 ? 'true' : 'false'?>;
//check if support input type=file/multiple
function supportMultiple() {
	var el = document.createElement("input");
	return ("multiple" in el);
}  
if(supportMultiple() && !not_use_multiple) {
	$('#<?php echo $genid ?>quickaddfile').attr("action", "<?php echo get_url('files', 'quick_add_multiple_files') ?>");
	$('#<?php echo $genid ?>fileFormFile').attr("name", "file_file[]");
	$('#<?php echo $genid ?>fileFormFile').attr("multiple", "multiple");
}

$('#<?php echo $genid ?>fileFormFile').change(function (){
	if(supportMultiple() && !not_use_multiple) {
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
});
</script>

</form>
</div>
</div>