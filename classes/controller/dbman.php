<?php defined('SYSPATH') or die('No direct script access.');
/**
* Dbman
*
* @package        Dbman
* @author         Robert-Jan de Dreu
* @copyright      (c) 2010 Robert-Jan de Dreu
* @license        http://www.opensource.org/licenses/isc-license.txt
*/

class Controller_Dbman extends Controller
{
	public function action_update()
	{
		$module =  $this->request->param('module');
		$version = $this->request->param('version');
		Dbman::factory()->update($module, $version);
	}
}