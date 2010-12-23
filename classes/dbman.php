<?php
/**
* Dbman
*
* @package        Dbman
* @author         Robert-Jan de Dreu
* @copyright      (c) 2010 Robert-Jan de Dreu
* @license        http://www.opensource.org/licenses/isc-license.txt
*/
class Dbman
{

	public function __construct()
	{
	
	}
	
	public static function factory()
	{
		return new Dbman;
	}

	public function update($version = null)
	{
		$dbman = new Model_Dbman;
		$dbman->update($version);
	}
}