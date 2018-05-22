<?php
  if (!isset($genid)) $genid = gen_id();
  set_page_title(lang('edit picture'));

  $action = $contact->getUpdatePictureUrl();
  if (isset($reload_picture) && $reload_picture) {
  	$action .= "&reload_picture=$reload_picture";
  }
  if (isset($new_contact) && $new_contact) {
  	$action .= "&new_contact=$new_contact";
  }
  if (array_var($_REQUEST, 'is_company')) {
  	$action .= "&is_company=".array_var($_REQUEST, 'is_company');
  }

  ajx_set_no_toolbar();
?>
<div class="coInputHeader" style="margin-top:20px;">
  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo lang('update avatar') ?>
	</div>
  </div>
</div>

<table><tr><td>

<div id="<?php echo $genid?>current_picture" style="float:left; padding-right:20px; border-right: 1px dotted #999;">
	
	<div style="padding:10px;">
<?php if($contact->hasPicture()) { ?>
    <img src="<?php echo $contact->getPictureUrl('medium') ?>" alt="<?php echo clean($contact->getObjectName()) ?> picture" style="max-width:500px; max-height:500px;"/>
    <p><a class="internalLink link-ico ico-delete" href="<?php echo $contact->getDeletePictureUrl() ?>" onclick="return confirm('<?php echo escape_single_quotes(lang('confirm delete current picture')) ?>')"><?php echo lang('delete current picture') ?></a></p>
<?php } else { ?>
    <?php echo lang('no current picture') ?>
<?php } // if ?>
	</div>
</div>

</td><td>

<div style="float: left;" id="<?php echo $genid?>uploadPreviewContainer">
	<!-- image preview area-->
	<img id="<?php echo $genid?>uploadPreview" style="display:none; max-width:500px; max-height:500px;"/>
</div>
<div style="float: left;padding:10px; margin-left: 20px;">
	<h1><?php echo lang('new picture')?></h1>
	<div class="desc"><?php echo lang('new picture notice')?></div>
	<div style="margin-top:20px;">
	<!-- image uploading form -->
	  <form action="<?php echo $action ?>" method="post" enctype="multipart/form-data" onsubmit="og.beforePictureSubmit();return og.submit(this)" target="_blank">
		<?php echo file_field('new picture', null, array('id' => $genid.'uploadImage')) ?>
		<div><?php echo submit_button(lang('save'), 's', array('id' => $genid.'submit_btn', 'onclick'=>'og.ExtModal.hide();return true;')) ?>
		<?php //echo button(lang('back'), 'b', array('id' => $genid.'back_btn', 'onclick' => "og.beforePictureSubmit();og.goback(this);")) ?></div>

		<!-- hidden inputs -->
		<input type="hidden" id="<?php echo $genid?>x" name="x" />
		<input type="hidden" id="<?php echo $genid?>y" name="y" />
		<input type="hidden" id="<?php echo $genid?>w" name="w" />
		<input type="hidden" id="<?php echo $genid?>h" name="h" />
	  </form>
	</div>
</div>

</td></tr></table>

<?php ajx_extra_data(array('genid' => $genid, 'is_company' => array_var($_REQUEST, 'is_company')))?>
