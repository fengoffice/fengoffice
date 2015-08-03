<?php if(isset($contacts) && is_array($contacts) && count($contacts)) { ?>
<div id="contactsList">
<?php $counter = 0; ?>
<?php foreach($contacts as $contact) {?>
<?php $counter++; ?>
  <div class="listedContact <?php echo $counter % 2 ? 'even' : 'odd' ?>">
	<table>
  	<tr><td rowspan=2 style="padding-right:10px; padding-top:3px; padding-left:3px; padding-bottom:3px">
    <div class="contactPicture"><img src="<?php echo $contact->getPictureUrl() ?>" alt="<?php echo clean($contact->getObjectName()) ?> <?php echo lang('picture') ?>" /></div>
    </td><td>
      <div class="contactName"><h2><a class="internalLink" href="<?php echo $contact->getCardUrl() ?>"><?php echo clean($contact->getObjectName()) ?></a></h2></div>
    <?php
  $options = array();
  if($contact->canEdit(logged_user())) {
    $options[] = '<a class="internalLink" href="' . $contact->getEditUrl() . '">' . lang('update contact') . '</a>';
    $options[] = '<a class="internalLink" href="' . $contact->getUpdatePictureUrl() . '">' . lang('edit picture') . '</a>';
  } // if
  if($contact->canDelete(logged_user())) {
    $options[] = '<a class="internalLink" href="' . $contact->getDeleteUrl() . '" onclick="return confirm(\'' . escape_single_quotes(lang('confirm delete contact')) . '\')">' . lang('delete') . '</a>';
  } // if
?>
		</td></tr><tr><td>
      <div class="contactOptions"><?php echo implode(' | ', $options) ?></div>
      </td></tr></table>
    </div>
<?php } // foreach ?>
</div>

<?php } else { ?>
<p><?php echo lang('no contacts in company') ?></p>
<?php } // if 
	echo  "<a href='" . $company->getAddContactUrl() . "' class='internalLink coViewAction ico-add'>" . lang('add contact') . "</a>"; ?>