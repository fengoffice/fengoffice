og.reports = {};
og.reports.createPrintWindow = function(title) {
	var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,scrollbars=yes,";
	var printWindow = window.open("","",disp_setting);
	printWindow.document.open(); 
	printWindow.document.write('<html><head><title>' + title + '</title>');
	printWindow.document.write('<link href="' + og.hostName + '/public/assets/themes/default/stylesheets/website.css" rel="stylesheet" type="text/css">');
	printWindow.document.write('<link href="' + og.hostName + '/public/assets/themes/default/stylesheets/general/rewrites.css" rel="stylesheet" type="text/css">');
	printWindow.document.write('</head><body onLoad="self.print()" id="body" style="padding:10px;">');
	printWindow.document.write(og.reports.buildReportHeader(title));
	return printWindow;
}

og.reports.buildReportHeader = function(title) {
	var html = '<div class="report-print-header"><div class="title-container"><h1>' + title + '</h1></div>';
	html += '<div class="company-info">';
	if (og.ownerCompany.logo_url) {
		html += '<div class="logo-container"><img src="'+og.ownerCompany.logo_url+'"/></div>';
	} else {
		html += '<div class="comp-name-container">'+ og.ownerCompany.name +'</div><br />';
	}
	if (og.ownerCompany.address) {
		html += '<div class="address-container">'+og.ownerCompany.address+'</div>';
		html += '<br />';
	}
	if (og.ownerCompany.email) {
		html += '<div class="email-container link-ico ico-email">'+og.ownerCompany.email+'</div>';
	}
	if (og.ownerCompany.phone) {
		html += '<div class="phone-container link-ico ico-phone">'+og.ownerCompany.phone+'</div>';
	}
	html += '</div></div>';
	html += '<div class="clear"></div>';

	return html;
}

og.reports.closePrintWindow = function(printWindow) {
	printWindow.document.write('</body></html>');    
	printWindow.document.close();
	printWindow.focus();
}

og.reports.printReport = function(genid, title) {
	var printWindow = og.reports.createPrintWindow(title);

	printWindow.document.write(document.getElementById(genid + 'report_container').innerHTML);
	
	og.reports.closePrintWindow(printWindow);
}
