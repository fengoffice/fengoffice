<?php
  	/* ProjectCharts class
  	* 
  	* @author Carlos Palma <chonwil@gmail.com>
  	*/
  	class TasksByDueDate extends ProjectChart {

  		function __construct(){
  			parent::__construct();
  			$this->setDisplayId(20); //Default display type = Pie
  			$this->setTypeId(2);
  		}
  		
  		function ExecuteQuery(){
  			$this->data = array();
  			$date = new DateTimeValue(Time());
  			
			$notYet = ProjectTasks::findAll(array(
  				'conditions' => 'created_by_id = ' . logged_user()->getId() . ' AND ( due_date = \'0000-00-00 00:00:00\' OR due_date > \'' . substr($date->toMySQL(),0,strpos($date->toMySQL(), ' ')) . "')"));
			
			$today = ProjectTasks::findAll(array(
  				'conditions' => 'created_by_id = ' . logged_user()->getId() . ' AND due_date = \'' . substr($date->toMySQL(),0,strpos($date->toMySQL(), ' ')) . "'"));
			
			$past = ProjectTasks::findAll(array(
  				'conditions' => 'created_by_id = ' . logged_user()->getId() . ' AND due_date > \'1900-01-01 00:00:00\' AND due_date < \'' . substr($date->toMySQL(),0,strpos($date->toMySQL(), ' ')) . "'"));
  			
	    	
			$value = 0;
	    	if(isset($past))
	    		$value = count($past);
	    	$this->data['values'][0]['labels'][] = 'Overdue';
	    	$this->data['values'][0]['values'][] = $value;
	    	
			$value = 0;
	    	if(isset($notYet))
	    		$value = count($notYet);
	    	$this->data['values'][0]['labels'][] = 'Not yet due';
	    	$this->data['values'][0]['values'][] = $value;
	    	
			$value = 0;
	    	if(isset($today))
	    		$value = count($today);
	    	$this->data['values'][0]['labels'][] = 'Due today';
	    	$this->data['values'][0]['values'][] = $value;
  		}
  		
  		function Draw($g, $returnGraphObject){
			return parent::Draw($g, $returnGraphObject);
  		}
  	}
?>