<?php 
if ($linked_objects_object->isNew()){
	$linked_objects = array();
	echo render_link_to_new_object(lang('link more objects'));
} else if (isset($linked_objects) && is_array($linked_objects) && count($linked_objects)) { ?>
	<div class="objectFiles">
	<div class="objectFilesTitle"><span><?php echo lang('linked objects') ?>:</span></div>
	
	<table style="width:100%;margin-left:2px;margin-right:3px">
	
	<?php 
	$counter = 0;
	//catch the value of the users configuration
	
	$amountOfObjects = user_config_option('amount_objects_to_show',null,logged_user()->getId());
	$moreLinkedObjects = false;
		
	foreach ($linked_objects as $linked_object) {
		if( !$linked_object instanceof ApplicationDataObject ) continue ; //check that it is a valid object
		
		if (!$linked_object->canView(logged_user()) ) continue; // check permissions
		
		$object_title = $linked_object->getObjectName();
		if (strlen($object_title) > 28){
			$object_title = clean(utf8_substr($object_title,0,26)) . "&hellip;";	
		} else {
			$object_title = clean($object_title);
		}
		$icon_class = $linked_object->getObjectTypeName();
		if ($linked_object instanceof ProjectFile)
			$icon_class = 'file ico-' . str_replace(".", "_", str_replace("/", "-", $linked_object->getTypeString()));
		$counter++;?>
		
		<tr class="linkedObject<?php echo $counter % 2 ? 'even' : 'odd' ?>">
		<td rowspan=2 style="padding-left:1px;vertical-align:middle;width:22px">
		<?php $attr = 'class="internalLink"'; ?>
		<a href="<?php echo $linked_object->getObjectUrl() ?>">
		<div class="db-ico unknown ico-<?php echo clean($icon_class) ?>" title="<?php echo clean($linked_object->getObjectTypeName()) ?>"></div>
		
		</a></td><td><b><a <?php echo $attr ?> href="<?php echo $linked_object->getObjectUrl() ?>" title="<?php echo clean($linked_object->getObjectName()) ?>">
		<span><?php echo $object_title ?></span> </a></b> </td></tr>
		<tr class="linkedObject<?php echo $counter % 2 ? 'even' : 'odd' ?>"><td>
		<?php if ($linked_object instanceof ProjectFile){ 
				$download_url = $linked_object->getDownloadUrl();
				include_once ROOT . "/library/browser/Browser.php";
				if (Browser::instance()->getBrowser() == Browser::BROWSER_IE) {
					$download_url = "javascript:location.href = '$download_url';";
				}
			?>
			<a target="_self" href="<?php echo $download_url ?>"><?php echo lang('download') . ' (' . format_filesize($linked_object->getFilesize()) . ')'?></a> | 
		<?php }
		if ($linked_object instanceof ProjectWebpage) { ?>
			<a target="_blank" href="<?php echo $linked_object->getUrl() ?>"><?php echo lang('open weblink')?></a> |
		<?php }
		if ($linked_objects_object->canUnlinkObject(logged_user(), $linked_object)) { 
			$action = 'og.openLink(\'' . $linked_objects_object->getUnlinkObjectUrl($linked_object) . '\', {callback: function(){ og.redrawLinkedObjects('. $linked_objects_object->getId() .')}});';
			echo '<a class="internalLink" href="#"' . '" onclick="if (confirm(\'' . escape_single_quotes(lang('confirm unlink object')) . '\')){'.$action.'}" title="' . lang('unlink object') . '">' . lang('unlink') . '</a>';
		} ?>
		</td></tr>
		
		<?php if (($counter >= $amountOfObjects || $amountOfObjects == null))
		{ 
			$moreLinkedObjects = true;
			break;		
		 	
		} //if
		 ?>
	<?php 	} // foreach ?>
	</table>
	<?php if ($moreLinkedObjects == true) {?>
	<?php /*  <a class="internalLink" href="<?php echo get_url('object','show_all_linked_objects',array('object_id' => $linked_objects_object->getId(),'obj_manager'=>get_class($linked_objects_object->manager()))) ?>"> */?>
	<?php /*,{action:'listLinkedObjects',object:'<?php echo $linked_objects_object->getId()?>',manager:'<?php echo get_class($linked_objects_object->manager())?>'} */?>
	
	<a class="internalLink" href="javascript:og.openLink(
		og.getUrl(
			'object',
			'show_all_linked_objects',
				{linked_object:'<?php echo $linked_objects_object->getId()?>',
				linked_manager:'<?php  echo get_class($linked_objects_object->manager()) ?>',
				linked_object_name:'<?php echo escape_single_quotes(clean($linked_objects_object->getObjectName())) ?>',
				linked_object_ico:'<?php echo 'ico-' . $linked_objects_object->getObjectTypeName()?>'}),
			{caller:'linkedobjects'})" >
		<?php echo lang('show all linked objects',count($linked_objects))?>
	</a>
	
	<?php }//if?>
	
	<?php 		
	if ($linked_objects_object->canLinkObject(logged_user()) && $enableAdding) { ?>
		<p><?php echo render_link_to_object($linked_objects_object,lang('link more objects')); ?> </p>
		<!--a class="internalLink" href="<?php echo $linked_objects_object->getLinkObjectUrl() ?>">&raquo; <?php echo lang('link more objects') ?></a-->
	<?php } // if ?>
	</div>
<?php } else {
	//echo $shortDisplay ? '' : lang('no linked objects').'.';
	if ((!($linked_objects_object->isNew())) && $linked_objects_object->canLinkObject(logged_user()) && $enableAdding) {
		echo render_link_to_object($linked_objects_object,lang('link objects'));
	} // if
} // if ?>