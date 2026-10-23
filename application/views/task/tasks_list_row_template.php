<script id="task-list-col-names-template" type="text/x-handlebars-template">
{{#each  tasks_list_cols}}
  <col style="width: {{this.col_width}};">
{{/each }}
<thead id="ogTasksPanelColNamesThead">
  <tr id="ogTasksPanelColNames" class="task-list-col-names-template texture-n-1">
    {{#each  tasks_list_cols}}
      <th class="{{this.id}} {{this.css_class}}" {{this.data}}>{{#if @first}}<div id="ogTasksGroupsExpandCollapseHeader"><div id="ogTasksCollapseAllGroupsBtn" onclick="ogTasks.collapseAllGroups()" title="{{lang 'collapse all groups'}}"></div><div id="ogTasksExpandAllGroupsBtn" onclick="ogTasks.expandAllGroups()" title="{{lang 'expand all groups'}}"></div></div>{{/if}}{{#if this.title}}{{{this.title}}}{{/if}}</th>
    {{/each }}
  </tr>
</thead>

<!-- <tbody id="ogTasksPanelAddNewTaskThead">
<tr id="new_task{{genid}}" class="task-list-row-template">
    {{#each  tasks_list_cols}}
    <td>
      {{#if_eq this.id 'task_name'}}
        <input style="width:95%;" name="name" type="text" class="form-control" placeholder="{{lang 'task'}}">
      {{/if_eq}}

      {{#if_eq this.id ../add_btn_position}}
        <buton class="btn btn-xs btn-primary" type="" onclick="ogTasks.drawAddNewTaskFromData('new_task{{../../genid}}')">{{lang "add task"}}</buton>
      {{/if_eq}}
    </td>
    {{/each }}
</tr>
</tbody> -->
</script>

<script id="task-list-group-template" type="text/x-handlebars-template">
<tbody id="ogTasksPanelGroup{{group.group_id}}" class="tasks-panel-group tasks-panel-group-droppable">
  <tr class="ogTasksGroupHeaderRow">
    <td colspan={{cols_total}} class="ogTasksGroupHeader task-list-row-template">
      <div style="" onclick="ogTasks.expandCollapseAllTasksGroup('{{group.group_id}}')" class="task-single-div og-task-expander toggle_expanded" id="ogTasksPanelGroupExpanderG{{group.group_id}}"></div>

      <div class="task-single-div">
        <input style="width:14px;height:14px;vertical-align: middle;" type="checkbox" id="ogTasksPanelGroupChk{{group.group_id}}" {{#if group.isChecked}}checked{{/if}} onclick="ogTasks.GroupSelected(this,'{{group.group_id}}')"/>
      </div>

      <div class='task-single-div'>
        <div class='{{group.group_icon}}'></div>
      </div>

      <div class='ogTasksGroupHeaderName task-single-div'>{{{group.group_name}}}</div>

      {{#if group.group_memPath}}
      <div class='task-single-div task-breadcrumb-container' style="float: none; width: 300px;">
        {{{mem_path}}}
      </div>
      {{/if}}

      {{#each  group.view}}
        <div id='{{this.id}}' class='ogTasksGroupHeaderName task-single-div {{this.css_class}}'>{{{this.text}}}</div>
      {{/each }}
    </td>
  </tr>
</tbody>
</script>

<script id="task-list-group-totals-template" type="text/x-handlebars-template">
<tbody id="ogTasksPanelGroup{{group.group_id}}Totals">
  <tr id="" class="task-list-row-template task-list-group-totals-template">
    {{#each  total_cols}}
      <td class="{{{this.id}}}">{{{this.text}}}</td>
    {{/each }}
  </tr>
  {{#if draw_options.showMore}}
  <tr>
  <td colspan={{total_cols.length}}>
    <div style="clear: left; margin-bottom: 20px;">
           <a class="internalLink nobr" style="margin-left: 15px;font-size: 12px;" href="#" onclick="ogTasks.showMoreTasks('{{draw_options.groupId}}');return false;" id="show_more_group_{{draw_options.groupId}}">
            {{lang 'show more'}}../
           </a>
           <a class="internalLink nobr" style="font-size: 12px;" href="#" onclick="ogTasks.showAllTasks('{{draw_options.groupId}}');return false;" id="show_all_group_{{draw_options.groupId}}">
            {{lang 'show all tasks'}}..
           </a>
    </div>
  </td>
  </tr>
  {{/if}}
</tbody>
</script>


<script id="task-list-row-template" type="text/x-handlebars-template">
<tr id="ogTasksPanelTask{{task.id}}G{{group_id}}" class="task-list-row-template task-list-row" data-task-id="{{task.id}}" data-level="{{level}}" {{#if task.parentId}} data-parent-id="{{task.parentId}}"{{/if}}>
  <td>
  <div style="width: 53px;margin-left: 16px;">
	<div class="ddhandle">&nbsp;</div>
    <div class="task-row-checkbox" >
      <div class="priority {{priorityColor}}">
        <input type="checkbox" id="ogTasksPanelChk{{tgId}}" onclick="ogTasks.TaskSelected(this,{{task.id}}, '{{group_id}}')"/>
      </div>
    </div>

      {{#if showSubtasksExpander}}
      <div id='ogTasksPanelFixedExpander{{tgId}}' data-task-toggle="{{task.id}}" w class='og-task-expander {{subtasksExpander}}'
           onclick='ogTasks.toggleSubtasks({{task.id}}, "{{group_id}}")'></div>
      {{/if}}
    <div id="ogTasksPanelMarkasTd{{task.id}}" class='read-un-read-task {{#unless showSubtasksExpander}} task-no-expander-subtask-filler {{/unless}}'>
      <div title="{{lang 'mark as unread'}}" id="readunreadtask{{task.id}}" class="db-ico {{#if task.isRead}}ico-read{{else}}ico-unread{{/if}}" onclick="ogTasks.readTask({{task.id}},{{task.isRead}})"></div>
    </div>
  </div>
  </td>


  {{#if draw_options.show_by}}
  <td>
    <div class='task-row-avatar'>
        {{#if assigned_by}}
          <img src="{{{assigned_by.img_url}}}" alt="" title='{{assigned_by.name}}'/>
        {{else}}
          <div class='icon-user-search' title='{{lang 'unassigned'}}'></div>
        {{/if}}
    </div>
  </td>
  {{/if}}

  {{#if draw_options.show_assigned_to}}
  <td data-editable="assigned_to">
    {{#if assigned_to_show_name}}
        <div class='task-row-avatar big'>
            {{#if assigned_to}}
                {{#if assigned_to_show_name}}
                    <div class="name assignee-hover-target" data-assignee-id="{{assigned_to.id}}">{{assigned_to.name}}</div>
                {{else}}
                    <img class="assignee-hover-target" data-assignee-id="{{assigned_to.id}}" src="{{{assigned_to.img_url}}}" alt="" title='{{assigned_to.name}}'/>
                {{/if}}
            {{else}}
                <div class='icon-user-search' title='{{lang 'unassigned'}}'></div>
            {{/if}}
        </div>
    {{else}}
        <div class='task-row-avatar'>
            {{#if assigned_to}}
                <img class="assignee-hover-target" data-assignee-id="{{assigned_to.id}}" src="{{{assigned_to.img_url}}}" alt="" title='{{assigned_to.name}}'/>
            {{else}}
                <div class='icon-user-search' title='{{lang 'unassigned'}}'></div>
            {{/if}}
        </div>
    {{/if}}

  </td>
  {{/if}}
  <td class="task_name" data-editable="task_name">
    <div class='task-name' data-elbow-line-container="true">
      <span data-elbow-type="true" ></span>
      {{#if task.status}}
      <a class="internalLink" href="{{view_url}}" data-task-span-name="true"
         style='text-decoration:line-through; margin-left: {{level}}px;' title='{{tool_tip}}'
         onclick="ogTasks.onTaskLinkClick({{task_id}});og.openLink('{{view_url}}');return false;">{{escape task_name}}</a>
      {{else}}
      <a class="internalLink" href="{{view_url}}" data-task-span-name="true"
         style='margin-left: {{level}}px;' title='{{escape task_name}}'
         onclick="ogTasks.onTaskLinkClick({{task_id}});og.openLink('{{view_url}}');return false;">{{escape task_name}}</a>
      {{/if}}
      {{#if task.repetitive}}
      <span class="ico-recurrent" style='margin-left: {{level}}px;' title="{{lang 'repetitive task'}}"></span>
      {{/if}}
    </div>
  </td>

  {{#if draw_options.show_classification}}
  <td>
    <div class='task-breadcrumb-container'>
        {{{mem_path}}}
    </div>
  </td>
  {{/if}}

  {{#each dim_classification}}
  <td class='task_name' data-editable="dim_classification" data-dim-id="{{dim_id}}">
    <div class='task-breadcrumb-container'>
        {{{this.dim_mem_path}}}
    </div>
  </td>
  {{/each}}

  {{#if draw_options.show_percent_completed_bar}}
  <td class="task-percent-completed-bar-container">
  <!-- <td class="task-percent-completed-bar-container" data-editable="percent_completed"> -->
    {{{percent_completed_bar}}}
  </td>
  {{/if}}

  {{#if draw_options.show_start_dates}}
  <td class="task-date-container" data-editable="start_date">
    {{#if task.startDate}}
        <span class="nobr {{#if start_date_overdue}}task-date-overdue{{else}}task-date-muted{{/if}}">{{{start_date}}}</span>
    {{/if}}
  </td>
  {{/if}}

  {{#if draw_options.show_end_dates}}
  <td class="task-date-container" data-editable="due_date">
    {{#if task.dueDate}}
     <span class="nobr {{#if due_date_late}}task-due-date-overdue{{else}}task-date-muted{{/if}}">{{{due_date}}}</span>
    {{/if}}
  </td>
  {{/if}}

  {{#each row_total_cols}}
  <td class="task-date-container">
    <span class="nobr" style='color: {{color}};'>
      {{{text}}}
    </span>
  </td>
  {{/each}}

	{{#if additional_task_list_columns}}
		{{#each additional_task_list_columns}}
			{{{html}}}
		{{/each}}
	{{/if}}

  {{#if draw_options.show_previous_pending_tasks}}
  <td>
    {{#if task.previous_tasks_total}}
    <span class="ctmBadge previous-pending">{{task.previous_tasks_total}}</span>
    {{/if}}
  </td>
  {{/if}}


  {{#each task.custom_properties}}

	{{#if (isTasksColumnCPVisible id)}}
		<td class="task-cp-container" data-editable="cp_{{id}}" data-cp-id="{{id}}">
			<span class="break-word task-cp-content">
				{{{value}}}
			</span>
		</td>
	{{/if}}

  {{/each}}


  <td>
    {{#if show_quick_actions_container}}
    {{#each task_actions}}
      {{#unless act_collapsed}}
      {{#unless act_hide_from_row}}
        <div class='task-single-action'>
          {{#if act_disabled}}
          <a href='#' onclick='return false;'>
            <div id='{{act_id}}' class='{{act_class}} task-action-icon disabled' title='{{act_text}}'>
              <div class="icon-{{act_icon}}"></div>
            </div>
          </a>
          {{else}}
          <a href='#' onclick='{{act_onclick}}({{#each act_onclick_param}}{{param_val}}{{/each}})'>
            <div id='{{act_id}}' class='{{act_class}} task-action-icon' title='{{act_text}}'>
              <div class="icon-{{act_icon}}"></div>
            </div>
          </a>
          {{/if}}
        </div>
      {{/unless}}
      {{/unless}}
    {{/each}}
    {{/if}}

    {{#if draw_options.show_time}}
      {{#unless_or task.is_parent task.prevent_add_time_to_parent_task}}
      <div class="task-single-div">
      {{#if user_is_working}}
        <div class="og-timeslot-work-{{user_state}} task-single-div">
          <input type="hidden" value="{{user_start_time}}" id="{{genid}}{{tgId}}user_start_time" name="user_start_time">
          <span id="{{genid}}{{tgId}}timespan{{user_paused_time}}">{{user_paused_time}}</span>
        </div>

        <a href='#' class="task-single-div" onclick='ogTasks.closeTimeslot([{{task.id}}])' data-id="{{task.id}}">
          <div class='ogTasksTimeClock stop task-action-icon' title='{{lang 'close_work'}}'>
            <div class="icon-circle-stop"></div>
          </div>
        </a>

        {{#if user_paused}}
        <a href='#' class="task-single-div" onclick='ogTasks.executeAction("resume_work",[{{task.id}}])'>
          <div class='ogTasksTimeClock play task-action-icon' title='{{lang 'pause_work'}}'>
            <div class="icon-circle-play"></div>
          </div>
        </a>
        {{else}}
        <a href='#' class="task-single-div" onclick='ogTasks.executeAction("pause_work",[{{task.id}}])'>
          <div class='ogTasksTimeClock pause task-action-icon' title='{{lang 'pause_work'}}'>
            <div class="icon-circle-pause"></div>
          </div>
        </a>
        {{/if}}

        <a href='#' class="task-single-div" onclick='ogTasks.executeAction("cancel_work",[{{task.id}}])'>
          <div class='ogTasksTimeClock cancel task-action-icon' title='{{lang 'discard_work'}}'>
            <div class="icon-circle-x"></div>
          </div>
        </a>
      {{else}}
        {{#if can_add_timeslots}}
          {{#if task.estimated_hours_limit_reached}}
          <a class="internalLink task-single-div" href="#" onclick="return false;">
            <div title="{{lang 'cannot add time task estimated hours reached'}}" class="ogTasksTimeClock play task-action-icon disabled">
              <div class="icon-timer"></div>
            </div>
          </a>
          {{else}}
          <a class="internalLink task-single-div" href="#" onclick="ogTasks.executeAction('start_work',[{{task.id}}],'','#tasksPanelContainer')">
            <div title="{{lang 'start_work'}}" class="ogTasksTimeClock play task-action-icon">
              <div class="icon-timer"></div>
            </div>
          </a>
          {{/if}}
        {{/if}}
      {{/if}}

      {{#if show_working_on_users}}
      <a class="internalLink tasksActionsBtn task-single-action" href="#" id="workingOnUsersbtn{{tgId}}" data-templateid="workingOnUsers{{tgId}}" data-container="body" data-toggle="popover" data-placement="left" data-trigger="hover" >
        <div class="ogTasksTimeClock ico-b-users task-action-icon"></div>
      </a>
      {{/if}}
      </div>
      {{/unless_or}}
    {{/if}}

    {{#if show_actions_popover_button}}
    <div class="task-single-action">
      <button type="button" id="tasksActionsBtn{{tgId}}" class="tasksActionsBtn" data-templateid="tasksActionsTemplate{{tgId}}" data-container="body" data-toggle="popover" data-placement="left" data-trigger="{{action_trigger}}" aria-label="{{lang 'actions'}}">
        <div class="task-action-icon task-overflow-menu-icon">
          <div class="icon-ellipsis"></div>
        </div>
      </button>
    </div>
    {{/if}}

    {{!-- actions popover content (read live at show-time by og.initPopoverBtns via
         data-templateid — see og.js — so no outer .popover/.arrow chrome is needed
         here; Bootstrap's own default popover template supplies that and injects
         this div's innerHTML into its .popover-content) --}}
    <div id='tasksActionsTemplate{{tgId}}' style="display: none;">
      <div class="menu-task-actions">
        <ul>
        {{#each task_actions}}
          {{#if act_collapsed}}
            <li>
              {{#if act_disabled}}
              <a href='#' class='menu-task-action-item {{act_class}} disabled' onclick='return false;'>
                <span class='menu-task-action-icon icon-{{act_icon}}'></span>
                <span class='menu-task-action-label'>{{act_text}}</span>
              </a>
              {{else}}
              <a href='#' class='menu-task-action-item {{act_class}}' onclick='{{act_onclick}}({{#each act_onclick_param}}{{param_val}}{{/each}})'>
                <span class='menu-task-action-icon icon-{{act_icon}}'></span>
                <span class='menu-task-action-label'>{{act_text}}</span>
              </a>
              {{/if}}
            </li>
          {{/if}}
        {{/each}}
        </ul>
      </div>
    </div>

    {{!-- working users popover content (same live-read mechanism as above) --}}
    <div id='workingOnUsers{{tgId}}' style="display: none;">
      <div class="workin-on-users-list">
        <ul>
          <li class="workin-on-users-list-title">
            {{lang 'work in progress'}}:
          </li>
          {{#each working_on_users}}
            <li class="divi"></li>
            <li style="vertical-align:middle;">
              <img src="{{{img_url}}}" alt=""/>
              {{name}}
            </li>
          {{/each}}
        </ul>
      </div>
    </div>
  </td>
</tr>
</script>
