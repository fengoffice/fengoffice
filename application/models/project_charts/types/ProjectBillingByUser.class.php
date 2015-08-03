<?php
  	/* ProjectCharts class
  	*
  	* @author Carlos Palma <chonwil@gmail.com>
  	*/
  	class ProjectBillingByUser extends ProjectChart {

  		private $total = 0;
  		
    	protected $hasParameters = false;
    
  		function __construct(){
  			parent::__construct();
  			$this->setDisplayId(10); //Default display type = Bar chart
  			$this->setTypeId(6);
  			$this->hasParams = false;
  		}
  		
  		function SetData($billing_chart_data){
  			$this->data = array();
			
	    	foreach ($billing_chart_data as $row){
	    		$value = 0;
	    		$user = Users::findById($row['user']);
	    		if ($user instanceof User){
		    		$this->data['values'][0]['labels'][] = $user->getDisplayName();
		    		$this->data['values'][0]['values'][] = $row['total_billing'];
	    		}
		    	$this->total += $row['total_billing'];
	    	} // foreach
	    	
	    	$this->data['values'][0]['name'] = lang('current');
  		}
  		
  		function PrintInfo(){
  			return "<b>" . lang('total') . "</b> ". config_option('currency_code', '$') . $this->total;
  		}
  		
  		function Draw($g, $returnGraphObject){
			return parent::Draw($g, $returnGraphObject);
  		}
  	}
?>