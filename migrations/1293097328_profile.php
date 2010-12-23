<?php defined('SYSPATH') or die('No direct script access.');

class Profile
{
	function up()
	{
		DB::query(null, '
			CREATE TABLE profiles
			(
			    id int(11) NOT NULL AUTO_INCREMENT,
			    user_id int(11) NOT NULL,
			    firstname VARCHAR(100) NOT NULL,
			    lastname VARCHAR(100) NOT NULL,
			    PRIMARY KEY (id)
			);
		')->execute();
	}
	
	function down()
	{
		DB::query(null, '
			DROP TABLE profiles
		')->execute();
	}
}