<?php

function load_help($template){
	$lang_dir = Localization::instance()->getLanguageDirPath();
	$locale = Localization::instance()->getLocale();
	$candidates = array_merge(array($locale), getLanguageFallbackLocales($locale));

	foreach ($candidates as $loc) {
		$help_file = $lang_dir.'/'.$loc.'/help/'.$template.'.html';
		if (is_file($help_file)) {
			return tpl_fetch($help_file);
		}
	}

	$noHelp = $lang_dir.'/en_us/help/no_help.html';
	if (is_file($noHelp)) {
		return tpl_fetch($noHelp);
	}

	return '';
}

?>