<div style="padding:10px">

<?php $step = 1; ?>

<b><?php echo lang('welcome to new account', logged_user()->getObjectName()) ?></b><br/>
<?php echo lang('welcome to new account info', '<a target="_blank" href="'.ROOT_URL.'">'. ROOT_URL . '</a>') ?><br/><br/>

<?php if(logged_user()->isAccountOwner()){
		$step++;
		// FIXME: definir esta funcion si corresponde, sino sacarla
		if(true || owner_company()->isInfoUpdated()) { ?>
  <p><b><?php echo '<a class="internalLink dashboard-link" href="'. get_url('contact', 'edit_company', array('id' => owner_company()->getId())) .'">' . lang('new account step1 owner'). '</a>'?></b><img src="<?php echo image_url('16x16/complete.png');?>"/></p>
  <?php echo lang('new account step1 owner info')?><br/><br/>
<?php 	} else { ?>
  <p><b><?php echo '<a class="internalLink dashboard-link" href="'. get_url('contact', 'edit_company', array('id' => owner_company()->getId())) .'">' . lang('new account step1 owner'). '</a>'?></b></p>
  <?php echo lang('new account step1 owner info')?><br/><br/>
<?php } // if 
}?>

<?php 
	// FIXME: definir esta funcion si corresponde, sino sacarla
	if(true || logged_user()->isInfoUpdated()) { ?>
  <p><b><?php echo '<a class="internalLink dashboard-link" href="'.get_url('account','index').'">'. lang('new account step update account', $step).'</a>'?></b><img src="<?php echo image_url('16x16/complete.png');?>"/></p>
  <?php echo lang('new account step update account info') ?><br/><br/>
<?php } else { ?>
  <b><?php echo '<a class="internalLink dashboard-link" href="'.get_url('account','index').'">'. lang('new account step update account', $step).'</a>' ?></b><br/>
  <?php echo lang('new account step update account info') ?><br/><br/>
<?php } // if
	$step++;
?>

<?php if (count(Projects::count('`created_by_id` = ' . logged_user()->getId())) > 0) { ?>
  <p><b><?php echo '<a class="internalLin dashboard-link" href="' . get_url('project', 'add') . '">' . lang('new account step start workspace', $step) . '</a>' ?></b><img src="<?php echo image_url('16x16/complete.png');?>"/></p>
  <?php echo lang('new account step start workspace info', '<img src="'.image_url('16x16/add.png').'" />', logged_user()->getPersonalProject()->getName()) ?><br/><br/>
<?php } else { ?>
  <b><?php echo '<a class="internalLink dashboard-link" href="' . get_url('project', 'add') . '">' . lang('new account step start workspace', $step) . '</a>' ?></b><br/>
  <?php echo lang('new account step start workspace info', '<img src="'.image_url('16x16/add.png').'" />', logged_user()->getPersonalProject()->getName()) ?><br/><br/>
<?php } ?>
<?php $step++ ?>  

<b><?php echo lang('new account step actions',$step) ?></b>
	<?php 
	$task_count = ProjectTasks::count('`created_by_id` = ' . logged_user()->getId());
	$note_count = ProjectMessages::count('`created_by_id` = '.logged_user()->getId());
	//$contact = ProjectContacts::findOne(array('conditions'=>'created_by_id='.logged_user()->getId()));
	if ($task_count > 0 || $note_count > 0) {
		echo '<img src="'.image_url('16x16/complete.png').'" />';
	}?><br/>
<?php echo lang('new account step actions info') ?><br/>

<img src='<?php echo image_url('16x16/message.png')?> ' />&nbsp;
<a class='internalLink dashboard-link' href='<?php echo get_url('message', 'add')?> ' ><?php echo lang('message')?></a>&nbsp;|&nbsp;

<img src='<?php echo image_url('16x16/types/contact.png')?> ' />&nbsp;
<a class='internalLink dashboard-link' href='<?php echo get_url('contact', 'add')?> ' ><?php echo lang('person')?></a>&nbsp;|&nbsp;

<img src='<?php echo image_url('16x16/companies.png')?> ' />&nbsp;
<a class='internalLink dashboard-link' href='<?php echo get_url('contact', 'add_company')?> ' ><?php echo lang('company')?></a>&nbsp;|&nbsp;

<img src='<?php echo image_url('16x16/types/event.png')?> ' />&nbsp;
<a class='internalLink dashboard-link' href='<?php echo get_url('event', 'add')?> ' ><?php echo lang('event')?></a>&nbsp;|&nbsp;

<img src='<?php echo image_url('16x16/upload.png')?> ' />&nbsp;
<a class='internalLink dashboard-link' href='<?php echo get_url('files', 'add_file')?> ' ><?php echo lang('upload a file')?></a>&nbsp;|&nbsp;

<img src='<?php echo image_url('16x16/documents.png')?> ' />&nbsp;
<a class='internalLink dashboard-link' href='<?php echo get_url('files', 'add_document')?> ' ><?php echo lang('document')?></a>&nbsp;|&nbsp;

<img src='<?php echo image_url('16x16/prsn.png')?> ' />&nbsp;
<a class='internalLink dashboard-link' href='<?php echo get_url('files', 'add_presentation')?> ' ><?php echo lang('presentation')?></a>&nbsp;|&nbsp;

<img src='<?php echo image_url('16x16/milestone.png')?> ' />&nbsp;
<a class='internalLink dashboard-link' href='<?php echo get_url('milestone', 'add')?> ' ><?php echo lang('milestone')?></a>&nbsp;|&nbsp;

<img src='<?php echo image_url('16x16/types/task.png')?> ' />&nbsp;
<a class='internalLink dashboard-link' href='<?php echo get_url('task', 'add_task')?> ' ><?php echo lang('task')?></a>&nbsp;|&nbsp;

<img src='<?php echo image_url('16x16/types/webpage.png')?> ' />&nbsp;
<a class='internalLink dashboard-link' href='<?php echo get_url('webpage', 'add')?> ' ><?php echo lang('weblink')?></a>

<?php /*&nbsp;|&nbsp;
<image src='<?php echo image_url('/16x16/time.png')?> ' />&nbsp;
<a class='internalLink dashboard-link' href='<?php echo get_url('time', 'index')?> ' ><?php echo lang('time')?></a>&nbsp;|&nbsp;

<image src='<?php echo image_url('/16x16/reporting.png')?> ' />&nbsp;
<a class='internalLink dashboard-link' href='<?php echo get_url('reporting', 'index')?> ' ><?php echo lang('reporting')?></a>&nbsp;&nbsp;
*/?>

<?php Hook::fire('render_getting_started', null, $ret)?>

<br/><br/><p><a class='internalLink' href='<?php echo get_url('config', 'remove_getting_started_widget')?> ' ><?php echo lang('remove this widget')?></a></p>

</div>
