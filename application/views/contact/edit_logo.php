<?php
  
  set_page_title(lang('edit company logo'));
  if($company->isOwnerCompany()) {
    administration_tabbed_navigation(ADMINISTRATION_TAB_COMPANY);
    administration_crumbs(array(
      array(lang('company'), get_url('administration', 'company')),
      array(lang('edit company logo'))
    ));
  } else {
    administration_tabbed_navigation(ADMINISTRATION_TAB_CLIENTS);
    administration_crumbs(array(
      array(lang('clients'), get_url('administration', 'clients')),
      array($company->getObjectName(), $company->getViewUrl()),
      array(lang('edit company logo'))
    ));
  } // if

?>
<form target="_blank" style='height:100%;background-color:white' action="<?php echo $company->getEditPictureUrl() ?>" method="post" enctype="multipart/form-data" onsubmit="return og.submit(this)">

<div class="avatar">
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">

<?php tpl_display(get_template_path('form_errors')) ?>
  
  <fieldset>
    <legend><?php echo lang('current logo') ?></legend>
<?php if($company->hasPicture()) { ?>
    <img src="<?php echo $company->getPictureUrl() ?>" alt="<?php echo clean($company->getObjectName()) ?> logo" />
    <p><a class="internalLink" href="<?php echo $company->getDeletePictureUrl() ?>" onclick="return confirm('<?php echo escape_single_quotes(lang('confirm delete company logo')) ?>')"><?php echo lang('delete company logo') ?></a></p>
<?php } else { ?>
    <?php echo lang('no current logo') ?>
<?php } // if ?>
  </fieldset>
  
  <div>
    <?php echo label_tag(lang('new logo'), 'avatarFormAvatar', true) ?>
    <?php echo file_field('new_logo', null, array('id' => 'avatarFormAvatar')) ?>
<?php if($company->hasPicture()) { ?>
    <p class="desc"><?php echo lang('new logo notice') ?></p>
<?php } // if ?>
  </div>
  
  <?php echo submit_button(lang('edit company logo')) ?>
</div>
</div>
</form>