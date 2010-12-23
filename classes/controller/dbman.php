<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Dbman extends Controller
{
	public function action_update($version = null)
	{
		Dbman::factory()->update($version);
	}
}