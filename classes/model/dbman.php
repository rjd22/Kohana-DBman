<?php defined('SYSPATH') or die('No direct script access.');
/**
* Dbman
*
* @package        Dbman
* @author         Robert-Jan de Dreu
* @copyright      (c) 2010 Robert-Jan de Dreu
* @license        http://www.opensource.org/licenses/isc-license.txt
*/

class Model_Dbman
{
	public $_directory = '';

	public function __construct()
	{
		$this->_directory = Kohana::$config->load('dbman.path');
		
		//check if the versioning table exists
		$table = DB::query(null, '
			SHOW TABLES LIKE "schema_version"
		')->execute();
		
		if($table == 0)
		{
			//if the table doesn't exist create
			DB::query(null, '
				CREATE TABLE schema_version
				(
				    id int(11) NOT NULL AUTO_INCREMENT,
				    module VARCHAR(100) NOT NULL,
				    version int(11) NOT NULL,
				    name VARCHAR(100) NOT NULL,
				    extra VARCHAR(100) NOT NULL,
				    PRIMARY KEY (id)
				);
			')->execute();
		}
	}

	public function update($module = null, $version = null)
	{
		
		if (isset($module) or isset($version))
		{
			if (null === $module)
			{
				$module = '';
			}
			$this->update_module($module, $version);
		}
		else
		{
			$this->update_module('');
			foreach (Kohana::modules() as $module => $path)
			{
				$this->update_module($module);
			}
		}
		
	}
	
	public function update_module($module, $version = null)
	{
		//check what version the database is on.
		$dbversion = $this->get_db_version($module);
		$version = (isset($version)) ? $version : $this->get_migration_version($module);
		
		//Check the direction you want to update to
		$direction = ($dbversion < $version) ? 'up' : 'down';
		
		//if not the latest version. Update.
		$migrations = $this->get_migration_list($module, $dbversion, $version);
		
		foreach($migrations as $migration)
		{
			$this->run_file($migration, $direction);
		}
	}
	
	public function get_db_version($module = '')
	{
		$version = DB::select()
			->from('schema_version')
			->where('module', '=', $module)
			->order_by('version', 'DESC')
			->limit(1)
		->execute()->get('version');
		
		return ($version) ? $version : 0;
	}
	
	public function get_migration_version($module = '')
	{
		$migrations = $this->get_list($module);
		
		sort($migrations[$module]);
		$latest = end($migrations[$module]);
		
		preg_match('/(\d+)_([a-z_]+)/', $latest, $migration);
		
		return isset($migration[1]) ? $migration[1] : 0;
	}
	
	public function get_list($order = 'DESC')
	{
		$files = array('' => array());
		$order = ($order == 'DESC') ? 0 : 1;
		
		if (is_dir(APPPATH.$this->_directory)) {
			foreach(scandir(APPPATH.$this->_directory, $order) as $file) 
			{
				if (!preg_match('/^\./', basename($file)))
				{
					$files[''][] = APPPATH.$this->_directory.$file;
				}
			}
		}
		foreach(Kohana::modules() as $name => $path)
		{
			$files[$name] = array();
			if (is_dir($path.$this->_directory))
			{
				foreach(scandir($path.$this->_directory, $order) as $file) 
				{
					if (!preg_match('/^\./', basename($file)))
					{
						$files[$name][] = $path.$this->_directory.$file;
					}
				}
			}
		}
		return $files;
	}
	
	public function get_migration_list($module = '', $start_version, $stop_version)
	{
		$migrations = array();
		
		$start = $start_version;
		$stop = $stop_version;
		$direction = 'up';
		
		if($start > $stop)
		{
			$start = $stop_version;
			$stop = $start_version;
			$direction = 'down';
		}
		
		$list = $this->get_list();
		foreach($list[$module] as $file)
		{
			
			if(preg_match('/(\d+)_([a-z_]+)/', $file, $file_parts))
			{
				$version 	= $file_parts[1];
				$class	= $file_parts[2];
				
				if($version > $start && $version <= $stop)
				{
					$migrations[$version] = array(
						'module' => $module,
						'version' => $version,
						'file' 	=> $file,
						'class'	=> $class
					);
				}
			}
		}
		
		if($direction != 'up')
			krsort($migrations);
		
		return $migrations;
	}
	
	public function run_file($migration, $direction = 'up')
	{
		extract($migration);
		
		require_once $file;
		
		$class_name = 'Migration_' . $class;
		
		if(!class_exists($class_name, false))
		{
			throw new Exception("{$class_name} does not exist in {$file}");
		}
		
		$migration = new $class_name();
		$migration->$direction();
		echo get_class($migration), "->{$direction}<br />\n";
		
		if($direction == 'up')
		{
			DB::query(Database::INSERT, 'INSERT INTO schema_version (module, version, name) VALUES (:module, :version, :name)')
				->bind(':module', $module)
				->bind(':version', $version)
				->bind(':name', $class)
			->execute();
		}
		else
		{
			DB::query(Database::DELETE, 'DELETE FROM schema_version WHERE module = :module AND version = :version')
				->bind(':module', $module)
				->bind(':version', $version)
			->execute();
		}
	}
}