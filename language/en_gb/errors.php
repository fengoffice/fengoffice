<?php

  /**
  * Error messages
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */

  // Return langs
  return array(
  
    // General
    'invalid email address' => 'Email address format is not valid',
	'error invalid recipients' => 'Invalid email addresses found in field "{0}": {1}',
	'no context permissions to add' => 'You don\'t have permissions to add {0} in {1}',
	'you must select where to keep' => 'You must select where to keep {0}.',

    // Company validation errors
    'company name required' => 'Company / organization name is required',
    'company homepage invalid' => 'Homepage value is not a valid URL',
    
    // User validation errors
    'username value required' => 'Username value is required',
    'username must be unique' => 'Selected username is already taken',
    'email value is required' => 'Email address is required',
    'email address must be unique' => 'Sorry, selected email address is already taken',
    'company value required' => 'User must be part of company / organization',
    'password value required' => 'Password value is required',
    'passwords dont match' => 'Passwords don\'t match',
    'old password required' => 'Old password value is required',
    'invalid old password' => 'Old password is not valid',
    'users must belong to a company' => 'Person must belong to a company in order to generate a user',
    'contact linked to user' => 'Person is linked to user {0}',
  
  	// Password validation errors
  	'password invalid min length' => 'Password length must be at least {0} characters',
  	'password invalid numbers' => 'Password must have at least {0} numerical characters',
  	'password invalid uppercase' => 'Password must have at least {0} uppercase characters',
  	'password invalid metacharacters' => 'Password must have at least {0} metacharacters',
  	'password exists history' => 'Password was used on one of last ten passwords',
  	'password invalid difference' => 'Password must differ in at least 3 characters with last 10 passwords',
  	'password expired' => 'Your password has expired',
  	'password invalid' => 'Your password is no longer valid',
    
    // Avatar
    'invalid upload type' => 'Invalid file type. Allowed types are {0}',
    'invalid upload dimensions' => 'Invalid image dimensions. Max size is {0}x{1} pixels',
    'invalid upload size' => 'Invalid image size. Max size is {0}',
    'invalid upload failed to move' => 'Failed to move uplaoded file',
    
    // Registration form
    'terms of services not accepted' => 'In order to create an account you need to read and accept our terms of services',
    
    // Init company website
    'failed to load company website' => 'Failed to load website. Owner company not found',
    'failed to load project' => 'Failed to load active workspace',
    
    // Login form
    'username value missing' => 'Please insert your username',
    'password value missing' => 'Please insert your password',
    'invalid login data' => 'Failed to log you in. Please check your login data and try again',
    
    // Add project form
    'project name required' => 'Workspace name value is required',
    'project name unique' => 'Workspace name must be unique',
    
    // Add message form
    'message title required' => 'Title value is required',
    'message title unique' => 'Title value must be unique in this workspace',
    'message text required' => 'Text value is required',
    
    // Add comment form
    'comment text required' => 'Text of the comment is required',
    
    // Add milestone form
    'milestone name required' => 'Milestone name value is required',
    'milestone due date required' => 'Milestone due date value is required',
    
    // Add task list
    'task list name required' => 'Task name value is required',
    'task list name unique' => 'Task name must be unique in workspace',
    'task title required' => 'Task title is required',
  
    // Add task
    'task text required' => 'Task text is required',
	'repeat x times must be a valid number between 1 and 1000' => 'Repeat X times must be a valid number between 1 and 1000.',
	'repeat period must be a valid number between 1 and 1000' => 'Repeat period must be a valid number between 1 and 1000.',
  	'to repeat by start date you must specify task start date' => 'To repeat by start date you must specify task start date',
	'to repeat by due date you must specify task due date' => 'To repeat by due date you must specify task due date',
	'task cannot be instantiated more times' => 'Task cannot be instantiated more times, this is the last repetition.',
	
    // Add event
    'event subject required' => 'Event subject is required',
    'event description maxlength' => 'Description must be under 3000 characters',
    'event subject maxlength' => 'Subject must be under 100 characters',
    
    // Add project form
    'form name required' => 'Form name is required',
    'form name unique' => 'Form name must be unique',
    'form success message required' => 'Success note is required',
    'form action required' => 'Form action is required',
    'project form select message' => 'Please select note',
    'project form select task lists' => 'Please select task',
    
    // Submit project form
    'form content required' => 'Please insert content into text field',
    
    // Validate project folder
    'folder name required' => 'Folder name is required',
    'folder name unique' => 'Folder name need to be unique in this workspace',
    
    // Validate add / edit file form
    'folder id required' => 'Please select folder',
    'filename required' => 'Filename is required',
  	'weblink required' => 'Link url is required',
    
    // File revisions (internal)
    'file revision file_id required' => 'Revision needs to be connected with a file',
    'file revision filename required' => 'Filename required',
    'file revision type_string required' => 'Unknown file type',
    'file revision comment required' => 'Revision comment required',
    'there are no changes' => 'No changes were made. The document has not been overwritten.',
    
    // Test mail settings
    'test mail recipient required' => 'Recipient address is required',
    'test mail recipient invalid format' => 'Invalid recipient address format',
    'test mail message required' => 'Mail message is required',
    
    // Mass mailer
    'massmailer subject required' => 'Message subject is required',
    'massmailer message required' => 'Message body is required',
    'massmailer select recepients' => 'Please select users that will receive this email',
    
  	//Email module
  	'mail account name required' => 'Account name required',
  	'mail account id required' => 'Account Id required',
  	'mail account server required' => 'Server required',
  	'mail account password required' => 'Password required',
	'send mail error' => 'Error while sending mail. Possibly wrong SMTP settings.',
    'email address already exists' => 'That email address is already in use.',
  
  	'session expired error' => 'Session expired due to user inactivity. Please login again',
  	'unimplemented type' => 'Unimplemented type',
  	'unimplemented action' => 'Unimplemented action',
  
  	'workspace own parent error' => 'A workspace can\'t be its own parent',
  	'task own parent error' => 'A task can\'t be its own parent',
  	'task child of child error' => 'A task can\'t be child of one of its descendants',
  
  	'chart title required' => 'Chart title is required.',
  	'chart title unique' => 'Chart title must be unique.',
    'must choose at least one workspace error' => 'You must choose at least one workspace where to put the object.',
    
    
    'user has contact' => 'There is a person already assigned to this user',
    
    'maximum number of users reached error' => 'The maximum number of users has been reached',
	'maximum number of users exceeded error' => 'The maximum number of users has been exceeded. The application will not work anymore until this issue is resolved.',
	'maximum disk space reached' => 'Your disk quota is full. Please delete some object before trying to add new ones, or contact support to enable more users.',
    'name must be unique' => 'Sorry, but selected name is already taken',
  	'not implemented' => 'Not implemented',
  	'return code' => 'Return code: {0}',
  	'task filter criteria not recognised' => 'Task filter criteria \'{0}\' not recognised',
  	'mail account dnx' => 'Mail account doesn\'t exist',
    'error document checked out by another user' => 'The document was checked out by another user.',
  	//Custom properties
  	'custom property value required' => '{0} is required',
  	'value must be numeric' => 'Value(s) must be numeric for {0}',
  	'values cannot be empty' => 'Value(s) cannot be empty for {0}',
  
  	//Reports
  	'report name required' => 'Report name is required',
  	'report object type required' => 'Report object type is required',

  	'error assign task user dnx' => 'Trying to assign to an inexistent user',
	'error assign task permissions user' => 'You don\'t have permissions to assign a task to that user',
	'error assign task company dnx' => 'Trying to assign to an inexistent company',
	'error assign task permissions company' => 'You don\'t have permissions to assign a task to that company',
  	'account already being checked' => 'Account is already being checked.',
  	'no files to compress' => 'No files to compress',
	'error cannot assign task to user' => '{0} cannot be task asignee for {1}.',
  
  	//Subscribers
  	
  	'cant modify subscribers' => 'Cannot modify subscribers',
  	'this object must belong to a ws to modify its subscribers' => 'This object must belong to a workspace to modify its subscribers.',

  	'mailAccount dnx' => 'Email account does not exist',
  	'error add contact from user' => 'Could not add person from user.',
  	'zip not supported' => 'ZIP is not supported by the server',
  	'no tag specified' => 'No tag specified',
  
    'no mailAccount error' => 'Action unavailable. You do not have an email account added.',
	'content too long not loaded' => 'Previous email content is too long and was not loaded, but will be sent with this email.',
  	'member name already exists in dimension' => 'Member \'{0}\' already exists in selected dimension.',  
	'must choose at least one member of' => 'You must choose at least one member of {0}.',
	'timeslot dnx' => 'Timeslot does not exist',
	'you dont have permissions to classify object in member' => 'You don\'t have permissions to classify \'{0}\' in \'{1}\'',
  
  	'uploaded file bigger than max upload size' => 'You are trying to upload a document that is over your current upload size limit of {0}.',
  	'date format error' => 'You have an error when typing due date or start date, the correct date format is "{0}", please use this format or change it in your account preferences.',
  	
  	'upload error msg UPLOAD_ERR_INI_SIZE' => 'The uploaded file size exceeds the maximum upload size ({0}).',
  	'upload error msg UPLOAD_ERR_FORM_SIZE' => 'The uploaded file size exceeds the maximum upload size ({0}).',
  	'upload error msg UPLOAD_ERR_PARTIAL' => 'The uploaded file was partially uploaded.',
  	'upload error msg UPLOAD_ERR_NO_FILE' => 'No file could be uploaded.',
  	'upload error msg UPLOAD_ERR_NO_TMP_DIR' => 'No file could be uploaded, missing temporary folder.',
  	'upload error msg UPLOAD_ERR_CANT_WRITE' => 'Could not write file to disk.',
  	'upload error msg UPLOAD_ERR_EXTENSION' => 'A PHP extension stopped the file upload.',
  	
  	'failed to authenticate email account' => 'Failed to authenticate email account',
  	'failed to authenticate email account desc' => 'Could not authenticate account "{0}" to send notifications, please make sure that the account\'s username and password are correct.',
  ); // array

?>