<?php
Hook::register("fengoffice");

/* List of available hooks:
 * 
 * - after_delete: 
 * 		- $object
 * 		- $unused
 * 
 * - before_delete: 
 * 		- $object
 * 		- $unused
 * 
 * - after_save: 
 * 		- $object
 * 		- $unused
 * 
 * - before_save: 
 * 		- $object
 * 		- $unused
 * 
 * - after_update: 
 * 		- $object
 * 		- $unused
 * 
 * - before_delete: 
 * 		- $object
 * 		- $unused
 * 
 * 
 * - render_page_actions: Called when drawing actions for an object's view. Call add_page_action to add actions.
 *  	- $object : ProjectDataObject,
 *  		Object that is being viewed.
 *   	- &$unused
 *   
 * - render_page_header: Called when drawing the page header.
 *  	- $unused
 *   	- &$unused
 * 
 * - render_page_footer: Called when drawing the page footer.
 *  	- $unused
 *   	- &$unused
 *   
 * - render_getting_started: Add additional getting started help.
 * 		- $unused
 * 		- &$unused
 *
 * - render_object_properties: Called when drawing properties for an object's view. Echo the HTML to be drawn.
 *  	- $object : ProjectDataObject,
 *  		Object that is being viewed.
 *   	- &$unused
 * 
 * - reminder_email: Called when an email reminder is being sent.
 *  	- $reminder : ObjectReminder,
 *   	- &$count : integer. Add to this number.
 *   		Count of reminders sent.
 *   
 * - render_userbox_crumbs: Called when drawing the userbox (top-right of the page).
 *  	- $ignored,
 *   	- &$crumbs : array of assoc. Add to this array.
 *   		- url,
 *   			URL to open.
 *   		- text,
 *   			Text to be displayed on the crumb.
 *   		- target
 *   			Panel where the link will be opened (or _blank for new page).
 *   
 * - render_help_options: Called when drawing the help panel.
 *  	- $ignored,
 *   	- &$help_options : array of assoc. Add to this array.
 *   		- url,
 *   			URL to open.
 *   		- title,
 *   			Title to be displayed on the option.
 *   		- desc,
 *   			Optional desc to explain what the option does.
 *   		- target
 *   			Panel where the link will be opened (or _blank for new page).
 *   
 * - email_base_css: Called to allow altering email styling when composing an email. Append
 *   CSS rules to the $css argument to alter how email is displayed and sent on HTML emails.
 *   For example, to change font size and color: $css .= 'font-size: 12px; color: red';
 *  	- $ignored,
 *  	- &$css : CSS string to apply to the email's body. 
 * 
 * - autoload_javascripts: Tells which javascripts should be load when the application starts.
 *  	- $ignored,
 *  	- &$js : array of javascript urls to load. Add to this array.
 *  
 * - autoload_stylesheets: Tells which CSSs should be loaded when the application starts.
 *  	- $ignored,
 *  	- &$css : array of CSS urls to load
 *  
 * - render_administration_icons: Called when drawing administration panel.
 *  	- $ignored,
 *  	- &$icons : array of assoc. Add to this array.
 *  		- ico,
 *  			CSS class that has a background image of 48x48 pixels.
 *  		- url,
 *  			Url to open.
 *  		- name
 *  			Name of the panel option.
 *  		- extra
 *  			Any extra HTML to be added below the icon (like add actions).
 *  
 * - object_definition: Allows to define extra columns for a system object.
 *  	- $type : string
 *  		Type of object (e.g. ProjectFile),
 *  	- &$columns : assoc of database columns to add to the object (column_name => column_type). Add to this array.
 *  
 * - add_user_permissions: Allows to define permissions for objects.
 * 		Must be combined with 'object_definition' hook, to add the columns to users table.
 * 		- $ignored
 *  	- &$permissions : array of assoc
 *  		Name for the permission (e.g. can_drink_coffee): value of the permission at users table (boolean)
 *  
 * - render_object_description: Called when rendering the description that goes below the title in an object's view.
 *  	- $object,
 *  	- &$description : string
 *  		Html description that will be shown on the object's view. Append to this string.
 *  
 * - permissions_sql: Called when generating the SQL permissions string for object listings.
 *  	- $args : assoc of arguments to permissions_sql_for_listings function.
 *  		- 'manager',
 *  		- 'user',
 *  		- 'access_level',
 *  		- 'project_id',
 *  		- 'table_alias',
 *  	- &$sql : string. Add to this string. (e.g. $sql = "($sql OR `column` = 'value') AND NOT `column2` = 'value2'")
 *  
 * - can_access: Called before checking access permissions for an object.
 *  	- $args : assoc of arguments to can_access function.
 *  		- user
 *  		- object
 *  		- access_level
 *  	- &$can : bool
 *  			Put this value to false or true to determine beforehand the permissions for an object.
 *  			If this value is already false or true it means that another event handler has already determined the permissions.
 * - object_edit_categories: Called when rendering categories for an object creation or edition interface.
 *  	- $object : ApplicationDataObject
 *  		Object that is being added / edited
 *  	- &$categories : array of assoc
 *  		name: Name for the category (e.g. Description).
 * 			content: HTML content for the category (e.g. "<input type="text" name="file[desc]" value="Enter a desc" />)
 *  		visible: Whether the category will be expanded by default,
 *  		required: Whether it should be marked as required (red asterix after the name)
 *  
 *  - before_object_save: Called before saving an object, to be able to set some fields on the last minute.
 *   	- $object : DataObject,
 *   	- &$ignored
 *   
 *  - after_object_save: Called after saving an object.
 *   	- $object : DataObject,
 *   	- &$ignored
 *   
 *  - object_validate: Executed before saving an object to validate object fields.
 *  	- $object : DataObject,
 *  	- &$errors : array of strings. Error messages. Add to this array.
 *   
 *  - before_action: Called before executing an action to determine if the action will be called.
 *    For example, you can validate some request parameters and send an error message if they're not valid,
 *    and return false so that the action is not executed.
 *   	- $args : assoc
 *   		- controller: Controller instance.
 *   		- action: string. Action name.
 *   	- &$ret:
 *   		if set to false, the action will not be called.
 *
 *  - after_action: Called after executing an action.
 *   	- $args : assoc
 *   		- controller: Controller instance.
 *   		- action: string. Action name.
 *   	- &$ret:
 *   		if set to false, the action will not be called.
 *   
 *  - override_action_view: Called before generating an action's view, so that you can change it.
 *  	- $controller : PageController
 *  		You can call setTemplate and setLayout on this controller to change the rendered view.
 *  	- &$ignored  
 */

