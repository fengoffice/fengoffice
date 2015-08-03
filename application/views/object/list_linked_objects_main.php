<?php 
$genid = gen_id();
if (isset($linked_objects) && is_array($linked_objects) && count($linked_objects)) { ?>
	<div>
	<div style="border-bottom:1px solid #CCCCCC;"><table style="width:100%"><tr><td>
	<strong><?php echo lang("linked main title") ?>:</strong>&nbsp;&nbsp;&nbsp;
	<span id="bt<?php echo $genid?>">
	<?php 
	$amountOfObjects = user_config_option('amount_objects_to_show');
	$sorted_objects = array();
	$maxcount = 0;
	$maxkey = '';
	foreach ($linked_objects as $linked_object){

		if (ObjectTypes::isListableObjectType($linked_object->getObjectTypeId())) {
			if (!isset($sorted_objects[$linked_object->getObjectTypeName()]))
				$sorted_objects[$linked_object->getObjectTypeName()] = array($linked_object);
			else
				$sorted_objects[$linked_object->getObjectTypeName()][] = $linked_object;
			if (count($sorted_objects[$linked_object->getObjectTypeName()]) > $maxcount){
				$maxcount = count($sorted_objects[$linked_object->getObjectTypeName()]);
				$maxkey = $linked_object->getObjectTypeName();
			}
		}
	}	
	foreach ($sorted_objects as $key => $object_group){ ?>
		<a href="#" style="<?php echo $key == $maxkey ? 'font-weight:bold' : 'font-weight:normal'?>;margin-right:10px" onclick="og.toggleSimpleTab('<?php echo $genid . $key ?>LO', 'hp<?php echo $genid ?>', 'bt<?php echo $genid ?>', this)">
		<?php echo lang("linked $key tab") ?><span style="font-size:80%"><?php echo ' (' . count($object_group) . ')' ?></span></a>
		
	<?php
	}
	?></span></td><td style="text-align:right;"><?php 
		$displayShowAll = count($sorted_objects) > 1;
		$displayLinkObjects = $linked_objects_object->canLinkObject(logged_user()) && $enableAdding;
		
		if ($displayShowAll) {?>
			<a class="internalLink" href="javascript:og.openLink(
				og.getUrl(
					'object',
					'show_all_linked_objects',
						{linked_object:'<?php echo $linked_objects_object->getId()?>',
						linked_manager:'<?php  echo get_class($linked_objects_object->manager()) ?>',
						linked_object_name:'<?php echo escape_single_quotes(clean($linked_objects_object->getObjectName())) ?>',
						linked_object_ico:'<?php echo 'ico-' . $linked_objects_object->getObjectTypeName()?>'}),
					{caller:'linkedobjects'})" >
				<?php echo lang('show all') . '&hellip;'?>
			</a>
		<?php }//if
		
	if ($displayLinkObjects && $displayShowAll) echo ' | ';
			
	if ($displayLinkObjects) { ?>
		<?php echo render_link_to_object($linked_objects_object,lang('link more objects'), true); ?>
	<?php } // if ?></td></tr></table></div><div id="hp<?php echo $genid?>"><?php
	foreach ($sorted_objects as $key => $object_group){ ?>
		<div id="<?php echo $genid . $key ?>LO" style="<?php echo $key == $maxkey ? '' : 'display:none'?>">
		<table style="width:100%">
		<?php 
		
		//Sort object group according to Updated On
		$upto = min(count($object_group), $amountOfObjects); // only need to order the first elements
		for($i = 0; $i < $upto - 1; $i++){
			for ($j = $i + 1; $j < count($object_group); $j++){
				if ($object_group[$i]->getUpdatedOn() < $object_group[$j]->getUpdatedOn()){
					$aux = 	$object_group[$i];
					$object_group[$i] = $object_group[$j];
					$object_group[$j] = $aux;
				}
			}
		}
		
		$counter = 0;
		$moreLinkedObjects = false;
		foreach ($object_group as $linked_object){
			if( !$linked_object instanceof ApplicationDataObject ) continue ; //check that it is a valid object
			
			if (!$linked_object->canView(logged_user()) ) continue; // check permissions
			
			tpl_assign('counter', $counter);
			tpl_assign('linked_object', $linked_object);
			tpl_assign('genid', $genid);
			switch($key){
				case 'task': echo tpl_fetch(get_template_path('llo_task', 'object')); break;
				case 'contact': echo tpl_fetch(get_template_path('llo_contact', 'object')); break;
				case 'email':
				case 'emailunclassified':
				case 'emailclassified': 
					tpl_assign('date_format', user_config_option('date_format')); 
					echo tpl_fetch(get_template_path('llo_email', 'object')); break;
				default: echo tpl_fetch(get_template_path('llo_generic', 'object'));
			} // switch
			$counter++;
			$moreLinkedObjects = ($counter >= $amountOfObjects || $amountOfObjects == null);
			if ($counter >= $amountOfObjects) break;
		} // foreach linked object
		?>
		</table>
		<?php if ($moreLinkedObjects) {?>
			<a class="internalLink" href="javascript:og.openLink(
				og.getUrl(
					'object',
					'show_all_linked_objects',
						{linked_object:'<?php echo $linked_objects_object->getId()?>',
						linked_object_name:'<?php echo escape_single_quotes(clean($linked_objects_object->getObjectName())) ?>',
						linked_object_ico:'<?php echo 'ico-' . $linked_objects_object->getObjectTypeName()?>'}),
					{caller:'linkedobjects'})" >
				<?php echo lang('show more') . '&hellip;'?>
			</a>
		<?php }//if?></div><?php
	} // foreach group
	?></div>
	</div>
<?php } else {
	if ((!($linked_objects_object->isNew())) && $linked_objects_object->canLinkObject(logged_user()) && $enableAdding) {
		echo '<div style="text-align:right;margin:-7px; margin-right:0px">' . render_link_to_object($linked_objects_object,lang('link objects'), true) . '</div>';
	} // if?>
<?php 
} // if ?>