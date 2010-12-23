<?php defined('SYSPATH') or die('No direct script access.');

Route::set('default', 'dbman/<action>(/<id>)')
	->defaults(array(
		'controller' => 'dbman',
		'action'     => 'update',
	));