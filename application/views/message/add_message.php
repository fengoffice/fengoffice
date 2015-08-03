<?php
	require_javascript('og/modules/addMessageForm.js'); 
	$genid = gen_id();
	$object = $message;
	$loc = user_config_option('localization');
	if (strlen($loc) > 2) $loc = substr($loc, 0, 2);
	$categories = array();
	Hook::fire('object_edit_categories', $object, $categories);
	
	// on submit functions
	if (array_var($_REQUEST, 'modal')) {
		$on_submit = "og.setDescription(); og.submit_modal_form('".$genid."submit-edit-form'); return false;";
	} else {
		$on_submit = "og.setDescription();";
	}
	
	$has_custom_properties = CustomProperties::countAllCustomPropertiesByObjectType($object->getObjectTypeId()) > 0;
?>
<form onsubmit="<?php echo $on_submit?>" class="add-message" id="<?php echo $genid ?>submit-edit-form" style='height:100%;background-color:white' action="<?php echo $message->isNew() ? get_url('message', 'add') : $message->getEditUrl() ?>" method="post" enctype="multipart/form-data" >
<div class="message">
<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo $message->isNew() ? lang('new message') : lang('edit message') ?>
	</div>
  </div>

  <div>
	<div class="coInputName">
	<?php echo text_field('message[name]', array_var($message_data, 'name'), 
		array('id' => $genid . 'messageFormTitle', 'class' => 'title', 'placeholder' => lang('type name here'))) ?>
	</div>
		
	<div class="coInputButtons">
		<?php echo submit_button($message->isNew() ? lang('add message') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
	</div>
	<div class="clear"></div>
  </div>
</div>

<div class="coInputMainBlock">
	
	<input id="<?php echo $genid?>merge-changes-hidden" type="hidden" name="merge-changes" value="">
	<input id="<?php echo $genid?>genid" type="hidden" name="genid" value="<?php echo $genid ?>">
	<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo !$message->isNew() ? $message->getUpdatedOn()->getTimestamp() : '' ?>">
	
	<div id="<?php echo $genid?>tabs" class="edit-form-tabs">
	
		<ul id="<?php echo $genid?>tab_titles">
		
			<li><a href="#<?php echo $genid?>add_message_text"><?php echo lang('details') ?></a></li>
			<li><a href="#<?php echo $genid?>add_message_select_context_div"><?php echo lang('related to') ?></a></li>
			
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
	
		<div id="<?php echo $genid ?>add_message_select_context_div" class="context-selector-container form-tab">
		<?php
			$listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$object->manager()->getObjectTypeId().')');
			if ($message->isNew()) {
				render_member_selectors($message->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners), null, null, false);
			} else {
				render_member_selectors($message->manager()->getObjectTypeId(), $genid, $message->getMemberIds(), array('listeners' => $listeners), null, null, false);
			} 
		?>
		</div>
		
		<div id="<?php echo $genid ?>add_message_text" class="editor-container form-tab">
		
		<?php
			if(config_option("wysiwyg_messages")){
				if($message->isNew()) {
					$ckEditorContent = '';
				} else {
					if(array_var($message_data, 'type_content') == "text"){
						$ckEditorContent = nl2br(htmlspecialchars(array_var($message_data, 'text')));
					}else{
						$ckEditorContent = purify_html(nl2br(array_var($message_data, 'text')));
					}
				}
			?>
	        <div>
	            <div id="<?php echo $genid ?>ckcontainer" style="height: 400px">
	                <textarea cols="80" id="<?php echo $genid ?>ckeditor" name="message[text]" rows="10"><?php echo clean($ckEditorContent) ?></textarea>
	            </div>
	        </div>
	        
	        <script>
	            var h = document.getElementById("<?php echo $genid ?>ckcontainer").offsetHeight;
	            var editor = CKEDITOR.replace('<?php echo $genid ?>ckeditor', {
	            	//height: (h-45) + 'px',
	            	height: '330px',
	            	allowedContent: true,
	            	resize_enabled: false,
	            	enterMode: CKEDITOR.ENTER_DIV,
	            	shiftEnterMode: CKEDITOR.ENTER_BR,
	            	disableNativeSpellChecker: false,
	            	language: '<?php echo $loc ?>',
	            	customConfig: '',
	            	contentsCss: ['<?php echo get_javascript_url('ckeditor/contents.css').'?rev='.product_version_revision();?>', '<?php echo get_stylesheet_url('og/ckeditor_override.css').'?rev='.product_version_revision();?>'],
	            	toolbar: [
								['Bold','Italic','Underline','Strike','-',
								 'Font','FontSize','-', 'Blockquote','-',
								 'SpellChecker', 'Scayt','-', 
								 'NumberedList','BulletedList','-',
								 'TextColor','BGColor','RemoveFormat','-',
								 'Link','Unlink','-',
								 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']
	                        ],
	                on: {
	                        instanceReady: function(ev) {
	                                og.adjustCkEditorArea('<?php echo $genid ?>');
	                                editor.resetDirty();
	                        }
	                    },
	                entities_additional : '#39,#336,#337,#368,#369,#124'
	            });
	
	            og.setDescription = function() {
	                    var form = Ext.getDom('<?php echo $genid ?>submit-edit-form');
	                    if (form.preventDoubleSubmit) return false;
	
	                    setTimeout(function() {
	                            form.preventDoubleSubmit = false;
	                    }, 2000);
	
	                    var editor = og.getCkEditorInstance('<?php echo $genid ?>ckeditor');
	                    form['message[text]'].value = editor.getData();
	
	                    return true;
	            };    
	        </script>
	        <?php }else{?>
	        <div>
	            <?php 
	                if(array_var($message_data, 'type_content') == "text"){
	                    $content_text = array_var($message_data, 'text');
	                }else{
	                    $content_text = html_to_text(html_entity_decode(nl2br(array_var($message_data, 'text')), null, "UTF-8"));
	                }   
	            ?>
	            <?php echo label_tag(lang('text'), 'messageFormText', false) ?>
	            <?php echo editor_widget('message[text]', $content_text, array('id' => $genid . 'messageFormText')) ?>
			</div>
	        <script>
	                og.setDescription = function() {
	                        return true;
	                };    
	        </script>
		<?php }?>
	
		</div>
		
		<?php if ($has_custom_properties || config_option('use_object_properties')) { ?>
		<div id="<?php echo $genid ?>add_custom_properties_div" class="form-tab">
			<?php  echo render_object_custom_properties($message, false) ?>
			<?php  echo render_add_custom_properties($object); ?>
		</div>
		<?php } ?>
		
		<div id="<?php echo $genid ?>add_subscribers_div" class="form-tab">
			<?php $subscriber_ids = array();
				if (!$object->isNew()) {
					$subscriber_ids = $object->getSubscriberIds();
				} else {
					$subscriber_ids[] = logged_user()->getId();
				}
			?>
			<input type="hidden" id="<?php echo $genid ?>subscribers_ids_hidden" value="<?php echo implode(',',$subscriber_ids)?>"/>
			<div id="<?php echo $genid ?>add_subscribers_content"></div>
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

	
	<?php if (!array_var($_REQUEST, 'modal')) {
		echo submit_button($message->isNew() ? lang('add message') : lang('save changes'),'s', array('style'=>'margin-top:0px')); 
	}?>
</div>
</div>
</form>
<script>
$(function() {
	$("#<?php echo $genid?>tabs").tabs();
});
</script>