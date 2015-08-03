	locale = 'nl_nl';
	var langObj = {};
<?php $lang_array = include 'lang.php'; ?>
		
<?php foreach ($lang_array as $k => $v): ?>
	langObj["<?php echo $k ;?>"] = "<?php echo $v ;?>" ;	 
<?php endforeach ;?>
	addLangs(langObj);
	