
	About Feng Office 3.7.2.16
	================================
	
	Feng Office is a Collaboration Platform and Project Management System.
	It is licensed under the Affero GPL 3 license.
	
	For further information, please visit:
		* http://www.fengoffice.com/
		* http://fengoffice.com/web/forums/
		* http://fengoffice.com/web/wiki/
		* http://sourceforge.net/projects/opengoo
	
	Contact the Feng Office team at:
		* contact@fengoffice.com
	
	
	System requirements
	===================
	
	Feng Office requires a running Web Server, PHP (5.0 or greater) and MySQL (InnoDB
	support recommended). The recommended Web Server is Apache.
	
	Feng Office is not PHP4 compatible and it will not run on PHP versions prior
	to PHP 5.
	
	Recommendations:
	
	PHP 7.1+
	MySQL 5+ with InnoDB support
	Apache 2.0+
	
		* PHP    : http://www.php.net/
		* MySQL  : http://www.mysql.com/
		* Apache : http://www.apache.org/
	
	Please have a look at our requirements here:
	http://www.fengoffice.com/web/wiki/doku.php/installation:server_reqs
	
	Alternatively, if you just want to test Feng Office and you don't care about security
	issues with your files, you can download XAMPP, which includes all that is needed
	by Feng Office (Apache, PHP 7, MySQL) in a single download.
	You can configure MySQL to support InnoDB by commenting or removing
	the line 'skip-innodb' in the file '<INSTALL_DIR>/etc/my.cnf'.
	
		* XAMPP  : http://www.apachefriends.org/en/xampp


	Installation
	============
	
	1. Download Feng Office - http://www.fengoffice.com/web/community/downloads.php
	2. Unpack and upload to your web server
	3. Direct your browser to the public/install directory and follow the installation procedure
	
	Further information can be found here: http://www.fengoffice.com/web/wiki/doku.php/installation:installation
 
	You should be finished in a matter of minutes.
	
	4. Some functionality may require further configuration, like setting up a cron job.
	Check the wiki for more information: http://fengoffice.com/web/wiki/doku.php/setup
	
	WARNING: Default memory limit por PHP is 8MB. As a new Feng Office install consumes about 10 MB,
	administrators could get a message similar to "Allowed memory size of 8388608 bytes exhausted".
	This can be solved by setting "memory_limit=32" in php.ini.
	
	Upgrade
	=======
	
	There currently are two kind of upgrades:
	1- From 3.X to 3.X (or from 2.X to 2.X, or 1.X to 1.X)
	2- From 1.X to 2.X
	
	Either way, we strongly suggest reading the following article in our Wiki for further information:
	http://www.fengoffice.com/web/wiki/doku.php/installation:migration
	
	Note: Plugins must also be updated (if it corresponds)
	
	Open Source Libraries
	=====================
	
	The following open source libraries and applications have been adapted to work with Feng Office:
	- ActiveCollab 0.7.1 - http://www.activecollab.com
	- ExtJs - http://www.extjs.com
	- jQuery - http://www.jquery.com
	- jQuery tools - http://flowplayer.org/tools/
	- jQuery Collapsible - http://phpepe.com/2011/07/jquery-collapsible-plugin.html
	- jQuery Scroll To - http://flesler.blogspot.com/2007/10/jqueryscrollto.html
	- jQuery ModCoder - http://modcoder.com/
	- jQuery User Interface - http://jqueryui.com/
	- jQuery ImgAreaSelect plugin - http://odyniec.net/projects/imgareaselect/
	- jQuery SimpleModal plugin - http://www.ericmmartin.com/projects/simplemodal/
	- H5F (HTML 5 Forms) - http://thecssninja.com/javascript/H5F
	- http://flowplayer.org/tools/
	- Reece Calendar - http://sourceforge.net/projects/reececalendar
	- Swift Mailer - http://www.swiftmailer.org
	- Open Flash Chart - http://teethgrinder.co.uk/open-flash-chart
	- PHPExcel - https://github.com/PHPOffice/PHPExcel
	- Slimey - http://slimey.sourceforge.net
	- FCKEditor - http://www.fckeditor.net
	- JSSoundKit - http://jssoundkit.sourceforge.net
	- PEAR - http://pear.php.net
	- Gelsheet - http://www.gelsheet.org
	- TimeZoneDB - https://timezonedb.com
	
	
	Changelog
	=========
	
	Since 3.7.2.15
	-----------------
	feature: allow to sort member trees by member type before alphabetically
	bugfix: when uploading a new doc and already exists, by default select the option to upload as a new revision
	bugfix: fix permissions tree in users form to include timeslots in permissions query
	bugfix: fix error in contacts report grouped by folder and with or conditions in classification
	
	Since 3.7.2.14
	-----------------
	feature: allow to view the sent emails in newsletter view
	bugfix: verify that sharing table group_id key does not exists before executing the query to add it
	bugfix: fix newsletter sent contacts popup list
	bugfix: fix plugin folder name for additional member permissions plugin
	
	Since 3.7.2.13
	-----------------
	bugfix: fix error in custom reports with advanced billing columns
	bugfix: fix timeslots form start date calculations
	bugfix: prevent trailing/starting white spaces in template object property variable values
	bugfix: fix product types filtering
	bugfix: fix hour_types installer
	bugfix: autoselect project's client in quick-add row when filtering timeslots list by project
	bugfix: fix ignoring labor categories when creating subtasks using multi assignment
	bugfix: fix 7th day rule in overtime calculations after registering 10 overtime hours
	
	Since 3.7.2.12
	-----------------
	bugfix: ignore some properties when checking if only classification has been changed
	bugfix: ignore persons dimension in application log details
	
	Since 3.7.2.11
	-----------------
	feature: config option to ignore some dimensions when applying classification to subtasks
	feature: overtime calculations, classify as overtime the 7th consecutive day of work no matter the amount of hours
	bugfix: fix group name coulumn text when exporting to excel/csv a grouped report without details
	
	Since 3.7.2.10
	-----------------
	bugifx: fix week start/end calculation when adding a timeslot for next week or more in the future
	bugfix: when exporting to excel the time columns were not using the custom format
	bugfix: fix overtime calculations, ignore non-worked timeslots
	bugfix: when splitting timeslots the task id was not copied to the new ones
	
	Since 3.7.2.9
	-----------------
	feature: overtime calculations plugin
	bugfix: fix getting controller by classname in member controller
	bugfix: fix langs for billing category column in timeslot reports
	bugfix: fix timeslots totals query, make it independent of currency if there is only 1 currency in the system
	
	Since 3.7.2.8
	-----------------
	bugfix: fix notifications manager config options saving
	bugdix: dont send email when only description is changed if not selected in config option
	
	Since 3.7.2.7
	-----------------
	feature: when invoicing timeslots classify them in the invoiced status member
	bugfix: dont inherit members from parent task if dimension is not multiple and subtask has already a member of that dimension
	
	Since 3.7.2.6
	-----------------
	bugfix: fix task list filters when filtering by subscribed by user and completed tasks
	bugfix: fix notifications manager config options and user new template for due date reminders
	fearure: make overtime_hours dimension hierarchical
	
	Since 3.7.2.5
	-----------------
	feature: new section in dimension options to define the default value for each dimension
	feature: new user preference to define the dimension that dictates color to show for each object in the calendar
	feature: new plugin that adds the dimension "Overtime hours"
	feature: new plugin that adds the dimension "Billing rates"
	bugfix: several fixes in timesheet status dimension plugin
	bugfix: notification manager fixes in consolidation
	bugfix: dont include unsubscribed contacts when sending newsletters
	
	Since 3.7.2.4
	-----------------
	feature: config option to exclude add,open,close task from consolidation
	
	Since 3.7.2.3
	-----------------
	feature: First version of the "status dimension for timesheets" plugin aka: status_dimension_timesheet
	bugfix: modifications to evx widgets plugin
	bugfix: fix error when adding project phase
	bugfix: fixes in notifications manager
	bugfix: prevent enqueue newsletter emails for persons that are not subscribed to newsletters (using cp subs_newsletter)
	
	Since 3.7.2.2
	-----------------
	feature: default labor category for users in timestlots module
	bugfix: fix timeslots list totals when filtering by more than one member
	bugfix: autopopulate project's associated members when adding timeslots and selecting a project
	bugfix: fix column of unclassified worked time when grouping results by a dimension and showing the group in columns
	bugfix: fix sum of timeslot report custom properties when grouping by user
	bugfix: fix timeslots start time lang
	bugfix: fix email system setup config options saving

	Since 3.7.2.1
	-----------------
	bugfix: fix notification manager when consolidating comments and open/close
	
	Since 3.7.2.0
	-----------------
	feature: add contact cps to report group lines when grouping by contact
	feature: allow to add project cp columns in timeslot reports/Reports
	feature: when grouping timeslot reports by project, put the same name as in the trees (using configuration if present)
	
	Since 3.7.2-rc11
	-----------------
	feature: add invoicing status filter to time tab
	bugfix: fix report condition input for invoicing status
	
	Since 3.7.2-rc10
	-----------------
	bugfix: notifications manager fixes
	
	Since 3.7.2-rc9
	-----------------
	bugfix: notifications manager fixes
	
	Since 3.7.2-rc8
	-----------------
	feature: add contact custom properties to timeslot custom reports
	bugfix: fix expenses plugin pre requisites
	bugfix: Fixed typo
	
	Since 3.7.2-rc7
	-----------------
	bugfix: fix member add/edit when using templates and member subtypes
	bugfix: fix evx projects widget list height
	
	Since 3.7.2-rc6
	-----------------
	bugfix: fix copy task, was not copying members
	bugfix: show all users in calendar filter when not filtering by any member
	bugfix: fix auto heights in evx projects widget

	Since 3.7.2-rc5
	-----------------
	feature: evx_widgets plugin version alpha
	feature: associate member templates with member subtypes
	bugfix: use plural title for projects and clients widgets
	bugfix: fix old billing categories, they were not saving the currency
	
	Since 3.7.2-rc4
	-----------------
	bugfix: fixes in notifications manager

	Since 3.7.2-rc3
	-----------------
	feature: update notification manager
	
	Since 3.7.2-rc2
	-----------------
	bugfix: fix time module date filters
	
	Since 3.7.2-rc
	-----------------
	feature: allow getArrayInfo function to return members data for tasks
	bugfix: allow mobile to show project name with custom properties
	bugfix: list of clients should work for single or divided dimension in mobile.
	bugfix: show custom properties in timeslot lists
	bugfix: group_by_sql wasn't initialized.
	
	Since 3.7.2-beta6
	-----------------
	bugfix: fix listing query for trashed objects
	bugfix: fix permissions for internal collaborators in time module
	bugfix: fix abm of emails in client form
	bugfix: fix text custom property edition when adding zeros before the text and the text can be casted to number
	bugfix: fix height calculation in custom reports
	bugfix: fix additional text in member trees feature when expanding member childs
	
	Since 3.7.2-beta5
	-----------------
	bugfix: fix clients email edition, main email was not modified correctly
	
	Since 3.7.2-beta4
	-----------------
	feature: notifications manager improvements
	
	Since 3.7.2-beta3
	-----------------
	bugfix: dont show disabled dimensions in config option handlers
	feature: notifications manager improvements
	
	Since 3.7.2-alpha5
	-----------------
	bugfix: dont check module permissions automatically when giving permissions in a member
	bugfix: fix object billing calculations after instantiating task templates
	bugfix: fix dimension panel filtering when expanding a project and filtering by any of the associated dimensions
	bugfix: fix user selector for custom properties
	bugfix: Function had a duplicate variable '$ignored' in its parameters.
	bugfix: type was incorrectly set as dimension_object instead of dimension_group
	bugfix: Missing lang for hint
	bugfix: Improved wording for new feature.
	bugfix: Fixed. Misspelled class. Constructor wasn't working.
	bugfix: Fixed. Issues when upgrading a PHP 7 installation. 

	Since 3.7.2-alpha4
	-----------------
	change: Removed the property "status" for Payment Receipt. Replacing with a dimension.
	feature: Showing name of client for tasks in calendar view - in all views (weekly, monthly, daily).
	fix: Warnings for declarations of functions not compatible with parent Class.
	fix: Several small fixes on coding style

	Since 3.7.2-alpha3
	-----------------
	feature: update dimension association between projects and rate schedules to allow selection of related members
	feature: when selecting a member autoselect the related members if the association is marked with 'allows_default_selection'

	Since 3.7.2-alpha2
	-----------------
	bugfix: error 500 when deleting payment receipts
	bugfix: show billable column as yes/no instead of true/false
	
	Since 3.7.2-alpha
	-----------------
	feature: new billable column for expenses and receipts
	feature: system permission to enable adding hours in time tab
	
	Since 3.7.1.x
	-----------------
	feature: new plugin notifications_manager
	feature: modifications to object_templates plugin to work with notifications_manager
	
	Since 3.7.1-rc
	-----------------
	feature: allow to use "and" and "or" conditions in custom reports
	bugfix: fix remembering the option to delete objects when deleting member
	bugfix: when deleting member and not its objects they were deleted anyways
	bugfix: default value of option to delete objects when deleting member should be 'no'
	
	Since 3.7.1-beta
	-----------------
	feature: when deleting member ask user to delete objects or not
	feature: hide payment name in add/edit form only for macro facultad
	bugfix: Mobile was not working after 3.7.0 (PHP 7.0 support). 'Escape' functions 'escape_string' were not valid.
	bugfix: fix member selector hidden input format when adding and removing members from template tasks
	bugfix: Prevents an error when the DB connection is not established.
	bugfix: PHP 7.2 now reserves the word 'object', so it can be used as a class name.
	bugfix: fix getColumnType function for most of the content objects added by plugins
	bugfix: check if member can have parents before rendering the 'located under' component
	bugfix: fix typo in pear/net/socket library
	bugfix: Link to old download page was not working. Updated to new download page.
	
	Since 3.7.1-alpha4
	-----------------
	feature: add pdf support to payment receipt images in add/edit form and receipt view
	feature: show in red the payment receipts that are greater than their expense's cost
	feature: autocomplete payment receipt name using date and expense category
	feature: disable submit button while uploading payment receipt image
	bugfix: when editing expense using the 'add new item' button in the items grid, add a new empty item line
	bugfix: add link to expense in payment receipt view
	bugfix: fix feature that puts client name in calendar objects to also check member hierarchy

	Since 3.7.1-alpha3
	-----------------
	feature: Add "Description" field to payment receipts
	feature: Allow to upload image of the payment receipt
	feature: Allow to add payment receipt from expense view
	feature: Allow to add new item from expense view's items grid
	feature: Add view for payment receipts
	
	Since 3.7.1-alpha2
	-----------------
	feature: when adding/editing expenses improve the way that product types are filtered
	feature: when adding/editing expenses allow product type selection to fill member selectors
	bugfix: Fix view for expenses, including items grid
	bugfix: Improvements in format of payment receipts list view
	bugfix: Set default payment receipt date to today
	bugfix: can't edit payment receipt
	bugfix: payment receipt doesn't save custom properties, subscribers, linked objects and members
	bugfix: In payment receipt add/edit put the expense selector first
	bugfix: Several fixes in installer
	
	Since 3.7.1-alpha
	-----------------
	bugfix: fix member template quick add urls
	
	Since 3.7.0.x
	-----------------
	feature: new expenses module for evx.
	
	Since 3.7.0-beta7
	-----------------
	bugfix: tasks list doesnt stop timeslots in view, when completing
	bugfix: add not minified ext lang file for sv_se
	
	Since 3.7.0-beta6
	-----------------
	bugfix: fix add/edit client form position when hiding contact inputs
	bugfix: ensure that reporting contact config options are inserted in the upgrade script
	bugfix: fix member selectors in quota form
	bugfix: use a timeout when selecting columns to show in tasks list
	feature: initialize associated dimension selectors with active context
	
	Since 3.7.0-beta5
	-----------------
	bugfix: dont use expense column in payments if expenses are not used
	bugfix: fix content object listing query group by
	
	Since 3.7.0-beta4
	-----------------
	feature: allow to specify number of hours in invoice lines when generating invoices automatically from tasks/timeslots
	bugfix: in time tab open ts report in reports tab, to use the css of that tab
	
	Since 3.7.0-beta3
	-----------------
	bugfix: fix member form custom properties alignment
	bugfix: fix missing langs
	bugfix: disable workday cp by default
	
	Since 3.7.0-beta2
	-----------------
	bugfix: cant set or change project parent
	bugfix: cant complete tasks in php7
	
	Since 3.7.0-beta
	-----------------
	bugfix: can't save client custom properties if advanced_core plugin is not activated
	bugfix: can't instantiate templates from tasks list
	
	Since 3.7.0-alpha
	-----------------
	bugfix: override extjs datefield validation function to ensure that correct format is used when typing dates without zeros
	feature: New clients and contacts section in configuration
	feature: Allow to specify if all clients created will have contact information
	feature: Include "Located under" in dimension members properties, to change its order in add/edit form
	
    Since 3.6.3.x
    ----------------
    feature: PHP7 compatibility.
    
    Since 3.6.3.19
    ----------------
    bugfix: fix in breadcrumbs with associated dimensions
    bugfix: required dimension associations were not being checked
	
	Since 3.6.3.18
    ----------------
    bugfix: dont reload tasks list if not needed
    bugfix: improvements in payments list and view with quota
	
	Since 3.6.3.17
    ----------------
	bugfix: improvements in tasks repetition form
	
	Since 3.6.3.16
    ----------------
	bugfix: changes in tasks repetition variables
	
	Since 3.6.3.15
    ----------------
    bugfix: fix member reports conditions by associated dimension
	
	Since 3.6.3.14
    ----------------
    bugfix: fix custom reports totals for time columns when not using usual format
	
	Since 3.6.3.13
    ----------------
	bugfix: fix reports detail lines when grouping by intersection of dimensions and showing groups as columns
	bugfix: set newsletters contacts selector object type to contact
	bugfix: fix timeslot add and current_time variable
	
    Since 3.6.3.12
    ----------------
    bugfix: fix invoice logo width
	
    Since 3.6.3.11
    ----------------
    feature: new quota dropdown selector
    bugfix: disabling tasks notifications not always working
    bugfix: improve tasks list load more groups, do it when less than 20% of the scrollbar is left
    bugfix: fix persons filter in time module when no context is selected
	
    Since 3.6.3.10
    ----------------
    bugfix: several changes in advanced expenses with quota and payments
	
    Since 3.6.3.9
    ----------------
    bugfix: fix task dependency selector in templates
    bugfix: fix member templates when removing member association
	bugfix: dont remove enters in description when adding timeslot
	
    Since 3.6.3.8
    ----------------
    bugfix: performance issue in imap sync when sending to trash in bulk
	
    Since 3.6.3.7
    ----------------
    bugfix: fix grouped reports when grouping by date
	
    Since 3.6.3.6
    ----------------
    bugfix: fix calendar sync configuration
    bugfix: in grouped reports use date format to parse dates when grouping rows by a date property
    bugfix: trim web plugin notiification config connection values to ensure they are parsed correctly
    bugfix: fix time list filters
    bugfix: dont use the attachment name when wrinting to tmp folder only for view
	
    Since 3.6.3.5
    ----------------
    bugfix: improve timeslots add/edit
    bugfix: show description cols with pre-wrap in timeslots lists
	
    Since 3.6.3.4
    ----------------
	bugfix: fix scroll and resize of mail composing view
	bugfix: fix no filter in time module period filter
	
    Since 3.6.3.3
    ----------------
	bugfix: cant delete payments
	bugfix: fix tickets installer
	bugfix: add payment from inside quota does not select quota
	bugfix: new payment menu from insied quota position is wrong
	bugfix: error when executing dimension group reports
	bugfix: fix js error in risks module
	bugfix: fix risks installer

    Since 3.6.3.2
    ----------------
    bugfix: fix customer widget
    bugfix: minor adjustments in invoices module
    bugfix: persist time module filters
    bugfix: fix time module toolbar, panel height and totals row.
    bugfix: allow to delete multiple members in member list
	
    Since 3.6.3.1
    ----------------
    bugfix: escape line detail when editing invoice
    bugfix: use default value as it is in config option for task groups pagination count
    bugfix: several fixes in invoicing module
    bugfix: dont show unknown status in clients widget, dont show status if cp is disabled
    bugfix: filter disabled cps in get cp columns function
    bugfix: fix scroll pagination when scrollbar does not appear
	
    Since 3.6.3-rc7
    ----------------
    bugfix: cannot paste using right click menu in notes.
	
    Since 3.6.3-rc6
    ----------------
    bugfix: error in tasks list when no tasks present
	
    Since 3.6.3-rc5
    ----------------
    bugfix: fix tempalte task repetition when adding.
	
    Since 3.6.3-rc4
    ----------------
    bugfix: improve performance of pop3 mail download
    bugfix: generating next invoice doesn't copy company id
    bugfix: cannot edit repetitive tasks in templates
    bugfix: can't edit client organization
    feature: improve objects_import plugin
	
    Since 3.6.3-rc3
    ----------------
    bugfix: several fixes in tasks list performance
	
    Since 3.6.3-rc2
    ----------------
    bugfix: dont redraw all tasks if already rendered after loading more groups
	
    Since 3.6.3-rc
    ----------------
    bugfix: small fixes to tasks list group pagination.
    bugfix: in custom reports dont disable the toolbar in report parameters view.
	
    Since 3.6.3-beta11
    ----------------
    bugfix: cant classify mails with attachments if attachment has / in the name
	bugfix: error editing workspace if associated content data object does not exist
	bugfix: dont load all tasks when only refreshing tasks list group totals
	bugfix: not adding to sharing table unclassified objects to new users
	bugfix: reorganize custom reports header (conditions block and buttons container)
	bugfix: report and print from clients tab doesnt use the same order
	feature: when custom report data is too narrow, expand it to use all the panel width
	feature: tasks list groups pagination
	
    Since 3.6.3-beta10
    ----------------
	bugfix: Fix error in application logs query when filtering
	bugfix: Add margins to the direct-url container in object views
	bugfix: cant add name in timeslots reports
	bugfix: worked time column showing wrong data
	bugfix: dont break report title in the middle of a word
	bugfix: total estimated cost is not showing
	bugfix: Remove unnecesary paddings in custom report views
	bugfix: Fix custom report content height calculation
	feature: Remove toolbar in custom reports
	feature: Reorganize custom reports buttons
	
    Since 3.6.3-beta9
    ----------------
    bugfix: profile picture crop and upload fixes
    bugfix: cannot edit indicators
    bugfix: stop running timeslots was only affecting timeslots without task
    bugfix: cusotm reports associated dimension columns fixed to prevent duplicates
	
    Since 3.6.3-beta8
    ----------------
    bugfix: fix worked time color in tasks list
    bugfix: reminders dont tell wich date they have as reference
    bugfix: templates with lots of tasks and variables cannot be saved and gets broken if input count is greater than 1000
    bugfix: cannot modify timeslot billing if they are classified in a submember
	
    Since 3.6.3-beta7
    ----------------
    bugfix: dim columns were added twice and this causes an error when saving them in custom reports
    feature: return member id and billing data when requesting event ticket data
	
    Since 3.6.3-beta6
    ----------------
    bugfix: when generating event invoice name put a generic name if the invoice is for more than one event
	feature: disable events registration when using classes workflow
	feature: add new object template parameter: summary_of_all_events
	
    Since 3.6.3-beta5
    ----------------
    bugfix: fix searchable objects installer plugin 
    bugfix: fix config option values table in installer
    bugfix: dont use null as def value of primary keys
    bugfix: fix escape characters for users selector in custom report parameters
    feature: allow login function to receive parameters and be called by api
    feature: include billing emails in request event pricing function
	
    Since 3.6.3-beta4
    ----------------
    bugfix: ensure that invoice is classified in the event of the ticket
    bugfix: cant search members with ampersand
    bugfix: Show archived objects in the trashed objects list
    bugfix: dont show billing tab when editing ts if user doesnt have permissions
    bugfix: undefined function was being used to set billing category id
    bugfix: error redeclaring function when including twice cp table
    bugfix: duplicated tasks are loaded sometimes
    bugfix: check if member with the same member already exists before trying to create a new one
    bugfix: invoice classification fixed in class registration
    bugfix: several modifications to class registration and attendee list
    feature: include list values and list value labels in list cps definition
    feature: config option to ignore custom invoice layout when generating invoice
    
    Since 3.6.3-beta3
    ----------------
    feature: change return value of save_event_tickets_bulk
    feature: split contact address fields in reports
    feature: config option to send emails in background
    feature: email templates
    bugfix: error 500 on events views when event doesnt have end date
    bugfix: add a code for return the name of members when requesting price
    bugfix: fixes in affinity report batch
    bugfix: error in query to update invoice preview file id
    bugfix: in web plugin take the installation name from the filesystem path, not from the url
	
    Since 3.6.3-beta2
    ----------------
    feature: register attendees to multiple events
    feature: online member registration form conditional improvements
    bugfix: problems when sending invoice to billing contact
    bugfix: exclude customer_folder and project_folder from member templates
    bugfix: when sending outbox in cron send for all users with permissions in the account
    bugfix: show comments option was only working for tasks reports
    bugfix: exclude object types of disabled plugins in configuration

    Since 3.6.3-beta1
    ----------------
    bugfix: adding clients from api doesnt save custom properties
    bugfix: custom property filters are not translated in tickets module
    bugfix: fixes in affinity members report
    bugfix: cant save associated members by api
    bugfix: in tasks workflow, copy the linked objects to the next task
    bugfix: when showing comments in reports encode date comment part to to prevent errors in view
    
    Since 3.6.2.X
    ----------------
    feature: Allow to send objects by email (for api use)
    feature: Allow to create clients by api

	Since 3.6.2-beta41
    ----------------
    bugfix: allow MemberChooserTreeLoader to send parameters in post, to avoid making the url too long
    bugfix: fix custom properties add
    feature: evx_edition plugin and login page modifications
    feature: affinity reports plutin

    Since 3.6.2-beta40
    ----------------
    bugfix: fixed report totals for calculated columns
    bugfix: fix obj type hierarchy options query when saving expense
    bugfix: improved drag and drop in mails tab
    bugfix: fix table custom properties in forms and views
    bugfix: missing langs in advanced expenses
    bugfix: add zipcode to the toString function in contact addresses
    bugfix: cant add cps for member types that are not dimension objects
    feature: allow to define plural names in the dimension options section

    Since 3.6.2-beta39
    ----------------
    bugfix: Show Custom Properties with scroll mode
	bugfix: Show bill_billing_rate with currency format in reports
	bugfix: add option to mark invoice as paid in view
	bugfix: member type names fixed in task and report selectors
	bugfix: execute function to ensure that all dim associations have their custom properties after every plugin update
	bugfix: when creating user always set checked by default the option to send notfication
	bugfix: init gantt genid and preferences before response comes
	bugfix: fix verify_peer options when connecting to mail server using tls
	bugfix: company picture form fixed
	bugfix: ignore disabled dimensions in tasks list columns
	bugfix: send and save invoice fixed
	bugfix: when cp is special and is defined as id@text try to get the lang from the text part

    Since 3.6.2-beta38
    ----------------
    feature: separate labor categories dimension (hour types) to other plugin
    feature: allow to configure if the phone types are shown in reports
    bugfix: fix misspelled langs in custom property admin
    

    Since 3.6.2-beta37
    ----------------
    bugfix: Custom reports when ordering by due date and due date is the same the order is different when viewing report and when exporting to pdf
    bugfix: Custom reports export to pdf, second page header overlaps with the data rows
    bugfix: Fixed status column for projects in custom reports
    bugfix: wrong langs in projects and missing langs in task reports with show comments
    bugfix: dont draw ignored conditions info when executing custom reports
    bugfix: remove duplicated models that were in the views directory

    Since 3.6.2-beta36
    ----------------
    bugfix: reload current panel was not called after adding an invoice
    bugfix: contable report must only include users with padron
    bugfix: automatic timeslot generation must not classify in common hour type if already classified in holiday hour type.
    feature: modify new event registration to allow free registrations

    Since 3.6.2-beta35
    ----------------
    bugfix: filter member list by context
    feature: fixed report -> hours by user and intersection of dimensions from advance services plugin

    Since 3.6.2-beta34
    ----------------
    bugfix: member list order by cp
    bugfix: report ignore condition pagination
    bugfix: show more on member list group is not working

    Since 3.6.2-beta33
    ----------------
    bugfix: mail parser encoding error

    Since 3.6.2-beta32
    ----------------
    bugfix: fix searchable objects for invoices
    bugfix: put sent status as invoice sent for confirmed invoices

    Since 3.6.2-beta31
    ----------------
    feature: new project phases dimension
    bugfix: cron error with empty dates on cron events
    bugfix: task workflow not working

    Since 3.6.2-beta30
    ----------------
    bugfix: reports are displayed collapsed
    bugfix: include the mail that pays online when sending invoice


    Since 3.6.2-beta29
    ----------------
    bugfix: invoice sent status filter
    bugfix: report columns order
    bugfix: When sending invoices if to is sent using GET, check if it is an array before adding to the mail paramters

	Since 3.6.2-beta28
    ----------------
    bugfix: report columns order
    feature: change sent status to a list of values and update the invoice preview

    Since 3.6.2-beta27
    ----------------
    bugfix: search box on user list not working
    bugfix: pdf reports are collapsed
    bugfix: contact billing info on invoices

    Since 3.6.2-beta26
    ----------------
    bugfix: do not check permissions when classifying contacts from member custom property
    bugfix: check if is a valid datetime value before using the object

	Since 3.6.2-beta25
    ----------------
    bugfix: mobile not working, missing api view
    bugfix: Automatic timeslots must not check permissions when being classified
    bugfix: lang Cuentaes to Cuentas

    Since 3.6.2-beta24
    ----------------
    bugfix: add contact emails to searchable objects
    bugfix: fixes in context widget
    bugfix: do not check permissions when classifying contacts from member custom property
    bugfix: missing lang report category
    bugfix: error 500 in total worked time report
    bugfix: company billing info on invoices
    bugfix: break line on comment column

	Since 3.6.2-beta23
    ----------------
    bugfix: force add member associations to searchable objects

    Since 3.6.2-beta22
    ----------------
    feature: add member associations to searchable objects
    bugfix: billing info properties on event registration
    bugfix: prevent multiple timeslots running
    performance: performance improvements on permissions

	Since 3.6.2-beta21
    ----------------
    bugfix: invoices: migrate payment method columns to custom properties

	Since 3.6.2-beta20
    ----------------
    feature: Allow to select properties from selected contact on a custom property (for members)
    feature: new rate schedules dimension, associated to projects
    feature: allow to select list custom properties to display on contact selectors
    feature: show comments on report on a separated column
    bugfix: prevent blank screen on mail view
    bugfix: error 500 on member reports
    bugfix: get user by email function not working properly
    bugfix: return websites for each contact on the api
    bugfix: subtask display fails after expand parent task
    bugfix: report categories are filtered by permissions

    Since 3.6.2-beta19
    ----------------
	feature: SAML Single Sign On plugin.
	bugfix: invoice preview does not use custom invoice template.

    Since 3.6.2-beta18
    ----------------
    bugfix: breadcrumbs from related dimensions are not displayed on object lists
    bugfix: max amount of tickets per event calculation
    feature: custom reports, allow to define conditions using associated dimension on member reports

    Since 3.6.2-beta17
    ----------------
    bugfix: allow to change sent status of invoices on edit form
    bugfix: check multiple contacts with the same email configuration before creating new members automatically
    bugfix: wrong ticket type for non-members on event registration
    bugfix: totals on reports
    feature: properties groups for content objects view
    feature: improve contact billing information tab on invoices
    feature: display all members subtypes when creating new member from dimension tree
    feature: max amount of tickets per event

    Since 3.6.2-beta16
    ----------------
    feature: show contact columns on client reports
    feature: contact selector on "billing to" tab on invoices form
    bugfix: wrong total on new event registration
    bugfix: remove sent status from invoices and add other property for that

    Since 3.6.2-beta15
    ----------------
    bugfix: notification config override by hook
    bugfix: no space between imploded address fields when printing invoice
    bugfix: bug when saving subtype dependencies, duplicated row in table
    bugfix: fix report_category_id column, it was added as a tinyint
    bugfix: fix managed events to allow them to have empty date/time
    feature: allow more than one contact with the same email
    feature: was added the ticket type custom properties to event registration

    Since 3.6.2-beta14
    ----------------
    feature: multiple email templates for different actions
    feature: classify invoices on managed event related account
    bigfix: custom properties on object subtypes not working

	Since 3.6.2-beta13
    ----------------
    bugifx: error 500 when ticket type has been deleted
    bugifx: invoice number was not generated when creating new repetition or from new event registration
    bugfix: show date and time on field "created on" invoices

    Since 3.6.2-beta12
    ----------------
    bugfix: show date and time on field "created on" invoices
    bugfix: show date and time on field "created on" event tickets
    bugfix: secondary dimensions must filter primary dimensions
    bugfix: don't change invocie status to sent if is paid.
    security: security issues sql injections and xss

	Since 3.6.2-beta11
    ----------------
    bugfix: replay to all not working
    security: remove public/assets/javascript/ckeditor/ck_upload_handler.php
    security: security issues in api controller
    feature: allow to assign invoice number in non-pending status
    feature: new bootstrap style for members tree on "new event registration"

    Since 3.6.2-beta10
    ----------------
    bugfix: don't show mail rules recomendation configuration not working
    bugfix: managed events, filter only by member the git contact selector on "new event registration"

    Since 3.6.2-beta9
    ----------------
    feature: classify event invoices in a configurable member of the accounts dimension
    bugfix: new event registration contact selector scroll

    Since 3.6.2-beta8
    ----------------
    bugfix: error adding attendees
    bugfix: member status on members widget
    feature: generate report button on attendees tab

    Since 3.6.2-beta7
    ----------------
    bugfix: member tree selector performance

    Since 3.6.2-beta6
    ----------------
    bugfix: managed events, ticket type name and desc must be escaped before sending data to view
    bugfix: only group by o.id if query is for content_objects

    Since 3.6.2-beta5
    ----------------
    bugfix: SWIFT_DISABLE_VERIFYPEER_SOCKET_OPTION not working
    bugfix: managed events, attendees classification
    bugfix: fix custom properties view on members
    bugfix: don't use timezone in getObjectData if the object doesn't have a timezone
    bugfix: don't filter related dimensions on members tree
    bugfix: totals on reports and objects lists

	Since 3.6.2-beta4
    ----------------
    bugfix: class roster attendee report

    Since 3.6.2-beta3
    ----------------
    feature: class roster attendee report

	Since 3.6.2-beta2
    ----------------
    bugfix: error 500 on managed events tab
    feature: managed events, new contact selector on event registration process to select attendees
    feature: contact selector select wich properties display
    feature: advanced billing, new columns on task report "Estimated profit margin" and "Expected profit %"
    feature: member pricing on ticket types

    Since 3.6.2-beta
    ----------------
    bugfix: advance report installer error
    bugfix: fixes on Billing rate and Cost rate report
    bugfix: error 500 on reports when income plugin is not installed
    bugfix: reports column names break word
    bugfix: totals on reports
    bugfix: contact custom property combo langs
    bugfix: newsletter recipients selector
    feature: was added contact cps to attendees panel

    Since 3.6.1-rc
    ----------------
    feature: advance billing on tasks (estimated cost and estimated price)
    feature: billing information on reports
    feature: copy members function
    feature: extend contact custom property selector to allow selecting unclassified contacts

    Since 3.6.1-beta4
    ----------------
    bugfix: report column title words break
    bugfix: can't create timeslots for yesterday

    Since 3.6.1-beta3
    ----------------
    bugfix: send invoice doesn't refresh status in pdf

    Since 3.6.1-beta2
    ----------------
    feature: allow custom invoice mail template for different installations
    bugfix: invoices generated in background are not saving the subtype id
    bugfix: changing event subtype loses dimension associations

	Since 3.6.1-beta
    ----------------
    performance: on sharing table calculation after the user was being edited
    bugfix: move event invoice automatic generated name to event_tickets plugin
    bugfix: financial accunts has wrong table specified in model
    bugfix: new event registration member selector fix
    bugfix: managed event start-end fields fix on submembers

    Since 3.6.0-rc1
    ----------------
    feature: contact custom properties now you can add contacts from the combo
    feature: timeslots now you can select the related task
    feature: filter events by subtype on listing function
    feature: objectives description use ckeditor
    feature: member templates for all member types
    feature: send invoice by email
    feature: stop previous timeslot configuration

    Since 3.6.0-rc
    ----------------
    bugfix: error 500 on templates
    bugfix: advance reports, report categories not working
    bugfix: export reports to pdf
    bugfix: apply permissions to all sub-members on the member edit view.

    Since 3.6.0-beta.3
    ----------------
    bugfix: report list not working
    bugfix: automatic calculation of worked time on add time modal
    bugfix: time list add no filter by date option
    bugfix: task list tasks displayed twice
    bugfis: totals on grouped reports

    Since 3.6.0-beta.2
    ----------------
    bugfix: fix upgrade problem to version 3.5.3 with index in table custom_property_values. 
    
    Since 3.6.0-beta.1
    ----------------
    bugfix: invoices query failed with message 'Unknown column 'external_id' in 'field list'
    bugfix: report list not working if advance report is installed


    Since 3.6.0-beta
    ----------------
    feature: new time form


	Since 3.5.3
    ----------------
    feature: add members in object notifications subject
    feature: config generate next invoice repetition when printing.
    feature: invoice Payment Period
    feature: new linked object view
    feature: add search input to users list
    feature: fix report headers
    feature: time-entries improvements
    feature: add a new condition unclassified to reports configuration
    feature: new feature on list of report, now you can create categories for reports
    feature: new function to create contact from name and mail
    feature: added bootstrap for internal use
    feature: managed eventes plugin
    bugfix: main tabs bar resize
    bugfix: resize was added to columns assigned_to and assigned_by in the task panel
    bugfix: several missing langs
    bugfix: subtask render
    bugfix: query error when ordering tasks list by assigned to
    bugfix: error 500 fixed when listing customers in api
    bugfix: missing useHelper in contact controller function
    bugfix: modify get_public_file function to receive file id as an optional parameter
    bugfix: modify image cp value when requested using raw_data=true to return the original json value of the db
    bugfix: fix tasks list group totals when filtering by more than one dimension
    bugfix: cutom reports dont put empty th if no details are shown, icon column is not rendered
    bugfix: custom reports without details misalignments
    bugfix: fix config option handler for members to allow multiple selection
    bugfix: exclude archived members when giving automatic permissions to new users
    bugfix: in context widget dont use features of inactive plugins
    bugfix: fix broken billing tab in timeslot edit
    bugfix: cant generate first task repetition batch
    bugfix: problem of padding in button in dashboard
    bugfix: task list fix for edge cases (yesterday or tomorrow also changing the week, etc), new weeks (Mon-Sun instead of Sun-Sat)
    bugfix: add generic actions Time tab
    bugfix: fix widget expenses
    bugfix: new sharing table logic
    bugfix: time reports missing task column status
    bugfix: send mail directly when clicking send mail
    bugfix: task list filter status today.
    bugfix: mail account signature change fail
    bugfix: When edit a member if the name contains & it shows &amp
    bugfix: list cp rendered with errors.
    bugfix: template task date variable + minutes not working properly
    bugfix: invoice report expiration date timezone error
    bugfix: calendar view don't displays tasks for guest users
    performance: listing order
    performance: sharing table

    plugin advanced_core:
    feature: ticket new filter by custom property
    bugfix: Fix date format on report headers
    bugfix: object list function broken by hook custom_properties_filter_get_objects_list_extra_conditions
    bugfix: Call to a member function getFixedColumnValue on null

    plugin advanced_reports:
    feature: custom report group by intersection: allow to define specific members to intersect

    plugin advanced_services:
    bugfix: Hour type changes
    bugfix: when generating first set of repetions, dont continue after repeat_end date if it is specified
    bugfix: add transactions for generate x days tasks cron function

    plugin crpm:
    bugfix: dont create invoice customer if no customer data received

    plugin event_tickets:
    feature: new event management functions

    plugin income:
    feature: invoice send via email
    feature: new config option to make client mandatory or not in invoices
    feature: address in print view, now show the billing address
    bugfix: in print view of invoice calling to worng variable for client zip code
    bugfix: fixed message when delete a income
    bugfix: image on invoice whe generating pdf
    bugfix: error 500 when trying to delete invoice from view

    plugin newsletters:
    feature: use "To" field for each email when configuration has Persons per email: 1


	Since 3.5.2.X
    ----------------
    feature: member custom properties groups on member information widget
    feature: use property groups for member custom properties in member add/edit forms
    feature: property groups -> add default group and put all cps in that group
    feature: show previous and next invoice if they exist when viewing an invoice
    feature: text filter input in invoices module
    feature: new managed events plugin
    feature: new event tickets plugin: event tickets abm and attendees widget
    feature: modal member add/edit
    feature: config options to disable the popup dialogs when archiving or trahsing objects
    feature: allow to set a member template to an existing member
    bugfix: transform users widget in contacts widget
    bugfix: default payments report includes trashed payments
    bugfix: invoice line with 0 unit value and total>0 does not sum in general totals
    bugfix: cant link object to invoices that are not pending
    bugfix: dont show generate next invoice link if it is already generated
    bugfix: only the last invoice of the repetition should be shown as repetitive in the invoices list
    bugfix: conditional tasks new bool cp values support
    bugfix: cant generate next invoice if user does not have the can_edit_confirmed_invoices permissions and the invoice is printed
    bugfix: MAIL SIGNATURE font size 24px by default
    bugfix: invoices when adding a new line use the same currency selected for the invoice
    bugfix: dont show member history tab if member has no history


	Since 3.5.1.X
	----------------
	feature: show previous and next invoice if they exist when viewing an invoice
	feature: text filter input in invoices module
	bugfix: dont show generate next invoice link if it is already generated
	bugfix: only the last invoice of the repetition should be shown as repetitive in the invoices list

	
	Since 3.5.1.5
	----------------
	feature: when grouping reports allow to hide the total element count of each group.
	feature: allow expenses list to have horizontal scroll.
	feature: add logic to filter expenses by quarters in expenses widget.
	feature: expenses widget new configuration add filters.
	feature: allow boolean custom properties with undefined values.
	bugfix: Filter is remove on change page. Feature - Send file by email without mail plugin.
	bugfix: escape imap folder names when making queries to the db
	

	Since 3.5.1.4
	----------------
	feature: always show payment history in payment view
	feature: expenses temporal status
	feature: new config option type: date range
	feature: new config option type: general list
	feature: new column balance in advanced expenses lists and reports
	feature: Menu show for tasks is working as email accounts filter, with timeout for each click 
	feature: Added custom configuration for user for set the font-size of emails
	bugfix: imap sync, when removing from folder if we dont have the mail uid => query it to the server and perform the removal
	bugfix: in objects view, the breadcrumbs shows always one workspace in all associated dimensions
	bugfix: allowed users to assign in task controller does not return the same when called from the reasign users popup
	bugfix: js error in timeslot add when drawing user combo and logged user doesn't have "can_manage_time"
	bugfix: Change value "Serie" to plural for keys "series" and "field Object series"
	
	Since 3.5.1.3
	----------------
	feature: improve combobox on report conditions
	feature: system config option disable notifications for object type
	feature: paste email list from excel on mails
	bugfix: prevent widgets links to be opened on a new tab
	bugfix: add delete log when deleting mails
	bugfix: enabled spellcheck in description of add task

	Since 3.5.1.2
	----------------
	bugfix: Expense totals fixed when ordering by a dimension column.
	feature: imap sync plugins.
	
	
	Since 3.5.1.1
	----------------
	feature: invoicing - config option to make final consumer mandatory
	fetaure: invoicing - when changing final consumer value change company id/client document label
	feature: invoicing - when changing invoice currency to a non default one, show exchange rate input
	bugfix: invoicing,facturalista - exchange rate is not sent to DGI when currency is not UYU
	bugfix: display list custom properties in objects view fixed
	bugfix: missing langs in project widget when projects dimension is disabled
	bugfix: dont include timeslots of deleted tasks in reports
	bugfix: adv.services: sometimes night hours are generated in the night start instead of the timeslot start
	performance: tasks list and task view when system has lots of users
	
	Since 3.5.1
	----------------
	bugfix: cant add tasks with advanced_services and  not using time in date pickers.
	bugfix: dont filter files by their associated mail if they are classified.
	bugfix: dont set the font size in ckeditor's contents.css body rule.
	feature: imap synchronization plugin (alpha).
	feature: allow to reclassify mail from the list.
	
	Since 3.5.1-rc
	----------------
	feature: objects import tool
	feature: allow to type in user combo box in timeslots add/edit
	feature: plugin advanced_mail_imap_folders - new dimension for imap folders
	bugfix: permissions, users can't see documents if they can't see the mail related.
	bugfix: check that class exists before executing "eval()"
	bugfix: assigned to notifications are sent to resource users
	
	
	Since 3.5.1-beta4
	----------------
	feature: search input in tickets module.
	feature: config option to exclude associated dimensions from general breadcrumbs.
	bugfix: dont show associated dimension's member history widget.
	bugfix: unescaped names in time module.
	bugfix: task asignee notification on templates.
	bugfix: in object view the custom properties repeats last value if is empty.
	
	Since 3.5.1-beta3
	----------------
	feature: invoicing module: allow to set client address and city fields as mandatory when adding an invoice.
	bugfix: some dimensions members are not selected when clicked in breadcrumbs.
	bugfix: if dimension is hidden the members are not selected when clicked in breadcrumbs.
	bugfix: the contact associated to the client is not classified in the client's related members.
	bugfix: breadcrumbs are not showing the members of the dimensions that have its selector disabled and they are autoclassified.
	bugfix: template tasks subscribers and assign to permissions.
	bugfix: Double quotes are not correctly escaped in custom properties description.
	bugfix: member templates: ensure that new member is in context before rendering the task template paramters form.
	bugfix: timeslot quick add does not show all users with read permissions on timeslots in the user selector.
	bugfix: when assigning client permissions deleted user permission groups appears in selector.
	bugfix: when copying expenses the payments are not copied.
	feature: advanced_services: allow to define works shift days as dat off.
	feature: advanced_services: config option to define the hour type member for days off.
	feature: action to generate first repetitive instances for tasks.
	feature: users of type "resource" (only to be assignees of tasks and timeslots)
	

	Since 3.5.1-beta2
	----------------
	bugfix: attachments inside other attachments cannot be downloaded sometimes.
	bugfix: user token is not removed when disabling user.
	bugfix: in listings when adding a custom property as a column and is not visible by default the system does not remember the users' choice.
	feature: several improvements in grouped custom reports.
	advanced_reports: conditions for groups (only for contact_id and assigned_to_contact_id)
	advanced reports: when grouping by person, allow to add emtpy groups and where to put them (beggining or end)
	advanced_reports: new condition to filter by classified in or not classified in the selected members
	bugfix: tasks list group by person filters different than assigned to, are not shown when loading the list.
	feature: allow to disable custom report parameter when running the report
	bugfix: advanced_reports: timeslot totals are not correct
	bugfix: error when filtering tasks list by milestone.
	bugfuix: report group by date columns was not applying timezone.
	bugfix: when printing/exporting grouped custom reports only the current page is shown.
	performace: limit mails search critera with a minimum of 3 characters.
	feature: add calculated columns to custom reports (for now only 'status' in tasks reports)
	
	Since 3.5.1-beta
	----------------
	feature: start timeslot from without task.
	feature: allow to specify end date and time when adding a timeslot.
	feature: task list group by member type.
	feature: allow to disable custom report parameter when running the report.
	advanced reports: new group by: dimension intersection.
	bugfix: group order in tasks workflow definition.
    bugfix: not all birthdays were shown in calendar's full week view.
    bugfix: advanced_reports: timeslot totals are not correct
    bugfix: report group by date columns was not applying timezone.
    bugfix: tasks list group by person filters different than assigned to, are not shown when loading the list.
	bugfix: error when filtering tasks list by milestone.
    performance: mail search.
	
	
	Since 3.5.0.X
	----------------
	feature: new timeslot module.
	feature: new timeslot list in tasks view.
	feature: allow to specify paused time when adding a timeslot.
	bugfix: tasks that dont use time are not shown correctly in calendar
	
	
	Since 3.5.0.9
	----------------
	feature: make hierarchical all dimensions in crpm_types plugin.
	feature: member tool to change its dimension.
	
	
	Since 3.5.0.8
	----------------
	feature: advanced reports: new condition type to check the availability of users in contact custom reports.
	feature: advanced reports: grouped timeslots report.
	feature: advanced services: render time amount config options as hour:minutes.
	feature: templates: add fixed time when assigning vairable "date of task creation".
	bugfix: advanced billing categories list does not filter by context.
	bugfix: template tasks does not copy the repetition options added in plugins to instantiated tasks.
	bugfix: repetitive tasks generated from templates are not chained correctly with the rest of the repetitions of the same instantiation.
	
	
	Since 3.5.0.7
	----------------
	bugfix: prevent use of browser cache when downloading files.
	bugfix: when completing a task and the wokflow changes the project/client status the member status history is not saved.
	
	Since 3.5.0.6
	----------------
	bugfix: phone number form component shares variable with other phone components and causes malfunction when adding new phones in contacts.
	bugfix: breadcrumbs displayed near name on object lists.
	bugfix: assigned to logged user by default when creating task from email.
	bugfix: when editing timeslot starting at 12:00 am and depending in the timezone of the user, sometimes it adds 24 hours.
	feature: user preference to show the inactive users or not in the contacts list.

	
	Since 3.5.0.5
	----------------
	bugfix: permissions error when trying to view a document associated to an email.
	bugfix: when instantiating templates the permissions are not checked for the assigned person.
	
	Since 3.5.0.4
    ----------------
	bugfix: general search is checking permissions for super administrators.
    bugfix: when trying to complete task with uncompleted dependencies the notification is sent anyways.
    bugfix: object picker filters after removing the filter (continues to use the active context)
    bugfix: currency in total tasks time time report not correct
    feature: add invoice description to the preview.
    feature: mail account option to specify if the ssl certificate has to be validated
	feature: pear/net/socket connection function updated to use stream_socket_client() if needed

	Since 3.5.0.3
    ----------------
    bugfix: list custom property values are not shown in object view.
    bugfix: custom properties config to hide in listings is not working. 
	bugfix: Contact report mysql error when filtering by phone or webpage
	
	Since 3.5.0.2
	----------------
	bugfix: don't check permissions when auto classifying instantaited tasks from templates
	feature: in timeslot form optional start time field
	feature: modal form to add worked hours in tasks
	featere: config option to choose if show the name or picture of the assigned person
	
	Since 3.5.0.1
	----------------
	bugfix: template instantiation, text in variable property editor is always instantiated in lowercase
	bugfix: when tying to classify in a member where you don't have permissions the system doesn't tell you that the object was not classfied.
	bugfix: when you press 'check mail' it tells you an email has been received but it doesn't show up in your mailbox. You have to refresh the mailbox.
	
	Since 3.5.0.0
	----------------
	bugfix: disabling clients and projects lists deactivates the plugin
	bugfix: mime parser does not parse correclty pdf sent as application/octet-stream

	Since 3.5-beta
    ----------------
	bugfix: ensure that the last_uid_in_folder is updated correctly always.
	bugfix: spellcheck on mail ckeditor.
	bugfix: mail paging toolbar is disabled after search.
	bugfix: js error when custom dimension names have single quotes.
	bugfix: template tasks instantiation: conditional tasks with subtasks does not create the subtasks correctly.
	bugfix: when email attachments are parsed as "Related" they are not included when forwarding.
	bugfix: when email is classified only in dimensions that doesn't define permissions and user is not the mail account owner, the email cannot be reclassified.
	bugfix: object_id is not saved in sent_notifications table.
	feature: advanced services: config option to check the minimum percentage of the work shift that must be worked to allow night hours classification
	feature: advanced services: work shift break configuration and usage when calculating automatic timeslots.

	Since 3.5-alpha
	----------------
	feature: separate timeslots classification from its task
	bugfix: click in mail subject, from, to and open link in new tab doesn't open the email.
	bugfix: when downloading image it is an inline image of another document or an email attachment then check the permissions of the container doc or email.
	
	Since 3.4.4.X
	----------------
	feature: advanced reports allow to include comments below each object line.
	feature: member relations history, tab in edition and widget.
	feature: put client/project/workspaces/tags lists in a fixed tab.
	feature: separate timeslots classification from its task.
	feature: timeslot custom reports new column "paused time".
	feature: group reports by date does: put the dates of the same day in the same group
	feature: advanced services: repetitive tasks by fixed days
	feature: advanced services: generate first repetitive instnaces when creating a repetitive task.
	feature: advanced services: config option to define the minimum amount of night hours to classify the timeslots
	feature: advanced services: night hours administration section, work shifts administration section, contact work shifts assignation
	feature: advanced services: new member type - station in crpm dim.
	feature: advanced services: allow custom reports to group by hour types and service types and show these dims as columns
	feature: advanced services: automatic split of timeslots by hour types.
	feature: advanced services: config options for normal, extra and holiday hours
	feature: advanced billing: allow to specify more than one member for each billing category dimensions.
	feature: advanced billing: modifications for timeslot reports and generation.
	feature: advanced billing: billing cat with conflict timeslots in a separate section and allow to make custom reports.
	feature: custom report groups ordered alphabetically
	feature: set color to mails panel filter buttons when filtering mail listing.
	feature: set color to current mail folder in mail listing.
	feature: adv. mail: allow to select more than one account in the mail account filter menu.
	feature: add/edit timeslot reports: separate task and timeslot columns
	feature: css improved in grouped reports
	feature: new redundant column "worked_time" in timeslots
	feature: config handler to select members
	feature: marketing channels dimension.
	feature: config option to allow or not multiple lines in invoice line description.
	
	language: nl_nl fixed for "first name" and "surname".
	language: missing langs in tickets module.
	
	bugfix: when email type is text/calendar the ical file is not parsed.
	bugfix: editors line breaks fixed.
	bugfix: reload all tasks modified when editing a task and affecting other instantiated repetitions.
	bugfix: member listing group by "located under" column fixed
	bugfix: sometimes associated member selectors are filtered by current context.
	bugfix: timeslot reports replicates task columns.
	bugfix: reporting timeslots - if ts has no name or desc, use associated task name
	bugfix: reporting - group by three criterias, in the third sometimes generates separated groups for timeslots in the same group.
	bugfix: sometimes click on email opens it in a new tab.
	bugfix: contacts search does not find if searching by name and surname together
	bugfix: click in mail subject, from, to and open link in new tab doesn't open the email
	bugfix: in gantt if a task is in more than one group it is displayed only once
	
	
	Since 3.4.4.63
	----------------
	bugfix: mail rules that forward text mails does the forwarding as html.
	
	Since 3.4.4.62
	----------------
	bugfix: sent emails imap synchronization fixed.
	
	Since 3.4.4.61
	----------------
	bugfix: gantt task dependencies not rendered.
	bugfix: ensure that the last_uid_in_folder is updated correctly always
	bugfix: delete mails from server fixes.
	
	Since 3.4.4.60
	----------------
	bugfix: when email attachments are parsed as "Related" they are not included when forwarding.
	bugfix: mysql scape on mail list query.
	bugfix: prevent sending e-invoice twice.
	
	Since 3.4.4.59
	----------------
	bugfix: templates with tasks and subtasks that are generated through conditional actions does not generate the subtasks correctly.
	
	Since 3.4.4.58
	----------------
	bugfix: export ticket custom reports to csv and excel does not change the html entities for the characters in the description.
	bugfix: missing lang in tickets custom report columns.
	
	Since 3.4.4.57
	----------------
	bugfix: in contacts view - don't show job title data if the custom property is disabled.
	
	Since 3.4.4.56
	----------------
	bugfix: sometimes when send email btn is pressed it saves a draft.
	
	Since 3.4.4.56
	----------------
	bugfix: contacts search does not find if searching by name and surname together
	
	Since 3.4.4.55
	----------------
	bugfix: email download performance when trying to request emails that are not in server.
	bugfix: sent email message_ids are not saved in mail_contents_imap_folders.
	
	Since 3.4.4.54
	----------------
	bugfix: when viewing mail its read status is not always reloaded until the complete list is reloaded. 
	
	Since 3.4.4.53
	----------------
	bugfix: missing langs in tickets notifications.
	bugfix: ticket description spacing fixed.
	
	Since 3.4.4.52
    ----------------
    bugfix: added constant to disable verify_peer and verify_peer_name when sending emails using ssl/tls and using php >= 5.6.
	
	Since 3.4.4.51
    ----------------
    bugfix: in gantt if a task is in more than one group it is shown only once.
    bugfix: missing lang in project types when the association is multiple.
    bugfix: member selectors order.
	bugfix: associated member selectors js error.
	feature: config option to allow or not multiple lines in invoice line description.
	
	Since 3.4.4.50
    ----------------
    bugfix: after selecting first multiple associated member you need to click outside and then inside the textbox to load the list.
    bugfix: disable multiple lines in invoice line description.   
    feature: invocing module: credit notes must have at least one reference.
	
	Since 3.4.4.49
    ----------------
    bugfix: change delete member js prompt for a custom modal to avoid the browser to remember the last answer.
    bugfix: make advanced_core a pre-requisite for expenses plugin.
    bugfix: member listing group by "located under" column fixed.
	
	Since 3.4.4.48
    ----------------
    bugfix: when clasifying emails it was always clasifying all the conversation without looking the preference that defines that.
    bugfix: breadcrumbs in emails must not be shown next to the subject.
    bugfix: 'created_by_id' in where clause is ambiguous' on calendar widget.
	
	Since 3.4.4.47
    ----------------
    bugfix: email parser was not recognizing attachments in multipart/related content.
	bugfix: missing lang in deleted emails notifications.
	
	Since 3.4.4.46
    ----------------
	feature: create member templates from received emails.
	bugfix: performance of search component within the mail tab.
	
	Since 3.4.4.45
    ----------------
	bugfix: error in advanced reports when grouping by created by.
	
	Since 3.4.4.44
    ----------------
	bugfix: imap check mail function fail when last received mail was deleted from the mail server.

    Since 3.4.4.43
    ----------------
    bugfix: imap checking fixed when last received mail is not in server.
    feature: new plugin "working_cycles"
    feature: new plugin "modules"

    Since 3.4.4.42
    ----------------
    feature: hierarchical client types dimension.
    bugfix: custom dimension name not used in associated dimensions.
    bugfix: send mail sometimes saves draft.

    Since 3.4.4.41
    ----------------
    bugfix: mail tracking.

    Since 3.4.4.40
    ----------------
    bugfix: error parsing Message-ID from mail.

    Since 3.4.4.39
    ----------------
    bugfix: unable to set can_update_other_users_invitations and can_link_objects permissions value.
    bugfix: imap download mails from specific date.
    bugfix: ambiguous column error when object subtype plugin is active and user group tasks by a date field.

	Since 3.4.4.38
    ----------------
    bugfix: imap mail download

	Since 3.4.4.37
    ----------------
	feature: mobile api updated
	
	Since 3.4.4.36
    ----------------
	bugfix: disable mail track
	
	Since 3.4.4.35
    ----------------
    bugfix: assigned to component does not fill with users
	bugfix: when no context selected, the add task form is not shown

	Since 3.4.4.34
    ----------------
    bugfix: mail contents - imap folder association normalized.
	
	Since 3.4.4.33
    ----------------
	feature: gantt chart includes dimension columns, custom property columns and basic properties columns
	
	Since 3.4.4.32
    ----------------
	bugfix: task member group breadcrumb shows wrong parent
	
	Since 3.4.4.31
    ----------------
    feature: action in document view - send document by email
    bugfix: cannot open weblinks files from internal network
    bugfix: tasks drag and drop between milestone groups
	
	Since 3.4.4.30
    ----------------
    bugfix: if sandbox defined then don't use the purifier to edit html documents.
	bugfix: when downloading an email whose "from" is marked as "no spam" it is sent to spam folder if has spam level headers greater than the configured level.
	bugfix: crpm types dimensions are not shown in separate columns.
	feature: allow to separate clients and projects columns in tasks list.
	
	Since 3.4.4.29
    ----------------
	bugfix: not all tasks are included in monthly view if they are too many.

	Since 3.4.4.28
    ----------------
	bugfix: newsletter cp filters not working

	Since 3.4.4.27
    ----------------
	bugfix: don't save same email more than one time if it belongs to more than one imap folder.
	bugfix: don't send reminders for template objects.
	
	Since 3.4.4.26
    ----------------
    bugfix: member custom reports pagination when hiding details
    bugfix: total of rows is not shown in reports when grouping by and hiding details
	bugfix: name column not forced in reports when grouping by and hiding details
    bugfix: contact links cannot be clicked
	bugfix: if contact/company links doesn't have scheme they are treated as relative urls.
	
	Since 3.4.4.25
    ----------------
    bugfix: check negative spam score when saving mail.
	bugfix: in notifications subscribers section, the last two users are not separated by spaces.
	feature: add column status in users list
	feature: use different background color for inactive users in users list
	
	Since 3.4.4.24
    ----------------
	bugfix: don't show company name between brackets for companies.
	bugfix: mail content fix when replying
	bugfix: error when editing timeslot with advanced billing
	bugfix: don't show add task form if user doesn't have permissions to add.
	feature: migrate payment types to object subtypes
	feature: add checkbox to select all object types in permissions definition interface.
	
	Since 3.4.4.23
    ----------------
	bugfix: custom reports not filtering by created on.
	
	Since 3.4.4.22
    ----------------
    bugfix: contacts listing order by last updated fixed.
	
	Since 3.4.4.21
    ----------------
	bugfix: when user triggers email dowload and has no permissions over the email rule's members the email is not classified there.
	
	Since 3.4.4.20
    ----------------
	bugfix: grid toolbar dissapears when entering an object, collapsing left panel and then closing the object view
	
	Since 3.4.4.19
    ----------------
    bugfix: in object view, changes in css of classification widget to use all available width 
    bugfix: when deleting mail it is not removed from list until reloading
	
	Since 3.4.4.18
    ----------------
    bugfix: custom reports error when conditions uses "is_user".
    bugfix: email addresses are not shown in custom reports.
    bugfix: performance upgraded when grouping custom reports by dimension member types.
    bugfix: in contacts list when clicking a company goes to other view.
    bugfix: email custom reports fails when using "to" in conditions.
    change: dont show payments table when editing expense.
	
	Since 3.4.4.17
    ----------------
    bugfix: in template instantiation error when calculating date from creation plus more days
	
	Since 3.4.4.16
    ----------------
    bugfix: when filtering object picker by object type the dimension filter is not applied
    bugfix: performance issue in permissions table
    bugfix: grouped reports should put the "unclassified" group in the last place
    feature: reminders post due date
    feature: config option to autoselect if milestone should be applied to subtasks
	
	Since 3.4.4.15
    ----------------
	bugfix: invoice updated_on column empty
	bugfix: tree panels wrong ordering with lower/upper case members, also with characters with accents. 
	bugfix: notifications from cron incorrect hostname
	bugfix: pdf export with accents in filename fixed
	feature: new dimension "Account executives"
	feature: show custom properties in tasks list
	
	Since 3.4.4.14
	----------------
	feature: new address type: postal
	feature: adminsitration users list using paginated grid filtering by active, inactive or all.
	feature: new action "Classify only attachments" in email view actions panel.
	
	Since 3.4.4.13
	----------------
	bugfix: don't filter users without permissions in root in tasks user's filter
	bugfix: permissions check when adding or removing task dependency
	bugfix: error 500 logged_user()->getgetUserTimezoneHoursOffset();
	bugfix: subtype assignation in invoicing module when generating next repetition
	bugfix: reports pagination when grouped and not showing details
	feature: more calculated columns in expenses/payments custom reports
	
	Since 3.4.4.12
	----------------
	bugfix: mail plugin installer and updater must put the max_spam_level in 5
	bugfix: fixed created date display for objects created before dst change
	feature: In event view, if event time zone is different than logged user time zone then show the original time with timezone below the calculated time for the logged user.
	feature: allow to change the time zone of tasks and events
	
	Since 3.4.4.11
    ----------------
    feature: timezone improvements, dst usage.
	
	Since 3.4.4.10
    ----------------
	bugfix: user without delete permissions cannot edit file revision comment.
	bugfix: file revision comment form is not modal
	bugfix: several expenses fixes and upgrades
	bugfix: invoice preview include expiration date
	bugfix: resize objectGrid when resizing containers
	feature: expenses cache tables for totals

	Since 3.4.4.9
    ----------------
	config: notification from name
	
	Since 3.4.4.8
	----------------
	bugfix: when user does not have the "can manage tasks" permission and completes a task an error message is shown after the completion.
	bugfix: automatic project status calculation not working
	bugfix: member selector, when getting child if it has to be replaced sometimes it is not added again
	
	Since 3.4.4.7
	----------------
	feature: multiple currency integration with invoicing, expenses and advanced billing.
	feature: electonic-invoice plugins.
	feature: spam score verification upgraded.
	feature: mail list - icon to show if it has been replied or forwarded.
	feature: add fixed totals for invoices.
	feature: allow to totalize content object's fixed numeric properties.
	feature: separated list totals for each defined currency in expenses and invoicing.
	bugfix: expense list and payment list totals does not use standard functions.
	bugfix: expense and payment reports are not including totals.
	
	Since 3.4.4.6
	----------------
	bugfix: in member custom reports, when adding a list custom property condition the possible values are not loaded correctly.
	
	Since 3.4.4.5
	----------------
	bugfix: don't show object print options if object subtypes plugin is not installed
	bugfix: don't use noreply@fengoffice.com as the from email address in notificacions/reminders
	bugfix: check if object subtypes plugin is activated before using it in expenses
	
	feature: object type hierarchy options
	feature: when custom properties are more than 10 then split them in two columns in the add/edit form
	feature: ensure that notifications always has something in the from name.
	feature: dont send notifications if demo_web plugin is active.
	feature: add content object id to sent_notifications table
	feature: dimension association config to autoclassify in parent member's associated member
	
	Since 3.4.4.4
	----------------
	bugfix: in grouped custom reports when a task is in more than one group then the hours are modified twice by the timezone.
	bugfix: check that parent exists before copying task info from parent
	
	Since 3.4.4.2
	----------------
	bugfix: object picker for mail attachments is not filtering correctly
	bugfix: group by parameters are not shown when adding a custom report
	bugfix: when adding custom properties, show in lists and show in main tab should be selected by default
	bugfix: list custom properties edition fails when values have '
	bugfix: breadcrumbs in object view are not using the standard function
	bugfix: expenses widget graph

	feature: show payment history in payment edit form and view
	feature: text filters in expense and payments list
	feature: expense and payment listings double toolbar
	feature: add custom property attribute to specify if the property is shown as a column in listings by default


	Since 3.4.4.1
	----------------
	feature: inherit company addresses and phones in contact reports if contact doesn't have any.
	bugfix: emails with no message_id header are not downloaded when connecting through imap
	
	Since 3.4.4
	----------------
	bugfix: upgrade script was setting version to beta.
	
	Since 3.4.3.35
	----------------
	bugfix: member name length is limited to 160 chars.
	bugfix: after searching in the member selector, the child nodes are not indented.
	bugfix: dont show disabled dimensions as listing columns
	bugfix: in invoicing module when hiding a column, the payments columns are wrong
	bugfix: after adding member it is always appended to the end of the left panel.
	
    language: de_de, fr_ca updated
    
    
    Since 3.4.3.34
    ----------------
	feature: copy expense action in expense's view
	
	Since 3.4.3.33
    ----------------
    bugfix: limit repetitive tasks shown in calendar.

	Since 3.4.3.32
    ----------------
    bugfix: executive users cannot edit expenses or link to other objects.

	Since 3.4.3.31
    ----------------
    bugfix: when editing a client, the custom properties inputs are duplicated.

	Since 3.4.3.30
    ----------------
    bugfix: error message appears when trying to reload uninitialized panel.
    bugfix: mail account dropdown menu doesn't have scroll.
	
	Since 3.4.3.29
    ----------------
    bugfix: list bullets not shown in tasks description in IE 11.

	Since 3.4.3.28
    ----------------
    bugfix: order of groups in task list when grouped by members.

	Since 3.4.3.27
    ----------------
	bugfix: inherit parent milestone and classification when creating sub tasks

    Since 3.4.3.26
    ----------------
    bugfix: In contact reports address conditions are not working.

    Since 3.4.3.25
    ----------------
    bugfix: task assigned to filter error when "Let users create objects without classifying them" is false.

	Since 3.4.3.24
    ----------------
	feature: breadcrumb on task group name for member groups.

    Since 3.4.3.23
    ----------------
	bugfix: confidential users are shown in mail account permissions section.
	
    Since 3.4.3.22
    ----------------
	bugfix: mails signatures were not editable.
	bugfix: subtasks use parent dates.
	
    Since 3.4.3.21
    ----------------
    bugfix: when changing member parent the expand/collapse element is not updated.
    bugfix: if og.openlink function returns an object then when it is called from an "href" in Firefox attribute if fails and no action is performed

    Since 3.4.3.20
    ----------------
    bugfix: when writing an email if content is put inside the signature div then it is not saved.

    Since 3.4.3.19
    ----------------
    bugfix: sql error when grouping by milestone and filtering by dates on the task list.

    Since 3.4.3.18
    ----------------
    bugfix: report pagination does not work when report has conditions.
    bugfix: client and project list tab title.
    bugfix: error printing advanced reports.
    bugfix: inherit parent dates when creating sub tasks.
    bugfix: milestone order in tasks list.
    feature: gantt weekend color.
    feature: allow to order member listings by associated dimensions

    Since 3.4.3.17
    ----------------
    bugfix: widget client statistics langs.
    bugfix: spanish lang 'in'  was  'En'.
    bugfix: client and project list tab title.
    bugfix: estimated and worked time widget language fixes.
	bugfix: completed tasks with worked time message.
	bugfix: special custom properties langs in member information widget.
	bugfix: lang fixes in member listing buttons.
	feature: dont show dimensions that are not selectable in object breadcrumbs.
	feature: show default color icon in activity widget for members that doesn't have icons.
	feature: show status with colors in clients list.

    Since 3.4.3.16
    ----------------
    config: header texture config.
    bugfix: notification logo width.
    bugfix: customer and project widget "view all" not working
    plugin demo_web: send all spam mails to inbox.
    bugfix: unread mails widget
    bugfix: widget late and upcoming tasks add new tasks.
    bugfix: widget: latest comments.
    bugfix: clients widget does not show tasks graph
    bugfix: project and clients statistics widget does not show status meter
    bugfix: member listing shows custom properties without localization settings
    bugfix: dont show status property column if status dimension is being used.

    Since 3.4.3.15
    ----------------
    bugfix: calendar print

    Since 3.4.3.14
    ----------------
    bugfix: performance fixed in tasks list when using subtasks structure and no filters or groups are used.

    Since 3.4.3.13
    ----------------
	feature: user preference to select the time column format in custom reports

    Since 3.4.3.12
    ----------------
    bugfix: prevent double request when double clicking the export to csv/excel button in custom reports.

    Since 3.4.3.11
    ----------------
    bugfix: member templates dont override the associated member with the one defined in the template initial data if the user has changed it when adding a member.
    
    Since 3.4.3.10
    ----------------
    bugfix: when saving suppliers the secondary email addresses are not saved.

    Since 3.4.3.9
    ----------------
    bugfix: mail account filter css error.

    Since 3.4.3.8
    ----------------
    bugfix: task and time reporting errors
    bugfix: css fixes to prevent vertical scroll in body element when using browser zoom

	Since 3.4.3.7
    ----------------
	bugfix: mail rules action foward mail.

	Since 3.4.3.6
	----------------
	bugfix: custom report reorder fixed when clicking in column header.
	bugfix: dont process the member search response if it is not from the last request.
	bugfix: users with mail account permissions cannot see unclassified emails if they are not the account owners.
	bugfix: cant add clients because of contact custom properties.
	
	Since 3.4.3.5
	----------------
	feature: cancel prevous search request when the search criteria has changed and the response has not returned yet.
	feature: tasks workflow - config option to select which dimensions should be copied from the completed task to the next task in workflow.
	
	Since 3.4.3.4
	----------------
	bugfix: tree nodes show more does not apply associated dimension filters
	feature: more custom login customizations
	
	Since 3.4.3.3
	----------------
	bugfix: member reports fixed when using column object_subtype_id and no object subtype is defined.
	bugfix: fix pdf export process when the exported report is too large.
	bugfix: dimension search cache is not cleared when associated dimension changes.
	bugfix: associated dimensions are fully loaded when selecting a member with no associations.

	Since 3.4.3.2
	----------------
	bugfix: template tasks conditional actions edition fixed.

	Since 3.4.3.1
	----------------
	bugfix: when disabling all the dimension selectors for an object type and editing an object of this type, the members were removed.

	Since 3.4.3.0
	----------------
	bugfix: after deleting mail account the account filter of the listing must remove the deleted id
	bugfix: google calendar sync, exception management.
	bugfix: classification component for single selection dimensions fixed.
	bugfix: permission error over file revisions.
	bugfix: when reopening a task, only the first level of dependencies are reopened.
	bugfix: task conditional actions are not replicated if the task is generated later in the workflow.
	bugfix: task workflow boolean conditions fixed.
	bugfix: when saving template task, the members and workflow should not be copied to subtasks.
	
	Since 3.4.3-rc
	----------------
	bugfix: if user does not manage permissions then don't let to create members without parent, otherwise no one will see it as user cant assign its permissions.
	language updates: fr_fr, fr_ca, tr_tr, nl_nl
	
	Since 3.4.3-beta
	----------------
	bugfix: cut user permissions when changing role must be done only if downgraded and executed in background when possible.
	bugfix: main custom properties are not shown in main tab
	
	Since 3.4.2.x
	----------------
	feature: allow to select for every dimension if its selector is shown when adding/editing an object
	feature: allow associated dimension columns in member reports
	feature: filter classification components with related dim-members when selecting the main dimension member
	feature: preload classification components with related dim-members when selecting the main dimension member
	feature: member listing group by
	feature: member custom reports group by
	feature: add relation between samples and countries
	feature: tasks workflow - new action to change project status
	feature: expenses plugin: show previous status amounts in listing and view, show payment history in expense view.
	
	bugfix: export to excel: sheet title length must have less than 31 characters
	bugfix: report totals row data alignment fixes
	bugfix: remove spaces between parameter brackets in translation tool
	bugfix: cannot delete object subtypes
	bugfix: Reload dimensions when selecting a member of an associated dimension fixed.
	bugfix: when ids are stored as string instead of int in the tree the associated dimensions does not filter other trees
	bugfix: remove strtolower usage from custom property views

	Since 3.4.2.20
    ----------------
    bugfix: When completing a repetitive task, sometimes not all original subscribers were added to the next task.
    bugfix: mail plugin installer does not add default permissions in members where users already have permissions.
	bugfix: new addToSharingTable function does not adds to the table the mail account owners when adding an email.
	bugfix: when viewing e-mail company addresses are not recognized when the company is in the system.

	Since 3.4.2.18
    ----------------
    feature: document types plugin.

	Since 3.4.2.17
    ----------------
	bugfix: updateContactMemberCache prevent error 500 on this function if the parameter user is not a contact.
	bugfix: dont use member cache for tags listing.
	
	Since 3.4.2.16
    ----------------
    bugfix: js error when printing total worked time report
    bugfix: actions button in tasks list does not work in firefox

	Since 3.4.2.15
    ----------------
	language: fr_ca language puts the same month abbreviation for June and July. 

	Since 3.4.2.14
    ----------------
	bugfix: Mail module not compatible with uuencoeded attachments.

	Since 3.4.2.13
    ----------------
	bugfix: if user cannot manage permissions, update contact member cache after adding new member.

	Since 3.4.2.12
    ----------------
    bugfix: Cannot select logged user in tasks widget configuration if user doesn't have permissions for tasks without classifying.
    bugfix: Prevent multiple submit when downloading an email attachment.

	Since 3.4.2.11
    ----------------
    bugfix: Language fixes for Nederlands (nl_nl).

	Since 3.4.2.10
    ----------------
    bugfix: upgrade from 3.4.1 fixed.

	Since 3.4.2.9
    ----------------
    bugfix: table prefix error when sending mails.

	Since 3.4.2.8
    ----------------
    bugfix: Reload dimensions when selecting a member of an associated dimension fixed.
    bugfix: check file extension before download file revision.
	
	Since 3.4.2.7
    ----------------
    bugfix: google calendar sync error when event doesn't have a name.

	Since 3.4.2.6
    ----------------
    bugfix: deleted emails are shown in trash panel.

	Since 3.4.2.5
    ----------------
    bugfix: check file extension before download file.

	Since 3.4.2.4
    ----------------
    feature: Allow to define additional member permissions in user and group edition.

	Since 3.4.2.3
    ----------------
    bugfix: User groups permissions not loaded when editing a member.
    bugfix: Cannot add additional member permissions to user groups.
    bugfix: Additional member permissions were not applied to children when "Apply to submembers" is checked.

	Since 3.4.2.2
    ----------------
    bugfix: newsletter default account not working.

	Since 3.4.2.1
    ----------------
    bugfix: overview error after selecting a member if the data base table prefix is different than fo.

	Since 3.4.2
    ----------------
    bugfix: company name length restriction.

	Since 3.4.2-rc2
	----------------
	bugfix: dont use JSON_NUMERIC_CHECK constant if not defined (is a php predefined constant).
	language: nl_nl updated.
	
	Since 3.4.2-rc
	----------------
	bugfix: member cache construction function fixed.
	
	Since 3.4.2-beta2
	----------------
	feature: Export custom reports to excel
	bugfix: fixed object classification after changing member parent.
	
	Since 3.4.2-beta
	----------------
	bugfix: custom reports pdf export compatibility in windows (need to install wkhtmltopdf)
	bugfix: custom report print view only shows first page
	bugfix: custom reports print and csv/pdf export adjustments
	bugfix: replicate purchase orders permissions over suppliers in the other content objects
	bugfix: assigned user cannot complete task from task view if doesn't have write permissions
	feature: filter member reports by associated dimensions (selected in left panel)
	
	Since 3.4.1.13
    ----------------
    language: fr_fr updated.

    Since 3.4.1.12
    ----------------
    bugfix: users widget not working properly.

	Since 3.4.1.11
    ----------------
	feature: search filter on mails

	Since 3.4.1.10
    ----------------
	performance: email recommendations when sending emails
	bugfix: active context information widget is not shown when more than one member is in context.

	Since 3.4.1.9
	----------------
	bugfix: disabled custom properties are shown in contact form
	bugfix: cannot export contact/company custom properties
	bugfix: scroll on mail panel after delete mails
	
	Since 3.4.1.8
	----------------
	feature: new system permission can instantiate template
	
	Since 3.4.1.7
    ----------------
    bugfix: prevent infinite loop when calculating repetitive tasks instances for calendars.

	Since 3.4.1.6
    ----------------
    bugfix: cannot complete task from task view when user does not have write permissions.
    language: nl_nl updated.

	Since 3.4.1.5
    ----------------
    bugfix: when adding permissions to user by a group in one dimension and by personal permissions in other, the task list is not correct.
    bugfix: fixed email decoding when entire email is an inline attachment.
    performance: task assigned selector, subscribers selector, other user selectors.

	Since 3.4.1.4
    ----------------
    bugfix: add unique index in object prefixes to avoid duplicated prefixes
    bugfix: object selector, if custom property filters not defined the list is not initialized
    bugfix: when checking email prevent querying all the mailbox mails when some mails are not found in server.
    feature: newsletter errors management improved
	private plugins updates

	Since 3.4.1.3
    ----------------
    bugfix: attached eml attachments are not parsed, and attached .eml cant be downloaded.

	Since 3.4.1.2
    ----------------
    bugfix: encoding problem on mail attachments names.

	Since 3.4.1.1
	----------------
	bugfix: cannot delete client email.
	bugfix: client email format is not controlled when editing client.
	
	Since 3.4.1
	----------------
	bugfix: several reference errors when using previous task on task edit view (affect reminders, multi assignment, repetition, assign to, custom properties, subscription, subtasks, linked objects)
	bugfix: edit tasks does not send notifications
	bugfix: when editing mail account, the signature of other users with permissions in the account are deleted.
	feature: user config option to show/hide date filters in tasks list
	css updates: key-value object properties input css modifications
	language: fr_fr language updates
	
	Since 3.4.1-rc
	----------------
	bugfix: error when adding task with more than 10 custom properties
	bugfix: error when changing user role and permissions in the same submit.
	bugfix: timeslot report has wrong time format
	bugfix: when editing a task and adding subtasks notifications are not sent if parent task assigned to is not changed.
	language: Türkçe (tr_tr) language updates
	
	Since 3.4.1-beta
	----------------
	bugfix: in firefox monthly calendar view does not expand the boxes if there are more events to show.
	bugfix: duplicate mails when to and from are the same mail.
	bugfix: google calendar sync permission issue when importing events on root.
	bugfix: in advanced search when cutting results name substr_utf function must be used.
	bugfix: missing config option for amount of events shown in monthly view.
	bugfix: events repeating by fixed day aren't shown correctly in monthly view.
	bugfix: events repeating until date doesn't include the last day in monthly view.
	
	Since 3.4.0.x
    ----------------
	feature: new calculated column total_worked_time in tasks
	feature: Tasks list drag & drop between task groups.
	feature: Tasks list drag & drop to dimension members.
	feature: Newsletters plugin.
	feature: Include member custom properties in member reports.
	feature: Make client sectors dimension multiple and hierarchical.
	feature: add address and phones info to client dashboard information
	feature: imap config option preserve emails state (read, unread)
	feature: Newsletters plugin.
	
	Since 3.4.0.29
    ----------------
	bugfix: several reference errors when using previous task on task edit view (affect reminders, multi assignment, repetition, assign to, custom properties, subscription, subtasks, linked objects)
	css updates: object properties input.

	Since 3.4.0.28
	----------------
	Config option to use or not the dates filters in tasks 
	
	Since 3.4.0.27
	----------------
	bugfix: Error when sending notifications after editing tasks
	
	Since 3.4.0.26
	----------------
	bugfix: Error when upgrading to 3.3.2 with fixed table prefix.
	
	Since 3.4.0.25
	----------------
	bugfix: Error when changing permissions and role.
	
	Since 3.4.0.24
	----------------
	bugfix: Non-working days calculation in template instantiation fixes.
	bugfix: search associated members was being filtered by current context.
	
	Since 3.4.0.23
	----------------
	bugfix: Private plugins modifications.
	
	Since 3.4.0.22
	----------------
	bugfix: Contact member cache generation fixed for new users.
	
	Since 3.4.0.21
	----------------
	bugfix: When editing user and removing a group the member cache was not deleted correctly for some submembers.
	
	Since 3.4.0.20
	----------------
	bugfix: when searching a child member and then searching the parent member, only the other child members are loaded after expanding the parent.
	
	Since 3.4.0.19
	----------------
	bugfix: gantt tasks order is not correct.
	
	Since 3.4.0.18
	----------------
	bugfix: import google calendar event classification fixed.
	
	Since 3.4.0.17
	----------------
	bugfix: export reports to pdf is adding html content to the file.
	
	Since 3.4.0.16
	----------------
	bugfix: check mail error when emails uid change on the mail server.
	bugfix: some user properties are set when adding contact
	
	Since 3.4.0.15
	----------------
	bugfix: cannot enable email module if it has been disabled.
	bugfix: email address length is too short.
	
	Since 3.4.0.14
	----------------
	bugfix: users cannot see other users contact cards if they have a higher role.
	
	Since 3.4.0.13
	----------------
	bugfix: mail list is not reloading if viewing an email and changing context.
	bugfix: export contacts to csv;
	bugfix: create contact from email when sending email was setting user fields.
    bugfix: additional member permissions - sometimes the checkboxes are checked and the permissions is not set.
   	
	Since 3.4.0.12
	----------------
	bugfix: get_member_childs function for member tree.
	bugfix: javascript errors when moving to trash tasks on tasks list.
	performance: multi tasks action remove member aditional data calculation.
	
	Since 3.4.0.10
	----------------
	bugfix: instantiate template from email is not working properly.
	bugfix: javascript error when completing subtask on the tasks list.
	
	Since 3.4.0.9
	----------------
	performance: remove member aditional data from member lists (project and clients lists)
	
	Since 3.4.0.8
	----------------
	bugfix: add member custom properties tab render error.
	
	Since 3.4.0.7
	----------------
	bugfix: error on object list when sorting by custom properties or dimensions.
	bugfix: comments widget not working.
	bugfix: prevent json errors on members list. 
	
	Since 3.4.0.6
	----------------
	bugfix: do not check permissions for super admin on archived objects list.
	bugfix: users can view other users with lower rol if they don't have can_manage_security.
	bugfix: root objects permissions.
	bugfix: classification error on user edit form.
	
	Since 3.4.0.5
	----------------
	feature: allow to edit precharged subejct and body variables when instantiating a template from an email.
	feature: allow all day events container to be expanded to show all items.
	feature: mails panel, when filtering by unread, dont remove mails immediately after marking as read, remove them when leaving the tab or opening an email.
	feature: constant to remove help links in settings.
	
	bugfix: in mail list when several requests are sent to load the list (different filters) only the last response must be loaded.
	bugfix: when classifying from mail view always return to first page.
	bugfix: if template parameters has name with capital letters and are saved in lowercase (if browser sent them in lowercase) the parameter is not applied.
	bugfix: if sunday is a working day, 5 days calendar view should start in sunday.
	bugfix: encoding error in general search.
	bugfix: show all childs in memeber trees after expand.
	bugfix: cannot advance to second page of projects list.
	bugfix: mail panel grid selections doesn't work fine after marking mail as read and filtering by unread.
	
	Since 3.4.0.4
	----------------
	bugfix: single member selector does not show filter input in chrome.
	bugfix: performance improved when checking for new emails.
	bugfix: tasks toolbar complete button does not prompt to complete subtasks.
	bugfix: remove rows from mail list when mail is deleted. archived, classified in other place, etc.
	bugfix: only admins can change timeslot person.
	bugfix: if pdf file doesn't have extension the preview is not displayed
	bugfix: when clients and projects are in different dimensions the tasks cannot be grouped by projects
	
	Since 3.4.0.3
	----------------
	feature: massive task reassignation button in tasks list
	feature: allow to add/subtract minutes of date variables in task templates.
	
	bugfix: member custom properties of type user does not load all users with permissions
	bugfix: email panel, new emails must be loaded only in first page
	bugfix: members list custom properties columns, disabled custom properties must not be shown.
	bugfix: member templates add/edit render empty divs for selectors of disabled dimensions
	bugfix: let superadmins view and modify confidential users
	bugfix: when template adds more than one day to a date variable and the resulting date is a non-working day then that amount must be added to keep the gap.
	bugfix: add subtask from task view.

	Since 3.4.0.2
	----------------
	bugfix: when submitting comment disable the button to prevent double comment if pressed twice.
	bugfix: email list performance improvements.

	Since 3.4.0.1
	----------------
	feature: email polling check if there are new mails and add them to the list instead of reloading all the panel
	feature: in lists allow checkboxes to work with shift key
	bugfix: breadcrumbs does not appear in expenses report the first time it is executed.

	Since 3.4
	----------------
	bugfix: some non-standard characters cause that some workspaces are not shown unless the searchbox is used.
	bugfix: companies csv export does not use .csv as file extension.
	bugfix: sql modified for superamins in clients and projects listings.
	bugfix: in permissions "all" checkbox doesn't work if user is collaborator or guest.
	bugfix: user subscribers are deleted after user edition.
	bugfix: mail report fields to, cc, bcc and body doesn't allow 'like' condition.	
	
	Since 3.4-rc
	----------------
	bugfix: webpages display issue in contacts view.
	bugfix: tasks list grouped by milestones, if showing empty milestones, when adding/editing task, it is replicated in all emtpy milestones.
	bugfix: change 'null' for '0' in plugin_id column in installer initial data quieries.
	
	Since 3.4-beta
	----------------
	bugfix: cannot instantiate template from mail if template has no variables.
	bugfix: in some languages somtimes the messages widget crashes the overview.
	
	Since 3.3.0.11
	----------------
	feature: filter by type in archived objects, trash panel and overview list.
	feature: member info widget improved.
	feature: objects subtypes plugin, allow to define different types of notes, tasks, etc., each one with different sets of custom properties.
	feature: custom properties interface improved.
	feature: expenses - allow expenses report to be exported to csv.
	feature: expenses - definition of different type of payments, each one with different sets of attributes.
	feature: expenses - config option to allow to add negative amounts.
	feature: advanced billing plugin.
	feature: add to listings the possibility to order by custom properties and dimension members.
	feature: don't persist attached documents after sending the email.
	feature: allow to filter time report by associated task status.
	feature: when completing a task show popup to ask if user wants to complete subtasks too.
	feature: allow to set user when adding worked time from tasks list.
	
	bugfix: when editing an object, don't modify linked objects for objects that logged user doesn't have permissions.
	bugfix: when editing an object, linked objects are not instantly loaded in the form.
	bugfix: add/edit mail account - sent emails imap sync fields are not shown in a tab.
	bugfix: don't show non manageable dimensions in object view (except for clients).
	bugfix: view/edit company without logo shows person default image.
	bugfix: when changing company logo it doesn't refresh the preview.
	bugfix: company logos are not shown in contacts tab list.
	bugfix: delete contact picture does not delete all sizes
	bugfix: when all sizes pictures are generated by the upgrade process, they cannot be edited (medium and small sizes).
	bugfix: plugin installer does not update version if plugin is already scanned.
	bugfix: single member selector style adjusted to look like multiple member selector.
	
	languages updated: tr_tr and fr_ca.
	
	
	Since 3.3.0.10
	----------------
	bugfix: pagination in archived and trashed panels.
	bugfix: user and date information in archived and trashed panels is not shown.
	bugfix: ckeditor sometimtes puts an overlay that cannot be removed (when pasting links).
	bugfix: when viewing emails don't show attachments container if there are only inline attachments that are shown in mail body.
	bugfix: linked objects js error when creating task from email
	bugfix: repeat by fixed date events sometimes are shown in the next day (when timezone > 0)
	bugfix: when viewing emails don't show attachments container if there are only inline attachments that are shown in mail body.
	bugfix: comments text area has a max of 13 lines.
	bugfix: cannot remove tags from users.
	bugfix: cannot edit some picture files.
	bugfix: non administrators can't link objects to template tasks when editing.
	bugfix: give a proper message when imap extension is not installed.
	bugfix: permissions components does not have horizontal scrollbar.
	
	Since 3.3.0.9
	----------------
	bugfix: member panel children display issue fixed.
	
	Since 3.3.0.8
	----------------
	bugfix: cannot download emails with .docx attachments if zip php extension is not installed.
	bugfix: when filtering by another dimension member the members that have parents are not included.
	
	Since 3.3.0.7
	----------------
	bugfix: reminders are always saved to apply all the subscribers.
	bugfix: reminders are sent to non-subscribed users (when reminders are copied in repetitive tasks)
	language updates: fr_ca and tr_tr

	Since 3.3.0.6
	----------------
	bugfix: Deleting from project listing is not using the member controller delete function.
	
	Since 3.3.0.5
	----------------
	bugfix: don't add contact member permissions for dimension_object and dimension_group object types.
	
	Since 3.3.0.4
	----------------
	bugfix: single member selector on firefox.
	bugfix: send mail error when is called by cron and the sender is not the account owner. 
	bugfix: email address support.
	
	Since 3.3.0.3
	----------------
	bugfix: member list order by parent name.
	bugfix: search on member trees is not working if the node name have html entities.
	
	Since 3.3.0.2
	----------------
	bugfix: web documents only working for super admins.
	bugfix: workspaces and tags list not working.
	bugfix: add new contact/user/company picture error
	
	Since 3.3.0.1
	----------------
	bugfix: when adding/editing suppliers the associated contact custom properties are not rendered and if there is any required cp the supplier cannot be added.
	bugfix: clear filter after select on member selectors.
	bugfix: single member selector
	bugfix: breadcrumbs js error
	bugfix: ensure to archive-unarchive associated object or associated member
	bugfix: filter dimensions by other dimensions does not get the filtered members always.
	bugfix: ensure that deprecated gantt overview widget is not rendered.
	bugfix: custom properties address fields renderization crashes when it has enters.

	performance: member tree search.
	performance: member trees.
	
	
	Since 3.3
	----------------
	
	bugfix: js error and new tab opens when trying to add new invoice and module is not configured.
	bugfix: tasks list group by dimension not showing tasks without member.
	bugfix: when no formula status is matched then the last one is assigned.
	bugfix: super admin can not see other users attachments if the mail is not classified.
	bugfix: repeating template tasks not working with config option repeating_task.
	bugfix: purchase orders "ordered by" field must be only users.
	bugfix: expenses payments numeration
	
	lang: Turkish translation


	Since 3.3-rc
	----------------
	bugfix: contact list image width.
	bugfix: settings icons misalignments when language is es_es or es_la
	bugfix: search results not always shows where is the match (e.g.: if match is in a comment)
	bugfix: template tasks parent id is not set in some cases.
	bugfix: dont delete .htaccess when cleaning tmp folder using cron events
	bugfix: set "attach document to notification" default value to "false"
	bugfix: filters bug in linked objects picker.
	bugfix: custom report dates are not correct.
	bugfix: search results not always shows where is the match (e.g.when matched word is in a comment)
	bugfix: report is shown in permissions screen when reports tab is disabled
	bugfix: sharing table not updated for user when permissions changed.
	bugfix: birthday query not correct for leap-years
	bugfix: dont set user images to expire today.
	bugfix: incorrect error message when user cannot upload document
	
	
	Since 3.3-beta
	----------------
	bugfix: breadcrumbs on mail list.
	bugfix: template tasks depth.
	bugfix: some template tasks disappear from template view.
	bugfix: contact member cache is not recalculated when enabling or disabling modules.
	bugfix: weblinks names.
	bugfix: non working days - remove option "leave as they are" and set min amount of days to 1.
	bugfix: user birthday
	bugfix: administrator cannot edit user in some cases
	bugfix: in template user variables: dont filter fixed user variables by context or company
	bugfix: all prev tasks completed notification cant be sent if company logo file does not exists
	bugfix: dont show member custom properties if plugin not installed.

	
	Since 3.2.3.2
	----------------

	bugfix: dont show object in listings if it is only classified in a person member.
	bugfix: resize members panel.
	bugfix: multiline text on street address.
	bugfix: missing contact data inputs in clients edition when client is a person.
	bugfix: langs in tasks groups.
	bugfix: user's widget in dashboard showing contacts.
	bugfix: php execution in tmp folder must be disabled
	bugfix: no message is shown when tasks report does not have tasks
	bugfix: change project_manager to custom property
	bugfix: related dimensions are reloaded with selected node object type not having an association.
	bugfix: edit button on members trees
	bugfix: after change client parent classify contact 
	bugfix: update parent after adding child node on members trees 
	bugfix: print tasks list
	bugfix: don't allow duplicated username
	bugfix: Error when filtering tasks by tag.
	bugfix: dont show object in listings if it is only classified in a person member
	bugfix: do not reload tasks list after complete tasks
	bugfix: javascript infinite loop on tasks list
	bugfix: dont force repository files download, keep cache for these files
	bugfix: mail rules mark as read fail when is called from cron
	bugfix: when filtering members by another dimension, cannot view filtered member childs
	bugfix: autoclassify components misaligned in mail account edition
	bugfix: drag and drop on member tree node
	bugfix: zip code is not shown in user/contact/company card
	bugfix: cannot download attachments if email has inline images before the attachments
	bugfix: checkbox to send notification to assignee does not appear
	
	
	feature: resize columns on tasks list.
	feature: separate dimensions columns on tasks list.
	feature: general member listings.
	fetaure: allow to reorder and disable description and color fields (in custom properties administration).
	feature: custom properties of type user
	feature: table to add options for different member types
	feature: dimension object type option to decide if member is selected or not after its creation
	feature: description field for all type of members.
	feature: lab samples plugin and object prefixes modifications
	feature: dimension options normalization
	feature: dimension options settings section
	feature: allow to set custom dimension names
	feature: allow to enable/disable dimension member types
	feature: show or hide overview action links checking by current context (e.g.: dont show clients list link if a client is selected)
	feature: sent notifications history log
	feature: ability to log in filesystem the error details when sending a notification fails
	feature: dont reload all panel when task workflow generates new tasks, only add new tasks to the list
	
	perfromance: mobile member listing
	performance: remove object timeslots permissions 
	performance: close timeslots when completing tasks
	
	language: non working days traductions for es_es and es_la
	
	usability: time report using 100% width, min-width:750
		
	
	Since 3.2.3.1
	----------------
	bugfix: Error when filtering tasks by tag.
	
	Since 3.2.3
	----------------
	feature: view object history paginated.
	
	Since 3.2.3-beta
	----------------
	bugfix: fixed addToSharingTable when called from rebuild_sharing_table.php
	bugfix: unclassified emails are not added to sharing table.
	bugfix: group totals on tasks list.
	bugfix: new mail old view from contact list.
	bugfix: blocking file for uploading new revision.
	bugfix: when instantiating template from email, assigned users should be filtered using email's members
	bugfix: ensure that member template tasks are classified in the recently created member.
	bugfix: when viewing mails, container must be bigger before the resize
	bugfix: view more french.

	
	Since 3.2.2
	----------------
	bugfix: object prefix is not deleted after deleting object.
	bugfix: removed trailing commas from javascript objects.
	bugfix: classify contact after change client parent.
	bugfix: mail view doesn't reload after classifying mail.
	bugfix: remember notify my self checkbox in add and edit file. 
	bugfix: notify myself checkbox view in add and edit file.
	bugfix: notifications to asignee when creating and editing tasks.
	bugfix: update parent after adding child node on members trees.
	bugfix: mail rules history view.
	bugfix: not possible to upload picture when creating contact or user.
	bugfix: not showing which tab is selected in the mail panel.
	bugfix: phone number name with special characters. 
	bugfix: language and css of installed plugins that are not activated are not included.
	bugfix: template milestone add is not modal.
	bugfix: when modifying/trashing several tasks don't make all client/project calculations foreach task, do it once after all modifications
	bugfix: calculate percent completed on tasks.
	bugfix: group totals on tasks list.
	bugfix: Superadmin cannot edit other superadmins.
	
	feature: generate template from mail - edit email fields - link mail to generated tasks.
	feature: calendar new event view when clicking on the calendar.
	feature: when creating subsecuent tasks (tasks workflow) also creates a task dependency between the completed task and the new ones
	
	performance: member trees paginated (left panel and selectors).
	performance: remove object timeslots permissions.
	performance: close timeslots when completing tasks.
	
	config option: number of previous pending tasks showing next to each task.
	config option: notification to subscribers when creating and editing tasks.
	
	Since 3.2.2-rc
	----------------
	bugfix: object prefix is not deleted after deleting object
	
	Since 3.2.2-beta
	----------------
	bugfix: breadcrumbs on mail list.
	bugfix: calculate percent completed on tasks.
	bugfix: group totals aligned on tasks list.
	bugfix: error when instantiating template milestones.
	bugfix: showing which tab is selected in mail panel.
	bugfix: prevent "duplicate key" message in permission groups table when creating an user.
	bugfix: some checkboxes are not submitted correctly.
	
	
	Since 3.2.2-alpha
	----------------
	fetaure: When editing a task and start or due date is changed, ask the user to advance/rewind the subtasks dates.
	feature: new config option to configure which address fields are mandatory.
	
	performance: breadcrumbs.
	performance: tasks list.
	
	bugfix: when clicking home, the panel is reloaded once per enabled dimension.
	bugfix: when filtering tree by another dimension and selecting the node it is reloading again unnecessarilly.
	bugfix: remove comment and template objects types from role_object_type_permissions, max_role_object_type_permissions and contact_member_permissions.
	bugfix: old breadcrumbs.
	bugfix: object prefices - setting object name must escape characters.
	bugfix: linked objects component always filters by context, and intersects with the own member pickers selections.
	bugfix: when editing object, when cleaning object_members before adding to members, members of non-manageable dimensions must not be cleaned.
	bugfix: template instantiation with non-working days does not leave task dates as they are if advanced days are 0.
	bugfix: don't classify "inline" attachments when classifying emails.
	bugfix: auto_classify_attachments config option is not included in mail plugin installer.
	bugfix: color input does not render "light grey" color.
	bugfix: project manager selector changes.
	bugfix: csv contact/company import encoding problem with iso-8859-1.
	bugfix: csv contact/company import fails if some fields has apostrophe.
	bugfix: csv contact/company import duplicates addresses, phones and webpages if import is executed twice.
	bugfix: custom properties with apostrophe can't be added to searchable objects.
	bugfix: some checkboxes are not submitted correctly.
		
	
	Since 3.2.1
	----------------
	feature: user picture files are scaled in 3 sizes for performace
	
	Since 3.2.1-rc
	----------------
	bugfix: linked objects and view as list in dashboard does not filter.
	bugfix: notification of previous task completed is the same as task completed.
	bugfix: template instantiation with non-working days does not leave task dates as they are if advanced days are 0
	bugfix: contact selector langs are always in spanish.
	bugfix: tasks not showing in dashboard calendar widget.
	
	
	Since 3.2.1-beta
	----------------
	bugfix: mobile client's list.
	bugfix: uploading file with empty name.
	bugfix: mail panel navigation history.
	bugfix: when instantiating tasks from template in second step, subscribers are not copied changes: new function to centralize the copy of the subscribers, reminders, linked objs, members and custom props.
	bugfix: lang unknown group on tasks list for dates groups.
	bugfix: instantiate template parameters is not filtering by recently created member (when using member templates).
	bugfix: lang nl_nl contains enters inside the langs and that causes javascript to be broken.
	bugfix: when adding/editing an email account, if a blank is after mail server address then cannot connect to mail server.
	
	
	Since 3.2.0.4
	----------------	
	bugfix: google calendar sync permissions errors
	
	Since 3.2.0.3
	----------------	
	language: language fr_ca updated.
	
	Since 3.2.0.2
	----------------	
	bugfix: member custom properties values close div on active feed.
	
	Since 3.2
	----------------
	bugfix: feng1 upgrade
	bugfix: mail listing must not show emails that are not of my account and are not classified.
	bugfix: config option notify myself when uploading documents. 
	bugfix: total pending time in tasks list. 
	bugfix: add timeslot on task view not visible in root.
	bugfix: expenses, objectives and purchase orders where sending notifications before commit.
	bugfix: input type checkbox in modals
	bugfix: in community editions (tasks does not use time in dates) the reports are adding the timezone.
	bugfix: projects list and clients list links are not included in dashboard list view
	bugfix: google calendar sync.
	
	performance: linked objects list
	performance: index by object_type_id, trashed_on, archived_on in objects table
	
	Since 3.2-rc2
	----------------
	bugfix:	ie fixes.
	bugfix: custom properties description in forms is not aligned.
	
	
	Since 3.2-rc
	----------------
	feature: allow to have companies and contacts with the same email address.
	feature: clients list.
	feature: clients and projects listings allows to show custom properties and order the list by them.
	feature: check attach word with langs.
	feature: listing contacts with picture.
	feature: config option to allow choosing in which email field (to,cc,bcc) will the recipients be put when sending a notification.
	feature: config option changing name order to name surname.
	feature: config option remember working days preference in task's push.
	
	bugfix: updating percentage completed in tasks list.
	bugfix: returning to company view after editing contact picture.
	bugfix: number of archived objects.
	bugfix: member selector overflow.
	bugfix: style when changing order of deleted custom property. 
	bugfix: custom property order. 
	bugfix: remembering checkboxes in reports. 
	bugfix: order by start date in tasks list.
	bugfix: saving documents without name.
	bugfix: refreshing read/unread ball colour in tasks list.
	bugfix: alignment in add event.
	bugfix: number of elements in trash.
	bugfix: when sending email to contact with comma in their name. 
	bugfix: showing milestones options when disable in task panel.
	bugfix: remembering gantt zoom.
	bugfix: classify modal autofocus.
	bugfix: plugin installers: dont give permissions in members that users doesn't have permissions for tasks (expenses, objectives, purchase_orders, income).
	bugfix: plugin installers: dont give permissions in members that users doesn't have permissions for tasks.
	bugfix: members of dimensions that dont use permissions are not shown in listings.
	bugfix: tags on memeber selectors.
	bugfix: contact custom properties on client edit.
	bugfix: when "adding objects without classifying": cannot create task if not filtering by member.
	bugfix: when grouping tasks by dim-members unclassified group is shown always however it has no tasks.
	bugfix: in additional member permissions.
	bugfix: when setting a project's client, the client contact is not classified into the project.
	bugfix: tab panel permission was not set when installing income plugin.
	bugfix: tasks report headers are wrong when grouping by priority.
	bugfix: when creating task repetition the subscribers are not copied from the original task.
	bugfix: when creating new client classify contact or company on the client.
	bugfix: error 500 when adding expense without payments.
	bugfix: when adding timeslot in "view all" no user is available.
	bugfix: when creating new client classify contact or company on the client.
	bugfix: events without invitation are not shown.
	bugfix: tasks workflow - only second step inherits templates variables.
	bugfix: prevent adding duplicated nodes in left panel trees.
	bugfix: task templates, only the first step works.
	bugfix: error 500 when colaborator edit a timeslot.
	bugfix: cannot edit root permissions when changing user type.
	bugfix: notes widgets is not cutting note text correctly. 
			
	
	Since 3.2-beta2
	----------------
	feature: new custom property type : address
	feature: suppliers dimension (for purchase orders)
	feature: user config option check attach word on mails.
	feature: modal view for classify email.
	feature: more read permissions for guest customers.
	feature: custom reports for dimension members.
	feature: confirm delete in members.
	feature: time on report dates (crpm)
	
	bugfix: don't close modal on overlay click.
	bugfix: users can see all users profiles.
	bugfix: adapting image size on contact view.
	bugfix: user disappear from other users view after edit.
	bugfix: dynamic message when adding linked objects.
	bugfix: editting web document from view.
	bugfix: checking url when adding web document.
	bugfix: in income table definitions and queries.
	bugfix: save user permissions in background twice.
	bugfix: converting a contact to user loses custom properties and linked objects.
	bugfix: when editing an user and changing its user groups the member cache is not recalculated.
	bugfix: when creating user from contact, if all system permissions are removed, the default are added.
	bugfix: subscribers are lost if object is saved before the reload subscribers request returns.
	bugfix: duplicate phone on edit contact view.
	bugfix: mysql reconnect on executeAll.
	bugfix: "Cannot read property createChild of null" when adding a task and closing the modal form quickly.
	bugfix: in user groups permissions when applying permissions to all submembers of a workspace that has no children, the workspace is removed from the tree.
	bugfix: search input in listings has many bugs.
	bugfix: do not check feng_persons dimensions when checking for email classification (for listing icon and classification filter)
	bugfix: create user from contact view.
	bugfix: cannot export to csv custom reports with date_time custom properties.
	bugfix: draw located under selector only if member can have parent.
	bugfix: workspace description value is not rendered when editing a workspace.
	bugfix: breadcrumbs asking for members where user don't have permissions.
	bugfix: after add a contact on root.
	bugfix: feng 3 mobile.
	bugfix: can't remove "only working days for the tasks" on templates.
	bugfix: client fee is not saved correctly
	bugfix: deleting permission for single object types removes all permissions for member (when changing permissions from user form)
	
	
	member custom properties: use "visible_by_default" column to show cp in main tab or "custom props" tab
	
	performance: get imap mail function optimization.
	performance: update timeslots billing values is using too many objects and memory runs out if there are a lot of timeslots to update.
		
		
	Since 3.2-beta
	----------------
	feature: php path config.
	
	bugfix: can't subscribe users.
	bugfix: upgrade from versions lower than 3.1.5.3
	bugfix: after add a contact in root.
	bugfix: after updating permissions for a user on a member, all objects on sub-members are deleted from sharing_table for that user.
	
	Since 3.1.5.3
	----------------
	feature: dont use sharing table to check canView for individual objects.
	feature: instantiate task templates from email.
	feature: upgrade script change searchable_objects table to InnoDB if mysql version is  5.6 or greater.
	feature: checkbox to apply same permissions to submembers in edit member form, permissions modal form.
	feature: new table to define max member permissions by role.
	feature: send notifications grouped by language and timezone, max of 20 users x group.
	feature: invoicing repetition - repeat by fixed date.
	feature: When calculating task dates in templates and task push, do not count non-working days.
	feature: google calendar sync api v3.
	feature: notify assigned user of task A when A has a previous task B and B has been completed.
	feature: allow time inputs in date custom properties, only if crpm plugin is installed.
	feature: when instantiating template tasks check dates and if it is a non-working day execute the action defined in the template.
	feature: non working days abm.
	
	permissions: give read permissions over reports to all roles as default permissions 
	permissions: hide "templates" object type radio buttons in permissions forms
	
	bugfix: when member filters associated dimension, get_child_members is not filtering. 
	bugfix: when member filters associated dimension, if filtered member is clicked then the other dimensions are cleared and no member is selected.
	bugfix: when changing user role to a lower role, max permissions are not updated.
	bugfix: root permission are set to ext. collaborators.
	bugfix: nested transactions when saving mail.
	bugfix: contact export reloads the page. 
	bugfix: export all contacts to vcard does not filter by context.
	bugfix: pagination in invoices listing.
	bugfix: config option "inherit_permissions_from_parent_member" was not inserted in installer.
	bugfix: when conditional task is automatically instantiated it is non added to sharing table.
	bugfix: purge trash performance.
	bugfix: if role cannot read object type the permission radio button is not hidden.
	bugfix: create task from email form is not modal and  does not autoselect the email's context.
	bugfix: template workflow - when tasks are automatically created, the parameters are not instantiated in the new tasks.
	bugfix: use post for get_members function.
	bugfix: breadcrumbs sometimes shows parent members that doesn't have permissions.
	
	Since 3.1.5.2
	----------------
	bugfix: on overview member selectors not working.
	bugfix: remove all members from contact.
	
	Since 3.1.5.1
	----------------
	usability: dont remove all items when reloading mail list.
    bugfix: mail panel is reloaded foreach email received (only when checking mail manually)
	bugfix: push tasks dates.
	
	Since 3.1.5
	----------------
	bugfix: in member permissions radiobuttons that cannot be selected (depending on role) must not be shown.
	bugfix: collaborator user can't view tasks where is the assigned user.
	bugfix: when editing objects and changing context, subscribers are reloaded and users without permissions are not removed if they were subscribed previously.
	bugfix: permission trees loading concurrence fixes.
	bugfix: user can edit other users with higher role.
	bugfix: collaborator cannot add worked time from tasks list if no member selected.
	bugfix: adding folders from quick add does not inherit permissions from parent member.
	language: ja_jp updated
	
	Since 3.1.4.3
	----------------
	permissions: give guests and collaborators read permissions over reports by default.
	bugfix: error sending mail.
	
	feature: cron function send outbox mail.
	
	Since 3.1.4.2
	----------------
	bugfix: email download count fixed.
	bugfix: error when creating tasks.
	
	Since 3.1.4.1
	----------------	
	bugfix: contact export to csv.
	
	Since 3.1.4
	----------------
	bugfix: performance when saving tasks, if it has subtasks can produce timeout.
	bugfix: if role cannot read object type the permission radio button is not hidden.	
	
	Since 3.1.3.8
	----------------
	feature: System permission to let some users link objects.
	
	Since 3.1.3.7
	----------------	
	bugfix: notify myself when uploading files.
	
	Since 3.1.3.6
	----------------
	bugfix: only context permissions are checked when adding a timeslot.
	bugfix: can_add function is checking for disabled dimensions.
	
	Since 3.1.3.5
	----------------		
	bugfix: missing linked objects types on the selctor.
	bugfix: add billing on timeslots when select stop clock on a task. 
	bugfix: repeat event by fixed day is showing wrong dates for some events.
	bugfix: only context permissions are checked when editing a timeslot.
	bugfix: multiple file upload transaction broken by notifications.
	
	Since 3.1.3.4
	----------------	
	bugfix: at least one dimension must be selected on system modules.
	
	Since 3.1.3.3
	----------------	
	bugfix: add timeslot on tasks for user with permissions on the task context.
	
	Since 3.1.3.2
	----------------
	bugfix: when instantiating templates if subtask is instantiated before parent task the instantiation fails because parent instance is not found
	
	Since 3.1.3.1
	----------------
	bugfix: show tasks dependencies on gantt.
	bugfix: in edit task form, not all ' are escaped when drawing subtasks form.
	bugfix: task reminders are not sent for non administrators, task canView function was asking for logged user instead of using the user parameter.
	
	Since 3.1.3
	----------------
	bugfix: Mobile login fixed.
	bugfix: dates variables on tasks templates timezone error.
	bugfix: can_manage_contacts is checking permissions for super admins
	bugfix: logo_empresa.png limit size
	bugfix: cannot save "can_edit_completed_payments" system permission	
	
	Since 3.1.2.8
	----------------
	bugfix: if user has no permissions to see assigned to other user tasks he/she can view them in the calendar.
	bugfix: super admin cannot view all files.
	bugfix: task list print does not work
	bugfix: upgrade fix if cron event already exists
	bugfix: remove from sharing table objects when user has no permissions to access objects without classification.
	bugfix: when adding a workspace, client, project or folder without parent and changing the parent in the form, the permissions are not inherited.
	feature: scroll to comment after adding one.
	language: updaed ru_ru, fr_ca and fr_fr
	
	Since 3.1.2.7
	----------------
	bugfix: in user edition, user group names are not escaped and the form crahsed depending on the content.
	
	Since 3.1.2.6
	----------------
	perfomance: user selector on add/edit task view.
	performance: add to sharing table
	bugfix: add index member_id in object_members
    bugfix: get max uid for imap folder
	bugfix: when saving an email don't begin the transaction if it is not needed

	Since 3.1.2.5
	----------------	
	bugfix: upgrade scripts for expenses and objectives plugins, add max permissions for executives.
	
	Since 3.1.2.4
	----------------	
	bugfix: can not select empty milestones on tasks list.
	bugfix: when uploading an existing document, the list of files shows each file date 1 day after the real date.
	bugfix: email due date reminders shows company name instead of due date.
		
	Since 3.1.2.3
	----------------	
	bugfix: sql error when upgrading from feng 1.7.
	bugfix: on tasks drag and drop the task loses its description.
	bugfix: load gantt if user config option is set to do that.
	bugfix: show subtasks on gantt.
	bugfix: show milestone due date on task list.
	bugfix: show empty milestones on task list fails when filtering by dates ranges.
	bugfix: cannot delete user groups.
	bugfix: cannot edit user data.
	bugfix: document is always attached to notifications.
	bugfix: email due date reminders does not show the date correctly.
	language: russian translations updated.

	feature: template tasks workflow (in conditional_tasks plugin)

	Since 3.1.2.2
	----------------	
	bugfix: forward mail not working in some cases.
	
	Since 3.1.2.1
	----------------
	bugfix: Users without permissions to add timeslots can add timeslots if the task is assigned to him/her.
	bugfix: In tasks list, user has no time permissions for a task but time options are shown.
	bugfix: In tasks list, if action popover button has no actions, it is shown with an empty menu.
	bugfix: on template tasks add dependant task not working.
	bugfix: after edit member update all childs depths.
	bugfix: when deleting emails the register in objects table was not deleted.
	bugfix: document level filter is not set with its current value when logging in.
	
	feature: in single member selector when no member is selected show root node's text.
	feature: upgrade by console - no need to pass the version from and version to parameters.
	
	
	Since 3.1.2
	----------------
	bugfix: When checking mail, check for spam level in headers improved.
	bugfix: Error when adding tasks.
	bugfix: Cannot delete user group.
	bugfix: Feng1 to Feng3 upgrade script does not fill the "enabled_dimensions" config option.

	
	Since 3.1.1
	----------------
	feature: Cron process to reprocess last objects' sharing table entries
	
	
	Since 3.1
	----------------
	bugfix: Sql error in tasks list.
	bugfix: In upgrader script, if DEFAULT_LOCALIZATION not defined then define it with value "en_us".
	bugfix: After adding tasks, actions buttons not working.
	bugfix: Reminders on task templates are not saved.
	bugfix: Can't see subtasks if parent task  is not displayed.
	bugfix: If email account is set as default, then the "Sender name" field is ignored.
	bugfix: After change group by on tasks list the groups are still the same.
	bugfix: Timezones on tasks list groups.
	bugfix: When adding an event checkboxes "subscribe invited users" and "send email notifications" are not working.
	bugfix: Duplicated tasks on tasks list in last month and last week when this groups are overlapping.
	bugfix: Sql error table missing prefix. table im_types.
	bugfix: Can't add tasks in french.
	
	feature: in contact csv import allow to match custom properties
	
	language: fr_ca updated.
	
	
	Since 3.1-beta
	----------------	
	bugfix: Template tasks subscribers were not copied when instantiating the tasks.
	bugfix: Remove contextmenu from the email editor.
	bugfix: Autoclassifying email fix in query.
	bugfix: Refresh the task row after adding timeslots to tasks.
	bugfix: Sql query malformed on tasks list.
	bugfix: Duplicate signature sometimes when replying emails.
	bugfix: When creating collaborators positioned in a workspace, the workspace is not added to the member cache, permissions are fine.
	bugfix: Javascript eerror "member is undefined" in member cache js file.
	
	
	Since 3.0.8
	----------------
	feature: hierarchy filter on documents tab.
	feature: in custom reports if object name is printed now it is a link to the object.
	feature: when classifying users using drag and drop the system asks if you want to add the default permissions for the users in the workspace where they are being classified.
	feature: add tags selector in user add/edit form.
	
	performance: tree node asks for childrens to the server twice after click .
	
	bugfix: reminders on template tasks.
	bugfix: after adding a client, the client tree shows the client twice.
	bugfix: do not reload member trees after editing a member.
	bugfix: use current time when adding timeslots from tasks list.
	bugfix: permission errors when adding timeslots from tasks list.
	bugfix: on tasks list after add the first task remove  "There are no tasks in".
	bugfix: wrong order when grouping by priority on tasks list.
	bugfix: wrong signature when replying mail from a non default account.
	bugfix: after add subtasks send assignment notifications.
	bugfix: when dragging members to no-permissions tree children are not moved.
	bugfix: cannot edit user tags.
	bugfix: select milestones on templates.
	bugfix: when composing an email with other email address the autosave asks if you want to send with that adddess (it must ask only when sending or saving draft).
	bugfix: collaborators should not have access to mail tab
	
	language: fr_ca updated.

	Since 3.0.7
	----------------
	
	
	Since 3.0.6
	----------------
	feature: in custom reports, show name column as a link to the listed object and open the link in a new feng tab.
	feature: add projects to available object types when configuring autonumeric prefixes.
	feature: crpm types plugin - new dimension Client type, Project type and Project status.
	feature: when replying an email of other account, a warning must appear telling that email will be sent using that account and give the posibility to change the account before sending the email.
	
	bugfix: upgrade script to 3.0 fails when inserting in tab_panels if not all columns are specified depending in mysql server configuration.
	bugfix: dont use the same "from name" when sending mails with different account.
	bugfix: cannot autoclassify mails in more than one workspace.
	bugfix: checklang translation tool does not show plugin missing/incomplete translation files.
	bugfix: non-exec directors should not be task assingable.
	bugfix: header breadcrumbs are not reloaded when deleting a workspace.
	bugfix: when reordering workspaces, tags, clients and projects columns in any listing (notes, documents, etc), the values of these columns are lost for all rows, must reload the list to reappear.
	bugfix: send notification when a task is assigned.
		
	performance: ajax load on tasks list.

	Since 3.0.5.1
	----------------
	feature: multiple status factors for each status.
	feature: add/edit billing category in modal form.
	feature: cancel button added to permissions popup in member edition
	
	performance: on tasks list view.
	
	bugfix: cannot upload logo when adding company or client.
	bugfix: cannot add company when creating a client with type=person.
	bugfix: unclassify user from members where permissions have been removed.
	bugfix: when replying an email and changing the "from" the mail content is lost.
	bugfix: calendar events only shows one member color (fixed for week and day views, must be activated in user preferences).
	bugfix: expenses plugin - totals row doesn't check permissions.
	bugfix: csv export should only export the contacts, users or companies if they are being shown in the contacts tab.
		
	Since 3.0.5
	----------------
	bugfix: Error when creating user from company view
	bugfix: Error when viewing empty custom property if it is of type=contact.
	bugfix: Installer error, missing column 'can_update_other_users_invitations'.
	
	Since 3.0.4.1
	----------------
	bugfix: Performance issue when changing workspace parent.
	bugfix: Cannot add user from exisiting contact.
	
	feature: System permission to let some users change event invitations state for other users.
	
	Since 3.0.3.1
	----------------
	bugfix: Performance issue when ordering documents list by size.
	
	Since 3.0.3
	----------------

	bugfix: do not show trashed comments on mails view.
	bugfix: member tree filter not working properly in some cases.
	bugfix: revision number in file view header shows the number including trashed revisions
	bugfix: show more on users selector not working.
	
	Since 3.0.2
	----------------
	feature: Choose if you want to exclude a client or project from automatic status changes.
	feature: Added custom properties to choose a default status when creating a new project or client. 
	feature: When creating a new client, you can now choose if its a company, a contact, or nothing. 
	
	bugfix: Pending factor removed from automated status formulas. 
	bugfix: Dimension Members with no creation application log were not displayed on the Dimension member list in the Administration panel. 
	bugfix: Contact emails are not being displayed on the suggested emails. 
	bugfix: Dimension member selectors were not functioning on the contacts module. 
	bugfix: Object members were not being displayed correctly, and when more than possible to display were added, "and 1 more" was not displayed.
	bugfix: cannot set permissions for users with the same user type to a project.
		
	
	Since 3.0.1
	----------------
	bugfix: after removing a member from a task, refresh member status.
	bugfix: after ading a task, refresh member status.
	bugfix: after editing status formulas, refresh all members statuses.
	bugfix: add billing view.
	bugfix: performance problem displaying contacts birthday
	
	feature: push tasks dates.
	
	Since 3.0
	----------------
	bugfix: members permissions on breadcrumbs .
	bugfix: located under selector on add tags view.
	bugfix: custom properties with multiple values are not saving properly.
	bugfix: files edit is not validating fields, and causing transaction rollback.
	bugfix: error editing web document.
	bugfix: undefined variable on filescontroller
	bugfix: check mail function make a lot of work if the last mail on the system is not on the server.
	bugfix: templates sub tasks.
	bugfix: templates view.
	bugfix: task titles on task list.
	bugfix: mails view.
	bugfix: in member permissions, don't allow to modify permissions of superior users
	
	performance: download imap mails function.
	
	
	Since 3.0-rc
	----------------
	bugfix: Enabled_dimensions where not inserted correctly by plugins installation.
	bugfix: When uploading a file, blue button moves left and then returns to its original place.
	bugfix: Logo is not clickable.
	bugfix: When modal form is rendered and controller sends an error, screen is masked anyways.
	bugfix: Don't show radio buttons to delete/write if role cannot delete or write (e.g.: collaboratos cannot delete som object types).
	bugifx: Collaborators and guests should not have root permissions.
	
	Since 3.0-beta
	----------------
	feature: Check max permissions per user role when adding/editing permissions.
	bugfix: Do not show active context members on activity widget breadcrumbs.
	bugfix: Activity widget says that some users have been unsubscribed.
			
	Since 2.7.1.9
	----------------
	feature: Several improvements in user interface, experience and looks.
	feature: New ���Getting Started Wizard���.
	feature: New workspaces selector.
	feature: Improved user creation and management.
	feature: Improved Task  Management.
			
	Since 2.7.1.8
	----------------
	feature: Allow to configure if parent permissions are inherited when creating a new workspace, client or project.
	
	Since 2.7.1.7
	----------------
	bugfix: Active context member info widget reactivated.
	bugfix: Permissions not saved when applying to all submembers and permissions tree is collapsed.
	
	Since 2.7.1.6
	----------------
	bugfix: do not show trashed emails from other accounts.
	bugfix: when adding a new member, inherit parent permissions.
	bugfix: When saving permissions for a workspace, client or project, the mandatory dimensions were not being analyzed.
	
	Since 2.7.1.5
	----------------
	bugfix: 'Unexpected token' error in tasks list.
	bugfix: 'after_contact_view' hook was lost when contact view was changed.
	
	Since 2.7.1.4
	----------------
	bugfix: task list actions
	bugfix: missing config option auto_classify_attachments
	
	Since 2.7.1.3
	----------------
	bugfix: repeating events not displayed correctly
	bugfix: checkmail function from cron fail if the mail have attachments
	
	Since 2.7.1.2
	----------------
	feature: Use "wkhtmltopdf" to convert custom reports to pdf
	
	Since 2.7.1.1
	----------------
	bugfix: When uploading a file greater than max size, the file is created but without content.
	bugfix: If error occurs when uploading file always shows the same message 'Error uploading file'.
	
	Since 2.7.1
	----------------
	bugfix: removed "fo_" prefix from queries.
	
	
	Since 2.7.1-beta
	----------------
	bugfix: quick edit task timezone errors.
	bugfix: performance improvement on object listing query.
	bugfix: performance improvement on mail listing query.
	bugfix: superadmins should not see unclassified emails of other people.
	bugfix: when selecting a workspace after searching it, it is not focused
	
	language: languages fr_ca, ar_sa updated
	

	Since 2.7.0.4
	----------------
	feature: Workspaces, tags, etc. selectors upgraded.
	
	bugfix: Performance upgrade in initial loading.
	bugfix: When editing permissions don't load information for hidden components, load data when expanding them.
	bugfix: date custom properties does not allow blank values, and cannot be deleted.
	bugfix: in custom reports remove text styles and leave new lines.
	bugfix: enters in timeslots descriptions are skipped in time report and task view.
	bugfix: after add new members on the tree select them.
	bugfix: can not print monthly calendar.
	bugfix: expenses and objectives installer: new users permissions.

	language: translation tool upgrade: Link to checklang tool to list missing langs foreach language file.
	language: translation tool upgrade: Added search functionality (searchs in keys and values)
	
	Since 2.7.0.3
	----------------
	bugfix: Performance upgrade in initial loading.
	bugfix: Workspaces, tags, etc. selectors upgraded.
	bugfix: When editing permissions don't load information for hidden components, load data when expanding them.
	
	Since 2.7.0.2
	----------------
	bugfix: email components in contact and company edition
	bugfix: show all emails in contact and company views
	bugfix: required list custom properties with default value are not filled with it when creating a new object
	
	Since 2.7.0.1
	----------------
	bugfix: date format selector saves incorrect date format when selecting m.d.Y or m-d-Y
	
	
	Since 2.7
	----------------
	bugfix: UTF-8 issue in tasks descriptions in the tasks widget.
	bugfix: Transactions reorganized when saving permissions.
	bugfix: Required custom properties control fixed.
	
	
	Since 2.7-rc
	----------------
	bugfix: Previous XSS fixes broke some post parameters (e.g. contact form).
	bugfix: If using a custom favicon, it is not used when printing an email.
	bugfix: Don't show "expiration_time" attribute in files reports (deprecated column).
	bugfix: Task descriptions cannot save character "|".
	bugfix: When composing an email and changing "from" sometimes the signature does  not refresh correctly.
	bugfix: Filtering tasks list by date, inputing the date manually does not filter the list.
	bugfix: Task descriptions overflow fixed.
	bugfix: Cannot import ical in a workspace if user doesn't have permissions in root.
	bugfix: Cannot print calendar month view.
	bugfix: Cannot export total tasks times report to csv.
	
	language: fr_ca updated.
	
	Since 2.7-beta
	----------------
	bugfix: performance when changing permissions.
	bugfix: XSS prevention fixes.
	bugfix: login screen broken in IE 7.
	bugfix: Viewing email with images like <img src="data:image/png;base64......."/> does not show images
	bugfix: Composing emails with images like <img src="data:image/png;base64......."/> are blank after sending them.
	
	
	Since 2.6.3
	----------------
	bugfix: Template tasks names overflow.
	bugfix: Calendar views event overlapping fixes.
	bugfix: If tmp/logo_empresa.png exists, then use it in all notifications.
	bugfix: When configuring widgets, changing to other section resets options.
	bugfix: When creating admins permissions were not saved in background and not using cache for dimension object type contents.
	bugfix: Permission issue when disabling a tab and then removing permissions for some workspaces.
	bugfix: Tasks "assign to" was not filtering by task context.
	bugfix: Performance in get mails query.
	bugfix: Remove "related to" option when adding a user.
	bugfix: Clients and projects widgets fixes.
	bugfix: Task quick-edit workspace, clients and projects selectors fixed.
	bugfix: Task drag & drop error after deleting a task.
	bugfix: Ical feed for calendar does not work.
	bugfix: Superadmin not viewing all elements.
	bugfix: Cannot upload file in Internet Explorer when a filtering by a member.
	bugfix: When uploading files, use a generated name instead of filename to save it in tmp.
	
	feature: Added a permissions cache to improve left panel trees load times. 
	feature: Initial loading performance improved.
	feature: Performance improved in "People widget" in overview tab.
	feature: Performance improved in "Activity widget" in overview tab.
	feature: Add flag by object for sharing table healing process.
	feature: Gantt full screen option.
	feature: Toolbar option to show/hide birthdays in caledar views.
	feature: Upgrade plugins after upgrading core using html interface (only if 'exec' php function is available).
	feature: Config option to allow (or not) new users to be granted with permissions over all existent workspaces depending on the user type.
	feature: Don't show popup when adding a new member.
	feature: Contact form upgraded.
	feature: Contact card upgraded.
	fetaure: Config option to decide if mail attachments are classified with the email.
	feature: Config option to show expanded/collapsed the attachments in mail view
	feature: Add 'name' field to telephone numbers (to support assistant name and assistant number)
	
	language: Languages fr_fr (French France) and fr_ca (French Canada) improved.
	language: Added language en_gb (English U.K.).
	
	
	Since 2.6.3-beta
	----------------
	bugfix: send email inline images attached instead of the link to tmp folder.
	bugfix: superadmin not viewing all elements.
	bugfix: task drag & drop error after deleting a task.
	bugfix: ical feed does not work.
	bugfix: cannot upload file in IE when a filtering by a member.
	bugfix: when uploading files, use a generated name instead of filename to save it in tmp.
	bugfix: send email inline images attached instead of the link to tmp folder.
	bugfix: task drag & drop error when grouping by milestone.
	bugfix: view day in calendar is not showing some tasks names.
	bugfix: line break in timeslots comments.
	bugfix: quick add task in milestone.
	bugfix: time report does not list results if a cp condition is added.
	bugfix: day calendar view show all task duration.
	bugfix: time reports grouping by clients & projects fixed.
	bugfix: time report - missing langs in group names when grouping by task.
	bugfix: edit person and company notes text box size.
	bugfix: when upload a new revision of a file keep old linked objects.
	bugfix: dont allow guest users to see other guest users.
	
	
	Since 2.6.2.1
	----------------
	bugfix: download actions were logged as 'read' instead of 'download'.

	feature: user group config handler	
	feature: permissions save in background process updated.
	feature: hook to override action veiw in ajax requests.
	feature: hook to edit application logs when saving them.
	feature: hook to edit template properties when instantiating.
	feature: allow to invoke hooks with dynamic list of parameters.
	
	Since 2.6.2
	----------------
	feature: save permissions in background upgraded.
	bugfix: custom report export to csv.
	bugfix: gantt undefined alert when a link task target is not present.
	bugfix: gmt errors in calendar when displaying tasks or dragging tasks.
	bugfix: when upload a new revision of a file keep old linked objects.
	bugfix: edit person and company notes text box size.
	bugfix: members selectors combos breadcrumbs errors.
	bugfix: milestones gmt problems in calendar view date.
	bugfix: don't use gmt in task dates if config option Use time in task dates is  disabled.
	bugfix: day calendar view show all task duration.
	bugfix: quick add task in milestone.
	bugfix: line break in timeslots comments.

	Since 2.6.2-rc
	----------------
	bugfix: javascript error when editing a completed task.
	bugfix: migration from feng version 1 to feng version 2
	fetaure: timeslot custom reports, including task columns
	
	Since 2.6.2-beta
	----------------
	language: Swedish
	bugfix: error when converting odt or docx to text with tabs
	
	Since 2.6.1.6
	----------------
	feature: gantt - new GUI
	feature: gantt - zooming by day, week, month, year
	feature: gantt - exporting to pdf
	feature: gantt - exporting to png
	feature: gantt - editable progress
	feature: gantt - task completition
	feature: gantt - groups show their entire progress
	feature: gantt - major performance improvements
	feature: gantt - being able to collapse or expand all groups
	feature: gantt - being able to show or not the tasks names
	bugfix: link objects list wrong total
	bugfix: sometimes new mail no signature shown
		
	Since 2.6.1.5
	----------------
	bugfix: cannot save client permissions

	Since 2.6.1.4
	----------------
	bugfix: error when converting odt or docx to text with tabs

	Since 2.6.1.3
	----------------
	bugfix: method_exists not working properly depending on php version if using a class method. 
	
	Since 2.6.1.2
	----------------
	bugfix: cannot download csv report in Internet Explorer.
	bugfix: time report date range shows the first day of actual month if "last month" is selected (data is correct, only date range info is showing this error).
	bugfix: check if mail plugin is installed before making queries in mail tables
	feature: allow to choose if "estimated time" column is shown in time report

	Since 2.6.1.1
	----------------
	bugfix: refresh sharing table when root permissions are updated.
	bugfix: dont call mb_string library functions if mb_string is not installed
	
	Since 2.6.1
	----------------
	bugfix: permissions in attachments when not classified
	feature: add 'workspaces' column to cvs export of total tasks times report

	Since 2.6.0.2
	----------------
	bugfix: disabled users are not loaded in tasks list.
	bugfix: add clients and projects column to total tasks times csv export.
		
	Since 2.6.0.1
	----------------
	bugfix: gantt crashes in ie.
	bugfix: function add_file_form_multi() doesn't exist, it should be add_file_from_multi().
	bugfix: default contact config options with wrong values after upgrade.
	
	Since 2.6
	----------------
	bugfix: task dependencies in templates did not show up correctly.
	bugfix: in calendar view the time line has a gmt error.
	bugfix: task quick add/edit alignment.
	bugfix: importing a contact csv does not send context when sending the file and permission query fails.
	bugfix: js error in save as button for documents, when comments are mandatory.
	
	Since 2.6-rc
	----------------
	bugfix: report date parameters are not formatted correctly the first time if they are not saved in contact config options
	
	
	Since 2.6-beta
	----------------
	
	bugfix: after contact csv inport, msg says "Company NULL" if contact has no comapany
	bugfix: templates import tasks, milestones (and several templates bugs)
	bugfix: activity widget fixed
	bugfix: email plugin upgrade script fixed
	bugfix: missing langs in advanced reports (es_es, es_la)
	bugfix: don't add the object to personal workspace if no workspace is selected
	bugfix: city is not imported if contact has no address when importing companies or contacts from csv.
	bugfix: if config option 'checkout_for_editing_online' is enabled don't show button to upload new revision when editing a document.
	bugfix: cannot link objects when editing tasks
	bugfix: after adding previous tasks in a template task the icon is broken
	bugfix: several fixes in templates and tasks dependencies.
	bugfix: do not notify my self quick add/edit tasks
	bugfix: if gantt plugin is installed there are restrictions with start date in task dependencies.
	bugfix: delete task dependencies after delete task.
	bugfix: don't show trashed or archived milestones in milestone combo.
	bugfix: performance in activity widget for members query
	bugfix: company name in person view is not a link to the company

	
	Since 2.5.1.5
	----------------
	
	feature: easier and faster way to classify tasks from the quick add/edit view
	feature: easier way to manage timeslots
	feature: multiple tasks drag and drop
	feature: custom properties can be added for Projects, Clients and other dimension members in the Professional Edition
	feature: Projects, Clients (and other dimensions) can now be colour coded
	feature: IMAP support has been developed
	feature: listings now show may show the breadcrumbs (workspace, client, project, etc.) in separate columns
	feature: templates now support subscribing users to the tasks, and take them into account
	feature: config option to select which milestones are shown in milestone selectors 
	 
	perfomance: cleaner load process
	performance: improved for most widgets
	 
	translations update: fr_fr, fr_ca, nb_no, tr_tr
	 
	bugfix: cannot print report when report title has a "'", it exports to csv
	bugfix: when saving clients using the complete form and permissions are saved in background the creator user is not associated
	bugfix: when note description is html and has no enters text overflow is visible and goes over other html, must break word.
	bugfix: search results shows html content and must show only text
	bugfix: when viewing tasks or notes with big font, the lines overlap
	bugfix: when deleting a Google calendar, events and invitations were not deleted correctly
	bugfix: import deleted external events (and try to delete in server again)
	bugfix: editing a user from contact tab redirected to edit company
	bugfix: permissions group were not working correctly for contacts
	bugfix: import companies from .csv file error
	bugfix: marking as read a document from an email resulted in an error
	bugfix: mails pagination bug
	bugfix: not all config options were set
	bugfix: show feng version for all users
	bugfix: tasks subscribers were receiving notifications even though they were not supposed to (check was disabled)
	bugfix: clients list fixes, view all links fixed in widgets
	bugfix: client, project, folder and workspace widgets fixed
	bugfix: client widget doesn't show all clients
	bugfix: in contacts tab users are not shown by default
	bugfix: assignees comboboxes selects and replace the input when it finds any match
	bugfix: dimension config handler alignments
	bugfix: when a document is blocked, it was not showing the right username
	bugfix: if a link href contains character "#" it is cut if sent in an email or in the html description of a task (e.g. feng wiki links)
	bugfix: other users can edit file properties of a checked out file
	bugfix: workspace quick add does not inherit color
	bugfix: tasks disappear when grouping by workspace and adding a new workspace
	bugfix: dont hide selected members in breadcrumbs
	bugfix: when attaching files to send mails
	bugfix: custom properties values are 256 chars length, now they have been changed to Text
	bugfix: several "undefined variable" fixes
	bugfix: missing lang when classifying emails (for "es_es" languages)
	bugfix: non utf-8 characters in custom reports produces an error when rendering
	bugfix: some folder icons are shown as '?' in activity widget.
	bugfix: file quick-add doesn't change the file modification date if it is a new revision. 
	bugfix: file quick-add doesn't create a log entry. 
	bugfix: file quick-add doesn't notify subscribers.
	bugfix: check if exec is available before using it in FileRepository
	bugfix: when loading lots of tasks and changing workspace before loading ends, two error messages appear.
	bugfix: after deleting a folder the top breadcrumb is not refreshed correctly
	
	
	Since 2.5.1.4
	----------------
	bugfix: Import companies from .csv file error
	bugfix: can't add subscribers in template task.
	
	
	Since 2.5.1.3
	----------------
	bugfix: when viewing a note or task and text is html and has no enters, text overflow is visible and goes over other html, it should break word.
	bugfix: search results shows html content and should show only text
	bugfix: application header default color changed to white
	bugfix: member tree and general breadcrumb are not reloaded correctly when adding a person
	bugfix: if a new member is added it is not added in real time to member selectors
	bugfix: in contacts tab users are not shown by default
	bugfix: performance in workspace, clients, projects and folders widgets
	bugfix: use 'exec' function only if it is enabled in the environment
	
	usability: put object breadcrumbs to the right
	
	language update: fr_ca
	
	Since 2.5.1.2
	----------------
	bugfix: When edit timeslots show all members.
	bugfix: Error occurs sometimes when attaching files to send mails.
	bugfix: Several "undefined variable" fixes.
	bugfix: Non utf-8 characters in custom reports produces an error when rendering.
	bugfix: After deleting a folder the top breadcrumb is not refreshed correctly.
	bugfix: Missing langs in document report columns.
	bugfix: When classifying emails a new revision is generated always for every attachment.
	bugfix: Attribute title not escaped in reports when renedering object links.
	bugfix: Workspace selectors are not preloaded in time panel.
	bugfix: add web document  fail if have http in the url.
	bugfix: add web document  fail if have http in the url.
	bugfix: search query problem with members.
	
	performance: When classifying emails classify attachments in background.
	
	Since 2.5.1.1
	----------------
	bugfix: gantt subtasks of subtasks not displayed.
	bugfix: mysql error when edit mail account.
	bugfix: search on dimensions doesn't work with files. 
	bugfix: getDaysLate and getDayLeft tasks functions fixed.
	bugfix: delete repeat number of times if "This Day Only" is selected on repeatinig tasks.
	bugfix: fo_ prefix table hardcoded.
	bugfix: templates errors when edit tasks.
	bugfix: milestones problems on template task.
	bugfix: can't add milestones in template task from edit.
	bugfix: mail plugin update failed if column conversation_last exists.
	bugfix: task dates are not shown with user timezone in custom reports.
	bugfix: logged user is not subscribed when uploading a file in the object picker (linked objects).
	bugfix: tasks widget shows tasks 1 day earlier for some user timezones > 0.
	bugfix: reload view (to show checkout information) when download and checkout in the same request.
	bugfix: in extjs when reloading combos.
	bugfix: if no member selected, in total tasks times report parameters, show all users.
	bugfix: separate transactions for saving user permissions.
	bugfix: multiple file upload enabled when uploading a new version.
	
	Since 2.5.1
	----------------
	bugfix: Cannot edit unclassified timeslot.
	bugfix: Bug when classifying email and attachments has no name.
	bugfix: Add weblinks in documents panel.
	bugfix: French langs fixed.
	bugfix: Total task times performance improvements.
	bugfix: Message changed when trying to add objects in root with no permissions.
	bugfix: If member name contains "'" advanced reports are broken.
	bugfix: Allow managers and administrators to add reports in root even if the don't have permissions to add reports in root.
	bugfix: Wrong date on activity widget if there are several changes on an object.
	bugfix: Selecting contact in contact tab must not filter by the contact dimension member.
	bugfix: Users and contact have the same icon.
	bugfix: Breadcrumbs are not shown for users.
	bugfix: Encoding fixed when saving files to filesystem.
	bugfix: When changing dimension member parent, breadcrumbs are not reloaded correctly.
	bugfix: Performance improvements in activity widget.
	bugfix: Cannot delete task description from quick-edit.
	
	feature: Add 'can_manage_tasks' permission to executive users.
	feature: Use "on duplicate key update" in DataObject insert queries, if specified.
	
	
	Since 2.5.1-rc
	----------------
	bugfix: can't view object link in notification when an email does not have a subject.
	
	
	Since 2.5.1-beta
	----------------
	bugfix: cannot add milestones in templates
	bugfix: when adding template, after adding milestone cannot select it when adding a task
	
	
	Since 2.5.0.6
	----------------
	bugfix: Template view broken by single quote in property name.
	bugfix: when edit a template if have milestones you can't see tasks.
	bugfix: don't show members that cannot be used in member selector.
	
	feature: Dashboards can be customized by user, and so can their widgets.
	
	Since 2.5.0.5
	----------------
	bugfix: Tasks grouping by dimension fixed.
	
	
	Since 2.5.0.4
	----------------
	performance: Issue when loading persons dim.
	bugfix: Imap folders are not saved when editing an email account.
	bugfix: Cannot unclassify mails from classify form.
	bugfix: Emessage not shown when inputing dates with incorrect format.
	bugfix: Add start date to task view.
	bugfix: Get tasks by range query does not include logged user's timezone.
	bugfix: In task complete edition form, assigned to are not displayed correctly.
	bugfix: Issue in include myself in document notifications.
	bugfix: Set db charset when reconnecting in abstract db adapter.
	
	
	Since 2.5.0.3
	----------------
	bugfix: Add attachments column in queued_emails in upgrade scripts.
	bugfix: Set db charset when reconnecting in abstract db adapter.
	
	
	Since 2.5.0.2
	----------------
	bugfix: Render member selectors with preloaded member info.
	bugfix: Order by name doesn't work on object list.
	bugfix: People widget only display users.
	
	
	Since 2.5.0.1
	----------------
	bugfix: on mysql 5.6 have_innodb variable is deprecated
	
	
	Since 2.5
	----------------
	
	feature: Allow to configure dashboard widget position and order for each user.
	feature: Allow to configure default dashboard widget position and order for all users.
	feature: Comments dashboard widget.
	feature: Email dashboard widget.
	feature: choose to filter calendar widget or not.
	feature: choose the user to filter the tasks widget.
	
	bugfix: when add a timeslot by clock on tasks update the percent complete.
	bugfix: if a file doesn't have revision when classify create one.
	bugfix: several minor fixes of undefined variables, missing langs, etc.
	bugfix: when disabling or reactivating users from company view, users list is not reloaded.
	bugfix: member selector displayed wrong data
	bugfix: on task add/edit view, assignee combo displayed wrong data
	bugfix: subscribers and invited people were not shown correctly
	bugfix: encoding when receiving emails
	bugfix: when editing a classified timeslot, its context was not shown
	bugfix: in file upload, the name is not changed if a new name is entered
	bugfix: missing langs and sql changes for email user config options
	
	
	Since 2.5-rc
	----------------
	
	bugfix: general search form submitted by enter key doesn't work in Google Chrome
	bugfix: links are now saved as such when using WYSIWYG
	bugfix: primary-breadcrumb show exact context
	bugfix: mysql transaction problem when sending emails without using a cronjob
	bugfix: when making a new installation, users were not shown by default
	
	
	Since 2.5-beta
	----------------
	
	bugfix: if a file doesn't have a revision, when classifying an email create one.
    bugfix: when adding a timeslot by clock on tasks, task progress bar was not updated correctly.
    bugfix: fixed custom reports using boolean conditions in false.
    bugfix: problems with paging on the overview list.
    bugfix: on activity widget, when clicking on a member, change dimension.
	
	Since 2.4.0.6
	----------------
	
	plugin: new Advanced Reports plugin for the Professional and Enterprise Edition

	feature: multiple files upload support has been added
	feature: cleaner reports tab
	feature: time reports can be filtered by custom properties
	feature: document notification improvements: allow notifying oneself, changing the default subject, choose whether to attach the document to its notification
	fetaure: unclassify objects dragging them to the "view all" node of a dimension tree
	feature: file revisions are easier to access
	feature: new button to add a template from the task tab
	
	security: XSS issue prevention fixed in login form
	
	performance: mail autocomplete, when contacts > 1000 don't load them all, make a query filtering when user begins to type (after 3 chars)
	performance: subscribers are rendered using ajax in add/edit forms
	performance: member selectors loaded using ajax in add/edit forms 
	performance: render subscribers queries optimized (index added) 
	performance: add index by member_id in contact_member_permissions in upgrade scripts
	perfomance: permissions checks in the advanced search take less time so the search is faster
	perfomance: permissions checks in the general search take less time so the search is faster
	perfomance: context checks in the general search take less time so the search is faster
	perfomance: when listing objects, permissions check query has been improved
	
	bugfix: remember columns selection in mail list
	bugfix: set 777 permission to autoloader to prevent future issues
	bugfix: drag and drop on mails panel don't uncheck selected mails
	bugfix: when instantiating a template, create logs for its tasks
	bugfix: on contact list, ordering by email failed
	bugfix: mysql transaction problem when getting emails
	bugfix: on template view, refresh view after editing a template task
	bugfix: when selecting a parent task in template tasks, only show template tasks from that template
	bugfix: repeating tasks don't allow selecting which days to repeat unless using specific repetition
	bugfix: attachments that start with "#" are not sent
	bugfix: max-height for attachments div when composing an email
	bugfix: undefined variable in message controller
	bugfix: when loading advanced search view, displays search results for empty string search 
	bugfix: when using Google Chrome, advanced search submit button breaks
	bugfix: hardcoded table prefix "fo_" in 2.4 upgrade script
	bugfix: when creating a superadmin, system permissions are not set
	bugfix: being able to order by custom property on notes list
	bugfix: search button disabled until results get displayed
	bugfix: mails in outbox alert show archived emails, it should not
	bugfix: don't send reminders for trashed or archived objects
	bugfix: date custom properties default value does not use user's timezones
	bugfix: when editing an email account do not synchronize folders with the mail server until user chooses to do it
	bugfix: email error reporting hook, for log errors on notifications delivery
	bugfix: when instantiating a template, do not display companies in assign combo unless config option allows so
	bugfix: fails when adding subtasks from task view
	bugfix: sending notifications from inside create log function, breaks the mysql transactions
	
	
	Since 2.4.0.5
	----------------
	
	bugfix: Don't send notification when add mail.
	
	
	Since 2.4.0.4
	----------------
	
	bugfix: Deprecated functions usage.
	bugfix: Emtpy trash can was using a deprecated function with performance issues.
	bugfix: Missing parameters in function invocation.
	
	
	Since 2.4.0.3
	----------------
	
	bugfix: can't delete template task, permission denied.
	
	
	Since 2.4.0.2
	----------------
	
	bugfix: langs customer_folder and project_folder.
	bugfix: can't add contacts from mail.
	bugfix: on activity widget move action don't display.
	bugfix: when create user, notifications break mysql transaction.
	
	
	Since 2.4.0.1
	----------------
	
	bugfix: cron process to emtpy trash can does not delete members asociated to contacts.
	
	
	Since 2.4.0
	----------------
	
	bugfix: tab order fix in quick add task; 
	bugfix: issue when create a subtask from task view;
	
	
	Since 2.4-rc
	----------------
	
	fetaure: error message improved when upload limit is reached.
 
	bugfix: on gantt, names of the tasks were not displayed completely.
	bugfix: on gantt, the time estimation for tasks was not displayed correctly.
	bugfix: date custom properties default value does not use user's timezone.
	bugfix: on people widget add user combo is not ordered by name.
	bugfix: on activity widget dates have gmt errors.
	bugfix: general search allways search for empty string.
	bugfix: url files are not saved correctly when url is not absolute.
	bugfix: imap fetch fixed when last email does not exists in server.
	bugfix: only invite automatically the "filtered user" when adding a new event, not when editing an existing one.
	bugfix: variable member_deleted uninitialized in a cycle, maintains the value of previous iterations and fills the log warnings.
	bugfix: don't display group-mailer button if user doesn't have an email account.
	bugfix: allow mail rules for all incoming messages, useful for autoreplies.
	bugfix: the invitations of the events created on google calendar will have the same special ID of the event.
		
	
	Since 2.4-beta
	----------------
	
	feature: alert users if they have mails in the outbox
	feature: contact custom reports - added columns for address, phones, webpages and im.
	feature: display time estimation in time reports when grouping by tasks
	feature: config option to add default permissions to users when creating a member.
	
	system: upgrade Swift Mailer from version 4.0.6 to 5.0.1, this improves and solves some issues when sending emails with exchange servers
	
	bugfix: on user login when save timezone don't change the update_on value
	bugfix: solved an issue when editing a repetitive task and changing its previous repetition value
	bugfix: solved when editing a template task can't remove a dimension member
	bugfix: solved using repeating tasks when applying a template
	bugfix: on tasks and timeslots reports, if grouped by task it diplay milestones
	bugfix: allow the creation of templates in the root (View all)
	bugfix: Users are now shown by default in the People tab.
	bugfix: when printing the task list view, tasks now display their progress and estimation
	bugfix: on general search prevent multiple form submit.
	
	
	Since 2.3.2.1
	----------------
	
	feature: templates have been greatly improved: they have changed completely for good!
	feature: remember selection on total task execution time report
	feature: when sending an email, if a word containing attach is found and no attachment if found, it triggers an alert.
	feature: new user config option to set how many members are shown in breadcrumbs
	feature: update plugins after running upgrade from console.
	feature: add root permission when creating executive or superior users.
	feature: contact edit form has been improved
	
	bugfix: when uploading avatars, if it is .png and its size is smaller than 128x128 the image is not resized
	bugfix: when sending an mail, the sender is now subscribed to it
	bugfix: when adding a file from an email attachment, its now set to be created by the account owner
	bugfix: reporting pagination fixed 
	bugfix: custom reports, csv and pdf export only exports the active page..now it exports everything!
	bugfix: don't collapse task group after performing an action to the task when group is expanded.
	bugfix: email parsing removes enters and some emails were not shown correctly
	bugfix: people widget in french used to cause a syntax error
	bugfix: don't classify email in account's member if conversation is already classified.
	bugfix: task filtering by user has been improved: it loads faster and more accurate
	bugfix: the users selectbox for assignees has been improved: it loads faster and more accurate
	bugfix: check for "can manage contacts" in system permissions if column exists
	bugfix: email parsing does not fetch addresses when they are separated by semicolon
	bugfix: tasks assigned to filter doesn't filter correctly when logged user is an internal collaborator and users 	can add objects without classifying them.
	bugfix: search result pagination issue
	bugfix: search results ordered by date again
	bugfix: add to searchable objects failed for some emails
	bugfix: custom properties migration fix
	bugfix: feng 1 to feng 2 upgrade improved
	bugfix: style fixes in administration tabs
	bugfix: checkbox in contact tab now is working properly. initially it does not show the users
	bugfix: google calendar sync issue for events with over 100 chars has been solved
	bugfix: contact csv export fixed: when no contact is selected => export all contact csv export fixed
	bugfix: some undefined variables have been defined
	bugfix: some translations that were missing were added
	
	security: remove xss from request parameters
	
	performance: search engine has been greatly improved
	
	other: the search button is disabled until returns the search result
	other: when upgrading to 2.4 the completed tasks from feng 1 will change to 100% in completed percentage
	
	
	Since 2.3.2
	----------------
	
	bugfix: When creating members, do not assign permissions for all executives (or superior users) if member has a parent.
	
	
	Since 2.3.2-rc2
	----------------
	
	bugfix: Cannot filter overview by tag.
	bugfix: Tasks tooltip in calendar views shows description as html.
	bugfix: Permissions issue when editing and subscribing for non-admins for not classiffied objects.
	
	
	Since 2.3.2-rc
	----------------
	
	bugfix: Show can_manage_billing permission.
	bugfix: Missing lang on javascript langs. 
	bugfix: Javascript plugin langs are not loaded.
	bugfix: When requesting completed tasks for calendar month view, it does not filter by dates and calendar hangs if there are too much tasks.
	bugfix: Administration / dimensions does not show members for dimensions that don't define permissions.
	bugfix: Permissions fix when email module is not installed.
	bugfix: Company object type name fixed.
	bugfix: Try to reconect to database if not conected when executing a query (if connection is lost while performing other tasks).
	bugfix: When users cannot see other user's tasks they can view them using the search.
	bugfix: Group permissions not applied in assigned to combo (when adding or editing tasks).
	bugfix: Minor bugfixes in 1.7 -> 2.x upgrade.
	bugfix: Activity widget: logs for members (workspaces, etc.) were not displayed.
	bugfix: General search sql query improved.
	bugfix: Don't include context in the user edited notification.
	bugfix: Don't show worked hours if user doesn't have permissions for it.
	bugfix: Don't send archived mails.
	
	feature: Only administrators can change system permissions.
	feature: Users can change permissions of users of the same type (only dimension member permissions).
	feature: Set permissions to executive, manager and admins when creating a new member.
	
	
	Since 2.3.2-beta
	----------------
	
	bugfix: Archiving a submember does not archive its objects.
	bugfix: Error 500 when adding group.
	bugfix: Installer fixes.
	bugfix: Modified the insert in read objects for emails.
	bugfix: Minor bugfixes in document listing.
	bugfix: Sql error when $selected_columns ins an empty array in ContentDataObjects::listing() function
	bugfix: root permissions not set when installing new feng office.
	bugfix: Person report fixed when displaying email field.
	bugfix: contacts are always created when sending mails.
	bugfix: Tasks list milestone grouping fixed.
	
	preformance: Search query improved.
	performance: Insert/delete into sharing table 500 objects x query when saving user permissions.
	
	
	Since 2.3.1
	----------------
	
	bugfix: When ordering tasks and subtasks and grouping by some criterias.
	bugfix: ul and ol (list) on task description doesn't show number or bullet.
	bugfix: Don't update email filter options when reloading email list if they are not modified.
	bugfix: Eail polling only when browser tab is active.
	bugfix: Wait time interval to check an email account.
	bugfix: Session managment fix.
	bugfix: Workspaces widget to the left.
	bugfix: When creating workspace it is not selected if it isn't a root workspace.
	bugfix: Update objects when linking to others, from user_config_option to config_option.
	bugfix: Calendar dalily view puts other days tasks.
	bugfix: Fixes of undefined variables logged in cache/log.php.
	bugfix: Call popup reminders only from active browser tab.
	bugfix: Format date funcions did not use config option for format.
	bugfix: Username is not remembered when creating a new user.
	bugfix: People widget is not displayed.
	bugfix: When unzipping a file the name has the url first.
	bugfix: On Trashed Objects breadcrumbs are not displayed if members are archived.
	bugfix: When add a timespan on a task was always taking logged user id for billing.
	bugfix: Time zone bug on list task in a range of dates.
	bugfix: Last login not saved into data base.
	bugfix: Google calendar synchronization bug fixes.
	
	performance: Save permissions asyncronically when saving member to improve performance.
	
	feature: New login form.
	feature: Field "Is user" added for people custom reports.
	feature: Users permissions can be configured to leave objects unclassified and choose the users that can read/write/delete these objects.
	feature: Tasks view improved.
	feature: People widget improved.
	feature: Improved member panels loading.
	
	language: Several language updates.	
	
	Since 2.3.1-beta
	----------------
	feature: View Contacts direct url if config option is enabled.
	feature: The system now remembers whether you are displaying the Overview as dashboard or as list.
	    
	bugfix: Duplicate key inserting read objects solved.
	bugfix: When writing an email from email tab, bcc was always displayed.
	bugfix: Events report end date did not show the time, now they do.
	bugfix: Objects history was not displaying linked objects logs.
	bugfix: On task list when you filter by a range of dates permissions filtering were not applied.
	bugfix: Exchange compatibility option has been removed.
	bugfix: When listing tasks timezones were not being taken into account.
	bugfix: Last login field was not being updated.
	bugfix: Gantt chart was showing some tasks as completed when their percentage was over 100% and they were not completed
	bugfix: When adding a timeslot for someone else within a task, the billing value was not being taken into account
	bugfix: Gantt chart tasks resizing has been improved 
	
	Since 2.3
	----------------
	feature: In the contact panel you can choose contacts in order to send a group mail
	feature: New user config option, updating an object��������s date when it is linked to another object
	feature: Gantt sub tasks can be out of range of parent task dates.
	feature: Gantt chart and Task List can be filtered by period.
	feature: Comments are now displayed on Activity Widget.
	feature: Gantt Chart now displays tasks with only start or due date
	feature: archive/unarchive dimension members from administration->dimensions.
	feature: when uploading a file with the same name as another that has been uploaded by other user and you don't have permissions over it, don't show as if exists.
	feature: New Projects by default will start with �������good�����? status
	feature: Listing function does not use limit if start parameter is not specified
	feature: When adding a client/project the initial focus is on name
	 
	performance: tasks list performance has been greatly enhanced by loading the descriptions afterwards through Ajax
	performance: when saving members save permissions using an async request.
	 
	bugfix: Users invited to an event can view/edit their invitation on Google Calendars
	bugfix: Editing  e-mail accounts correctly by administrator or user with permissions
	bugfix: Export only visible contacts/companies in contact panel
	bugfix: User e-mail duplication upon creation
	bugfix: Completing  tasks with child tasks error
	bugfix: Contact who trashed a document now shown in history
	bugfix: Worked time was not always displayed.
	bugfix: There were empty logs in the Activity widget
	bugfix: Group by on tasks lists, subtasks displayed in wrong place.
	bugfix: Sort listings by custom properties(contact, document) .
	bugfix: Activity Widget broken on small screens.
	bugfix: Activity Widget time zone issue.
	bugfix: Custom property, escape commas.
	bugfix: Contact custom reports now show their email addresses
	bugfix: Search contacts by phone number, email , im and by address.
	bugfix: add_to_members when no permissions over parent .
	bugfix: Duplicate key when adding emails to searchable objects.
	bugfix: User with permissions to edit account cannot delete unclassified emails.
	bugfix: projects widget does not show projects.
	bugfix: Sql was not using "select distinct" on searchable objects().
	bugfix: add task dependency js error .
	bugfix: ObjectController::list_objects malformed sql error.
	bugfix: Now all users can sync Feng Calendar with Google Calendar.
	bugfix: Google Calendar is no longer trashing old events
	bugfix: Trash fails when mail plugin is not installed
	bugfix: Member selector fixed for IE
	bugfix: Some permissions were not set when adding new member
	bugfix: Creating reports of �������grouping by user�����? and �������members�����? at the same time issue fix
	bugfix: Report not showing correct Date in condition legend
	bugfix: SQL issue in Report fixed
	bugfix: Description not set for all tasks when listing.
	bugfix: Left menu expands after adding first client or project
	
	Since 2.3-rc
	----------------
	
	bugfix: Tasks don't show custom properties when printing one.
	bugfix: Rearranged task name and remove button in template edition to avoid overlapping.
	bugfix: Edit task sometimes opens new tab when saving.
	bugfix: Several report grouping bugfixes.
	bugfix: Advanced search by custom property doesn't find the object.
	bugfix: Classify weblinks by drag & drop does nothing.
	bugfix: Event notifications broken with large user names.
	bugfix: IE crashes when loading member trees.
	bugfix: Bugfix when timeslot has no user.
	bugfix: Export to csv, escaped commas and semicolons, improved time management.
	bugfix: Workspace and tags added for new event and task notifications.
	bugfix: Company logo is not displayed well on IE.
	bugfix: Breadcrumbs for the search results.
	bugfix: Improved task list grouping when grouping by date fields.
	bugfix: Overview calendar widget "view more" link broken.
	bugfix: Show main dashboard when clicking on "View all" of People dimension.
	bugfix: Show "save with new name" button after saving a new document.
	bugfix: Object picker pagination shows wrong total.
	bugfix: Several missing langs fixed (en_us, es_la).
	
	Since 2.3-beta
	----------------
	
	feature: Action prompt after workspace creation.
	feature: Advanced search improved.
	feature: Improved export to csv in total tasks time report.
	feature: People panel, move to trash button (only for companies without contacts and for contacts that are not users).
	feature: People panel, improved filter by type (users, companies and contacts as checkboxes).
	feature: Task and event reminders improved.
	feature: Double clicking a workspace takes you to the workspace edition form.
	feature: Custom reports can be ordered by external columns (e.g. milestones, assigned to, etc).
	feature: add/edit template - can specify milestone for each task.
	feature: Height adjustment document preview.
	feature: New buttons to add workspaces and other objects in dashboard widgets.	
	
	bugfix: When uploading files: detect file type from extension when browser sends 'application/x-unknown-application' as file type.
	bugfix: Add to searchable objects doesn't add special characters correctly.
	bugfix: read_objects insert query reimplementation.
	bugfix: Parent workspace not passed when adding workspace from widget button.
	bugfix: Duplicated config option in upgrade script.
	bugfix: Advanced search: sql security issues fixed.
	bugfix: Render "table custom properties" fixed when object has no values for the property.
	bugfix: Error in activity widget for some comments.
	bugfix: Plugin installer returns 'duplicate key' when executing it twice for the same plugin.
	bugfix: Login layout broken for some languages.
	bugfix: Add milestones for tasks in templates: when editing template milestones combos are unselected.
	bugfix: Prevent user deletion from object listings (dashboard).
	bugfix: Workspaces plugin update 4 -> 5 fixed.
	bugfix: Event reminders don't show event name in popup.
	bugfix: Emails addToSharingTable() fixed.
	bugfix: Custom reports malformed conditions when using boolean fields (e.g. is_company).
	bugfix: Search pagination fixed for advanced search results.
	bugfix: Changing assinged to in tasks edition sometimes does not show the notification checkbox.
	bugfix: Member selector fix when not is_multiple.
	bugfix: sql injection in advanced search.
	bugfix: Cannot upload files if no workspace or tag is selected.
	bugfix: Template instantiation does not puts the objects in original members if instantiation is made with no member selected.
	bugfix: Don't save email if cannot save the email file in the filesystem.
	bugfix: csv export fix when & and enters are present in task names or descriptions.
	bugfix: Tasks status filters fixced.
	bugfix: When the whole mail is an attachment, it is not shown.
	bugfix: Show users and people lists expanded in company view.
	bugfix: Object picker pagination shows wrong total.
	
	language: Several language updates.
	
	
	Since 2.2.4.1
	----------------
	
	bugfix: Add permissions over timeslots, reports and templates for user in user's person member when creating the user.
	bugfix: Assigned to combo does not show users when filering by tag.
	bugfix: First person added not shown in tree.
	bugfix: Add object_id to searchable objects.
	bugfix: Empty trash will try to delete deleted emails.
	bugfix: Trash can shows deleted emails.
	bugfix: Create email account gives permissions to it to other users.
	bugfix: Cannot add user if any dimension is required.
	bugfix: Comments are not added to sharing table.
	
	feature: Config option to enable assign tasks to companies.
	
		
	Since 2.2.4
	----------------
	
	bugfix: Add/edit member form permissions goes down if screen is not wide enough.
	bugfix: Member selector onblur must select one of the list if there is any match and there is at least one character written.
	bugfix: Object picker: do not show object types not allowed for the user in the left panel
	bugfix: D&D classify is allowing to classify in read only members.
	bugfix: Do not show parent members in member selector if user has no permissions over them.
	bugfix: Upgrade 1.7 -> 2.X: give permissions over timeslots, reports and templates in all workspaces where the user can manage tasks.
	bugfix: Non admin users cannot delete timeslots.
	
	feature: Can define required dimension without specifying object types.
	feature: Option to view members in a separate column.
	
	
	Since 2.2.4-beta
	----------------
	
	bugfix: Cannot delete user with no objects associated.
	bugfix: Javascript error when loading and change logo link does not exists.
	bugfix: plugin administration fixes.
	bugfix: Email content parts that come in attachments are not shown.
	bugfix: Tasks edition in gantt chart loses task description.
	bugfix: Adding client or project under another member does not remember selected parent when using quickadd and details button.
	
	feature: More options for tasks edition.
	feature: More options for composing emails.
	
	language: Languages updated: German, French, Japanese, Polski.
	
	
	Since 2.2.3.1
	-------------
	
	bugfix: Cannot add user without password if complex passwords are enabled.
	bugfix: Include ";" as metacharacter for complex password validations.
	bugfix: Member name is username when adding a contact (editing contact fixes member).
	bugfix: Change logo link does not work.
	bugfix: Repetitive tasks fix.
	bugfix: fo_ table prefix hardcoded one time.
	bugfix: Calendar tasks display fixed.
	bugfix: Always check if member can be deleted.
	bugfix: Cannot delete mail account with mails.
	bugfix: Add contact was checking if user has can_manage_security.
	bugfix: Cannot select parent member using checkboxes.
	bugfix: Error 500 in some notifications.
	bugfix: New client/project from overview fixed.
	bugfix: Breadcrumbs only show 2 members x dimension.
	bugfix: Total tasks time reports csv export does not work.
	bugfix: Fix en c������lculo de porcentaje de avance de tareas.
	bugfix: Forwarding or replying mails in German only prints "null".
	bugfix: Function getCustomPropertyByName fixed.
	bugfix: Activity widget popup wider to put all buttons in one line.
	bugfix: Users in assign_to combo are not ordered.
	bugfix: 1.7 -> 2.x upgrade does not create table mail_spam_filters.
	bugfix: Tags are lost when dragging a task to another workspace.
	
	performance: Delete account emails performance and memory usage improvements.
	
	feature: Compose mail get contacts by ajax.
	feature: Custom properties columns in documents tab.
	feature: No breadcrumbs for users in activity widget.
	feature: Ckeditor option added: remove html format.
	
	language: Deutch, Russian, Ukranian, Portuguese and Indonesian language updates.
	language: Several language fixes.
	
	
	Since 2.2.3.1-beta
	------------------
	bugfix: Search in a member does not find file contents.
	bugfix: Click on "search everywhere" does not find file contents.
	bugfix: Groups listed alphabetically in the Administration Panel.
	bugfix: Monthly view calendar print shows empty calendar.
	bugfix: Improvements in performance of overview widgets.
	bugfix: Timeslots are not reclassified reclassifying tasks.
	bugfix: Cannot delete members if it has objects.
	bugfix: Member deletion does not clean all related tables.
	bugfix: Only managers or superior roles can change other user passwords.
	bugfix: Several missing langs and undefined variables warnings clean.
	bugfix: Db error when adding two workspaces with the same name.
	bugfix: Quick add files - all radio buttons can be selected.
	
	system: Russian translations updated.
	
	
	Since 2.2.2
	----------------
	bugfix: Owner company cannot be classified.
	bugfix: Task list group by user fix.
	bugfix: Add pdf and docx files to searchable objects.
	bugfix: js managers bugfixes.
	bugfix: Cannot edit/delete mails from deleted accounts.
	bugfix: Error in tasks reports when ordering by 'order' column.
	bugfix: Fixes in migration from 1.X of custom properties.
	
	usability: Reports can be edited to allow execution in every context.
	usability: Performance improved in tasks list.
	usability: Users are filtered by permissions in 'People' dimension when filtering by a workspace.
	usability: Contacts are filtered in 'People' dimension when filtering by a workspace if they belong to the workspace.
	
	system: Portuguese language updated.
	
	
	Since 2.2.1
	----------------
	bugfix: logged_user fix when classifying attachments
	bugfix: go back instead of redirect when editing file properties.
	bugfix: chmod after mkdir when repository file backend creates directory
	bugfix: Several template instatiation fixes
	bugfix: mail classification bugfix
	bugfix: allow to classify mails in workspaces,tags
	bugfix: administration/users: 10 users per page fix
	bugfix: do not use objects in estimated-worked widget, use raw data for better performance
	bugfix: language fixes
	bugfix: cannot use assigned_to combo when adding tasks in ie
	bugfix: ie compatibility fix in calendar toolbars
	bugfix: enable/disable cron events for calendar export/import when adding/deleting accounts
	bugfix: html tags in task tooltip description at calendar
	bugfix: cvs export prints html tags
	bugfix: users with can_manage_security cannot manage groups
	bugfix: view week calendar views don't show tasks all days if task starts or ends in another week
	bugfix: dont show timeslots of other users if cannot see assigned to other's tasks
	bugfix: ext buttons hechos a lo chancho
	bugfix: patch if not exists function array_fill_keys (para php < 5.2)
	bugfix: break large words in task description
	bugfix: administrator cannot log in to admin panel when asking for credentials
	bugfix: cannot edit file after uploaded from object picker
	bugfix: getTimeValue when 12:XX AM
	bugfix: bugfix in custom reports with boolean conditions on custom properties
	bugfix: admin users paging fix
	bugfix: migration companies comments fix

	
	
	Since 2.2.1-rc
	----------------
	bugfix: Cannot manage plugins if no super admin.
	bugfix: Reports were not grouping unclassified objects.
	bugfix: Reports grouping misses a group.
	bugfix: Fixed findById function in ContentDataObjects.
	bugfix: Fixed Email plugin installation.
	bugfix: Fixed translations for dimension names.
	bugfix: Error with company logo when sending notifications.
	bugfix: Time report fix when selecting custom dates and listing paused timeslots.
	bugfix: Fix when getting plugin's javascript translations.
	
	
	Since 2.2
	----------------
	bugfix: Calendar monthly view bugs with repeating events.
	bugfix: Permissions system fix.
	bugfix: Projects appear in object picker.
	bugfix: language fixes (en_us, es_la, es_es).
	bugfix: Error in calendar when user has timezone=0.
	bugfix: Formatted tasks description and notes content does not shows italics and quotes when viewing.
	bugfix: Compressing files does not create compressed file in the current context.
	bugfix: Sometimes can subscribe users with no permissions to the object.
	bugfix: Activity widget bug with general timeslots.
	bugfix: Error when selecting default workspace in mail account edition.
	bugfix: User types are not transalted.
	bugfix: Prevent double generation of tasks when completing a repetitive task instance (double click on complete link).
	bugfix: CSV export fixes at Total tasks times report.
	
	usability: Create events according the filtered user.
	usability: Config option to show tab icons.
	usability: Config option to enable/disable milestones.
	
	
	Since 2.2-rc
    ----------------
    bugfix: calendar monthly view performance upgrades.
    bugfix: translation tool for plugins fixed.
    bugfix: email html signature puts br tags when composing email.
    bugfix: Person email modification does not work.
    bugfix: Prevent double task completion (when double clicking on complete link).
    bugfix: Fixed company edit link from people tree.
    
	
	Since 2.2-beta
	----------------
	bugfix: several fixes in custom reports display.
	bugfix: custom reports csv/pdf export always show status column.
	bugfix: dashboard activity widget does not control permissions correctly.
	bugfix: dashboard activity widget shows username instead of person complete name.
	bugfix: subworkspace creation does not inherit color.
	bugfix: email autoclassification does not classify attachments.
	bugfix: email view shows wrong "To" value when "To" field is empty or undefined.
	bugfix: unclassified mails allows to subscribe other users.
	bugfix: error when forwarding another user's account emails with attachments.
	bugfix: several fixes in email classification functions.
	bugfix: company comments are not displayed.
	bugfix: dashboard's tasks widget breaks right widgets when scrolling (only in chrome).
	bugfix: permissions check in Administration/Dimensions.
	bugfix: css is being printed in csv exported reports.
	bugfix: error subscribing users when instantiating templates with milestones and subtasks.
	bugfix: don't use $this in static functions.
	bugfix: archiving and unarchiving members is not done in a transaction.
	bugfix: permissions in dimension member selectors.
	bugfix: cannot set task's due date to 12:30 PM, always sets the same time but AM.
	bugfix: tasks drag and drop losses some attributes.
	
	usability: mouseover highlight on member properties/restrictions tables.
	
	
	Since 2.1
	----------------
	bugfix: several fixes in repetitive tasks.
	bugfix: quick add of tasks does not subscribe creator.
	bugfix: google calendar import fixed.
	bugfix: fixed event deletion.
	bugfix: fixed email account sharing.
	bugfix: fixed AM/PM issue when selecting task's dates.
	bugfix: special characters in workspace when adding from quick add.
	bugfix: error 500 in workspaces dashboard.
	bugfix: error when searching emails by "From" field in advanced search.
	bugfix: 1.7 -> 2.x upgrade fixed subtasks.
	bugfix: permissions in user's card.
	bugfix: task's drag and drop edition bugfixes.
	bugfix: task's quick add does not keep the task name when switching to complete edition.
	bugfix: several LDAP integration fixes.
	bugfix: fixed contact phones display in list.
	bugfix: config option descriptions added.
	bugfix: user email is not required.
	bugfix: milestone selector does not show all available milestones.
	bugfix: person email cannot be edited.
	bugfix: disabled users are shown in subscribers and invited people.
	bugfix: permission groups upgrade does not set type.
	bugfix: Javascript problems in IE.
	bugfix: issues with breadcrumbs with special characters.
	bugfix: VCard import/export fixed.
	bugfix: cannot delete workspace with apostrophe.
	bugfix: fixed "enters" issue in tasks description wysisyg editor.
	bugfix: File copy makes two copies.
	bugfix: permissions fixed for submembers.
	bugfix: when updating a file, does not subscribe the updater user.
	bugfix: milestones display diferent dates in milestone view and task list.
	bugfix: "assigned to" filter in tasks does not work properly.
	bugfix: cannot archive dimension members.
	bugfix: cannot archive several tasks at once.

	feature: activity widget.
	feature: new workspace and tag selectors.
	feature: add timeslot entries to application_logs.
	feature: complete parent tasks asks to complete child tasks.

	usability: sort email panel by "to" column.
	usability: changes in advanced search for email fields.
	usability: can change imported calendar names.
	usability: email with attachments classification process upgraded.
	usability: linked objects selector can filter by workspace and tags.

	system: CKEditor updated.
	system: translation module upgraded - translate plugins files.
	system: German, Russian and French languages upgraded.

    
    Since 2.0.0.8
    ----------------
    bugfix: Google Calendar issues solved
	bugfix: 'Executive' users not being able to assign tasks to themseleves at some places
	bugfix: Admins and Superadmins may now unblock bloqued documents
	bugfix: Subscriptions and permissions issues solved
	bugfix: Solved some issues when editing objects
	bugfix: Solved issue when classifying emails and then accesing them
	bugfix: Solved issue when adding timeslots
	bugfix: Assigned to alphabetically ordered
	bugfix: Solved issue when editing email accounts
	bugfix: Custom properties were not appearing in weblinks
	bugfix: Solved issue when sending an email
	bugfix: Solved issue where Milestones were showing wrong data
	bugfix: File revisions were not being deleted
	bugfix: Timeslots were not able to be printed
	bugfix: Issues when retrieving passwords solved
	bugfix: Solved issue when deleting timeslots
	bugfix: Solved some permissions issues
	bugfix: Solved issue when adding pictures to documents
	bugfix: Solved issues with paginations
	bugfix: Solved some compatibility issues with IE
	bugfix: People profiles can be closed
	bugfix: Trashed emails not being sent
	bugfix: Repetitive tasks issues solved
	bugfix: Solved workspace quick add issue
	bugfix: Dimension members are now searchable
	 
	usability: Sent mails synchronization reintroduced
	usability: Selecting if repetitive events should be repeated on weekends or workdays
	usability: Templates now take into account custom properties
	usability: Dimension members filtering improvements
	usability: New & improved notifications
	usability: Adavanced search feature
	usability: Added quick task edition, and improved quick task addition
	usability: Improvements when linking objects
	usability: Improvements in task dependencies
	usability: Warning when uploading different file
	usability: Google Docs compatibility through weblinks
	usability: Improved the templates usability
	usability: Workspace widget introduced
	usability: Improvement with estimated time in reports
	usability: Added estimated time information in tasks list
	usability: Deletion from dimension member edition
	usability: Archiving dimension members funciton introduced
	usability: File extension prevention upload
	usability: WYSIWYG text feature for tasks descriptions and notes
	usability: View as list/panel feature reintroduced
	usability: .odt and .fodt files indexing
	 
	system: Improved upgrade procedure
	system: Improved the sharing table
	system: Improved performance when checking emails through IMAP
	system: Improved performance within tasks list
	system: Improved performance when accessing 'Users'
	system: Improved performance with ws colours
	system: Improved performance when loading permissions and dimensions
	system: Improvements within the Plugin system
	system: Major performance improvements at the framework level
	    

	Since 2.0 RC 1
	----------------
	bugfix: Uploading files fom CKEditor.
	bugfix: Some data was not save creating a company.
	bugfix: Error produced from documents tab - New Presentation.
	bugfix: Problems with task dates in some views.
	bugfix: Fatal error when you post a comment on a task page.
	bugfix: Generation of task repetitions in new tasks.
	bugfix: Do not let assign tasks (via drag & drop) to users that doesn't have permissions.
	usability: Interface localization improvements.
	system: Performance improvements.


	Since 2.0 Beta 4
	----------------
	bugfix: Extracted files categorization
	bugfix: When adding workspaces
	bugfix: Breadcrumbs were not working fine all the time 
	bugfix: Being able to zip/unzip files
	security: JS Injection Slimey Fix
	system: .pdf and .docx files contents search
	system: Improvement when creating a new user
	system: Plugin update engine
	system: Plugin manager console mode 
	system: Search in file revisions
	system: Import/Export contacts available again
	system: Import/Export events available again
	system: Google Calendar Sync 	
	system: Improvement on repeating events and tasks
	system: Cache compatibility (i.e.: with APC)
	usability: Completing a task closes its timeslots
	usability: Task progress bar working along the timeslots
	usability: Being able to change permissions in workspaces when editing
	
	
	Since 2.0 Beta 3
	----------------
	
	bugfix: Several changes in the permissions system
	bugfix: Invalid sql queries fixed
	bugfix: Issues with archived and trashed objects solved
	bugfix: Issues with sharing table solved
	bugfix: Improved IE7 and IE9 compatibility
	bugfix: Several timeslots issues solved
	bugfix: IMAP issue solved at Emails module
	bugfix: Solved issue with templates
	bugfix: Added missing tooltips at calendar 
	bugfix: Issue when completing repetitive task solved
	bugfix: Solved some issues with the Search engine
	bugfix: Solved issue with timezone autodetection
	buffix: Solved 'dimension dnx' error creating a workspace
	usability: Permission control in member forms
	usability: Disabling a user feature
	usability: Resfresh overview panel after quick add
	usability: Langs update/improvement
	usability: Drag & Drop feature added 	
	usability: Quick add task added, and improved
	usability: Slight improvement with notifications
	usability: Avoid double click at Search button (which caused performance issues)
	usability: Permissions by group feature added
	usability: Simple billing feature added
	system: Security Fixes
	system: Mail compatibility improved for different email clients 	 
	system: Feng 2 API updated
	system: General code cleanup
	system: Widget Engine
	system: Performance improvements in custom reports
	system: Print calendar
	system: Custom Properties

	Since 2.0 Beta 2
	----------------
	bugfix: Fixed problem uncompressing files
	bugfix: Loading indicator hidden
	bugfix: Search in mail contents
	bugfix: Mail reply js error
	bugfix: Filter members associated with deleted objects
	bugfix: Fixed permission error creating a contact
	usability: Contact View Improvements
	usability: Navigation Improvements
	system: Permission system fixes
	system: Performance issues solved. Using permission cache 'sharing table' for listing
	system: Weblinks module migrated
	
	
	Since 2.0 Beta 1
	----------------

	bugfix: Fixed problem with context trees when editing content objects
	bugfix: Fixed template listing
	bugfix: Fixed issues when instantiating templates with milestones
	bugfix: Fixed issue deleting users from 'people' and 'users' dimension.
	bugfix: Fixed 'core_dimensions' installer
	bugfix: Z-Index fixed in object-picker and header
	usability: Selected rows style in object picker
	system: General code cleanup
	
	
	Since 1.7
	-----------
	
	system: Plugin Support
	system: Search Engine performance improved
	system: Multiple Dimensions - 'Workspaces' and 'Tags' generalization
	system: Database and Models structure changes - Each Content object identified by unique id 
	system: Email removed from core (Available as a plugin)
	system: User Profile System
	feature: PDF Quick View - View uploaded PDF's
	usability: Default Theme improved
	usability: Customizable User Interface
	
