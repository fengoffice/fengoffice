<?php

/**
  Session class
 
  Enables us to see how many sessions there are and 
  get session information directly out the database.
  
  We'll even be able to link sessions to users so if we want
  we can track each user over the site.
**/
class Session {
	const DEFAULT_SESSION_PATH = './tmp/';
	
	private $sessionExists = false;
	
	private static 	$sessionStarted;
  
	public function __construct () {
		if(self::$sessionStarted === true)
		  return true;
		  
		session_set_save_handler(array(& $this, 'open'),
		                         array(& $this, 'close'),
		                         array(& $this, 'read'),
		                         array(& $this, 'write'),
		                         array(& $this, 'destroy'),
		                         array(& $this, 'gc'));
		register_shutdown_function("session_write_close"); 
		session_start();
		self::$sessionStarted = true;
	}
  
	function open($save_path, $session_name)
	{
	  global $sess_save_path;
		
	  $sess_save_path = $save_path;
	  if(!$sess_save_path)
	  	$sess_save_path = self::DEFAULT_SESSION_PATH;
	  return(true);
	}
	
	function close()
	{
	  return(true);
	}
	
	function read($id)
	{
	  global $sess_save_path;
	
	  $sess_file = "$sess_save_path/sess_$id";
	  return (string) @file_get_contents($sess_file);
	}
	
	function write($id, $sess_data)
	{
	  global $sess_save_path;
	
	  $sess_file = "$sess_save_path/sess_$id";
	  if ($fp = @fopen($sess_file, "w")) {
	    $return = fwrite($fp, $sess_data);
	    fclose($fp);
	    return $return;
	  } else {
	    return(false);
	  }
	
	}
	
	function destroy($id)
	{
	  global $sess_save_path;
	
	  $sess_file = "$sess_save_path/sess_$id";
	  FilesController::auto_checkin();
	  return(@unlink($sess_file));
	}
	
	function gc($maxlifetime)
	{
	  global $sess_save_path;
	
	  foreach (glob("$sess_save_path/sess_*") as $filename) {
	    if (filemtime($filename) + $maxlifetime < time()) {
	      @unlink($filename);
	    }
	  }
	  FilesController::auto_checkin();
	  return true;
	}
}
?>