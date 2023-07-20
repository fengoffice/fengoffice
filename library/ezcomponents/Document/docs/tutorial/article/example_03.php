<?php

require_once '../tutorial_autoload.php';

$graph = new ezcGraphBarChart();
$graph->title = 'Random data with average line.';
$graph->legend->position = ezcGraph::BOTTOM;
$graph->palette = new ezcGraphPaletteEzBlue();

$graph->xAxis = new ezcGraphChartElementNumericAxis();
$graph->xAxis->axisLabelRenderer = new ezcGraphAxisBoxedLabelRenderer();
$graph->xAxis->majorStep = 1;

$data = array();
for ( $i = 0; $i <= 10; $i++ )
{
    $data[$i] = mt_rand( -5, 5 );
}

// Add data
$graph->data['random data'] = $dataset = new ezcGraphArrayDataSet( $data );

$average = new ezcGraphDataSetAveragePolynom( $dataset, 3 );
$graph->data[(string) $average->getPolynom()] = $average;
$graph->data[(string) $average->getPolynom()]->displayType = ezcGraph::LINE;
$graph->data[(string) $average->getPolynom()]->symbol = ezcGraph::NO_SYMBOL;

$graph->options->fillLines = 150;

$graph->render( 400, 150, 'example_03.svg' );

?>
