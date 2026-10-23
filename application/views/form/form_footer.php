<!-- FORM FOOTER -->
<div class="form-footer">

    <!-- Action links (Archive / Delete) -->
    <div class="footer-left">
        <?php if (isset($object) && method_exists($object, 'isNew') && !$object->isNew()): ?>
            <?php
            // Detectar tipo de objeto si no fue pasado
            if (!isset($object_type_name) && method_exists($object, 'getObjectTypeId')) {
                $ot = ObjectTypes::instance()->findById($object->getObjectTypeId());
                $object_type_name = $ot ? $ot->getObjectTypeName() : '';
            }

            // Solo si el objeto admite archivado y es un Member (usa el controller 'member')
            if (method_exists($object, 'getArchivedById') && $object instanceof Member) {
                $is_archived = $object->getArchivedById() != 0;

                if (!$is_archived) {
                    $confirm = lang('confirm archive member', $object_type_name);
                    $url = get_url('member', 'archive', ['id' => $object->getId()]);
                    $arch_action = "if(confirm(" . json_encode($confirm) . ")) og.openLink(" . json_encode($url) . ", {callback:function(){\$('#_close_link').click();}});";

                } else {
                    $confirm = lang('confirm unarchive member', $object_type_name);
                    $url = get_url('member', 'unarchive', ['id' => $object->getId()]);
                    $arch_action = "if(confirm(" . json_encode($confirm) . ")) og.openLink(" . json_encode($url) . ", {callback:function(){\$('#_close_link').click();}});";

                }

                $delete_url = get_url('member', 'delete', ['id' => $object->getId(), 'start' => true]);
            ?>
                <!-- Archive / Unarchive -->
                <button type="button"
                    tabindex="-1"
                    class="btn link-ico"
                    onclick="<?php echo htmlspecialchars($arch_action, ENT_QUOTES); ?>">
                    <i class="icon-archive"></i>
                    <?php echo $is_archived ? lang('unarchive') : lang('archive'); ?>
                </button>

                <!-- Delete -->
                <button type="button"
                    tabindex="-1"
                    class="btn link-ico"
                    onclick="<?php echo htmlspecialchars('og.deleteMember(' . json_encode($delete_url) . ',' . json_encode($object_type_name) . ');', ENT_QUOTES); ?>">
                    <i class="icon-trash"></i>
                    <?php echo lang('delete'); ?>
            </button>
            <?php } ?>
        <?php endif; ?>
    </div>

    <!-- Action buttons (Save / Cancel) -->
    <div class="footer-right">
        <?php if (array_var($_REQUEST, 'modal')): ?>
            <button type="button" tabindex="-1" class="btn" onclick="$.modal.close();">
                <?php echo lang('cancel'); ?>
            </button>
        <?php endif; ?>
            <?php
                // Optional extra buttons
                if (!empty($extra_buttons)) {
                    echo $extra_buttons;
                }
                if (!isset($new_button_text) || trim($new_button_text) === '') {
                    $new_button_text = lang('save');
                }
                $submit_attributes = isset($submit_button_id) ? array('id' => $submit_button_id) : null;
                echo submit_button($object->isNew() ? $new_button_text : lang('save changes'), 's', $submit_attributes);
            ?>
    </div>

</div>
<!-- END FORM FOOTER -->
