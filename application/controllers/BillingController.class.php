<?php

class BillingController extends ApplicationController {

	function __construct() {
		parent::__construct();
		if(Plugins::instance()->isActivePlugin("income")){
			Env::useHelper('functions', 'income');
		}
		
		prepare_company_website_controller($this, 'website');
		//ajx_set_panel("administration");

		// Access permissios
		if(!can_manage_billing(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
		} // if
	}
	
	function index() {
		$can_see_billing_info = true;
		Hook::fire('get_can_see_billing_information', array('user'=>logged_user()), $can_see_billing_info);
		$can_see_cost_info = true;
    	Hook::fire('get_can_see_cost_information', array('user'=>logged_user()), $can_see_cost_info);

		if (!$can_see_billing_info || !$can_see_cost_info) {
			flash_error(lang('no access permissions'));
			ajx_current('empty');			
			return;
		}
	}
	
	function list_all() {
		tpl_assign('billing_categories', BillingCategories::instance()->findAll());
	}

	function add() {
		if (!can_manage_billing(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		$billingCategory = new BillingCategory();
		$billing_data = array_var($_POST, 'billing');
		if (!is_array($billing_data)) {
			// set layout for modal form
			if (array_var($_REQUEST, 'modal')) {
				$this->setLayout("json");
				tpl_assign('modal', true);
			}
			$billing_data = array(
				'name' => '',
				'description' => '',
				'default_value' => 0,
				'report_name' => ''
				);
		} else {
			$billingCategory = new BillingCategory();
			$billingCategory->setFromAttributes($billing_data);
			try {
				DB::beginWork();
				$billingCategory->save();
				DB::commit();
				flash_success(lang("success add billing category"));
				if (array_var($_REQUEST, 'modal')) {
					evt_add("reload current panel");
				} else {
					ajx_current("back");
				}
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
		tpl_assign('billing', $billingCategory);
		tpl_assign('billing_data', $billing_data);
	}
	
	
	function edit() {
		$this->setTemplate('add');
		
		$billingCategory = BillingCategories::instance()->findById(get_id());
		if(!($billingCategory instanceof BillingCategory)) {
			flash_error(lang('billing category dnx'));
			ajx_current("empty");
			return;
		} // if
		
		if (!can_manage_billing(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		
		$billing_data = array_var($_POST, 'billing');
		if (!is_array($billing_data)) {
			// set layout for modal form
			if (array_var($_REQUEST, 'modal')) {
				$this->setLayout("json");
				tpl_assign('modal', true);
			}
			$billing_data = array(
				'name' => $billingCategory->getName(),
				'description' => $billingCategory->getDescription(),
				'default_value' => $billingCategory->getDefaultValue(),
				'report_name' => $billingCategory->getReportName()
				);
		} else {
			$billingCategory->setFromAttributes($billing_data);
			try {
				DB::beginWork();
				$billingCategory->save();
				DB::commit();
				flash_success(lang("success edit billing category"));
				if (array_var($_REQUEST, 'modal')) {
					evt_add("reload current panel");
				} else {
					ajx_current("back");
				}
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
		tpl_assign('billing', $billingCategory);
		tpl_assign('billing_data', $billing_data);
	}

	function delete() {
		ajx_current("empty");
		
		$billingCategory = BillingCategories::instance()->findById(get_id());
		if(!($billingCategory instanceof BillingCategory)) {
			flash_error(lang('billing category dnx'));
			return;
		} // if

		if(!$billingCategory->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {
			DB::beginWork();
			$billingCategory->delete();
			DB::commit();
			flash_success(lang('success delete billing category', $billingCategory->getName()));
			ajx_current("reload");
		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		} // try
	}

	function assign_users(){
		if (!can_manage_billing(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		$users_data = array_var($_POST, 'users');
		if (is_array($users_data)) {
			try {
				DB::beginWork();
				foreach ($users_data as $user_id => $user_billing){
					$user = Contacts::instance()->findById($user_id);
					if ($user_billing != $user->getDefaultBillingId()){
						$user->setDefaultBillingId($user_billing);
						$user->save();
					}
				}
				DB::commit();
				flash_success(lang("success assign user billing categories"));
				ajx_current("back");
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
		
		tpl_assign('users_by_company', Contacts::getGroupedByCompany(false));
		tpl_assign('billing_categories', BillingCategories::instance()->findAll());
	}
	
	
	function update_unset_billing_values(){
		ajx_current("empty");
		
		if (!can_manage_billing(logged_user())) {
			flash_error(lang("no access permissions"));
			return;
		}
		try{
			DB::beginWork();
			$count = Timeslots::updateBillingValues();
			DB::commit();
			
			flash_success(lang("success update billing values", $count));
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		}
	}
}

?>