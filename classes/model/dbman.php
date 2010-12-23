<?php defined('SYSPATH') or die('No direct script access.');

class Model_Dbman
{
	public $_directory = '';

	public function __construct()
	{
		$this->_directory = Kohana::config('dbman.path');
		
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
				    version int(11) NOT NULL,
				    name VARCHAR(100) NOT NULL,
				    extra VARCHAR(100) NOT NULL,
				    PRIMARY KEY (id)
				);
			')->execute();
		}
	}

	public function update($version = null)
	{
		//check what version the database is on.
		$dbversion = $this->get_db_version();
		$version = (!$version) ? $this->get_migration_version() : $version;
		
		//Check the direction you want to update to
		$direction = ($dbversion < $version) ? 'up' : 'down';
		
		//if not the latest version. Update.
		$migrations = $this->get_migration_list($dbversion, $version);
		
		foreach($migrations as $migration)
		{
			$this->run_file($migration, $direction);
		}
	}
	
	public function get_db_version()
	{
		$version = DB::select()
			->from('schema_version')
			->order_by('version', 'DESC')
			->limit(1)
		->execute()->get('version');
		
		return ($version) ? $version : 0;
	}
	
	public function get_migration_version()
	{
		$migations = $this->get_list();
		$latest = end($migations);
		
		preg_match('/(\d+)_([a-z_]+)/', $latest, $migration);
		
		return $migration[1];
	}
	
	public function get_list($order = 'DESC')
	{
		$files = array();
		$order = ($order == 'DESC') ? 0 : 1;

		foreach(scandir($this->_directory, $order) as $file) 
		{
			if(!in_array($file, array('.', '..')))
			{
				$files[] = $file;
			}
		}
		
		return $files;
	}
	
	public function get_migration_list($start_version, $stop_version)
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
		
		foreach($this->get_list() as $file) 
		{
			if(preg_match('/(\d+)_([a-z_]+)/', $file, $file))
			{
				$version 	= $file[1];
				$class	= $file[2];
				$file	= $file[0];
				
				if($version > $start && $version <= $stop)
				{
					$migrations[$version] = array(
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
		
		require_once($this->_directory.'/'.$file.'.php');
		
		if(!class_exists($class, false))
		{
			die;
		}
		
		$migration = new $class();
		$migration->$direction();
		
		if($direction == 'up')
		{
			DB::query(Database::INSERT, 'INSERT INTO schema_version (version, name) VALUES (:version, :name)')
				->bind(':version', $version)
				->bind(':name', $class)
			->execute();
		}
		else
		{
			DB::query(Database::DELETE, 'DELETE FROM schema_version WHERE version = :version')
				->bind(':version', $version)
			->execute();
		}
	}
}