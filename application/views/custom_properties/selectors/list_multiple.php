<?php
require_javascript("og/CustomProperties.js");

$fieldValues = array_var($configs, 'default_value');
$name = array_var($configs, 'name');
$genid = array_var($configs, 'genid');

$is_mem_cp = isset($configs['member_id']);

?>
<div id="<?php echo $genid ?>listValues<?php echo $cp->getId()?>" name="listValues<?php echo $cp->getId()?>" class="cp-multiple">
<?php 
$selected_values = array();
foreach ($fieldValues as $v) {
	$selected_values[] = $v->getValue();
}

$count = 0;
?>
<div class="custom-properties-wrapper">
<?php
    foreach(explode(',', $cp->getValues()) as $value){

	$text = null;
	if (strpos($value, '@') !== false) {
		$exp = explode('@', $value);
		$value = array_var($exp, 0);
		$text = array_var($exp, 1);
	}

	if ($text == null) {
		$text = $value;
	}
	if ($cp->getIsSpecial()) {
		$lang_value = Localization::instance()->lang($text);
		$text = is_null($lang_value) ? $text : $lang_value;
	}
	
	$selected = in_array($value, $selected_values);
	
?>
	<div class="cp-list-multiple-option-container">
<?php 
	echo checkbox_field($name."[$value]", $selected, array('id' => "$genid-$name-$count", 'class' => "cp-list-multiple-checkbox"));
?>
		<span class="cp-list-multiple-option" onclick="document.getElementById('<?php echo "$genid-$name-$count" ?>').click();"><?php echo $text ?></span>
	</div>
<?php
	$count++;
}

?>

</div>
</div>