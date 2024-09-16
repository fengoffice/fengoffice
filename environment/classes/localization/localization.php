<?php

/**
 * Shortcut method for retriving single lang value
 *
 * @access public
 * @param string $neme
 * @return string
 */
function lang($name) {
	// Get function arguments and remove first one.
	$args = func_get_args();
	if(is_array($args)) array_shift($args);

	return langA($name, $args);

} // lang

function findSimilarLang(string $languageCode): ?string { 
    $response = null;
	switch ($languageCode) { 
        case 'es_es': 
            $response = 'es_la';
            break;
        case 'es_la': 
            $response = 'es_es'; 
            break;
        case 'en_gb': 
            $response = 'en_us'; 
            break;
        // Add more lenguage here 
    } 
	return $response; 
}

function langA($name, $args) {
	static $languageCompatibleBase = null;
	static $base = null;

    if($languageCompatibleBase === null) {
        if ($similarLang = findSimilarLang(Localization::instance()->getLocale())) {
            $languageCompatibleBase = new Localization();
            $languageCompatibleBase->loadSettings($similarLang, ROOT . "/language");
        } else {
        	$languageCompatibleBase = false;
        }
    }

	$value = Localization::instance ()->lang($name);

	if (is_null ( $value ) && $languageCompatibleBase !== false) {
        $value = $languageCompatibleBase->lang($name);
	}

	if (is_null ( $value )) {
		if (! Env::isDebugging ()) {

			if (! $base instanceof Localization) {
				$base = new Localization ();
				$base->loadSettings ( "en_us", ROOT . "/language" );
			}
			$value = $base->lang ( $name );
            if (is_null ( $value )) {
				$value = $base->lang(str_replace(" ", "_", $name ));
				if (is_null ($value)) {
					$value = $base->lang(str_replace("_", " ", $name ));
				}
				if (is_null($value)) {
					return $name;
				}
            }

		} else {
            $value = Localization::instance ()->lang ( str_replace ( " ", "_", $name ) );
            if (is_null ( $value )) {
                $value = Localization::instance ()->lang ( str_replace ( "_", " ", $name ) );
                if (is_null ( $value )) {
                    $bt = debug_backtrace();
                    $c = array_shift($bt);
                    $c1 = array_shift($c);
                    $c2 = array_shift($c);
                    $c = array_shift($bt);
                    $c1 = array_shift($c);
                    $c2 = array_shift($c);
                    $c = array_shift($bt);
                    $c1 = array_shift($c);
                    $c2 = array_shift($c);
                    $c = array_shift($bt);
                    $c1 = array_shift($c);
                    $c2 = array_shift($c);
                    return "Missing lang: $name";
                }
            }
        }
    }

    //if (is_null ( $value )) {

    //}
	
	
	// We have args? Replace all {x} with arguments
	if (is_array ( $args ) && count ( $args )) {
		$i = 0;
		foreach ( $args as $arg ) {
			$value = str_replace ( '{' . $i . '}', $arg, $value );
			$i ++;
		} // foreach
	} // if


	// Done here...
	return $value;
}
?>