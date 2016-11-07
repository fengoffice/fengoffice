
	About Feng Office 3.2.2.1
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
	
<a href="https://docs.megam.io" target="_blank">
<img src="https://s3-ap-southeast-1.amazonaws.com/megampub/images/vertice/DEPLOY-TO-MEGAM-VERTICE2.png" alt="wordpres button" />
</a>

	
	System requirements
	===================
	
	Feng Office requires a running Web Server, PHP (5.0 or greater) and MySQL (InnoDB
	support recommended). The recommended Web Server is Apache.
	
	Feng Office is not PHP4 compatible and it will not run on PHP versions prior
	to PHP 5.
	
	Recommendations:
	
	PHP 5.2+
	MySQL 5+ with InnoDB support
	Apache 2.0+
	
		* PHP    : http://www.php.net/
		* MySQL  : http://www.mysql.com/
		* Apache : http://www.apache.org/
	
	Please have a look at our requirements here:
	http://www.fengoffice.com/web/wiki/doku.php/installation:server_reqs
	
	Alternatively, if you just want to test Feng Office and you don't care about security
	issues with your files, you can download XAMPP, which includes all that is needed
	by Feng Office (Apache, PHP 5, MySQL) in a single download.
	You can configure MySQL to support InnoDB by commenting or removing
	the line 'skip-innodb' in the file '<INSTALL_DIR>/etc/my.cnf'.
	
		* XAMPP  : http://www.apachefriends.org/en/xampp


	Installation
	============
	
	1. Download Feng Office - http://fengoffice.com/web/community/
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
	1- From 2.X to 2.X (or from 1.X to 1.X)
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
	- Slimey - http://slimey.sourceforge.net
	- FCKEditor - http://www.fckeditor.net
	- JSSoundKit - http://jssoundkit.sourceforge.net
	- PEAR - http://pear.php.net
	- Gelsheet - http://www.gelsheet.org
	
	
	Changelog
	=========
	
	Since 3.2.2
	----------------
	bugfix: Superadmin cannot edit other superadmins.
	
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
	