function fengoffice_reminder_email($reminder, &$ret) {
	$object = $reminder->getObject();
	if (!$object instanceof ContentDataObject || $object->isTrashed() || $object->isArchived()) return;
	$date = $object->getColumnValue($reminder->getContext());
	$valid = true;
	Hook::fire('validate_reminder_email', $reminder, $valid);
	if (!$valid) return;
	if ($reminder->getContext() == "due_date" && ($object instanceof ProjectTask || $object instanceof ProjectMilestone)) {
		if ($object->isCompleted() || $object->isTrashed()) {
			// don't send due date reminders for completed or trashed tasks
			if ($object instanceof ProjectTask && $object->isRepetitive()) {
				// if repetitive task then don't delete the reminder, but disable it
				$reminder->setDate(EMPTY_DATETIME);
				$reminder->save();
			} else {
				$reminder->delete();
			}
			return;
		}
	}
	if (!$date instanceof DateTimeValue) return; // skip reminders without a date
	if ($date->getTimestamp() + 24*60*60 < DateTimeValueLib::now()->getTimestamp()) {
		// don't send reminders older than a day
		if ($object instanceof ProjectTask && $object->isRepetitive()) {
			// if repetitive task then don't delete the reminder, but disable it
			$reminder->setDate(EMPTY_DATETIME);
			$reminder->save();
		} else {
			$reminder->delete();
		}
		throw new Exception("Reminder too old");
	}
	Notifier::objectReminder($reminder);
	if ($object instanceof ProjectTask && $object->isRepetitive()) {
		// if repetitive task then don't delete the reminder, but disable it
		$reminder->setDate(EMPTY_DATETIME);
		$reminder->save();
	} else {
		$reminder->delete();
	}
	$ret++;
}

function fengoffice_render_upload_control($args, &$ret) {
	if (upload_hook() == 'fengoffice') {
		$attributes = $args['attributes'];
		echo file_field('file_file', null, $attributes);
	}
}

?>