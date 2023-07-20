<?php
require_javascript("og/CustomProperties.js");

$fieldValues = array_var($configs, 'default_value');
$name = array_var($configs, 'name');
$genid = array_var($configs, 'genid');

$is_mem_cp = isset($configs['member_id']);

?>
<div id="<?php echo $genid ?>listValues<?php echo $cp->getId()?>" name="listValues<?php echo $cp->getId()?>" class="cp-multiple">
<?php 

$count = 0;
if (!is_array($fieldValues) || count($fieldValues) == 0) {
	$def_cp_value = new CustomPropertyValue();
	$def_cp_value->setValue($cp->getDefaultValue());
	$fieldValues = array($def_cp_value);
}

foreach($fieldValues as $value){
	$value = str_replace('|', ',', $value->getValue());
	if($value != ''){
	?>
		<div id="value<?php echo $count?>" class="cp-val">
	<?php
		echo text_field($name.'['.$count.']', $value, array('id' => $name.'[]','style'=>'width: 400px; padding: 5px;'));
		
		?><a href="#" class="link-ico ico-delete"
				onclick="og.removeCPTextValue('<?php echo $cp->getId()?>', '<?php echo $genid?>', '<?php echo $count?>',0)" ></a>
		</div>
	<?php
		$count++;
	}
}
?>
	<div id="value<?php echo $count?>" class="cp-val">
<?php 
	echo text_field($name.'['.$count.']', '', array('id' => $name.'[]','style'=>'width: 400px; padding: 5px;'));
		
	?><a href="#" class="link-ico ico-add" onclick="og.addCPTextValue(<?php echo $cp->getId()?>, '<?php echo $genid?>', <?php echo ($is_mem_cp ? "1" : "0")?>)"><?php echo lang('add value')?></a>
	</div>
</div>

<script>
<?php if (array_var($configs,'disabled')) { ?>
	$("#<?php echo $genid ?>listValues<?php echo $cp->getId()?> input").attr("disabled", "disabled");
	$("#<?php echo $genid ?>listValues<?php echo $cp->getId()?> a").remove();
<?php } ?>
</script>
