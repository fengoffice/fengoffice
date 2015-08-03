<?php

class TaskdependencyController extends ApplicationController {
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	}
	
	function remove() {
		$pt = DB::escape(array_var($_GET, 'pt'));
		$t = DB::escape(array_var($_GET, 't'));
		$dep = ProjectTaskDependencies::findOne(array('conditions' => "`previous_task_id` = $pt AND `task_id` = $t"));
		if ($dep instanceof ProjectTaskDependency) {
			$dep->delete();
			flash_success(lang('success remove task dependency'));
		} else {
			flash_error(lang('task dependency dnx'));
		}
		
		$reload = array_var($_GET, 'reload', true);
		if($reload){
			ajx_current("reload");
		}else{
			ajx_current("empty");
		}		
	}
	
	function add() {
		$pt = DB::escape(array_var($_GET, 'pt'));
		$t = DB::escape(array_var($_GET, 't'));
		$dep = ProjectTaskDependencies::findOne(array('conditions' => "`previous_task_id` = $pt AND `task_id` = $t"));
		if (!$dep instanceof ProjectTaskDependency) {
			try {
				DB::beginWork();
				$dep = new ProjectTaskDependency();
				$dep->setPreviousTaskId(array_var($_GET, 'pt'));
				$dep->setTaskId(array_var($_GET, 't'));
				$dep->save();
				DB::commit();
			} catch (Exception $e) {
				flash_error($e->getMessage());
				DB::rollback();
			}
		}
		flash_success(lang('success add task dependency'));
		
		$reload = array_var($_GET, 'reload', true);
		if($reload){
			ajx_current("reload");
		}else{
			ajx_current("empty");
		}	
	}
	
}
?>