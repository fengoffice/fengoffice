<?php
  	/* ProjectCharts class
  	*
  	* @author Carlos Palma <chonwil@gmail.com>
  	*/
  	class BudgetExecution extends ProjectChart {

    	protected $hasParameters = true;
    
  		function __construct(){
  			parent::__construct();
  			$this->setDisplayId(20); //Default display type = Pie
  			$this->setTypeId(1);
  			$this->hasParams = true;
  		}
  		
  		function ExecuteQuery(){
  			$this->data = array();
  			$parameters = $this->getParameters();
			
	    	foreach ($parameters as $p){
	    		$value = $p->getValue();
	    		$date = new DateTimeValue(mktime(12,0,0,$p->getId(),1,2008));
	    		$this->data['values'][0]['labels'][] = $date->format("F");
	    		$this->data['values'][0]['values'][] =  (int)$value;
	    		$this->data['values'][1]['labels'][] = $date->format("F");
	    		$this->data['values'][1]['values'][] =  10000;
	    	} // foreach
	    	
	    	$this->data['values'][0]['name'] = lang('current');
	    	$this->data['values'][1]['name'] = lang('budgeted');
  		}
  		
  		function PrintInfo(){
  			return "<b>Max budget:</b> $" . $this->data['values'][1]['values'][0];
  		}
  		
  		function Draw($g, $returnGraphObject){
			return parent::Draw($g, $returnGraphObject);
  		}
  	}
?>