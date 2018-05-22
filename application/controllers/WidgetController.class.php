<?php
class WidgetController extends  ApplicationController {
	
    public function __construct() {

        parent::__construct();
        $this->addHelper('form', 'breadcrumbs', 'pageactions', 'tabbednavigation', 'company_website', 'project_website');
        
    }
    
    private function getAllConfigOptions($type) {

        $config_options = ContactConfigOptions::getOptionsByCategoryName("widget $type");

        return $config_options;
    }
    
    public function render_template_with_options() {
       
        $this->setLayout('empty');
        
        if (isset($_GET['type']) && !empty($_GET["type"])) {
            $type =  $_GET["type"];
        }else{
            return false;
        }

        $options = $this->getAllConfigOptions($type);
        tpl_assign('options', $options);
        
    }
   
    
}