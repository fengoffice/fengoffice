<script src="js/swfobject.js" />
<?php
chdir(dirname(__FILE__)."/lib");
include "open-flash-chart-object.php";

srand((double)microtime()*1000000);
$data = array();

// add random height bars:
for( $i=0; $i<10; $i++ )
  $data[] = rand(1,9);

require_once(OFC_LIBRARY_PATH . '/lib/OFC/OFC_Chart.php');

$title = new OFC_Elements_Title( date("D M d Y") );

$bar = new OFC_Charts_Bar_3d();
$bar->set_values( $data );
$bar->colour = '#D54C78';

$x_axis = new OFC_Elements_Axis_X();
$x_axis->set_3d( 5 );
$x_axis->colour = '#909090';
$x_axis->set_labels( array('poroto', '11-05-2011', '12-05-2011', '13-05-2011', '14-05-2011', '15-05-2011', '16-05-2011', '17-05-2011', '18-05-2011', '19-05-2011') );

$chart = new OFC_Chart();
$chart->set_title( $title );
$chart->add_element( $bar );
$chart->set_x_axis( $x_axis );

file_put_contents('tmp/sarasa.json', $chart->toPrettyString());

open_flash_chart_object( 700, 500, ROOT_URL . '/tmp/sarasa.json' );