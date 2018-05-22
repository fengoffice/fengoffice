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

og.reports.fillDisabledParams = function(genid, params) {
	var post_vars = $("#post"+genid).val().replace(/\'/g,'"');
	var post_json = null;
	try {
		post_json = $.parseJSON(post_vars);
	} catch (e) {}
	
	if (post_json && post_json.disabled_params) {
		for (x in post_json.disabled_params) {
			if (typeof(post_json.disabled_params[x]) == 'function') continue;
			params['disabled_params['+x+']'] = post_json.disabled_params[x];
		}
	}
}

og.reports.printReport = function(genid, title, report_id) {
	
	var params = {id: report_id};
	var params_json = $("#params_"+genid).val();
	var p = Ext.util.JSON.decode(params_json);
	for (var x in p) {
		params['params['+x+']'] = p[x];
	}
	
	og.reports.fillDisabledParams(genid, params);
	
	og.openLink(og.getUrl('reporting', 'print_custom_report', params), {
		callback: function(success, data) {
			var printWindow = og.reports.createPrintWindow(title);
			printWindow.document.write(data.html);
			og.reports.closePrintWindow(printWindow);
		}
	});
}

og.reports.printNoPaginatedReport = function(genid, title) {
	var printWindow = og.reports.createPrintWindow(title);

	printWindow.document.write(document.getElementById(genid + 'report_container').innerHTML);
	
	og.reports.closePrintWindow(printWindow);
}


og.reports.go_to_custom_report_page = function(params) {
	var offset = params.offset;
	var limit = params.limit;
	var link = params.link;
	if (!offset) offset = 0;
	if (!limit) limit = 50;
	if (!link) return;

	var report_config_el = $(params.link).closest("form").children("[name='post']");
	if (!report_config_el || report_config_el.length == 0) return;

	var str = $(report_config_el[0]).val();
	str = str.replace(/'/ig, '"');

	// initial parameters
	var report_config = $.parseJSON(str);
	
	// more params
	var more_params_el = $(params.link).closest("form").children("[name='params']");
	if (more_params_el) {
		var more_str = $(more_params_el[0]).val().replace(/'/ig, '"').replace(/\\/g, "");;
		if (more_str) {
			report_config['params'] = more_str;
		}
	}

	// fixed parameters
	report_config.offset = offset;
	report_config.limit = limit;
	report_config.replace = 1;
	
	og.openLink(og.getUrl(report_config.c, report_config.a, report_config));
	
}