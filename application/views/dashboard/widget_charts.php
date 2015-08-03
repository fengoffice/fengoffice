<div style="padding:10px">
<?php
	$pcf = new ProjectChartFactory();
	if (isset($charts)){
		$c = 1;
		foreach ($charts as $chart) {?>
			<div style="padding-bottom:10px; margin-bottom:10px;<?php echo $c != count($charts)? 'border-bottom:1px solid #DDDDDD':'' ?>">
			<div style="font-size:120%;font-weight:bold"><?php echo clean($chart->getTitle()) ?></div>
		<?php 
			$chart2 = $pcf->loadChart($chart->getId());
			$chart2->ExecuteQuery();
			echo $chart2->DashboardDraw();
			echo $chart2->PrintInfo();
			$c++;
		} // foreach?>
	<?php } // if isset ?>
	<?php if (isset($billing_chart_data) && is_array($billing_chart_data) && count($billing_chart_data) > 0) { 
		$billing_chart = $pcf->getChart(6);
		$billing_chart->setData($billing_chart_data);
		$billing_chart->setTitle(lang('total billing by user'));
		echo $billing_chart->DashboardDraw();
		echo $billing_chart->PrintInfo();
	}?>
	 </div>
</div>