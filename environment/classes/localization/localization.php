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

/**
 * Hardcoded language compatibility.
 * Maintain full translations for en_us and es_la, and only write deltas for the rest:
 *   en_ca -> en_us
 *   en_gb -> en_us
 *   es_la -> es_es -> en_us
 *   es_es -> es_la -> en_us
 */
function findSimilarLang(string $languageCode): ?string {
	$compatibility = array(
		'en_ca' => 'en_us',
		'en_gb' => 'en_us',
		'es_es' => 'es_la',
		'es_la' => 'es_es',
		// Add more languages here
	);
	return isset($compatibility[$languageCode]) ? $compatibility[$languageCode] : null;
}

/**
 * Fallback locales for a language, closest first, without the current locale.
 * Example: es_es => array('es_la', 'en_us')
 */
function getLanguageFallbackLocales(string $languageCode, $include_default = true) {
	$default = 'en_us';
	$fallbacks = array();

	$similar = findSimilarLang($languageCode);
	if ($similar && $similar !== $languageCode) {
		$fallbacks[] = $similar;
	}

	if ($include_default && $languageCode !== $default && !in_array($default, $fallbacks, true)) {
		$fallbacks[] = $default;
	}

	return $fallbacks;
}

function getCompatibleLocalization($reset = false) {
	static $languageCompatibleBase = null;

	if ($reset) {
		$languageCompatibleBase = null;
		return false;
	}

	if ($languageCompatibleBase === null) {
		$current_locale = Localization::instance()->getLocale();
		$similarLang = findSimilarLang($current_locale);
		// Skip when similar is en_us; the default localization already covers that case.
		if ($similarLang && $similarLang !== $current_locale && $similarLang !== 'en_us') {
			$languageCompatibleBase = new Localization();
			$languageCompatibleBase->loadSettings($similarLang, ROOT . "/language");
		} else {
			$languageCompatibleBase = false;
		}
	}

	return $languageCompatibleBase;
}

function getFallbackDefaultLocalization() {
	static $base = null;

	if (!$base instanceof Localization) {
		$base = new Localization();
		$base->loadSettings("en_us", ROOT . "/language");
	}

	return $base;
}

function langA($name, $args) {
	$value = Localization::instance()->lang($name);

	if (is_null ( $value )) {
		if (! Env::isDebugging ()) {

			$base = getFallbackDefaultLocalization();
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