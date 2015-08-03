<?php
  /* ProjectChartFactory class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  	class ProjectChartFactory {
  	
  		function __construct() {
    	} // __construct
 
    	/**
    	 * Returns a new chart object given the specified chart id
    	 *
    	 * @param integer $chartId
    	 * @return ProjectChart
    	 */
    	function loadChart($chartId){
    		$chart = ProjectCharts::findById($chartId);
    		$res = $this->getChart($chart->getTypeId());
    		
    		$res->setFromAttributes($chart->getAcceptableAttributes());
    		$res->setDisplayId($chart->getDisplayId());
    		$res->setTypeId($chart->getTypeId());
    		$res->setTitle($chart->getTitle());
    		$res->setNew(false);
    		$res->setId($chart->getId());
    		$res->setProject($chart->getProject());
    		$res->setCreatedById($chart->getCreatedById());
    		$res->setCreatedOn($chart->getCreatedOn());
    		$res->setDeleted($chart->isDeleted());
    		$res->setTags($chart->getTags());
    		$res->setUpdatedById($chart->getUpdatedById());
    		$res->setUpdatedOn($chart->getUpdatedOn());
    		return $res;
    	}
    	
    	/**
    	 * Returns a new chart object given the specified type id
    	 *
    	 * @param integer $chartTypeId
    	 * @return ProjectChart
    	 */
  		function getChart($chartTypeId){
  			$ct = $this->getChartTypes();
  			try{
  				$res = new $ct[$chartTypeId]();
  			} catch(Exception $e){
  				echo $e->getMessage(); die();
  			}
  			return $res;
  		}
  		
  		/**
  		 * Returns an array of all the available chart types
  		 *
  		 * @return array
  		 */
  		function getChartTypes(){
  			return array(
  				"1" => "ObjectTypeCount",
  				"2" => "TasksByDueDate",
  				"3" => "BudgetExecution",
  				"4" => "BudgetExecutionTrack",
  				"5" => "MonthlyBudgetExecution",
  				"6" => "ProjectBillingByUser");
  		}
  	
  		function getChartDisplays(){
  			return array(
  				10 => "bars chart",
  				11 => "bar glass",
  				12 => "bar 3d",
  				13 => "bar sketch",
  				20 => "pie chart",
  				30 => "lines chart",);
  		}
  	}
   
?>