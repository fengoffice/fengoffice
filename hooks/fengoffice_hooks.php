<?php
/*
 * Hooks para las instalaciones de Feng Office
 */

//Hook::register('fengoffice');
// No es necesario registrar porque en una instalación de FengOffice los OpenGoo hooks
// se llaman FengOffice hooks y por lo tanto ya está registrado el hook fengoffice

// para permitir loggearse en más de una instalación a la vez
if (!defined('USE_COOKIE_PREFIX')) define('USE_COOKIE_PREFIX', true);

function fengoffice_render_help_options($ignored, &$help_options) {
	$help_options[] = array(
		'title' => lang('how to purchase'),
		'desc' => lang('how to purchase desc'),
		'url' => 'http://www.fengoffice.com/web/choose_service.php',
		'target' => '_blank',
	);
	$help_options[] = array(
		'title' => lang('add ticket'),
		'desc' => lang('add ticket desc'),
		'url' => 'http://www.fengoffice.com/web/support/tickets.php',
		'target' => '_blank',
	);
}

?>