-- <?php echo $table_prefix ?> og_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB
-- varchar cannot be larger than 256
-- blob/text cannot have default values
-- sql queries must finish with ;\n (line break inmediately after ;)

CREATE TABLE  `<?php echo $table_prefix ?>mail_contents` (
  `object_id` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL default '0',
  `uid` varchar(255) <?php echo $default_collation ?> NOT NULL default '',
  `from` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  `from_name` VARCHAR( 250 ) NULL,
  `sent_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `received_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `has_attachments` int(1) NOT NULL default '0',
  `size` int(10) NOT NULL default '0',
  `state` INT( 1 ) NOT NULL DEFAULT '0' COMMENT '0:nothing, 1:sent, 2:draft',
  `is_deleted` int(1) NOT NULL default '0',
  `is_shared` INT(1) NOT NULL default '0',
  `imap_folder_name` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  `account_email` varchar(100) <?php echo $default_collation ?> default '',
  `content_file_id` varchar(40) <?php echo $default_collation ?> NOT NULL default '',
  `message_id` varchar(255) <?php echo $default_collation ?> NOT NULL COMMENT 'Message-Id header',
  `in_reply_to_id` varchar(255) <?php echo $default_collation ?> NOT NULL COMMENT 'Message-Id header of the previous email in the conversation',
  `conversation_id` int(10) unsigned NOT NULL default '0',
  `conversation_last` int(1) NOT NULL default '1',
  `sync` int(1) NOT NULL default '0',
  PRIMARY KEY  (`object_id`),
  KEY `account_id` (`account_id`, `uid`),
  KEY `sent_date` (`sent_date`),
  KEY `received_date` (`received_date`),
  KEY `uid` (`uid`),
  KEY `conversation_id` (`conversation_id`),
  KEY `message_id` (`message_id`),
  KEY `state` (`state`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>mail_datas` (
  `id` int(10) unsigned NOT NULL,
  `to` text <?php echo $default_collation ?> NOT NULL,
  `cc` text <?php echo $default_collation ?> NOT NULL,
  `bcc` text <?php echo $default_collation ?> NOT NULL,
  `subject` text <?php echo $default_collation ?>,
  `content` text <?php echo $default_collation ?>,
  `body_plain` longtext <?php echo $default_collation ?>,
  `body_html` longtext <?php echo $default_collation ?>,
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>mail_accounts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `contact_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(40) <?php echo $default_collation ?> NOT NULL default '',
  `email` varchar(100) <?php echo $default_collation ?> default '',
  `email_addr` VARCHAR( 100 ) <?php echo $default_collation ?> NOT NULL default '',
  `password` varchar(40) <?php echo $default_collation ?> default '',
  `server` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  `is_imap` int(1) NOT NULL default '0',
  `incoming_ssl` int(1) NOT NULL default '0',
  `incoming_ssl_port` int default '995',
  `smtp_server` VARCHAR(100) <?php echo $default_collation ?> NOT NULL default '',
  `smtp_use_auth` INTEGER UNSIGNED NOT NULL default 0,
  `smtp_username` VARCHAR(100) <?php echo $default_collation ?>,
  `smtp_password` VARCHAR(100) <?php echo $default_collation ?>,
  `smtp_port` INTEGER UNSIGNED NOT NULL default 25,
  `del_from_server` INTEGER NOT NULL default 0,
  `mark_read_on_server` INTEGER NOT NULL default 1,
  `outgoing_transport_type` VARCHAR(5) <?php echo $default_collation ?> NOT NULL default '',
  `last_checked` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_default` BOOLEAN NOT NULL default '0',
  `signature` text <?php echo $default_collation ?> NOT NULL,
  `sender_name` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  `last_error_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_error_msg` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `sync_addr` varchar(100) <?php echo $default_collation ?> NOT NULL,
  `sync_pass` varchar(40) <?php echo $default_collation ?> NOT NULL,
  `sync_server` varchar(100) <?php echo $default_collation ?> NOT NULL,
  `sync_ssl` tinyint(1) NOT NULL default '0',
  `sync_ssl_port` int(11) NOT NULL default '993',
  `sync_folder` varchar(100) <?php echo $default_collation ?> NOT NULL,
  `member_id` varchar(100) <?php echo $default_collation ?> NOT NULL,
  
  PRIMARY KEY  (`id`),
  INDEX `contact_id` (`contact_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>mail_account_imap_folder` (
  `account_id` int(10) unsigned NOT NULL default '0',
  `folder_name` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  `check_folder` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`account_id`,`folder_name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>mail_account_contacts` (
 `id` INT(10) NOT NULL AUTO_INCREMENT,
 `account_id` INT(10) NOT NULL,
 `contact_id` INT(10) NOT NULL,
 `can_edit` BOOLEAN NOT NULL default '0',
 `is_default` BOOLEAN NOT NULL default '0',
 `signature` text <?php echo $default_collation ?> NOT NULL,
 `sender_name` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
 `last_error_state` int(1) unsigned NOT NULL default '0' COMMENT '0:no error,1:err unread, 2:err read',
 PRIMARY KEY (`id`),
 UNIQUE KEY `uk_contactacc` (`account_id`, `contact_id`),
 KEY `ix_contact` (`contact_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>mail_conversations` (
 `id` INT(10) NOT NULL AUTO_INCREMENT,
 PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>mail_spam_filters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(10) unsigned NOT NULL,
  `text_type` enum('email_address','subject') COLLATE utf8_unicode_ci NOT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `spam_state` enum('no spam','spam') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = <?php echo $engine ?> <?php echo $default_charset ?>;
