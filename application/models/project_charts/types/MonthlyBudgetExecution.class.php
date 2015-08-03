<?php
  	/* ProjectCharts class
  	*
  	* @author Carlos Palma <chonwil@gmail.com>
  	*/
  	class MonthlyBudgetExecution extends ProjectChart {

  		private $exec = 0;
  		
  		private $notExec = 0;
  		
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
	    		$this->data['values'][0]['labels'][$p->getId()] = ($p->getId() == 1? lang('executed') : lang('not executed'));
	    		$this->data['values'][0]['values'][$p->getId()] =  (int)$value;
	    		if ($p->getId() == 1)
	    			$this->exec = (int)$value;
	    		else
	    			$this->notExec = (int)$value;
	    	} // foreach
	    	
	    	$this->data['values'][0]['name'] = lang('current');
  		}
  		
  		function PrintInfo(){
  			return "<b>Total budget:</b> ". config_option('currency_code', '$') . ($this->exec + $this->notExec);
  		}
  		
  		function Draw($g, $returnGraphObject){
			return parent::Draw($g, $returnGraphObject);
  		}
  	}
?>