<?php
$is_fallback_rendering = isset($is_hook_rendering) && $is_hook_rendering === false;

$loc = user_config_option('localization');
if (strlen($loc) > 2) $loc = substr($loc, 0, 2);

ob_start();

if (config_option("wysiwyg_messages")) {
    if($message->isNew()) {
        $ckEditorContent = '';
    } else {
        if(array_var($message_data, 'type_content') == "text"){
            $ckEditorContent = nl2br(htmlspecialchars(array_var($message_data, 'text')));
        }else{
            $ckEditorContent = purify_html(nl2br(array_var($message_data, 'text')));
        }
    }
    
    $label_target_id = $genid . 'ckeditor';
    ?>
    <div>
        <div id="<?php echo $genid ?>ckcontainer" style="height: 400px">
            <textarea cols="80" id="<?php echo $genid ?>ckeditor" name="message[text]" rows="10"><?php echo clean($ckEditorContent) ?></textarea>
        </div>
    </div>
    
    <script>
        var h = document.getElementById("<?php echo $genid ?>ckcontainer").offsetHeight;
        var editor = CKEDITOR.replace('<?php echo $genid ?>ckeditor', {
            // height: (h-45) + 'px',
            height: '300px',
            allowedContent: true,
            resize_enabled: false,
            enterMode: CKEDITOR.ENTER_BR,
            shiftEnterMode: CKEDITOR.ENTER_BR,
            disableNativeSpellChecker: false,
            language: '<?php echo $loc ?>',
            customConfig: '',
            contentsCss: [
                '<?php echo get_javascript_url('ckeditor/contents.css').'?rev='.product_version_revision();?>', 
                '<?php echo get_stylesheet_url('og/ckeditor_override.css').'?rev='.product_version_revision();?>'
            ],
            toolbar: [
                [
                    'Bold','Italic','Underline','Strike','-',
                    'Font','FontSize','-', 'Blockquote','-',
                    'SpellChecker', 'Scayt','-', 
                    'NumberedList','BulletedList','-',
                    'TextColor','BGColor','RemoveFormat','-',
                    'Link','Unlink','-',
                    'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'
                ]
            ],
            on: {
                instanceReady: function(ev) {
                    og.adjustCkEditorArea('<?php echo $genid ?>');
                    editor.resetDirty();
                }
            },
            fillEmptyBlocks: false,
            removePlugins: 'scayt,liststyle,magicline,contextmenu,tabletools',
            entities_additional : '#336,#337,#368,#369,#124'
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
<?php } else { ?>
    <div>
        <?php 
            if(array_var($message_data, 'type_content') == "text"){
                $content_text = array_var($message_data, 'text');
            }else{
                $content_text = html_to_text(html_entity_decode(nl2br(array_var($message_data, 'text')), null, "UTF-8"));
            }   
            
            $label_target_id = $genid . 'messageFormText';
        ?>
        <?php echo editor_widget('message[text]', $content_text, array('id' => $label_target_id)) ?>
    </div>
    <script>
        og.setDescription = function() {
                return true;
        };
    </script>
<?php 
} // end if wysiwyg

$input_elements = ob_get_clean();

if ($is_fallback_rendering) {
    echo label_tag(lang('text'), $label_target_id, false);
    echo $input_elements;
    echo '<div class="clear"></div>';
} else {
    echo $input_elements;
}
?>