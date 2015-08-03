<?php

function load_help($template){
	$dir = Localization::instance()->getLanguageDirPath().'/'.Localization::instance()->getLocale();
	$help_file = $dir.'/help/'.$template.'.html';
	if(is_file($help_file)){
		return tpl_fetch($help_file);
	}else{
		$default = Localization::instance()->getLanguageDirPath().'/en_us/help/'.$template.'.html';
		if(is_file($default)){
			return tpl_fetch($default);
		}else{
			$noHelp = Localization::instance()->getLanguageDirPath().'/en_us/help/no_help.html';
			if(is_file($noHelp)){
				return tpl_fetch($noHelp);
			}else{
				return '';
			}
		}
	}	
}

?>