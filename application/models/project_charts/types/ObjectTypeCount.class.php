<?php
  	/* ProjectCharts class
  	*
  	* @author Carlos Palma <chonwil@gmail.com>
  	*/
  	class ObjectTypeCount extends ProjectChart {

  		function __construct(){
  			parent::__construct();
  			$this->setDisplayId(20); //Default display type = Pie
  			$this->setTypeId(1);
  		}
  		
  		function ExecuteQuery(){
  			$this->data = array();
  			
  			$queries = ObjectController::getDashboardObjectQueries(active_project(), null, true);
			$query = '';
			foreach ($queries as $k => $q){
				if (substr($k, -8) == 'Comments') continue;
				if($query == '')
					$query = $q;
				else 
					$query .= " \n union \n" . $q;
			}
			
			$ret = 0;
			$res = DB::execute($query);
	    	if(!$res)  return $ret;
	    	$rows=$res->fetchAll();
			if(!$rows) return  $ret;
	    	foreach ($rows as $row){
	    		$value = 0;
	    		if(isset($row['quantity']))
	    			$value = $row['quantity'];
	    		$this->data['values'][0]['labels'][] = $row['objectName'];
	    		$this->data['values'][0]['values'][] = $value;
	    	}//foreach
  		}
  		
  		function Draw($g, $returnGraphObject){
			return parent::Draw($g, $returnGraphObject);
  		}
  	}
?>