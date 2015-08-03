<?php
  	/* ProjectCharts class
  	*
  	* @author Carlos Palma <chonwil@gmail.com>
  	*/
  	class BudgetExecutionTrack extends ProjectChart {

  		private $acum;
  		
  		private $acumExec;
  		
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
  			$this->acum = 0;
  			$this->acumExec = 0;
  			
	    	foreach ($parameters as $p){
	    		$value = $p->getValue();
	    		$id = $p->getId();
	    		$series = 0;
	    		if ($id > 1000){
	    			$series = 1;
	    			$id = $id % 1000;
	    			$this->acum +=(int)$value;
	    		} else 
	    			$this->acumExec += (int)$value;
	    		$date = new DateTimeValue(mktime(12,0,0,$id,1,2008));
	    		$this->data['values'][$series]['labels'][] = $date->format("F");
	    		$this->data['values'][$series]['values'][] =  (int)$value;
	    	} // foreach
	    	
	    	$this->data['values'][0]['name'] = lang('executed');
	    	$this->data['values'][1]['name'] = lang('budgeted');
  		}
  		
  		function PrintInfo(){
  			$balance = $this->acum - $this->acumExec;
  			return "<b>Budgeted (accum.):</b> ". config_option('currency_code', '$') . $this->acum
  				. "<br/><b>Executed (accum.):</b> ". config_option('currency_code', '$') . $this->acumExec
  				. "<br/><b>Balance:</b> " . ($balance < 0 ? '<font color="red">':'') . config_option('currency_code', '$') . $balance . ($balance < 0 ? '</font>':'');
  		}
  		
  		function Draw($g, $returnGraphObject){
			return parent::Draw($g, $returnGraphObject);
  		}
  	}
?>