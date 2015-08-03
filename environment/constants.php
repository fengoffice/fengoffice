<?php

  // Data type constants, used by data access object...
  define('DATA_TYPE_NONE',     'NONE');
  define('DATA_TYPE_INTEGER',  'INTEGER');
  define('DATA_TYPE_STRING',   'STRING');
  define('DATA_TYPE_FLOAT',    'FLOAT');
  define('DATA_TYPE_BOOLEAN',  'BOOLEAN');
  define('DATA_TYPE_DATETIME', 'DATETIME');
  define('DATA_TYPE_DATE',     'DATE');
  define('DATA_TYPE_TIME',     'TIME');
  define('DATA_TYPE_ARRAY',    'ARRAY');
  define('DATA_TYPE_RESOURCE', 'RESOURCE');
  define('DATA_TYPE_OBJECT',   'OBJECT');
  define('DATA_TYPE_WSCOLOR',  'WSCOLOR');
  
  
  define('EMPTY_IMAGE', 's.gif');
  
  define('LUCENE_SEARCH', false);
  define('CATDOC_PATH', 'catdoc');
  define('CATPPT_PATH', 'catppt');
  
  // Some nice to have regexps
  define('EMAIL_FORMAT', "/^([a-z0-9+_']|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i");
  //define('URL_FORMAT', "/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}((:[0-9]{1,5})?\/.*)?$/i");
  define('URL_FORMAT', "/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*((:[0-9]{1,5})?\/.*)?$/i"); // be a little more flexible with urls (allow ips or machine names)
  
  define('DATE_MYSQL', 'Y-m-d H:i:s');
  define('EMPTY_DATETIME', '0000-00-00 00:00:00');
  define('EMPTY_DATE', '0000-00-00');
  define('EMPTY_TIME', '00:00:00');
  
  // Compatibility constants (available since PHP 5.1.1). This constants are taken from
  // PHP_Compat PEAR package
  if (!defined('DATE_ATOM'))    define('DATE_ATOM',    'Y-m-d\TH:i:sO');
  if (!defined('DATE_COOKIE'))  define('DATE_COOKIE',  'D, d M Y H:i:s T');
  if (!defined('DATE_ISO8601')) define('DATE_ISO8601', 'Y-m-d\TH:i:sO');
  if (!defined('DATE_RFC822'))  define('DATE_RFC822',  'D, d M Y H:i:s T');
  if (!defined('DATE_RFC850'))  define('DATE_RFC850',  'l, d-M-y H:i:s T');
  if (!defined('DATE_RFC1036')) define('DATE_RFC1036', 'l, d-M-y H:i:s T');
  if (!defined('DATE_RFC1123')) define('DATE_RFC1123', 'D, d M Y H:i:s T');
  if (!defined('DATE_RFC2822')) define('DATE_RFC2822', 'D, d M Y H:i:s O');
  if (!defined('DATE_RSS'))     define('DATE_RSS',     'D, d M Y H:i:s T');
  if (!defined('DATE_W3C'))     define('DATE_W3C',     'Y-m-d\TH:i:sO');
  
  define('SQL_NOT_DELETED', " archived_on = '".EMPTY_DATETIME. "' AND trashed_on = '".EMPTY_DATETIME."'" );
?>