<?php
  /* ProjectCharts
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  class ProjectChartParams extends BaseProjectChartParams {
    
    /**
    * Return charts that belong to specific project
    *
    * @param Project $project
    * @return array
    */
    static function getProjectChartParams(ProjectChart $chart) {
      $conditions = array('`chart_id` = ?', $chart->getId());
      
      return self::findAll(array(
        'conditions' => $conditions,
        'order' => '`id` ASC',
      )); // findAll
    } // getProjectCharts
    
  } // ProjectCharts 
?>