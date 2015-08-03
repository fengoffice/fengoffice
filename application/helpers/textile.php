<?php

/**
 * Just textile the text and return
 *
 * @param string $text Input text
 * @param boolean $lite Skip lists, tables and blocks
 * @param boolean $encode Encode and return
 * @param boolean $noimage Don't insert images
 * @param boolean $strict Fix entities and fix whitespace
 * @param string $rel
 * @return string
 */
function do_textile($text, $lite = false, $encode = false, $noimage = false, $strict = false, $rel = '') {
	Env::useLibrary('textile');
	$textile = new Textile();
	return '<div class="textile-rewrite">' . $textile->TextileThis($text, $lite, $encode, $noimage, $strict, $rel) . '</div>';
} // do_textile

?>