INSERT INTO `<?php echo $table_prefix ?>administration_tools` (`name`, `controller`, `action`, `order`) VALUES
	('test_mail_settings', 'administration', 'tool_test_email', 1);

INSERT INTO `<?php echo $table_prefix ?>config_categories` (`name`, `is_system`, `category_order`) VALUES
	('system', 1, 0),
	('general', 0, 1),
	('mailing', 0, 2),
	('brand_colors', 0, 3),
	('passwords', 0, 4);


INSERT INTO `<?php echo $table_prefix ?>config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
	('system', 'project_logs_per_page', '10', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'messages_per_page', '5', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'max_avatar_width', '50', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'max_avatar_height', '50', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'logs_per_project', '5', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'max_logo_width', '50', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'max_logo_height', '50', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'files_per_page', '50', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'notification_from_address', '', 'StringConfigHandler', 1, 0, 'Address to use as from field in email notifications. If empty, users address is used'),
	('system', 'min_chars_for_match', '3', 'IntegerConfigHandler', 1, 0, 'If search criteria len is less than this, then use always LIKE'),
	('system', 'getting_started_step', '1', 'IntegerConfigHandler', 1, 0, ''),
	('general', 'upgrade_last_check_datetime', '2006-09-02 13:46:47', 'DateTimeConfigHandler', 1, 0, 'Date and time of the last upgrade check'),
	('general', 'upgrade_last_check_new_version', '0', 'BoolConfigHandler', 1, 0, 'True if system checked for the new version and found it. This value is used to hightligh upgrade tab in the administration'),
	('general', 'file_storage_adapter', 'fs', 'FileStorageConfigHandler', 0, 0, 'What storage adapter should be used? fs or mysql'),
	('general', 'theme', 'default', 'ThemeConfigHandler', 0, 0, NULL),
	('general', 'days_on_trash', '30', 'IntegerConfigHandler', 0, 0, 'Days before a file is deleted from trash. 0 = Not deleted'),
	('mailing', 'exchange_compatible', '0', 'BoolConfigHandler', 1, 0, NULL),
	('mailing', 'mail_transport', 'mail()', 'MailTransportConfigHandler', 0, 0, 'Values: ''mail()'' - try to emulate mail() function, ''smtp'' - use SMTP connection'),
	('mailing', 'smtp_server', '', 'StringConfigHandler', 0, 0, ''),
	('mailing', 'smtp_port', '25', 'IntegerConfigHandler', 0, 0, NULL),
	('mailing', 'smtp_address', '', 'StringConfigHandler', 0, 0, ''),
	('mailing', 'smtp_authenticate', '0', 'BoolConfigHandler', 0, 0, 'Use SMTP authentication'),
	('mailing', 'smtp_username', '', 'StringConfigHandler', 0, 0, NULL),
	('mailing', 'smtp_password', '', 'PasswordConfigHandler', 0, 0, NULL),
	('mailing', 'smtp_secure_connection', 'no', 'SecureSmtpConnectionConfigHandler', 0, 0, 'Values: no, ssl, tls'),
	('mailing', 'show images in document notifications', '0', 'BoolConfigHandler', 0, 0, NULL),
	('mailing', 'notification_recipients_field', 'to', 'MailFieldConfigHandler', '0', '10', NULL),
	('passwords', 'min_password_length', '0', 'IntegerConfigHandler', 0, '1', NULL),
	('passwords', 'password_numbers', '0', 'IntegerConfigHandler', 0, '2', NULL),
	('passwords', 'password_uppercase_characters', '0', 'IntegerConfigHandler', 0, '3', NULL),
	('passwords', 'password_metacharacters', '0', 'IntegerConfigHandler', 0, '4', NULL),
	('passwords', 'password_expiration', '0', 'IntegerConfigHandler', 0, '5', NULL),
	('passwords', 'password_expiration_notification', '0', 'IntegerConfigHandler', 0, '6', NULL),
	('passwords', 'account_block', '0', 'BoolConfigHandler', 0, '7', NULL),
	('passwords', 'new_password_char_difference', '0', 'BoolConfigHandler', '0', '8', NULL),
	('passwords', 'validate_password_history', '0', 'BoolConfigHandler', '0', '9', NULL),
	('passwords', 'block_login_after_x_tries', '0', 'BoolConfigHandler', '0', '20', NULL),
	('general', 'checkout_notification_dialog', '0', 'BoolConfigHandler', '0', '0', NULL),
	('general', 'file_revision_comments_required', '0', 'BoolConfigHandler', '0', '0', NULL),
	('general', 'currency_code', '$', 'StringConfigHandler', '0', '0', NULL),
	('general', 'checkout_for_editing_online', '0', 'BoolConfigHandler', '0', '0', NULL),
	('general', 'show_feed_links', '0', 'BoolConfigHandler', '0', '0', NULL),
	('general', 'use_owner_company_logo_at_header', '1', 'BoolConfigHandler', '0', '0', NULL),
	('general', 'ask_administration_autentification', 0, 'BoolConfigHandler', 0, 0, NULL),
	('general', 'use tasks dependencies', 1, 'BoolConfigHandler', 0, 0, NULL),
    ('general', 'untitled_notes', '0', 'BoolConfigHandler', '0', '0', NULL),
    ('general', 'repeating_task', '0', 'BoolConfigHandler', '0', '0', NULL),
    ('general', 'working_days', '1,2,3,4,5,6,7', 'StringConfigHandler', '0', '0', NULL),
    ('general', 'wysiwyg_tasks', '1', 'BoolConfigHandler', '0', '0', NULL),
    ('general', 'wysiwyg_messages', '1', 'BoolConfigHandler', '0', '0', NULL),
    ('general', 'wysiwyg_projects', '0', 'BoolConfigHandler', '0', '0', NULL),
    ('general', 'use_milestones', '0', 'BoolConfigHandler', '0', '0', NULL),
    ('general', 'show_tab_icons', '1', 'BoolConfigHandler', '0', '0', NULL),
	('general', 'can_assign_tasks_to_companies', '0', 'BoolConfigHandler', '0', '0', NULL),
    ('general', 'use_object_properties', '0', 'BoolConfigHandler', '0', '0', NULL),
    ('general', 'let_users_create_objects_in_root', '1', 'BoolConfigHandler', '0', '0', NULL),
    ('general', 'add_default_permissions_for_users', '1', 'BoolConfigHandler', '0', '0', NULL),
    ('general', 'inherit_permissions_from_parent_member', '1', 'BoolConfigHandler', '0', '0', NULL),
    ('general', 'give_member_permissions_to_new_users', '', 'UserTypeMultipleConfigHandler', '0', '0', NULL),
    ('general', 'milestone_selector_filter', 'current_and_parents', 'MilestoneSelectorFilterConfigHandler', 0, 0, NULL),
    ('general', 'show_owner_company_name_header', '0', 'BoolConfigHandler', 1, 100, ''),
	('general', 'enabled_dimensions', '', 'RootDimensionsConfigHandler', '1', '0', NULL),
	('general', 'last_sharing_table_rebuild', '', 'StringConfigHandler', '1', '0', NULL),
	('general', 'check_unique_mail_contact_comp', '0', 'BoolConfigHandler', 0, 0, NULL),
	('general', 'mandatory_address_fields', '', 'AddressFieldsConfigHandler', 0, 0, NULL),
	('system', 'last_template_instantiation_id', '0', 'IntegerConfigHandler', 1, 0, NULL),
	('brand_colors', 'brand_colors_head_back', '424242', 'ColorPickerConfigHandler', '0', '0', NULL),
	('brand_colors', 'brand_colors_head_font', 'FFFFFF', 'ColorPickerConfigHandler', '0', '0', NULL),
	('brand_colors', 'brand_colors_tabs_back', 'e7e7e7', 'ColorPickerConfigHandler', '1', '0', NULL),
	('brand_colors', 'brand_colors_tabs_font', '333333', 'ColorPickerConfigHandler', '1', '0', NULL);
		
INSERT INTO `<?php echo $table_prefix ?>file_types` (`extension`, `icon`, `is_searchable`, `is_image`) VALUES
	('zip', 'archive.png', 0, 0),
	('rar', 'archive.png', 0, 0),
	('bz', 'archive.png', 0, 0),
	('bz2', 'archive.png', 0, 0),
	('gz', 'archive.png', 0, 0),
	('ace', 'archive.png', 0, 0),
	('mp3', 'audio.png', 0, 0),
	('wma', 'audio.png', 0, 0),
	('ogg', 'audio.png', 0, 0),
	('doc', 'doc.png', 0, 0),
	('xls', 'xls.png', 0, 0),
	('docx', 'doc.png', 1, 0),
	('xlsx', 'xls.png', 0, 0),
	('gif', 'image.png', 0, 1),
	('jpg', 'image.png', 0, 1),
	('jpeg', 'image.png', 0, 1),
	('png', 'image.png', 0, 1),
	('mov', 'mov.png', 0, 0),
	('pdf', 'pdf.png', 1, 0),
	('psd', 'psd.png', 0, 0),
	('rm', 'rm.png', 0, 0),
	('svg', 'svg.png', 0, 0),
	('swf', 'swf.png', 0, 0),
	('avi', 'video.png', 0, 0),
	('mpeg', 'video.png', 0, 0),
	('mpg', 'video.png', 0, 0),
	('qt', 'mov.png', 0, 0),
	('vob', 'video.png', 0, 0),
	('txt', 'text.png', 1, 0),
	('html', 'html.png', 1, 0),
	('slim', 'ppt.png', 1, 0),
	('ppt', 'ppt.png', 0, 0),
	('webfile', 'webfile.png', 0, 0),
    ('odt', 'doc.png', '0', '0'),
    ('fodt', 'doc.png', '0', '0');

INSERT INTO `<?php echo $table_prefix ?>im_types` (`name`, `icon`) VALUES
	('ICQ', 'icq.gif'),
	('AIM', 'aim.gif'),
	('MSN', 'msn.gif'),
	('Yahoo!', 'yahoo.gif'),
	('Skype', 'skype.gif'),
	('Jabber', 'jabber.gif');


INSERT INTO `<?php echo $table_prefix ?>cron_events` (`name`, `recursive`, `delay`, `is_system`, `enabled`, `date`) VALUES
	('purge_trash', '1', '1440', '1', '1', '0000-00-00 00:00:00'),
	('send_reminders', '1', '10', '0', '1', '0000-00-00 00:00:00'),
	('send_password_expiration_reminders', '1', '1440', '1', '1', '0000-00-00 00:00:00'),
	('send_notifications_through_cron', '1', '1', '0', '0', '0000-00-00 00:00:00'),
	('delete_mails_from_server', '1', '1440', '1', '1', '0000-00-00 00:00:00'),
	('clear_tmp_folder', '1', '1440', '1', '1', '0000-00-00 00:00:00'),
	('check_upgrade', '1', '1440', '1', '0', '0000-00-00 00:00:00'),
	('import_google_calendar', '1', '10', '0', '0', '0000-00-00 00:00:00'),
	('export_google_calendar', '1', '10', '0', '0', '0000-00-00 00:00:00'),
	('sharing_table_partial_rebuild', '1', '1440', '1', '1', '0000-00-00 00:00:00'),
	('check_sharing_table_flags', '1', '10', '1', '1', '0000-00-00 00:00:00'),
	('rebuild_contact_member_cache', '1', '1440', '1', '1', '0000-00-00 00:00:00');
	
INSERT INTO `<?php echo $table_prefix ?>object_reminder_types` (`name`) VALUES
  ('reminder_email'),
  ('reminder_popup');
  
INSERT INTO `<?php echo $table_prefix ?>contact_config_categories` (`name`, `is_system`, `type`, `category_order`) VALUES 
	('general', 0, 0, 0),
	('task panel', 0, 0, 2),
	('calendar panel', 0, 0, 4),
	('context help', 1, 0, 5),
	('time panel', 0, 0, 3),
	('listing preferences', 0, 0, 10);
	
INSERT INTO `<?php echo $table_prefix ?>contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
 ('task panel','tasksDateStart','0000-00-00 00:00:00','DateTimeConfigHandler',1,0,'date from to filter out task list'),
 ('task panel','tasksDateEnd','0000-00-00 00:00:00','DateTimeConfigHandler',1,0,'the date up to filter the list of tasks'),
 ('task panel', 'show_notify_checkbox_in_quick_add', '1', 'BoolConfigHandler', 1, 0, 'Show notification checkbox in quick add task view'),
 ('task panel', 'can notify from quick add', '1', 'BoolConfigHandler', 0, 0, 'Notification checkbox default value'),
 ('task panel', 'can notify subscribers', '1', 'BoolConfigHandler', 0, 0, 'Notification checkbox default value'),
 ('task panel', 'tasksShowWorkspaces', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowTime', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowDates', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowPercentCompletedBar', '0', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowTimeEstimates', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowTimePending', '0', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowTimeWorked', '0', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowQuickEdit', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowQuickComplete', '0', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowQuickComment', '0', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowStartDates', '0', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowAssignedBy', '0', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowClassification', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowSubtasksStructure', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowEndDates', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowQuickAddSubTasks', '0', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowDescriptionOnTimeForms', '1', 'BoolConfigHandler', 0, 0, ''),
 ('task panel', 'tasksShowTags', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowEmptyMilestones', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksPreviousPendingTasks', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksGroupBy', 'due_date', 'StringConfigHandler', 1, 0, ''),
 ('task panel', 'tasksOrderBy', 'priority', 'StringConfigHandler', 1, 0, ''),
 ('task panel', 'task panel status', '1', 'IntegerConfigHandler', 1, 0, ''),
 ('task panel', 'task panel filter', 'assigned_to', 'StringConfigHandler', 1, 0, ''),
 ('task panel', 'task panel filter value', '0', 'UserCompanyConfigHandler', 1, 0, ''),
 ('task panel', 'noOfTasks', '15', 'IntegerConfigHandler', '0', '100', NULL),
 ('task panel', 'task_display_limit', '500', 'IntegerConfigHandler', '0', '200', NULL),
 ('task panel', 'pushUseWorkingDays', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('task panel', 'zoom in gantt', '3', 'IntegerConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowDimensionCols', '', 'StringConfigHandler', 1, 0, ''),
 ('general', 'listingContactsBy', '0', 'BoolConfigHandler', '0', '0', NULL),
 ('general', 'localization', '', 'LocalizationConfigHandler', 0, 100, ''),
 ('general', 'search_engine', 'match', 'SearchEngineConfigHandler', 0, 700, ''),
 ('general', 'lastAccessedWorkspace', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('general', 'work_day_start_time', '9:00', 'TimeConfigHandler', 0, 400, 'Work day start time'),
 ('general', 'work_day_end_time', '18:00', 'TimeConfigHandler', 0, 410, 'Work day end time'),
 ('general', 'time_format_use_24', '0', 'BoolConfigHandler', 0, 500, 'Use 24 hours time format'),
 ('general', 'date_format', 'd/m/Y', 'DateFormatConfigHandler', 0, 600, 'Date objects will be displayed using this format.'),
 ('general', 'descriptive_date_format', 'F j, Y (l)', 'StringConfigHandler', 0, 700, 'Descriptive dates will be displayed using this format.'),
 ('general', 'custom_report_tab', '5', 'StringConfigHandler', '1', '0', NULL),
 ('general', 'last_mail_format', 'html', 'StringConfigHandler', '1', '0', NULL),
 ('general', 'amount_objects_to_show', '5', 'IntegerConfigHandler', '0', '0', NULL),
 ('general', 'reset_password', '', 'StringConfigHandler', '1', '0', 'Used to store per-user tokens to validate password reset requests'),
 ('general', 'autodetect_time_zone', '1', 'BoolConfigHandler', '0', '0', NULL),
 ('general', 'detect_mime_type_from_extension', '0', 'BoolConfigHandler', '0', '0', NULL),
 ('general', 'root_dimensions', '', 'RootDimensionsConfigHandler', '0', '0', NULL),
 ('general', 'show_object_direct_url',0,'BoolConfigHandler',0,0,NULL ),
 ('general', 'drag_drop_prompt','prompt','DragDropPromptConfigHandler',0,0,NULL ),
 ('general', 'notify_myself_too', '0', 'BoolConfigHandler', '0', '100', ''),
 ('calendar panel', 'calendar view type', 'viewweek', 'StringConfigHandler', 1, 0, ''),
 ('calendar panel', 'calendar user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('calendar panel', 'calendar status filter', '', 'StringConfigHandler', 1, 0, ''),
 ('calendar panel', 'start_monday', '', 'BoolConfigHandler', 0, 0, ''),
 ('calendar panel', 'show_week_numbers', '', 'BoolConfigHandler', 0, 0, ''),
 ('calendar panel', 'show_birthdays_in_calendar', '1', 'BoolConfigHandler', 0, 0, ''),
 ('calendar panel', 'show_multiple_color_events', '1', 'BoolConfigHandler', 0, 0, ''),
 ('context help', 'show_tasks_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_account_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_active_tasks_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_general_timeslots_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_late_tasks_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_pending_tasks_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_documents_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_active_tasks_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_calendar_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_messages_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_dashboard_info_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_comments_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_emails_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_reporting_panel_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_file_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_administration_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_member_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_contact_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_company_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_subscribers_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_linked_object_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_reminders_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_linked_object_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_print_report_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_reminders_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_list_task_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_time_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_tag_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_repeat_options_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_reminders_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_inivitation_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('time panel', 'TM show time type', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('time panel', 'TM report show time type', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('time panel', 'TM user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('time panel', 'TM tasks user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('time panel', 'add_timeslot_view_dimensions_combos', '', 'ManageableDimensionsConfigHandler', '0', '0', 'dimensions ids for skip'),
 ('general', 'show_context_help', 'until_close', 'ShowContextHelpConfigHandler', '0', '0', NULL),
 ('dashboard', 'show charts widget', '1', 'BoolConfigHandler', 0, 600, ''),
 ('dashboard', 'show dashboard info widget', '1', 'BoolConfigHandler', 0, 900, ''),
 ('general', 'rememberGUIState', '1', 'RememberGUIConfigHandler', 0, 300, ''),
 ('calendar panel', 'calendar task filter', 'pending', 'StringConfigHandler', 1, 0, ''),
 ('task panel', 'close timeslot open', '1', 'BoolConfigHandler', 0, 0, ''),
 ('calendar panel', 'reminders_events', 'reminder_email,1,60', 'StringConfigHandler', '0', '0', NULL),
 ('dashboard', 'filters_dashboard', '0,0,10,0', 'StringConfigHandler', '0', '0', 'first position: entry to see the dimension, second position: view timeslot, third position: recent activities to show, fourth position: view views and downloads'),
 ('task panel', 'reminders_tasks', 'reminder_email,1,1440', 'StringConfigHandler', '0', '23', NULL),
 ('task panel', 'add_task_autoreminder', '0', 'BoolConfigHandler', '0', '21', NULL),
 ('task panel', 'add_self_task_autoreminder', '1', 'BoolConfigHandler', '0', '22', NULL),
 ('task panel', 'add_task_default_reminder', '1', 'BoolConfigHandler', '0', '20', NULL),
 ('task panel', 'quick_add_task_view_dimensions_combos', '', 'ManageableDimensionsConfigHandler', '0', '0', 'dimensions ids for skip'),
 ('calendar panel', 'add_event_autoreminder', '1', 'BoolConfigHandler', '0', '0', NULL),
 ('calendar panel', 'autoassign_events', '0', 'BoolConfigHandler', '0', '0', NULL),
 ('calendar panel', 'event_send_invitations', '1', 'BoolConfigHandler', '0', '0', NULL),
 ('calendar panel', 'event_subscribe_invited', '1', 'BoolConfigHandler', '0', '0', NULL),
 ('mails panel', 'mails_per_page', '50', 'IntegerConfigHandler', '0', '0', NULL),
 ('mails panel', 'attach_to_notification', '0', 'BoolConfigHandler', '0', '0', NULL),
 ('general', 'access_member_after_add', '1', 'BoolConfigHandler', '0', '1300', NULL),
 ('general', 'access_member_after_add_remember', '0', 'BoolConfigHandler', '0', '1301', NULL),
 ('general', 'sendEmailNotification', '1', 'BoolConfigHandler', '1', '0', 'Send email notification to new user'),
 ('general', 'viewContactsChecked', '1', 'BoolConfigHandler', '1', '0', 'in people panel is view contacts checked'),
 ('general', 'viewUsersChecked', '1', 'BoolConfigHandler', '1', '0', 'in people panel is view users checked'),
 ('general', 'viewCompaniesChecked', '1', 'BoolConfigHandler', '1', '0', 'in people panel is view companies checked'),
 ('general', 'updateOnLinkedObjects', '1', 'BoolConfigHandler', '0', '0', 'Update objects when linking others'),
 ('dashboard', 'overviewAsList', '0', 'BoolConfigHandler', '1', '0', 'View Overview as list'),
 ('general', 'contacts_per_page', '50', 'IntegerConfigHandler', '0', '1200', NULL),
 ('listing preferences', 'breadcrumb_member_count', '5', 'IntegerConfigHandler', '0', '5', NULL),
 ('general', 'timeReportDate', '4', 'IntegerConfigHandler', 1, 0, ''),
 ('general', 'timeReportDateStart', '0000-00-00 00:00:00', 'DateTimeConfigHandler', 1, 0, ''),
 ('general', 'timeReportDateEnd', '0000-00-00 00:00:00', 'DateTimeConfigHandler', 1, 0, ''),
 ('general', 'timeReportPerson', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('general', 'timeReportTimeslotType', '2', 'IntegerConfigHandler', 1, 0, ''),
 ('general', 'timeReportGroupBy', '0,0,0', 'StringConfigHandler', 1, 0, ''),
 ('general', 'timeReportAltGroupBy', '0,0,0', 'StringConfigHandler', 1, 0, ''),
 ('general', 'timeReportShowEstimatedTime', '1', 'BoolConfigHandler', 1, 0, ''),
 ('general', 'can_modify_navigation_panel', '1', 'BoolConfigHandler', 1, 0, ''),
 ('general', 'view_mail_attachs_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
 ('general', 'timeReportShowBilling', '0', 'BoolConfigHandler', 1, 0, ''),
 ('general', 'settings_closed', '0', 'BoolConfigHandler', 1, 0, '');

INSERT INTO `<?php echo $table_prefix ?>object_types` (`name`,`handler_class`,`table_name`,`type`,`icon`,`plugin_id`) VALUES
 ('workspace', 'Workspaces', 'workspaces', 'dimension_object', 'workspace', null),
 ('tag', '', '', 'dimension_group', 'tag', null),
 ('message', 'ProjectMessages', 'project_messages', 'content_object', 'message', null),
 ('weblink', 'ProjectWebpages', 'project_webpages', 'content_object', 'weblink', null),
 ('task', 'ProjectTasks', 'project_tasks', 'content_object', 'task', null),
 ('file', 'ProjectFiles', 'project_files', 'content_object', 'file', null),
 ('form', 'ProjectForms', 'project_forms', '', '', null),
 ('chart', 'ProjectCharts', 'project_charts', '', '', null),
 ('milestone', 'ProjectMilestones', 'project_milestones', 'content_object', 'milestone', null),
 ('event', 'ProjectEvents', 'project_events', 'content_object', 'event', null), 
 ('report', 'Reports', 'reports', 'located', 'reporting', null),
 ('template', 'COTemplates', 'templates', 'located', 'template', null),
 ('comment', 'Comments', 'comments', 'comment', 'comment', null), 
 ('billing', 'Billings', 'billings', '', '', null),
 ('contact', 'Contacts', 'contacts', 'content_object', 'contact', null),
 ('file revision', 'ProjectFileRevisions', 'file_revisions', 'content_object', 'file', null),
 ('timeslot', 'Timeslots', 'timeslots', 'located', 'time', null),
 ('template_task', 'TemplateTasks', 'template_tasks', 'content_object', 'task', null),
 ('template_milestone', 'TemplateMilestones', 'template_milestones', 'content_object', 'milestone', null);

INSERT INTO `<?php echo $table_prefix ?>address_types` (`name`,`is_system`) VALUES
 ('home', 1),
 ('work', 1),
 ('other', 1);

INSERT INTO `<?php echo $table_prefix ?>telephone_types` (`name`,`is_system`) VALUES
 ('home', 1),
 ('work', 1),
 ('other', 1),
 ('assistant', 0),
 ('callback', 0),
 ('mobile', 1),
 ('pager', 0),
 ('fax', 0);

INSERT INTO `<?php echo $table_prefix ?>email_types` (`name`,`is_system`) VALUES
 ('user',1),
 ('personal', 1),
 ('work', 1),
 ('other', 1);
 
INSERT INTO `<?php echo $table_prefix ?>webpage_types` (`name`,`is_system`) VALUES
 ('personal', 1),
 ('work', 1),
 ('other', 1);


INSERT INTO `<?php echo $table_prefix ?>tab_panels` (`id`,`title`,`icon_cls`,`refresh_on_context_change`,`default_controller`,`default_action`,`initial_controller`,`initial_action`,`enabled`,`type`,`ordering`,`plugin_id`,`object_type_id`) VALUES 
 ('calendar-panel','calendar','ico-calendar',1,'event','view_calendar','','',0,'system',7,0,(SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name='event')),
 ('contacts-panel','contacts','ico-contacts',1,'contact','init','','',0,'system',4,0,(SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name='contact')),
 ('documents-panel','documents','ico-documents',1,'files','init','','',1,'system',3,0,(SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name='file')),
 ('messages-panel','messages','ico-messages',1,'message','init','','',0,'system',10,0,(SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name='message')),
 ('overview-panel','overview','ico-overview',1,'dashboard','main_dashboard','dashboard','main_dashboard',1,'system',1,0,0),
 ('reporting-panel','reporting','ico-reporting',1,'reporting','index','','',1,'system',8,0,(SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name='report')),
 ('tasks-panel','tasks','ico-tasks',1,'task','new_list_tasks','','',1,'system',2,0,(SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name='task')),
 ('time-panel','time','ico-time-layout',1,'time','index','','',1,'system',5,0,0),
 ('webpages-panel','web pages','ico-webpages',1,'webpage','init','','',0,'system',9,0,(SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name='weblink')),
 ('more-panel','getting started','ico-more-tab',0,'more','index','','',1,'system',100,0,0);



INSERT INTO `<?php echo $table_prefix ?>permission_groups` (`name`, `contact_id`, `is_context`, `plugin_id`, `type`) VALUES
('Super Administrator',	0,	0,	NULL, 'roles'),
('Administrator',	0,	0,	NULL, 'roles'),
('Manager',	0,	0,	NULL, 'roles'),
('Executive',	0,	0,	NULL, 'roles'),
('Collaborator Customer',	0,	0,	NULL, 'roles'),
('Internal Collaborator',	0,	0,	NULL, 'roles'),
('External Collaborator',	0,	0,	NULL, 'roles'),
('ExecutiveGroup',	0,	0,	NULL, 'roles'),
('CollaboratorGroup',	0,	0,	NULL, 'roles'),
('GuestGroup',	0,	0,	NULL, 'roles'),
('Guest Customer',	0,	0,	NULL, 'roles'),
('Guest',	0,	0,	NULL, 'roles'),
('Non-Exec Director',	0,	0,	NULL, 'roles');

SET @exegroup := (SELECT pg.id FROM <?php echo $table_prefix ?>permission_groups pg WHERE pg.name = 'ExecutiveGroup');
SET @colgroup := (SELECT pg.id FROM <?php echo $table_prefix ?>permission_groups pg WHERE pg.name = 'CollaboratorGroup');
SET @guegroup := (SELECT pg.id FROM <?php echo $table_prefix ?>permission_groups pg WHERE pg.name = 'GuestGroup');
UPDATE `<?php echo $table_prefix ?>permission_groups` SET `parent_id` = (@exegroup) WHERE `name` IN ('Super Administrator','Administrator','Manager','Executive');
UPDATE `<?php echo $table_prefix ?>permission_groups` SET `parent_id` = (@colgroup) WHERE `name` IN ('Collaborator Customer','Internal Collaborator','External Collaborator');
UPDATE `<?php echo $table_prefix ?>permission_groups` SET `parent_id` = (@guegroup) WHERE `name` IN ('Guest Customer','Guest','Non-Exec Director');

INSERT INTO `<?php echo $table_prefix ?>tab_panel_permissions` (`permission_group_id`, `tab_panel_id`) VALUES 
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'mails-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'webpages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'contacts-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'reporting-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'more-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'mails-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'webpages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'contacts-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'reporting-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'more-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'mails-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'webpages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'contacts-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'reporting-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'more-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'mails-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'webpages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'contacts-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'reporting-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'more-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'webpages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'contacts-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'reporting-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'more-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'webpages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'more-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'webpages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'more-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest Customer'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest Customer'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest Customer'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest Customer'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest Customer'),	'webpages-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest'),	'webpages-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'webpages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'contacts-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'reporting-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'more-panel');


INSERT INTO `<?php echo $table_prefix ?>system_permissions` (`permission_group_id`, `can_manage_security`, `can_manage_configuration`, `can_manage_templates`, `can_manage_time`, `can_add_mail_accounts`, `can_manage_dimensions`, `can_manage_dimension_members`, `can_manage_tasks`, `can_task_assignee`, `can_manage_billing`, `can_view_billing`, `can_see_assigned_to_other_tasks`, `can_manage_contacts`, `can_update_other_users_invitations`, `can_link_objects`) VALUES
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	1,	1,	1,	1,	1,		1,	1,	1,	1,	1,	1,	1, 1, 1, 1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	1,	1,	1,	1,	1,		1,	1,	1,	1,	1,	1,	1, 0, 1, 1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	1,	0,	1,	1,	1,		0,	1,	1,	1,	1,	1,	1, 0, 1, 1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	0,	0,	0,	0,	1,		0,	1,	1,	1,	0,	1,	1, 0, 0, 1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	0,	0,	0,	0,	0,		0,	0,	0,	1,	0,	0,	1, 0, 0, 1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	0,	0,	0,	0,	0,		0,	0,	0,	1,	0,	0,	0, 0, 0, 1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	0,	0,	0,	0,	0,		0,	0,	0,	1,	0,	0,	0, 0, 0, 1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest Customer'),	0,	0,	0,	0,	0,		0,	0,	0,	0,	0,	0,	1, 0, 0, 0),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest'),	0,	0,	0,	0,	0,		0,	0,	0,	0,	0,	0,	0, 0, 0, 0),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	0,	0,	0,	0,	0,		0,	0,	0,	0,	0,	1,	1, 0, 0, 0);

INSERT INTO `<?php echo $table_prefix ?>max_system_permissions` (`permission_group_id`, `can_manage_security`, `can_manage_configuration`, `can_manage_templates`, `can_manage_time`, `can_add_mail_accounts`, `can_manage_dimensions`, `can_manage_dimension_members`, `can_manage_tasks`, `can_task_assignee`, `can_manage_billing`, `can_view_billing`, `can_see_assigned_to_other_tasks`, `can_manage_contacts`, `can_update_other_users_invitations`, `can_link_objects`) VALUES
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	1,	1,	1,	1,	1,		1,	1,	1,	1,	1,	1,	1, 1, 1, 1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	1,	1,	1,	1,	1,		1,	1,	1,	1,	1,	1,	1, 1, 1, 1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	1,	0,	1,	1,	1,		0,	1,	1,	1,	1,	1,	1, 1, 1, 1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	1,	0,	0,	0,	1,		0,	1,	1,	1,	0,	1,	1, 1, 1, 1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	0,	0,	0,	0,	0,		0,	0,	0,	1,	0,	0,	1, 0, 0, 1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	0,	0,	0,	0,	0,		0,	0,	0,	1,	0,	0,	1, 0, 0, 1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	0,	0,	0,	0,	0,		0,	0,	0,	1,	0,	0,	1, 0, 0, 1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest Customer'),	0,	0,	0,	0,	0,		0,	0,	0,	0,	0,	0,	1, 0, 0, 0),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest'),	0,	0,	0,	0,	0,		0,	0,	0,	0,	0,	0,	1, 0, 0, 0),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	0,	0,	0,	0,	0,		0,	0,	0,	0,	0,	1,	1, 0, 0, 0);

INSERT INTO `<?php echo $table_prefix ?>widgets` (`name`,`title`,`plugin_id`,`path`,`default_options`,`default_section`,`default_order`,`icon_cls`) VALUES 
 ('overdue_upcoming','overdue and upcoming',0,'','','left',3,'ico-task'),
 ('people','people',0,'','','right',-1,'ico-contact'),
 ('messages','notes',0,'','','right',1000,'ico-message'),
 ('documents','documents',0,'','','right',1100,'ico-file'),
 ('calendar','upcoming events milestones and tasks',0,'','','top',0,'ico-event'),
 ('completed_tasks_list','completed tasks list',0,'','','right',150,'ico-task'),
 ('activity_feed', 'activity_feed', 0, '', '', 'left', 10,'ico-properties'),
 ('active_context_info','active_context_info',0,'','','left',1,'ico-summary'),
 ('comments','comments',0,'','','left',15,'ico-comment');

INSERT INTO <?php echo $table_prefix ?>role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 1, 1
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','timeslot','report')
 AND p.`name` IN ('Super Administrator','Administrator','Manager');

INSERT INTO <?php echo $table_prefix ?>role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 0, 1
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','timeslot','report')
 AND p.`name` IN ('Executive');

INSERT INTO <?php echo $table_prefix ?>role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 0, 1
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('file','timeslot')
 AND p.`name` IN ('Collaborator Customer','Internal Collaborator');

INSERT INTO <?php echo $table_prefix ?>role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 0, 0
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('task','milestone','event','report','contact')
 AND p.`name` IN ('Collaborator Customer','Internal Collaborator');

INSERT INTO <?php echo $table_prefix ?>role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 0, 1
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('timeslot')
 AND p.`name` IN ('External Collaborator');

INSERT INTO <?php echo $table_prefix ?>role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 0, 0
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('task','file','milestone')
 AND p.`name` IN ('External Collaborator');

INSERT INTO <?php echo $table_prefix ?>max_role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 1, 1
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','timeslot','report')
 AND p.`name` IN ('Super Administrator','Administrator','Manager','Executive');

INSERT INTO <?php echo $table_prefix ?>max_role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 0, 1
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('message','weblink','file','timeslot','contact','report')
 AND p.`name` IN ('Collaborator Customer','Internal Collaborator','External Collaborator');

INSERT INTO <?php echo $table_prefix ?>max_role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 0, 0
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('task','milestone','event')
 AND p.`name` IN ('Collaborator Customer','Internal Collaborator','External Collaborator');

INSERT INTO <?php echo $table_prefix ?>max_role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 0, 0
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','timeslot','report')
 AND p.`name` IN ('Guest Customer','Guest','Non-Exec Director');

INSERT INTO `<?php echo $table_prefix ?>contact_widget_options` (widget_name,contact_id,member_type_id,`option`,`value`,config_handler_class,is_system) VALUES
('overdue_upcoming',0,0,'assigned_to_user',0,'UserCompanyConfigHandler',0),
('calendar',0,0,'filter_by_myself',1,'BooleanConfigHandler',0)
ON DUPLICATE KEY UPDATE widget_name=widget_name;

UPDATE `<?php echo $table_prefix ?>config_options` SET `value`=(
	SELECT GROUP_CONCAT(id) FROM <?php echo $table_prefix ?>permission_groups WHERE `name` IN ('Super Administrator', 'Administrator', 'Manager', 'Executive')
)
WHERE `name`='give_member_permissions_to_new_users';