<?php

  /**
  * Empty config.php is sample configuration file. Use it when you need to manualy set up 
  * your Feng Office installation (installer breaks from some reason or any other reason). 
  * 
  * When you set the values in this file delete original 'config.php' (it should just have 
  * return false; command) and rename this one to 'config.php'
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  
  define('DB_ADAPTER', 'mysql'); 
  define('DB_HOST', 'localhost'); 
  define('DB_USER', 'root'); 
  define('DB_PASS', ''); 
  define('DB_NAME', 'fengoffice'); 
  define('DB_PERSIST', true); 
  define('TABLE_PREFIX', 'og_'); 
  define('ROOT_URL', 'http://fengoffice.com'); 
  define('DEFAULT_LOCALIZATION', 'en_us'); 
  define('DEBUG', true); 
  define('DB_CHARSET', 'utf8'); 
  
  return true;
  
?>