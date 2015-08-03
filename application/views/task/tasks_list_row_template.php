<script id="task-list-row-template" type="text/x-handlebars-template"> 
  <div class="task-list-row-template task-list-row">
    <div class="task-row-checkbox">
      <div class="priority {{priorityColor}}">
        <input type="checkbox" id="ogTasksPanelChk{{tgId}}" onclick="ogTasks.TaskSelected(this,{{task.id}}, {{group_id}})"/>
      </div> 
    </div>
    
    <div id='ogTasksPanelFixedExpander{{tgId}}' class='og-task-expander {{subtasksExpander}}' onclick='ogTasks.toggleSubtasks({{task.id}}, {{group_id}})'></div>
    
    <div id="ogTasksPanelMarkasTd{{task.id}}" class='read-un-read-task'>
      <div title="{{lang 'mark as unread'}}" id="readunreadtask{{task.id}}" class="db-ico {{#if task.isRead}}ico-read{{else}}ico-unread{{/if}}" onclick="ogTasks.readTask({{task.id}},{{task.isRead}})"></div>
    </div>

	{{#if draw_options.show_by}}
	<div class='task-row-avatar'>
  		  {{#if assigned_by}}
          <img src="{{{assigned_by.img_url}}}" alt="" title='{{assigned_by.name}}'/>
        {{else}}
          <div class='empty-img ico-warning32' title='{{lang 'unassigned'}}'></div>
        {{/if}}
  	</div>
	{{/if}}  

  	<div class='task-row-avatar'>
  		  {{#if assigned_to}}
          <img src="{{{assigned_to.img_url}}}" alt="" title='{{assigned_to.name}}'/>
        {{else}}
          <div class='empty-img ico-warning32' title='{{lang 'unassigned'}}'></div>
        {{/if}}
  	</div>	
  
	<a class="internalLink" href="{{view_url}}" onclick="og.openLink('{{view_url}}');return false;" id="rx__dd{{rx__dd}}">

	 <div class='task-name-container'>
	{{#if rx__TasksDrag.allowDrag}}
   	 <div id='RX__ogTasksPanelDrag{{tgId}}' style="height: 100%;" onmouseover='rx__TasksDrag.prepareExt({{task.id}}, {{group_id}},this.id)' onmousedown='rx__TasksDrag.onDragStart({{task.id}}, {{group_id}},this.id); return false;'>
	{{/if}}  
      <div class='task-name' {{#if task.repetitive}}style='width:85%;'{{/if}}  >
        
          {{#if task.status}}
            <span style='text-decoration:line-through' title='{{tool_tip}}'>{{escape task_name}}</span>
          {{else}}
             <span title='{{escape task_name}}'>{{escape task_name}}</span>            
          {{/if}}
            
      </div>  
      {{#if task.repetitive}}
      <div class='task-row-obj-container' style='float:right;'>
        <div class='task-row-obj'>        
          <span style="margin: 0px 8px; padding: 0px 0px 0px 12px;" class="ico-recurrent" title="{{lang 'repetitive task'}}"></span>  
        </div>
      </div>  
      {{/if}} 

	{{#if rx__TasksDrag.allowDrag}} 
    </div>
	{{/if}} 
    </div>
	</a>  
	

	{{#if draw_options.show_classification}}
    <div class='task-breadcrumb-container'>
      
        {{{mem_path}}}
      
    </div>
	{{/if}}

    {{#if draw_options.show_percent_completed_bar}}
    <div class='task-row-obj-container task-percent-completed-bar-container'>
      <div class='task-row-obj'>
        {{{percent_completed_bar}}}
      </div>      
    </div>
    {{/if}}  

    {{#if draw_options.show_start_dates}}
    <div class='task-row-obj-container task-date-container'>
      <div class='task-row-obj'>
		<div class='task-row-obj-content'>
      		{{#if task.startDate}}
        	<span class="nobr" style='font-size: 9px;color: #888;'>{{{start_date}}}</span>  
     		{{/if}} 
		</div>    
      </div>
    </div>
    {{/if}}

    {{#if draw_options.show_end_dates}}
    <div class='task-row-obj-container task-date-container'>
      <div class='task-row-obj'>
		<div class='task-row-obj-content'>
      	{{#if task.dueDate}}
        	{{#if due_date_late}}
          	<span class="nobr" style='font-size: 9px;font-weight:bold;color: #F00;'>{{{due_date}}}</span>  
        	{{else}}
          	<span class="nobr" style='font-size: 9px;color: #888;'>{{{due_date}}}</span>
        	{{/if}}   
      	{{/if}} 
		</div>    
      </div>
    </div>
    {{/if}} 

    {{#each row_total_cols}}
    <div class='task-row-obj-container task-date-container'>
       	<div class='task-row-obj'>
		<div class='task-row-obj-content'>
       		<span class="nobr" style='font-size: 9px;color: {{color}};'>
           	{{{text}}}
       		</span>
		</div>   
    	</div>   
    </div>
	{{/each}} 
	
    {{#if show_quick_actions_container}}
    <div class='task-row-obj-container'>
        {{#each task_actions}}
        {{#unless act_collapsed}}
          <div class='task-row-obj task-single-action'>
              <a href='#' onclick='{{act_onclick}}({{#each act_onclick_param}}{{param_val}}{{/each}})'>
                <div id='{{act_id}}' class='{{act_class}} task-action-icon' title='{{act_text}}' style='cursor:pointer;height:16px;padding-top:0px;'>
                </div>
              </a>
          </div>
        {{/unless}} 
        {{/each}} 
    </div>
    {{/if}}    

    {{#if draw_options.show_time}}
    <div class='task-row-obj-container'>
      <div class='task-row-obj'>
        {{#if user_is_working}}
          <div class='task-row-obj'>
            <div class="og-timeslot-work-{{user_state}}">
                <input type="hidden" value="{{user_start_time}}" id="{{genid}}{{tgId}}user_start_time" name="user_start_time">
                <span id="{{genid}}{{tgId}}timespan{{user_paused_time}}">{{user_paused_time}}</span>
            </div>            
          </div>
          <div class='task-row-obj'>
            <a href='#' onclick='ogTasks.closeTimeslot([{{task.id}}])'>
              <div class='ogTasksTimeClock ico-time-stop task-action-icon' title='{{lang 'close_work'}}'></div>
            </a>
          </div>
          <div class='task-row-obj'>
          {{#if user_paused}}
            <a href='#' onclick='ogTasks.executeAction("resume_work",[{{task.id}}])'>
              <div class='ogTasksTimeClock ico-time-play task-action-icon' title='{{lang 'pause_work'}}'></div>
            </a>
          {{else}}
            <a href='#' onclick='ogTasks.executeAction("pause_work",[{{task.id}}])'>
              <div class='ogTasksTimeClock ico-time-pause task-action-icon' title='{{lang 'pause_work'}}'></div>
            </a>
          {{/if}}
          </div>
        {{else}}
		  {{#if can_add_timeslots}}
          <div class='task-row-obj'>
            <a class="internalLink" href="#" onclick="ogTasks.executeAction('start_work',[{{task.id}}])">
              <div title="{{lang 'start_work'}}" class="ogTasksTimeClock ico-time task-action-icon"></div>
            </a>
          </div>
		  {{/if}}
        {{/if}}

        {{#if show_working_on_users}}
        <div class='task-row-obj'>
           <a class="internalLink tasksActionsBtn" href="#" id="workingOnUsersbtn{{tgId}}" data-templateid="workingOnUsers{{tgId}}" data-container="body" data-toggle="popover" data-placement="left" data-trigger="hover" >
             <div class="ogTasksTimeClock ico-b-users task-action-icon"></div>
           </a>
        </div>
        {{/if}}  
        </div>   
    </div>
    {{/if}}    

	{{#if show_actions_popover_button}}
    <div class='task-actions-container'>
      <div class='task-actions'>
        <button id="tasksActionsBtn{{tgId}}" type="button" class="tasksActionsBtn tasksBtn" data-templateid="tasksActionsTemplate{{tgId}}" data-container="body" data-toggle="popover" data-placement="left" data-trigger="{{action_trigger}}" >
              {{lang 'actions'}}<span class="caret"></span>
        </button>
      </div>      
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

      

  </div>


  
</script>

<script id="task-list-col-names-template" type="text/x-handlebars-template"> 
  <div class="task-list-row-template task-list-col-names-template texture-n-1">
    <div class='task-row-avatar' style='width: 70px;margin:0;height: 1px;'>
      
    </div>
	
	{{#if draw_options.show_by}}
	<div class='col-left-sp task-row-obj-container col-name' style='width: 30px;margin:0;'>
      <div class='task-row-obj'>
		<div class='task-row-obj-content'>
          {{lang 'by uppercase'}}
		</div>
      </div>
    </div>
	{{/if}}

	<div class='col-left-sp task-row-obj-container col-name' style='width: 30px;margin:0;'>
      <div class='task-row-obj'>
		<div class='task-row-obj-content'>
          {{lang 'to'}}
		</div>
      </div>
    </div>
 
    <div class='task-name-container col-left-sp col-right-sp col-name' >
      <div class='task-name col-name' style='margin-left: 5px;'>
          {{lang 'task'}}
      </div>      
    </div>
    
	{{#if draw_options.show_classification}}    
    <div class='task-breadcrumb-container task-row-obj-container col-right-sp col-name'>
      <div class='task-row-obj'>
		<div class='task-row-obj-content'>
          {{lang 'classified under'}}
		</div>
      </div>
    </div>
	{{/if}}

    {{#if draw_options.show_percent_completed_bar}}
      <div class='task-row-obj-container task-percent-completed-bar-container col-right-sp col-name'>
        <div class='task-row-obj'>
		<div class='task-row-obj-content'>
          % {{lang 'completed'}}
		</div>
        </div>      
      </div>
    {{/if}} 

    {{#if draw_options.show_start_dates}}
      <div class='task-row-obj-container task-date-container col-right-sp col-name'>
        <div class='task-row-obj'>
		<div class='task-row-obj-content'>
           {{lang 'start m'}}
		</div>
        </div>
      </div>
    {{/if}}

    {{#if draw_options.show_end_dates}}
      <div class='task-row-obj-container task-date-container col-right-sp col-name'>
        <div class='task-row-obj'>
		<div class='task-row-obj-content'>
           {{lang 'due m'}}
		</div>
        </div>
      </div>
    {{/if}}

    {{#each  title_total_cols}}
    <div class='task-row-obj-container task-date-container col-right-sp col-name'>
      <div class='task-row-obj'>
		<div class='task-row-obj-content'>
        {{this}}
		</div>
      </div>
    </div>	
    {{/each }}

    {{#if draw_options.show_time}}
    <div class='task-row-obj-container col-name' >
      <div class='task-row-obj'>  
		<div class='task-row-obj-content'></div>      
      </div>
    </div>
    {{/if}}

    {{#if draw_quick_actions}}
    <div class='task-row-obj-container'>
        {{#if draw_options.show_quick_complete}}<div class='task-row-obj task-single-action'></div>{{/if}}
        {{#if draw_options.show_quick_add_sub_tasks}}<div class='task-row-obj task-single-action'></div>{{/if}}
        {{#if draw_options.show_quick_edit}}<div class='task-row-obj task-single-action'></div>{{/if}}        
    </div>
    {{/if}}    

    <div class='task-actions-container'>
      <div class='task-actions'>
      <button type="button" class="tasksActionsBtn tasksBtn" style="visibility: collapse;">
              {{lang 'actions'}}
              <span class="caret"></span>
      </button>        
      </div>      
    </div>

  </div> 

   {{!-- new task row--}}
  <div class="task-list-row-template texture-n-1" style="background-color: #e7e7e7;margin-top: -5px;"> 
    <div class='task-row-avatar' style='width: 100px;margin:0;height:10px;'>
    </div>

	{{#if draw_options.show_by}}
 	<div class='task-row-avatar' style='width: 30px;margin:0;height: 1px;'>
      
    </div>
	{{/if}}
  
    <div class='task-name-container col-left-sp col-right-sp' style='padding-left: 5px;'>
      <div class='task-name col-name'>
          <input type="text" onfocus="this.disabled='disabled';ogTasks.drawAddNewTaskForm(0,null,null,true)" value="" name="task[name]" placeholder="{{lang 'task'}}..." maxlength="255" size="255" id="ogTasksPanelListATTitle" style="width: 96%;" onblur="this.disabled='';">
      </div>      
    </div>

    <div class="coInputButtons" style="margin-top:2px;margin-left:10px;">
    <button type="button" class="tasksActionsBtn tasksBtn addBtn" onclick="ogTasks.drawAddNewTaskForm(0,null,null,true)">
              {{lang 'add task'}}
    </button>
    </div>
    <div class="clear"></div>
  </div> 
</script>

<script id="task-list-group-totals-template" type="text/x-handlebars-template"> 
{{!-- group totals--}}
  <div id="group_totals_{{draw_options.groupId}}" class="task-list-row-template task-list-group-totals-template">
    <div class='task-row-avatar' style='width: 85px;margin:0;height: 1px;'>      
    </div>
	
	{{#if draw_options.show_by}}
 	<div class='task-row-avatar' style='width: 30px;margin:0;height: 1px;'>
      
    </div>
	{{/if}}
  
    <div class='task-name-container col-left-sp col-right-sp'>
      <div class='task-name col-name'>   
         {{lang 'total'}}:      
      </div>      
    </div>
     
	{{#if draw_options.show_classification}}   
    <div class='task-breadcrumb-container col-right-sp col-name'>
      <div class='task-row-obj'>       
      </div>
    </div>
	{{/if}}

    {{#if draw_options.show_percent_completed_bar}}
      <div class='task-row-obj-container task-percent-completed-bar-container col-right-sp col-name'>
        <div class='task-row-obj'>         
        </div>      
      </div>
    {{/if}} 

    {{#if draw_options.show_start_dates}}
      <div class='task-row-obj-container task-date-container col-right-sp col-name'>
        <div class='task-row-obj'>          
        </div>
      </div>
    {{/if}}

    {{#if draw_options.show_end_dates}}
      <div class='task-row-obj-container task-date-container col-right-sp col-name'>
        <div class='task-row-obj'>           
        </div>
      </div>
    {{/if}}
    
	{{#each format_group_totals}}
    <div class='task-row-obj-container task-date-container col-right-sp col-name'>
      <div class='task-row-obj'>
         {{{text}}}
      </div>
    </div>	
	{{/each}}    

    {{#if draw_options.show_time}}
    <div class='task-row-obj-container col-right-sp col-name' style='width: 120px;'>
      <div class='task-row-obj'>        
      </div>
    </div>
    {{/if}}

    {{#if draw_quick_actions}}
    <div class='task-row-obj-container'>
        {{#if draw_options.show_quick_complete}}<div class='task-row-obj task-single-action'></div>{{/if}}
        {{#if draw_options.show_quick_add_sub_tasks}}<div class='task-row-obj task-single-action'></div>{{/if}}
        {{#if draw_options.show_quick_edit}}<div class='task-row-obj task-single-action'></div>{{/if}}        
    </div>
    {{/if}}    

    <div class='task-actions-container'>
      <div class='task-actions'>
      <button type="button" class="tasksActionsBtn tasksBtn" style="visibility: collapse;">
              {{lang 'actions'}}
              <span class="caret"></span>
      </button>        
      </div>      
    </div>

    {{#if draw_options.showMore}} 
    <div style="clear: left;">          
           <a class="internalLink nobr" style="margin-left: 15px;font-size: 12px;" href="#" onclick="ogTasks.showMoreTasks('{{draw_options.groupId}}');return false;" id="show_more_group_{{draw_options.groupId}}">
            {{lang 'show more'}}../
           </a>
           <a class="internalLink nobr" style="font-size: 12px;" href="#" onclick="ogTasks.showAllTasks('{{draw_options.groupId}}');return false;" id="show_all_group_{{draw_options.groupId}}">
            {{lang 'show all'}}..
           </a>    
    </div> 
    {{/if}}
  </div>  
</script>

