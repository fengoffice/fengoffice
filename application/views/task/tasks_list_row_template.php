<script id="task-list-col-names-template" type="text/x-handlebars-template">
{{#each  tasks_list_cols}}
  <col style="width: {{this.col_width}};">
{{/each }} 
<thead id="ogTasksPanelColNamesThead">
  <tr id="ogTasksPanelColNames" class="task-list-col-names-template texture-n-1">
    {{#each  tasks_list_cols}}
      <th class="{{this.id}} {{this.css_class}}" {{this.data}}>{{#if this.title}}{{{this.title}}}{{/if}}</th>
    {{/each }}   
  </tr>
</thead>

<tbody id="ogTasksPanelAddNewTaskThead">
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
</tbody>
</script>

<script id="task-list-group-template" type="text/x-handlebars-template"> 
<tbody id="ogTasksPanelGroup{{group.group_id}}">
  <tr>
    <td colspan={{cols_total}} class="ogTasksGroupHeader task-list-row-template">
      <div style="" onclick="ogTasks.expandCollapseAllTasksGroup('{{group.group_id}}')" class="task-single-div og-task-expander toggle_expanded" id="ogTasksPanelGroupExpanderG{{group.group_id}}"></div>
    
      <div class="task-single-div">
        <input style="width:14px;height:14px;vertical-align: middle;" type="checkbox" id="ogTasksPanelGroupChk{{group.group_id}}" {{#if group.isChecked}}checked{{/if}} onclick="ogTasks.GroupSelected(this,'{{group.group_id}}')"/>
      </div>
        
      <div class='task-single-div'>
        <div class='db-ico {{group.group_icon}}'></div>
      </div>

      <div class='ogTasksGroupHeaderName task-single-div'>{{{group.group_name}}}</div>
      
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
    <div style="clear: left;">          
           <a class="internalLink nobr" style="margin-left: 15px;font-size: 12px;" href="#" onclick="ogTasks.showMoreTasks('{{draw_options.groupId}}');return false;" id="show_more_group_{{draw_options.groupId}}">
            {{lang 'show more'}}../
           </a>
           <a class="internalLink nobr" style="font-size: 12px;" href="#" onclick="ogTasks.showAllTasks('{{draw_options.groupId}}');return false;" id="show_all_group_{{draw_options.groupId}}">
            {{lang 'show all'}}..
           </a>    
    </div> 
  </td>
  </tr>
  {{/if}}
</tbody>
</script>


<script id="task-list-row-template" type="text/x-handlebars-template"> 
<tr id="ogTasksPanelTask{{task.id}}G{{group_id}}" class="task-list-row-template task-list-row">
  <td>
  <div style="width: 53px;margin-left: 16px;">
    <div class="task-row-checkbox" >
      <div class="priority {{priorityColor}}">
        <input type="checkbox" id="ogTasksPanelChk{{tgId}}" onclick="ogTasks.TaskSelected(this,{{task.id}}, '{{group_id}}')"/>
      </div> 
    </div>

    <div id='ogTasksPanelFixedExpander{{tgId}}' class='og-task-expander {{subtasksExpander}}' onclick='ogTasks.toggleSubtasks({{task.id}}, "{{group_id}}")'></div>
    
    <div id="ogTasksPanelMarkasTd{{task.id}}" class='read-un-read-task'>
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
          <div class='empty-img ico-warning32' title='{{lang 'unassigned'}}'></div>
        {{/if}}
    </div>
  </td>
  {{/if}}  

  <td>
    <div class='task-row-avatar'>
        {{#if assigned_to}}
          <img src="{{{assigned_to.img_url}}}" alt="" title='{{assigned_to.name}}'/>
        {{else}}
          <div class='empty-img ico-warning32' title='{{lang 'unassigned'}}'></div>
        {{/if}}
    </div>  
  </td>

  <td class="task_name">
    <a class="internalLink" href="{{view_url}}" onclick="og.openLink('{{view_url}}');return false;">
        <div class='task-name'>        
          {{#if task.status}}
            <span style='text-decoration:line-through;margin-left: {{level}}px;' title='{{tool_tip}}'>{{escape task_name}}</span>
          {{else}}
             <span style='margin-left: {{level}}px;' title='{{escape task_name}}'>{{escape task_name}}</span>            
          {{/if}}  
          {{#if task.repetitive}}
            <span style='margin-left: {{level}}px;' class="ico-recurrent" title="{{lang 'repetitive task'}}"></span>  
          {{/if}}          
        </div>          
    </a>  
  </td>
  
  {{#if draw_options.show_classification}}
  <td>
    <div class='task-breadcrumb-container'>      
        {{{mem_path}}}      
    </div>
  </td>
  {{/if}}

  {{#each dim_classification}}
  <td class='task_name'>
    <div class='task-breadcrumb-container'>      
        {{{this.dim_mem_path}}}      
    </div>
  </td>
  {{/each}} 

  {{#if draw_options.show_percent_completed_bar}}
  <td class="task-percent-completed-bar-container">
    {{{percent_completed_bar}}}
  </td>
  {{/if}}  

  {{#if draw_options.show_start_dates}}
  <td class="task-date-container">
    {{#if task.startDate}}
    <span class="nobr" style='font-size: 9px;color: #888;'>{{{start_date}}}</span>  
    {{/if}} 
  </td>
  {{/if}}

  {{#if draw_options.show_end_dates}}
  <td class="task-date-container">
    {{#if task.dueDate}}
     {{#if due_date_late}}
     <span class="nobr" style='font-size: 9px;font-weight:bold;color: #F00;'>{{{due_date}}}</span>  
     {{else}}
     <span class="nobr" style='font-size: 9px;color: #888;'>{{{due_date}}}</span>
     {{/if}}   
    {{/if}} 
  </td>
  {{/if}} 

  {{#each row_total_cols}}
  <td class="task-date-container">
    <span class="nobr" style='font-size: 9px;color: {{color}};'>
      {{{text}}}
    </span>
  </td>
  {{/each}} 

  {{#if draw_options.show_previous_pending_tasks}}
  <td>  
    {{#if task.previous_tasks_total}}
    <span class="ctmBadge previous-pending">{{task.previous_tasks_total}}</span>
    {{/if}} 
  </td>   
  {{/if}} 

  <td>
    {{#if show_quick_actions_container}}
    {{#each task_actions}}
      {{#unless act_collapsed}}
        <div class='task-single-action'>
          <a href='#' onclick='{{act_onclick}}({{#each act_onclick_param}}{{param_val}}{{/each}})'>
            <div id='{{act_id}}' class='{{act_class}} task-action-icon' title='{{act_text}}' style='cursor:pointer;height:16px;padding-top:0px;'>
            </div>
          </a>
        </div>
      {{/unless}} 
    {{/each}} 
    {{/if}}    

    {{#if draw_options.show_time}}
    <div class="task-single-div">
    {{#if user_is_working}}
      <div class="og-timeslot-work-{{user_state}} task-single-div">
        <input type="hidden" value="{{user_start_time}}" id="{{genid}}{{tgId}}user_start_time" name="user_start_time">
        <span id="{{genid}}{{tgId}}timespan{{user_paused_time}}">{{user_paused_time}}</span>
      </div>            
      
      <a href='#' class="task-single-div" onclick='ogTasks.closeTimeslot([{{task.id}}])'>
        <div class='ogTasksTimeClock ico-time-stop task-action-icon' title='{{lang 'close_work'}}'></div>
      </a>
      
      {{#if user_paused}}
      <a href='#' class="task-single-div" onclick='ogTasks.executeAction("resume_work",[{{task.id}}])'>
        <div class='ogTasksTimeClock ico-time-play task-action-icon' title='{{lang 'pause_work'}}'></div>
      </a>
      {{else}}
      <a href='#' class="task-single-div" onclick='ogTasks.executeAction("pause_work",[{{task.id}}])'>
        <div class='ogTasksTimeClock ico-time-pause task-action-icon' title='{{lang 'pause_work'}}'></div>
      </a>
      {{/if}}
    {{else}}
      {{#if can_add_timeslots}}
      <a class="internalLink task-single-div" href="#" onclick="ogTasks.executeAction('start_work',[{{task.id}}])">
        <div title="{{lang 'start_work'}}" class="ogTasksTimeClock ico-time task-action-icon"></div>
      </a>
      {{/if}}
    {{/if}}

    {{#if show_working_on_users}}
    <a class="internalLink tasksActionsBtn task-single-action" href="#" id="workingOnUsersbtn{{tgId}}" data-templateid="workingOnUsers{{tgId}}" data-container="body" data-toggle="popover" data-placement="left" data-trigger="hover" >
      <div class="ogTasksTimeClock ico-b-users task-action-icon"></div>
    </a>
    {{/if}}  
       
    </div>  
    {{/if}}    
  </td>

  <td>
    {{#if show_actions_popover_button}}
    <div class="task-single-action">
      <button id="tasksActionsBtn{{tgId}}" type="button" class="tasksActionsBtn tasksBtn" data-templateid="tasksActionsTemplate{{tgId}}" data-container="body" data-toggle="popover" data-placement="left" data-trigger="{{action_trigger}}" >
        {{lang 'actions'}}<span class="caret"></span>
      </button>
    </div>
    {{/if}}

    {{!-- actions popover template--}}
    <div id='tasksActionsTemplate{{tgId}}' style="display: none;">
      <div class='popover'>
        <div class='arrow'></div>
        <div class='popover-inner'>
          <div class="menu-task-actions">
            <ul>
            {{#each task_actions}}
              {{#if act_collapsed}}
                <li>
                  <a href='#' onclick='{{act_onclick}}({{#each act_onclick_param}}{{param_val}}{{/each}})'>
                    <div id='{{act_id}}' class='{{act_class}}' title='{{act_text}}' style='cursor:pointer;height:16px;padding-top:0px;'>
                     {{act_text}}
                    </div>
                  </a>
                </li>
                {{#unless act_last}}
                <li class="divi"></li> 
                {{/unless}}
              {{/if}} 
            {{/each}}                                          
            </ul>
          </div>
        </div>
      </div>
    </div>  

    {{!-- working users popover template--}}
    <div id='workingOnUsers{{tgId}}' style="display: none;">
      <div class='popover'>
        <div class='arrow'></div>
        <div class='popover-inner'>
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
      </div>
    </div> 
  </td>
</tr>
</script>
