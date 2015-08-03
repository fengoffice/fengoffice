og.PrintCalendar = {};

og.PrintCalendar.printCalendar = function(genid){
	var printWindow = this.createPrintWindow(genid);
	var html = document.getElementById(genid + 'view_calendar').innerHTML.replace(/gridcontainer/, '');
	printWindow.document.write(html);
	og.PrintCalendar.closePrintWindow(printWindow);
}

og.PrintCalendar.createPrintWindow = function(genid){
	var ancho = document.getElementById(genid + 'view_calendar').offsetWidth;
	var alto = document.getElementById(genid + 'view_calendar').offsetHeight;
	var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,scrollbars=yes,width="+ancho+", height="+(alto+20)+", left=100, top=25";
	var printWindow = window.open("","",disp_setting);

	var cal_height = 0;
	var container = $('#calendar');
	while ($(container) && $(container).height() == 0) {
		container = $(container).parent();
	}
	if ($(container)) cal_height = $(container).height();
	if (cal_height > 0) {
		cal_height = (cal_height > 600 ? cal_height : 600) + 'px';
	} else {
		cal_height = '100%';
	}
	
	printWindow.document.open(); 
	printWindow.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml">');
	printWindow.document.write('<html><head><title>' + lang('print') + '</title>'); 
	printWindow.document.write('<link href="' + og.hostName + '/public/assets/themes/default/stylesheets/website.css" rel="stylesheet" type="text/css">');
	printWindow.document.write('<link href="' + og.hostName + '/public/assets/themes/default/stylesheets/event/week.css" rel="stylesheet" type="text/css">');
	printWindow.document.write('<link href="' + og.hostName + '/public/assets/themes/default/stylesheets/event/day.css" rel="stylesheet" type="text/css">');
	printWindow.document.write('<link href="' + og.hostName + '/public/assets/themes/default/stylesheets/calendar_print.css" rel="stylesheet" type="text/css">');
	printWindow.document.write('<style type="text/css" media="print">div.page {writing-mode: tb-rl;height: 80%;margin: 10% 0%;}</style>');
	printWindow.document.write('</head><body onLoad="self.print()"><div class="page">');
	return printWindow;
}


og.PrintCalendar.closePrintWindow = function(printWindow){
	printWindow.document.write('</div></body></html>');    
	printWindow.document.close();
	printWindow.focus();
}