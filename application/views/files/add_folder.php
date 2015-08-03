<?php
  set_page_title($folder->isNew() ? lang('add folder') : lang('edit folder'));
  
  if(ProjectFile::canAdd(logged_user(), active_context())) {
    add_page_action(lang('add file'), get_url('files', 'add_file'), 'ico-add');
  } // if

  
?>
<form class="internalForm" action="<?php echo ($folder->isNew())? get_url('files', 'add_folder') : $folder->getEditUrl()?>" method="post">

<?php tpl_display(get_template_path('form_errors')) ?>
  
  <div>
    <?php echo label_tag(lang('name'), 'folderFormName') ?>
    <?php echo text_field('folder[name]', array_var($folder_data, 'name'), array('id' => 'folderFormName')) ?>
  </div>
  
  <?php echo submit_button($folder->isNew() ? lang('add folder') : lang('edit folder')) ?>
  
</form>