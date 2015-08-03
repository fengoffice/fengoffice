<?php

require_once '../tutorial_autoload.php';
$wikidata = include '../tutorial_wikipedia_data.php';

// Create a bar chart
$graph = new ezcGraphBarChart();
$graph->data['German articles'] = new ezcGraphArrayDataSet( $wikidata['German'] );
$graph->render( 400, 150, 'example_02.svg' );

?>
