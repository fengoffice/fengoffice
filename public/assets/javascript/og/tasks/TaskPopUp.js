og.TaskPopUp = function(action, task_id) {
    var html_option  = "<div style='width:100%; margin: 10px; padding: 10px;'>";
        if(action == "task_complete"){
            html_option += "<div>" + lang('option repetitive title popup') + "</div>";
            html_option += "<div><input type='radio' name='type_related' value='only' onclick='selectRelated(this.value)' checked/>" + lang('option repetitive only task') + "</div>";
            html_option += "<div><input type='radio' name='type_related' value='pending' onclick='selectRelated(this.value)'/>" + lang('option repetitive pending task') + "</div>";
        }else{
            html_option += "<div>" + lang('apply changes to') + "</div>";
            html_option += "<div><input type='radio' name='type_related' value='only' onclick='selectRelated(this.value)'/>" + lang('only this task') + "</div>";
            html_option += "<div><input type='radio' name='type_related' value='news' onclick='selectRelated(this.value)' checked/>" + lang('this task alone and all to come forward') + "</div>";
            html_option += "<div><input type='radio' name='type_related' value='all' onclick='selectRelated(this.value)'/>" + lang('all tasks related') + "</div>";
        }       
        html_option += "<div><input type='hidden' name='action_related' id='action_related' value='" + action + "'/></div>";
        html_option += "<div><input type='hidden' name='complete_task_id' id='related_task_id' value='" + task_id + "'/></div>";
        html_option += "</div>";
    og.TaskPopUp.superclass.constructor.call(this, {
                y: 220,
                width: 390,
                height: 230,
                id: 'task-related',
                layout: 'border',
                modal: true,
                resizable: false,
                closeAction: 'close',
                border: false,
                buttons: [{
                        text: lang('accept'),
                        handler: this.accept,
                        scope: this
                }],
                items: [{
                        region: 'center',
                        layout: 'fit',
                        html: html_option
                }]
        });
}

og.TaskCompletePopUp = function(task_id) {
    var html_option  = "<div style='width:100%; margin: 10px; padding: 10px;'>";
        html_option += "<div>" + lang('complete task and subtask') + "</div>";
        html_option += "<div><input type='radio' name='complete_task' value='yes' onclick='selectTaskCompletePopUp(this.value)' checked/>" + lang('yes') + "</div>";
        html_option += "<div><input type='radio' name='complete_task' value='no' onclick='selectTaskCompletePopUp(this.value)'/>" + lang('no') + "</div>";
        html_option += "<div><input type='hidden' name='complete_task_id' id='complete_task_id' value='" + task_id + "'/></div>";
        html_option += "</div>";
    og.TaskCompletePopUp.superclass.constructor.call(this, {
                y: 220,
                width: 390,
                height: 230,
                id: 'task-complete',
                layout: 'border',
                modal: true,
                resizable: false,
                closeAction: 'close',
                border: false,
                buttons: [{
                        text: lang('accept'),
                        handler: this.accept,
                        scope: this
                }],
                items: [{
                        region: 'center',
                        layout: 'fit',
                        html: html_option
                }]
        });
}