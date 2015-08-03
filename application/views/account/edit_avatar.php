<?php
    set_page_title(lang('update avatar'));
    $action = $user->getUpdatePictureUrl();
    if (isset($reload_picture)) {
    	$action .= "&reload_picture=$reload_picture";
    }
?>


<form target="_blank" style='height:100%;background-color:white' action="<?php echo $action ?>" method="post" enctype="multipart/form-data" onsubmit="return og.submit(this)">

<div class="avatar">
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">
  
  <fieldset>
    <legend><?php echo lang('current avatar') ?></legend>
<?php if($user->hasPicture()) { ?>
    <img src="<?php echo $user->getPictureUrl() ?>" alt="<?php echo clean($user->getObjectName()) ?>" />
    <p><a class="internalLink" href="<?php echo $user->getDeletePictureUrl() ?>" onclick="return confirm('<?php echo escape_single_quotes(lang('confirm delete current avatar')) ?>')"><?php echo lang('delete current avatar') ?></a></p>
<?php } else { ?>
    <?php echo lang('no current avatar') ?>
<?php } // if ?>
  </fieldset>
  
  <div>
    <?php echo label_tag(lang('new avatar'), 'avatarFormAvatar', true) ?>
    <?php echo file_field('new avatar', null, array('id' => 'avatarFormAvatar')) ?>
<?php if($user->hasPicture()) { ?>
    <p class="desc"><?php echo lang('new avatar notice') ?></p>
<?php } // if ?>
  </div>
  
  <?php echo submit_button(lang('update avatar')) ?>
  
</div>
</div>
</form>