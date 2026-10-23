<?php

namespace App\Http\Controllers;

class CustomersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * get all customers
     */
    public function getAllCustomers()
    {

        $pg_array = logged_user()->getPermissionGroupIds();
        $all_customers=\Customers::instance()->findAll(array(
			'conditions' => " trashed_on = '0000-00-00 00:00:00' AND  archived_on = '0000-00-00 00:00:00'",
			'limit' => 10,
		));
        $customersArray = array();
        foreach($all_customers as $customer){
            //$customersArray[] = $customer->getArrayInfo();
            $customersArray[] =$customer->getStartDate();
            //calculate_single_customer_additional_data

        }

        return response()->json(array('error'=>false, 'data'=>$customersArray, 'message'=>'return all customers'));
    }


    //
}
