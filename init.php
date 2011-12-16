<?php defined('SYSPATH') or die('No direct script access.');
/**
* Dbman
*
* @package        Dbman
* @author         Robert-Jan de Dreu
* @copyright      (c) 2010 Robert-Jan de Dreu
* @license        http://www.opensource.org/licenses/isc-license.txt
*/

Route::set('dbman_default', 'dbman/<action>(/<version>(/<module>))')
	->defaults(array(
		'controller' => 'dbman',
		'action'     => 'update',
	));