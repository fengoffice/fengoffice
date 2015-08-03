<?php

  /**
  * ldap.config.example.php is sample configuration file for ldap authentication. 
  * Rename it in ldap.config.php and change the values depending on your environment
  * 
  *  
  * @author Luca Corbo <luca.corbo@2bopen.org>
  * 
  * Further information about LDAP settings within Feng Office can be found her:
  * http://www.fengoffice.com/web/wiki/doku.php/ldap
  * 
  */
 
 $config_ldap = array (
      'binddn'    => '',
      'bindpw'    => '',
      'basedn'    => 'ou=people,dc=my,dc=domain,dc=com',
      'host'      => 'ldap://192.168.1.5:389',
      'port'      => 389,    
      'uid' => 'uid', //unique id to match with the LDAP and the username
  );
  return true;
  
?>
