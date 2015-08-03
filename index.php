<?php

  if(!version_compare(phpversion(), '5.0', '>=')) {
    die('<strong>Installation error:</strong> in order to run Feng Office you need PHP5. Your current PHP version is: ' . phpversion());
  } // if
  
  require 'init.php';
  
?>