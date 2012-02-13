<?php defined('SYSPATH') or die('No direct script access.');

class upgrader{
	
	public $move_on;

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
			 $move_on=$this->_process_sql($newbie."/plugins/upgrader/sql_upgrade/");
			 if($move_on)
			 {
			   $this->copy_recursively($newbie."/plugins/upgrader/Ushahidi_New",$newbie);
		     }
		  
		     
			
		}

		
		if($view->version_in_config < "2.1")
		{
			$view->needs_upgrade = TRUE;
			
		}else{
			$view->needs_upgrade = FALSE;
		}
		

		$view->render(TRUE);
	}
	
	//recursively remove directory
	public function remove_recursively($dir) 
	{
		
		if (empty($dir) || !is_dir($dir))
			return false;
		if (substr($dir,-1) != "/")
			$dir .= "/";
		if (($dh = opendir($dir)) !== false) {
			while (($entry = readdir($dh)) !== false) {
			if ($entry != "." && $entry != "..") {
				if ( is_file($dir . $entry) ) {
				if ( !@unlink($dir . $entry) ) {
					url::redirect(url::base().'/reports');
				}
			} elseif (is_dir($dir . $entry)) {
				$this->remove_recursively($dir . $entry);
				
			}
			}
		}
		closedir($dh);
		if ( !@rmdir($dir) ) {
		
		}
			
			return true;
		}
		return false;
		
	}
	//recuresively copy folder to new destination
	
	public function copy_recursively($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755))
	{		
		
		if (is_file($source)) {
			//url::redirect(url::base().'/reports');
			if ($dest[strlen($dest)-1]=='/')
			{
				if (!file_exists($dest))
				{
					cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
				}
				$__dest = $dest."/".basename($source);
			}
			else
			{
				$__dest=$dest;
			}
			// Turn off error reporting temporarily
			error_reporting(0);
			$result = copy($source, $__dest);
			if ($result)
			{
				chmod($__dest,$options['filePermission']);
				
				//Turn on error reporting again
				error_reporting($this->error_level);
			}
			else
			{
				
				//Turn on error reporting again
				error_reporting($this->error_level);
				return false;
			}

		}
		elseif(is_dir($source))
		{
			
			if ($dest[strlen($dest)-1] == '/')
			{
				
			}
			else
			{
				//url::redirect(url::base().'/reports');
				if ( ! is_writable($dest))
				{
					//url::redirect(url::base().'/reports');
					@chmod($dest,777);
					 //return false;
				}
				
				if ($source[strlen($source)-1] == '/')
				{
					//Copy parent directory with new name and all its content
					//url::redirect(url::base().'/reports');
					//@mkdir($dest,$options['folderPermission']);
					//chmod($dest,$options['filePermission']);
				}
				else
				{
					
					//Copy parent directory with new name and all its content
					//url::redirect(url::base().'/reports');
					//@mkdir($dest,$options['folderPermission']);
					//chmod($dest,$options['filePermission']);
				}
				//url::redirect(url::base().'/reports');
			}
           // url::redirect(url::base().'/reports');
			$dirHandle=opendir($source);
			while($file=readdir($dirHandle))
			{
          	if($file!="." AND $file!=".." AND substr($file, 0, 1) != '.')
				{
					if(!is_dir($source."/".$file))
					{
						$__dest=$dest."/".$file;
					}
					else
					{
						$__dest=$dest."/".$file;
					}
					//echo "$source/$file ||| $__dest<br />";
					if ( ! is_writable($__dest))
					{
						
						//return false;
					}
					$result = $this->copy_recursively($source."/".$file, $__dest, $options);
				}
			}
			closedir($dirHandle);
		}
		
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
				//$this->upgrade->logger("Database imported ".$dir_path.$file);
				//$this->_execute_upgrade_script($dir_path.$file);
				$this->_import_sql($dir_path.$file);
			}
		}
		 //$this->copy_recursively($newbie."/plugins/upgrader/Ushahidi_New",$newbie);
		return true;
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
		if($connection)
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
