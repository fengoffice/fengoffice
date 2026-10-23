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
  define('DATA_TYPE_TIMEZONE', 'TIMEZONE');
  
  
  define('EMPTY_IMAGE', 's.gif');
  
  define('LUCENE_SEARCH', false);
  define('CATDOC_PATH', 'catdoc');
  define('CATPPT_PATH', 'catppt');

  // Upper bound for the member/project list page-size preference (see MemberManager.js)
  define('MEMBERS_PER_PAGE_MAX', 1000);
  
  // Some nice to have regexps
  define('EMAIL_FORMAT', "/^([a-z0-9+_']|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{1,50}\$/i");
  //define('URL_FORMAT', "/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}((:[0-9]{1,5})?\/.*)?$/i");
  define('URL_FORMAT', "/^(http|https):\/\/[a-z0-9_]+([\-\.]{1}[a-z0-9_]+)*((:[0-9]{1,5})?\/.*)?$/i"); // be a little more flexible with urls (allow ips or machine names)
  
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

  // What to do with the subtasks of a task when its classification changes, per dimension
  // (config option 'apply_classification_to_subtasks')
  define('SUBTASK_CLASSIFICATION_NEVER', 'never');
  define('SUBTASK_CLASSIFICATION_IF_EMPTY', 'if_empty');
  define('SUBTASK_CLASSIFICATION_ALWAYS', 'always');
  // used for the dimensions with no value stored and when the config option is missing
  define('SUBTASK_CLASSIFICATION_DEFAULT', SUBTASK_CLASSIFICATION_ALWAYS);

  // chrome-php sync/navigation/PDF timeout in ms. Override in config.php per server if needed.
  // Library default is 5000; large report HTML often needs more.
  if (!defined('CHROME_TIMEOUT_MS')) define('CHROME_TIMEOUT_MS', 60000);

  // Contact groups a contact custom property selector can be restricted to. Stored in
  // custom_properties.filter_values_by as comma separated tokens and sent to the selector
  // as a bitmask. The groups are disjoint and together cover every contact.
  if (!defined('CONTACT_CP_FILTER_COMPANIES')) define('CONTACT_CP_FILTER_COMPANIES', 1);
  if (!defined('CONTACT_CP_FILTER_CONTACTS'))  define('CONTACT_CP_FILTER_CONTACTS', 2);
  if (!defined('CONTACT_CP_FILTER_USERS'))     define('CONTACT_CP_FILTER_USERS', 4);
?>
