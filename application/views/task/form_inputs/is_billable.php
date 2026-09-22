<?php

tpl_assign('task', $object);
tpl_assign('genid', $genid);
tpl_assign('task_data', isset($task_data) ? $task_data : array());

tpl_display(get_template_path('tasks_billable_selector', 'task', 'advanced_billing'));
