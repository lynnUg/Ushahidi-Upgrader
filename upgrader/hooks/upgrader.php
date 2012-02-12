<?php defined('SYSPATH') or die('No direct script access.');

class upgrader{
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
		$this->session = Session::instance();
		// Hook into routing
		Event::add('system.post_routing', array($this, 'add'));
	}

	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		// Only fire if we are in the admin panel
		$uri_arr = explode('/',Router::$routed_uri);
		if (in_array('admin',$uri_arr))
		{
			Event::add('ushahidi_action.admin_header_top_left', array($this, '_upgrade'));
			
		}
	}

	/**
	 * Show version
	 */
	public function _upgrade()
	{
		$view = View::factory('upgrader');

	
		$view->version_in_config = Kohana::config('version.ushahidi_version');

		// Check if we are upgrading anything
       
		if (isset($_POST['versionnotifier_update']))
		{
			
	    
     		$newbie=dirname(dirname(dirname(dirname(__FILE__))));
     		//upgrade current database accordingly
			$this->_process_sql($newbie."/plugins/upgrader/sql_upgrade/");
			
			url::redirect('plugins/upgrader/index.html');
			
			   
			
		}

		
		if($view->version_in_config < "2.1")
		{
			$view->needs_upgrade = TRUE;
			
		}else{
			$view->needs_upgrade = FALSE;
		}
		

		$view->render(TRUE);
	}
	
	
	public function _process_sql($dir_path)
	{
	
		$upgrade_sql = '';

		$files = scandir($dir_path);
		sort($files);
		foreach ( $files as $file )
		{
			// We're going to try and execute each of the sql files in order
			$file_ext = strrev(substr(strrev($file),0,4));
			if ($file_ext == ".sql")
			{
				
				$this->_import_sql($dir_path.$file);
			}
		}
		return "";
	}
	public function _import_sql($file2, $table_prefix = NULL)
	{
		
		 $host=Kohana::config('database.default.connection.host');
		 $username=Kohana::config('database.default.connection.user');
		 $password=Kohana::config('database.default.connection.pass');
		 $db_name=Kohana::config('database.default.connection.database');
		$connection = @mysql_connect("$host", "$username", "$password");
		if(is_file($file2))
		{
			//url::redirect(url::base().'reports');
		}
		$db_schema = @file_get_contents($file2);

		// If a table prefix is specified, add it to sql
		if ($table_prefix) {
			$find = array(
				'CREATE TABLE IF NOT EXISTS `',
				'INSERT INTO `',
				'ALTER TABLE `',
				'UPDATE `',
				'DELETE FROM `'
				);
			$replace = array(
				'CREATE TABLE IF NOT EXISTS `'.$table_prefix.'_',
				'INSERT INTO `'.$table_prefix.'_',
				'ALTER TABLE `'.$table_prefix.'_',
				'UPDATE `'.$table_prefix.'_',
				'DELETE FROM `'.$table_prefix.'_'
				);
			$db_schema = str_replace($find, $replace, $db_schema);
		}

		
		@mysql_select_db($db_name,$connection);
		/**
		 * split by ; to get the sql statement for creating individual
		 * tables.
		 */
		$tables = explode(';',$db_schema);

		foreach($tables as $query) {

			$result = @mysql_query($query,$connection);
		}

		@mysql_close( $connection );

	}
	
	public function _do_upgrade() 
	{
       
	}
	
}

new upgrader;
