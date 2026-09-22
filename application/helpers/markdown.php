<?php

/**
 * Parse markdown text and return safe HTML
 *
 * @param string $text Input markdown text
 * @return string
 */
function do_markdown($text) {
	require_once ROOT . '/library/parsedown/Parsedown.php';
	$parsedown = new Parsedown();
	$parsedown->setSafeMode(true);
	$parsedown->setUrlsLinked(true);
	$parsedown->setBreaksEnabled(true);
	$html = $parsedown->text($text);
	// Additional sanitization via HTMLPurifier for defense in depth
	$html = purify_html($html);
	return '<div class="markdown-viewer">' . $html . '</div>';
} // do_markdown

/**
 * Check if a file is a markdown file by extension
 *
 * @param string $filename
 * @return bool
 */
function is_markdown_file($filename) {
	$ext = strtolower(get_file_extension($filename));
	return in_array($ext, array('md', 'markdown'));
} // is_markdown_file

?>
