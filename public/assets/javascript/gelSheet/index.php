 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>OpenGoo Gel SpreadSheet</title>
    
    <style type="text/css" media="screen">
	    @import url("./themes/opengoo/style.css");
	    @import url("./themes/opengoo/toolbar.css");
	    @import url("../../themes/default/extjs/css/ext-all.css");
    </style>

    <!--[if IE]>
    <style type="text/css" media="screen">
		@import url("./ie.css");
	</style>	
	<![endif]-->
	
	<!--[if lte IE 7]>
    <style type="text/css" media="screen">
		@import url("./ie7.css");
	</style>	
	<![endif]-->
	<script type="text/javascript" src="./server/?c=Language&m=getLanguages"></script>
	<script type="text/javascript" src="../extjs/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" src="../extjs/ext-all.js"></script>
	
	<?php 
    $cnf = array();
    $cnf['jspath'] = array();

    
    $includes = "";
    $developing = false;
	
   //<!--******************* External Libraries *********************-->
//    $cnf['jspath'][] = "../extjs/adapter/ext/ext-base.js";
//    $cnf['jspath'][] = "../extjs/ext-all.js"; 
    
	//<!--******************* Handlers/Managers *********************-->
	$cnf['jspath'][] ="./client/dev/handlers/key_handler.js";
	$cnf['jspath'][] ="./client/dev/handlers/style_handler.js";
	$cnf['jspath'][] ="./client/dev/handlers/names_handler.js";
	$cnf['jspath'][] ="./client/dev/handlers/json_handler.js";
   	
   	$cnf['jspath'][] ="./client/dev/common/style_wrapper.js";
   	$cnf['jspath'][] ="./client/dev/common/event_wrapper.js";
   	$cnf['jspath'][] ="./client/dev/common/error.js";
   	$cnf['jspath'][] ="./client/dev/common/functions.js";
   	
   	
   	//<!--******************* Comunication Classes *********************-->
    $cnf['jspath'][] ="./client/dev/comm/comm_manager.js";
    
   	//<!--******************* Interface Classes *********************-->
   	$cnf['jspath'][] ="./client/dev/toolbar/toolbar_callback.js";
   	$cnf['jspath'][] ="./client/dev/toolbar/toolbar.js";
   	
   	$cnf['jspath'][] ="./client/dev/application/colorPalette.js";
    $cnf['jspath'][] ="./client/dev/application/environment.js";
    $cnf['jspath'][] ="./client/dev/application/application.js";
    $cnf['jspath'][] ="./client/dev/application/configs.js";
    $cnf['jspath'][] ="./client/dev/application/application_api.js";
    $cnf['jspath'][] ="./client/dev/application/application_dialogs.js";
    $cnf['jspath'][] ="./client/dev/application/fonts.js";
  
    $cnf['jspath'][] ="./client/dev/grid/grid_selection.js";
    $cnf['jspath'][] ="./client/dev/grid/grid_operations.js";
    $cnf['jspath'][] ="./client/dev/grid/grid.js";
    $cnf['jspath'][] ="./client/dev/grid/grid_gui.js";
    $cnf['jspath'][] ="./client/dev/grid/grid_scrollbar.js";
    $cnf['jspath'][] ="./client/dev/grid/grid_components.js";
    $cnf['jspath'][] ="./client/dev/grid/grid_events.js";
    $cnf['jspath'][] ="./client/dev/grid/resize_handler.js";
    $cnf['jspath'][] ="./client/dev/grid/vcell.js";
    $cnf['jspath'][] ="./client/dev/grid/vrow.js";
    $cnf['jspath'][] ="./client/dev/grid/vcolumn.js";
   
    //<!--******************* Medium Layer *************************>
    $cnf['jspath'][] ="./client/dev/controllers/command_controller.js";
    $cnf['jspath'][] ="./client/dev/controllers/font_style_controller.js";    
    
    $cnf['jspath'][] ="./client/dev/model/model.js";
    $cnf['jspath'][] ="./client/dev/model/model_style_operations.js";
    $cnf['jspath'][] ="./client/dev/model/model_events.js";
    $cnf['jspath'][] ="./client/dev/model/model_navigation.js";
    $cnf['jspath'][] ="./client/dev/model/model_selection.js";
    $cnf['jspath'][] ="./client/dev/model/selection_handler.js";
    
	//<!--******************* Logic Classes *************************-->
	$cnf['jspath'][] ="./client/dev/logic/book.js";
    $cnf['jspath'][] ="./client/dev/logic/sheet.js";
    $cnf['jspath'][] ="./client/dev/logic/sheet_style_operations.js";    
    $cnf['jspath'][] ="./client/dev/logic/references.js";
    $cnf['jspath'][] ="./client/dev/logic/formula_parser.js";
    $cnf['jspath'][] ="./client/dev/logic/cell.js";
    $cnf['jspath'][] ="./client/dev/logic/row.js";
    $cnf['jspath'][] ="./client/dev/logic/column.js";
    $cnf['jspath'][] ="./client/dev/logic/range.js";
    $cnf['jspath'][] ="./client/dev/logic/store.js";
    $cnf['jspath'][] ="./client/dev/logic/functions.js";
    $cnf['jspath'][] ="./client/dev/logic/calculator.js";
    
     
    $fileName = "gelsheet.min.js";
    
    if($developing == false){
    	//if minified file doesn't exists build it
    	if(!file_exists($fileName)){
			include_once 'jsmin-1.1.1.php';
	 		$includes = "";
	        foreach ($cnf['jspath']  as $file) {
	            $includes .= file_get_contents($file) . "\n";
	        }
	        $includes = JSMin::minify($includes);
	        file_put_contents($fileName, $includes);
    	}
        $includes = "\t<script type=\"text/javascript\" src=\"".$fileName."\"></script>\n";
//		echo "<script type=\"text/javascript\">\n" .$includes ."\n</script>\n";
		echo $includes; 
	}else{
		foreach ($cnf['jspath'] as $file) 
			$includes .= "\t<script type=\"text/javascript\" src=\"".$file."\"></script>\n";
		echo $includes;
	}
		
		
   ?> 
    <script type="text/javascript" >
    	
    	
        function load(){			
        	window.ogID = '' ;
			window.ogWID = '' ;
	<?php if (isset($_GET['id'])) : ?>
			window.ogID = <?php echo $_GET['id'] ?>;
	<?php endif; ?>
	<?php if (isset($_GET['wid'])) : ?>
			window.ogWID = <?php echo $_GET['wid'] ?>;
	<?php endif; ?>

        	var application = new Application(document.body);
	<?php if (isset($_GET['book'])) :  ?>
			application.loadBook(<?php echo $_GET['book'] ?>);
	<?php endif; ?>


			// Display logo..
			var logo_div = document.getElementById('logo');
			if ( logo_div ) {
				logo_div.style.display = "block"; 
			}
        	
        }
    </script>
</head>

<body id="body" onload="load();" >
  <div id="logo" style="z-index: 1001; display: none" ></div>
  <div id="west"></div>
  <div id="north">
  </div>
  <div id="dialog-container" style="position: absolute; z-index: 50000 ;"></div>
  <div id="center"></div>
  <div id="east" style="width:200px;height:200px;overflow:hidden;"></div>
  <div id="south"></div>
</body>
</html>
