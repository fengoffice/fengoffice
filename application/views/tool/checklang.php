<?php set_page_title('Check lang' . (isset($to) ? " $to" : "")) ?>
<style>
body {
	padding: 5px 30px;
	font-family: Arial, sans-serif, serif;
	font-size: 12px;
}
.missing {
	color: red;
}
.ok {
	color: green;
}
.error {
	color: blue;
}
</style>
<p>This script allows you to compare translation files in some locale with translation files in english.
If you are translating to a locale this script can help you detect what translation keys you have missed.</p>
<p>You can use the <a href="<?php echo get_url('tool', 'translate') ?>">Translate Feng Office</a> tool to add missing translations.</p>
<p>Select a locale from the list below. The following locales have been detected on this installation:</p>
<ul>  <?php
foreach ($languages as $language) { ?>
	<li><a href="<?php echo get_url('tool', 'checklang', array('to' => $language)) ?>"><?php echo $language ?></a></li> <?php
} ?>
</ul>
<?php

if (isset($to)) { ?>
	<h2><?php echo $to ?> translation files</h2>
	<p>Next you can see the missing translation files in red, and the missing translation keys under each file, along with the english text:</p>
	<pre><?php
	foreach ($missing as $file => $data) {
		if (!is_array($data)) {
			?>- <span class="missing"><?php echo $file ?> missing</span><?php echo "\n";
		} else {
			?><span class="present"><?php echo $file ?></span><?php echo "\n";
			if (count($data) == 0) {
				?>    <span class="ok">File complete</span><?php echo "\n";
			} else {
				foreach ($data as $k => $v) {
					if (substr($file, -4) == '.php') {
						?>    '<?php echo $k ?>' => '<?php echo str_replace(array("'"), array("\\'"), clean($v)) . "',\n";
					} else {
						?>    '<?php echo $k ?>' : '<?php echo str_replace(array("'", "\n"), array("\\'", "\\n"), clean($v)) . "',\n";
					}
				}
			}
		}
	} ?></pre><?php
}
?>