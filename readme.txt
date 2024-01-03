	About Feng Office 3.10.8.0-beta3
	=================================
	
	Feng Office is a Collaboration Platform and Project Management System.
	It is licensed under the Affero GPL 3 license.
	
	For further information, please visit:
		* http://www.fengoffice.com/
		* http://fengoffice.com/web/forums/
		* http://fengoffice.com/web/wiki/
		* http://sourceforge.net/projects/opengoo
	
	Contact the Feng Office team at:
		* contact@fengoffice.com
	
	
	System requirements
	===================
	
	Feng Office requires a running Web Server, PHP (5.0 or greater) and MySQL (InnoDB
	support recommended). The recommended Web Server is Apache.
	
	Feng Office is not PHP4 compatible and it will not run on PHP versions prior
	to PHP 5.
	
	Recommendations:
	
	PHP 7.1+
	MySQL 5+ with InnoDB support
	Apache 2.0+
	
		* PHP    : http://www.php.net/
		* MySQL  : http://www.mysql.com/
		* Apache : http://www.apache.org/
	
	Please have a look at our requirements here:
	http://www.fengoffice.com/web/wiki/doku.php/installation:server_reqs
	
	Alternatively, if you just want to test Feng Office and you don't care about security
	issues with your files, you can download XAMPP, which includes all that is needed
	by Feng Office (Apache, PHP 7, MySQL) in a single download.
	You can configure MySQL to support InnoDB by commenting or removing
	the line 'skip-innodb' in the file '<INSTALL_DIR>/etc/my.cnf'.
	
		* XAMPP  : http://www.apachefriends.org/en/xampp


	Installation
	============
	
	1. Download Feng Office - http://www.fengoffice.com/web/community/downloads.php
	2. Unpack and upload to your web server
	3. Direct your browser to the public/install directory and follow the installation procedure
	
	Further information can be found here: http://www.fengoffice.com/web/wiki/doku.php/installation:installation
 
	You should be finished in a matter of minutes.
	
	4. Some functionality may require further configuration, like setting up a cron job.
	Check the wiki for more information: http://fengoffice.com/web/wiki/doku.php/setup
	
	WARNING: Default memory limit por PHP is 8MB. As a new Feng Office install consumes about 10 MB,
	administrators could get a message similar to "Allowed memory size of 8388608 bytes exhausted".
	This can be solved by setting "memory_limit=32" in php.ini.
	
	Upgrade
	=======
	
	There currently are two kind of upgrades:
	1- From 3.X to 3.X (or from 2.X to 2.X, or 1.X to 1.X)
	2- From 1.X to 2.X
	
	Either way, we strongly suggest reading the following article in our Wiki for further information:
	http://www.fengoffice.com/web/wiki/doku.php/installation:migration
	
	Note: Plugins must also be updated (if it corresponds)
	
	Open Source Libraries
	=====================
	
	The following open source libraries and applications have been adapted to work with Feng Office:
	- ActiveCollab 0.7.1 - http://www.activecollab.com
	- ExtJs - http://www.extjs.com
	- jQuery - http://www.jquery.com
	- jQuery tools - http://flowplayer.org/tools/
	- jQuery Collapsible - http://phpepe.com/2011/07/jquery-collapsible-plugin.html
	- jQuery Scroll To - http://flesler.blogspot.com/2007/10/jqueryscrollto.html
	- jQuery ModCoder - http://modcoder.com/
	- jQuery User Interface - http://jqueryui.com/
	- jQuery ImgAreaSelect plugin - http://odyniec.net/projects/imgareaselect/
	- jQuery SimpleModal plugin - http://www.ericmmartin.com/projects/simplemodal/
	- H5F (HTML 5 Forms) - http://thecssninja.com/javascript/H5F
	- http://flowplayer.org/tools/
	- Reece Calendar - http://sourceforge.net/projects/reececalendar
	- Swift Mailer - http://www.swiftmailer.org
	- Open Flash Chart - http://teethgrinder.co.uk/open-flash-chart
	- PHPExcel - https://github.com/PHPOffice/PHPExcel
	- Slimey - http://slimey.sourceforge.net
	- FCKEditor - http://www.fckeditor.net
	- JSSoundKit - http://jssoundkit.sourceforge.net
	- PEAR - http://pear.php.net
	- Gelsheet - http://www.gelsheet.org
	- TimeZoneDB - https://timezonedb.com
	
	
	Changelog
	=========
	
	Since 3.10.8.0-beta2
	-----------------------------------
	feature: invoice templates allow to show subprojects in projects tabl… (#2326)
	feature: add new column for markup amount at invoice templates (#2322)
	feature: allow to select date format in invoice templates (#2319)
	feature: Project earnings report: add 'Project progress' column (#2324)
	feature: new readme.md (#2321)
	bugfix: Budget by tasks report: Rename select options for the 'group by' option (#2323)
	bugfix: Notes widget: Keep the styling and line breaks of the notes (#2318)

	Since 3.10.8.0-beta1
	-----------------------------------
	bugfix: client form not saving associated object properties (#2316)
	bugfix: Add line breaks in memo custom properties when rendering (#2315)
	bugfix: Remove 'completed_on' date condition when getting earned value from tasks (#2314)
	bugfix: don't show the warning in widget when the member is selected (#2313)
	bugfix: fix time entries qbo sync in background (#2312)
	bugfix: fix expenses2 installer queries (#2311)
	bugfix: fixed timesheets report dont uses overtime (#2310)
	bugfix: 'Forecasted revenue' calc -> exclude bud expenses assigned to tasks (#2309)
	bugfix: widget show message (#2304)
	bugfix: time entry cost is not transferred to qbo (#2301)
	bugfix: Project billing report: in excel export use price in expenses table (#2299)
	bugfix: the search function under the workflow permission is not working (#2283)
	bugfix/auto scroll issue (#2317)
	bugfix: can't add lines to invoice in php8 (#2308)
	bugfix: cant classify email with attachments by drag and drop (#2307)
	bugfix: can't add task with subtasks (#2305)
	bugfix: can't instantiate templates in php8 (#2302)

	Since 3.10.7.x
	-----------------------------------
	bugfix: php8 compatibility fixes (#2295)
	bugfix: when sending vars to invoice print view 'invoice_notebook' must be an array always (#2292)
	bugfix: don't call abstract class ContentDataObjects methods directly, it gives errors in php8 and also we don't need to do that (#2291)
	bugfix: resize work performed data table in task module (#2273)
	bugfix: Remove manual estimated cost option for tasks (#2234)

	Since 3.10.7.4
	-----------------------------------
	improvement: forecast vs actual report (#2296)
	improvement: Contract status report: rename column to 'Cost remaining budget (vs. contract amount)' (#2298)
	bugfix: Correct filtering in widgets for the current year (#2297)
	bugfix: times transferred twice to qbo (#2294)

	Since 3.10.7.3
	-----------------------------------
	bugfix: remove default value of text columns to be compatible with Mysql (#2288)
	bugfix: after time drag and drop the status classification is doubled (#2289)

	Since 3.10.7.2
	-----------------------------------
	feature: add description to contract hours custom property (#2282)
	bugfix: widget breadcrumbs user preference is not using the default value (#2285)
	bugfix: add slashes to escape quotes (#2284)
	bugfix: string numbers needs to be casted to float before operating with them in php8 (#2286)
	bugfix: prevent error in notification manager when $log is not a valid object

	Since 3.10.7.1
	-----------------------------------
	feature: create forecasted vs actual revenue report (#2280) 
	bugfix: invoice print compatibility with line taxes and better alignment of line taxes in form (#2281)
	bugfix: exclude disabled user's calendar from import/export from google calendar (#2279)
	bugfix: add total_rows key to the response (#2278)
	bugfix: fix crpm installer when member_custom_properties is not installed

	Since 3.10.7.0
	-----------------------------------
	feature: develop 'contract status' and 'project cost summary' reports (#2259)
	bugfix: need to call save function on task when time added/edited/deleted (#2276)
	bugfix: if the user can't modify the associated member in the add/edit object form, then we must always inherit it from the main member, mo matter if it is already classified in a member of the dimension (example: update project type when changing project) (#2269)
	bugfix: invoice template not appending expense category to line description (#2268)
	bugfix: Change deleting a contact notification (#2267) 

	Since 3.10.7.0-rc2
	-----------------------------------
	bugfix: fix advanced core update script (#2274)

	Since 3.10.7.0-rc1
	-----------------------------------
	bugfix: don't use db commit in advanced billing helpers, they are already used in the main transaction, and nested transactions can cause unconsistent data (#2272)
	bugfix: remove comment that went into html of invoice print
	bugfix: use en_us as default localization in upgrade script when no language is defined in config.php

	Since 3.10.7.0-beta11
	-----------------------------------
	bugfix: client company not classified in new project (#2266) 
	bugfix: invoice generation excludes expense with no unit price (#2265) 
	bugfix: fix js errors when getting properties of null or undefined at income.js (#2264)
	bugfix: recalculate total worked time when subtask added/removed (#2262) 
	improvement: utilization report improvements (#2263)
	improvement: Weekly view: add task/subtask structure to the task list (#2261) 
	improvement: allow to set permissions for non-custom reports (#2257) 

	Since 3.10.7.0-beta10
	-----------------------------------
	feature: add contract type to the project member (#2220) 
	bugfix: can't edit user permissions (#2258) 
	bugfix: don't use project's parent member data instead of invoice's project data when printing (#2260)

	Since 3.10.7.0-beta9
	-----------------------------------
	bugfix: project due and paid amount not recalcualted after saving payment receipt (#2256)
	bugfix: make expense categories selctor not multiple (#2255)
	bugfix: fix errors that prevented general search to execute (#2254)
	bugfix: don't autoclassify objects in billing clients, most important when instantiating task or expense tempaltes (#2252)
	bugfix: avoid adding one member to the time members (#2251)
	bugfix: fix infinite reloading when opening a task from external link and task has budgeted expense list

	Since 3.10.7.0-beta8
	-----------------------------------
	bugfix: more overview performance issues (#2249) 
	bugfix: notes column not present in contacts list (#2246)
	bugfix: error adding expense from mobile when status dim is not installed (#2245)
	bugfix: php8 compatibility fixes (#2244) (#2247) 
	bugfix: mobile expenses add: no client assigned if approval status is set

	Since 3.10.7.0-beta7
	-----------------------------------
	bugfix: Change Contract hours per day it should say contract hours (#2243)
	bugfix: clients tab always empty (#2242) 
	bugfix: performance issues dashboard (#2241) 
	bugfix: invoice list initial load empty list (#2237) 
	bugfix: format number and amount custom properties in group totals (#2236) 
	bugfix: remove static def from function that uses $this, this was causing error when saving permissions (#2235)
	bugfix: minor php8 compatibility fix (#2239)
	bugfix: fix mail update fn, column length too big

	Since 3.10.7.0-beta6
	-----------------------------------
	bugfix: expenses from mobile are inheriting project's billing client (#2233)
	bugfix: php8 compatibility fixes (#2232)

	Since 3.10.7.0-beta5
	-----------------------------------
	bugfix: in project form get client billing address before main address (#2231) 
	bugfix: when adding timeslot don't initialize clients selector with billing clients (#2231) 
	bugfix: fix hardcoded order by "project_number" that was affecting project custom reports (#2230) 
	bugfix: Remove condition that prevented creating hidden input to read selected member in weekly view (#2228)
	bugfix: php8 compatibility fixes de 3.10.7.0-beta4 (#2226)
	bugfix: invoices list order by not working for custom props and due date (#2229)
	bugfix: import tool expenses importing issues (#2227)

	Since 3.10.7.0-beta4
	-----------------------------------
	bugfix: Improve performance for importing time entries (#2224)
	bugfix: add time: cast operands to use when parsing hours and minutes to prevent errors in php8
	bugfix: fix the critical issue that puts totals in zero after editing an invoice

	Since 3.10.7.0-beta3
	-----------------------------------
	bugfix: fix several critical issues

	Since 3.10.7.0-beta2
	-----------------------------------
	feature: new split invoice mode, recalculate each line amounts using allocation percentage (#2203)
	bugfix: duplicated subtotal line when printing invoice with taxes (#2214)
	bugfix: subscribers are lost when editing invoice (#2213)
	bugfix: when using qbo plugin and invoice split feature only the first invoice was being synchronized after creation (#2212)
	bugfix: lump sum invoices duplicates the expense amount if the project has a subproject (#2211)
	bugfix: ensure that we can use advanced billing functions before executing them in other plugins (#2208)
	bugfix: fx function that makes dimension groups in tasks list (#2216)
	bugfix: missing es_es and es_la translations (#2217) 

	Since 3.10.7.0-beta1
	-----------------------------------
	feature: add generic dimension columns in object picker for every object type and use breadcrumbs (#2201)
	feature: always ask if overwrite expense amount when product type change or budgeted expense change (#2199) 
	feature: Invoice templates: Automatic generation of line items -> Expenses -> (#2187)
	bugfix: improve how mandatory labor category field is handled in weekly view (#2194)
	bugfix: Remove 'Calculated method' duplicate from the task view (#2198)
	bugfix: plugin update fixes (#2210) 
	bugfix: broken dashboard in latest beta (#2209) 
	bugfix: fix some static/non-static function calls (#2207)
	bugfix: Add the missing columns in the update script for advanced_billing plugin (#2206)
	bugfix: non static getObjectTypeId() was called with self instead of $this in ContentDataObjects listing (#2204)
	bugfix: ensure that we can use advanced billing functions before executing them in other plugins (#2208)
	bugfix: footer sometimes is cut in half instead of rendering complete in next page (#2202)
	bugfix: fix project name at invoice print (#2193)

	Since 3.10.6.x
	-----------------------------------
	feature: invoice templates new option to group by labor category and task (#2175)
	feature: Rename timeslots to time entries (#2091) 
	feature: add 'billable' custom property to expense category (#2075) 
	feature: task financial add tm fixed fee (#2130) 
	feature: php8 compatibility changes (#2188)
	bugfix: order project by displayname for mobile (#2186)

	Since 3.10.6.8
	-----------------------------------
	bugfix: recalculate financials when the subtask is edited in the task form (#2195)
	bugfix: suggest using command line to upgrade plugins when no error message is found

	Since 3.10.6.7
	-----------------------------------
	feature: copy task templates (#2192)
	bugfix: member custom report issues (#2190) 
	bugfix: calculation for executed cost/price for tasks and project financials (#2191)

	Since 3.10.6.6
	-----------------------------------
	feature: Import tool: Ability to search tasks by name, project and job phase members (#2184)
	feature: Import tool: Add functionality to connect actual expense to task (#2184)
	feature: append member names to weekly view task selector (#2183) 
	bugfix: when adding/editing email templates the subject and to fields were initialized always as if were an email tempate for invoicing, (#2185)
	bugfix: in budget by tasks report excel export: include subtasks like in the print view (#2182)
	bugfix: deactivate mail rules plugin when deactivating mail plugin (#2181)
	bugfix: add default hardcoded mail template to use only when no email template is found for notifications (#2180)
	Invoice Margins need adjustment (#2178)
	bugfix: member selector based on extjs sometimes don't load the initial list (#2177)

	Since 3.10.6.5
	-----------------------------------
	bugfix: in core dim plugin update: expenses plugin must be in latest version before saving timeslot and triggering project calculations (#2176)
	bugfix: Project earnings report: Rename 'percent complete' to 'Revenue vs. Budget %' (#2173)
	bugfix: Fix time entry imports, avoid force recalculations (#2172)
	bugfix: prevent inconsistencies with tsheets time off sync (#2171) 
	bugfix: project financial calculations invoiced, due, paid amounts (#2170) 
	bugfix: remove the condition that the description is mandatory under time tab for mobile (#2166)

	Since 3.10.6.4
	-----------------------------------
	bugfix: fix error when trying to add subtask if task in not opened in tasks tab (#2167)
	bugfix: When generating invoice using tasks, remove 'is_billable' constraint (#2164)
	bugfix: multi assignment subtasks are not inheriting is_billable property (#2163)

	Since 3.10.6.3
	-----------------------------------
	feature: Add columns and filters to task picker (#2150) 
	feature: add app log details to user edition (#2143) 
	bugfix: fix plugin installer scripts (#2135)

	Since 3.10.6.2
	-----------------------------------
	bugfix: job task members not shown in time list (#2160) 
	bugfix: rollback Task: In budgeted expenses: When you enter an actual expense is not taking the price and the cost (#2159)
	bugfix: if vendors dimension not used then use paid_by when sync expenses to qbo (#2158)
	bugfix: time entries added using import tool ended with a company instead of an user, the query was searching only by name (#2155)
	bugfix: Show invoicing status; Fix 'is_billable' inheriting from labor category (#2154)
	improvement: run plugins update within general update process (#2157)
	improvement: Actual expenses quick add: Support payment method and payment account fields (#2156)
	improvement: custom login plugin - suport svg for custom logo (#2153) 

	Since 3.10.6.1
	-----------------------------------
	feature: new api (#2112)
	bugfix: Change password, picture, etc. loses user default labor category. Any call to save() function of an user was updating the category no matter if it was from add/edit form or not (#2152)
	bugfix: don't trigger qbo sync after each action in weekly view, queue the timeslots to be synchronized later by a cron event (#2151)
	bugfix: Fix scenarios when the labor category is mandatory in the weekly view (#2149)
	bugfix: add-vendor-name-and-bill-to-in-description-invoice-fields
	bugfix: fix all line discounts and labor subtotal (#2147) 
	bugfix: fix financials totals query when not grouping task list, the listing joins were duplicating several values depending on the dimension filters (#2146)
	bugfix: in widget use task's earned value if the estimated price is manual (#2145)
	bugfix: fix bad lang definition that overwrites empty langs (affecting invoice template selector) (#2144)
	bugfix: fix-invoice-preview-max-width (#2140)

	Since 3.10.6.0
	-----------------------------------
	feature: QBO: new config option to select dimension to map with classes for expenses (#2120)
	bugfix: qbo sync pay methods and accounts for expenses are not enabled the first time (#2138)
	bugfix: invoice print: fix project name (#2136)
	bugfix: bad error handling when sync timeslots to tsheets (#2129)
	bugfix: fix user classification in non-permission dimensions (#2139)
	bugfix: installer errors in plugins (#2119)
	bugfix: remove default values for TEXT columns in install/upgrade scripts.
	bugfix; remove horizontal scroll from invoice view (#2133)
	bugfix: sent to thsheets button was not re synchronizing already synchronized timeslots (#2137)
	bugfix: fix logic that adds the subtotal line to not add it when not necesary (#2134)
	bugfix: subtotal appearing twice in invoice template (#2131)

	Since 3.10.6.0-rc1
	-----------------------------------
	bugfix: Remove special query for user "view history" and use the same query as for other objects (#2127)

	Since 3.10.6.0-beta3
	-----------------------------------
	bugfix: Fix the total calculations when 'advanced_payment_receipts' is inactive (#2117)
	bugfix: project earnings report remap to use persisted values (#2118)
	bugfix: installer errors in plugins (#2119) 
	bugfix: mobile-fix-search-project-by-display (#2122) 
	bugfix: Remove redundant logs (#2124)
	bugfix: fix-lang-on-expenses
	bugfix: editing invoice in the form loses break down by exp category (#2126)
	bugfix: Add Excel/CSV exports to the 'Budget by tasks' report (#2123)
	bugfix: Fix bug that prevented PDF exports in budget by tasks report (#2123)
	bugfix: Fix invoice tabs horizontal scroll and set main tab with preview (#2121)

	Since 3.10.6.0-beta2
	-----------------------------------
	feature: add default approval status to expenses in mobile (#2111)
	bugfix: Minor fixes for the 'Budget by tasks' report (#2114)
	bugfix: task change classification triggers is billable question always (#2113)
	bugfix: fix income hooks that renders task invoicing status in list and view (#2110)
	bugfix: invoice print fix show hide subtotals and break down by exp cat (#2108)
	bugfix: in time form after assign task, don't let user save before the classification is fully updated (#2116)
	bugfix: fix reset password error handling (#2109)

	Since 3.10.6.0-beta1
	-----------------------------------
	feature: improve inv template break down by exp category (#2105) 
	feature: Redesign "Budget by tasks" report (#2090) 
	feature: config option qbo sync only approved objects (#2072)
	bugfix: if labor cat is required the add time button is not present when no context is selected in time list (#2107)
	bugfix: when labor cat is required for time and we remove it from a time entry in the weekly view, it breaks (#2106)
	bugfix: fix-remove-generate-project-invoice-buttom-in-time-module (#2104)
	bugfix: fix/remove-blank-spaces-in-email-address-contacts-module (#2099) 

	Since 3.10.5.5
	-----------------------------------
	bugfix: some tasks had wrong executed labor and that transferred the error to the project financials (#2103)
	bugfix: add expense type to add/edit form in mobile (#2102)

	Since 3.10.5.4
	-----------------------------------
	feature: Add 'remaining budgeted (vs Contract)' column to the project earnings report (#2096)
	feature: make more flexible the due date calculation using payment terms, so we can add new terms and use the amount of days in the name to calculate the due date (#2089)
	bugfix: ensure that tasks are added only once when calculating task group financial totals for list (#2100)
	bugfix: Fix bugs that prevented running the report (#2095)
	bugfix: fix-api-timeslot-with-members (#2094)
	bugfix: after code refactor time is wrongly linked to invoices (#2093)
	bugfix: fix loop in upgrade when saving timeslots for worked time recalculation and project financials (#2092)
	bugfix: Expenses from a budget expense are not taking the info in the mobile version (#2101)

	Since 3.10.5.3
	-----------------------------------
	bugfix: Fix the project financials calculation (#2088) 
	bugfix: memory exhausted when calculating financial totals for tasks list without filters and without grouping (#2087)
	bugfix: several fixes in installer

    Since 3.10.5.2
	-----------------------------------
	bugfix: when changing project's client the old client's company is not unclassified from the project member #2086
	bugfix: show avergae billing rate at project billing report labor summary for each labor category, mark blend ones in bold #2079
	bugfix: company csv export, when a company does not have an address the csv data is not aligned correctly, because no empty cells are written to the file #2085
	bugfix: Translate 'weekly view' to spanish #2076
	bugfix: when editing member the color is overriden by parent's color #2084

	Since 3.10.5.1
	-----------------------------------
	bugfix: fix invoice lines table when account does not have project managers dimension (#2083)
	bugfix: feature that remembers last report conditions causes bug in other forms replacing the members when editing objects (#2082)
	bugfix: dont allow to change project or client when editing invoice (#2081)
	bugfix: when instantiating task template don't recalculate financials each time a task is saved, do it only once after all tasks are created (#2080) 
	bugfix: improve performance calculating project financials in background when instantiating task template or massive task deletion (#2080) 
	bugfix: fix financial totals for groups in tasks list and move code to "advanced_billing" plugin (#2080) 
	bugfix: improve plugin manager to show better error messages (#2077)
	bugfix: fix advanced_core plugin update script, was failing when executed by command line (#2077)

	Since 3.10.5.0
	-----------------------------------
	feature: new widget and listing for vendors (#2069)
	feature: config option to set non billable time expenses for fixedfee tasks (#2065)
	bugfix: can't define variable for expenses that have several lines with same prod type (#2068)
	
	Since 3.10.5.0-rc2
	-----------------------------------
	bugfix: Issue with total worked time calculation after performance fixes (#2073)
	bugfix:Weekly view fix: Escape special charachtes in the task tooltip (#2071)
	bugfix: include subtotals in the project billing report subtotals (#2070)

    Since 3.10.5.0-rc1
	-----------------------------------
	bugfix: Fix discount title on split invoice
	bugfix: can't download excel export for custom report if report name has /

	Since 3.10.5.0-beta4
    -----------------------------------
	bugfix: fix-fixed-fee-tasks-split-trash-invoiced #2064

	Since 3.10.5.0-beta3
    -----------------------------------
	bugfix: Don't trigger task and project calculations after changing timeslot status, also improve performance at task calculations and save
	Bugfix: Cant edit parent task when subtask has multiline desc (#2052)
	Bugfix: Wrong members autoselected in forms and automatic invoice #2053
	Bugfix: fix-site-address-in-invoice-project-table #2019
	Bugfix: Develop fixed fee tasks for multi client invoices #2057
	Bugfix: fix hook that adds financial tab to tasks #2055
	Bugfix: Remove miscalculations by calculating project financials for estimated and executed values #2051
	Bugfix: Fix the calculations of the estimated financials in the widgets #2056
	Bugfix: unlink expenses and timeslots when trashing a task, ask confirmation to the user #2061
	Bugfix: Fix totals calculation in the task module for financial values #2059
	bugfix: Fix custom report calculation: 'profit margin' columns for the tasks #2063



	Since 3.10.5.0-beta2
	-----------------------------------
	bugfix: Fix installation with custom tables prefixes (#2037)
	bugfix: Check if config option for sync sent mails is present, if not the add (#2048)
	bugfix: Fix calcultion fee column on invoice (#2044)
	bugfix: Fix reasign button (#2049)
	bugfix: Fix calculate overtime based on week days (#2046)
	bugfix: In time module organize the columns by "Overtime"

	Since 3.10.5.0-beta1
	-----------------------------------
	feature: revamp old feature that sends sent email to the "sent emails" folder of an email account in the mail server (#2043)
	feature: new config option to define start date for overttime calculations (#2039)
	bugfix: Fix display cost fee unitprice in invoice v2 (#2041)
	bugfix: invoice generation not using overtime calcualted values for time lines (#2040)
	bugfix: work progress widget not counting time entries without task (#2038)
	bugfix: fix-install-prefix (#2037)

	Since 3.10.4.x
	-----------------------------------
	feature: qbo sync cc expenses with qbo purchase (#2024)
	feature: Improvement/save action channel in application logs (#2020)
	bugfix: in-task-form-set-tab-order (#2036)
	bugfix: fix-config-option-always-select-task-in-time-entries (#2029)
	bugfix: in-expenses-do-not-inherit-cost-and-price-values (#2028)
	bugfix: features-ermi-split (#2026)
	bugfix: fix-display-cost-fee-unitprice-in-invoice (#2023)
	
	Since 3.10.4.8
	-----------------------------------
	bugfix: invoice-preview-get-main-project-name (#2006)

	Since 3.10.4.7
	-----------------------------------
	bugfix: Fix invoice address config option (#2042)

	Since 3.10.4.6
	-----------------------------------
	bugfix: if-no-contact-billing-address-let-use-blank-addres (#2035)
	bugfix: Calculate estimated expenses financials when the task is changed (#2034)
	bugfix: fix-po_number_and_labor_detail_on_inoive_generation (#2033)
	bugfix: qbo sync must be triggered after drag and drop classification (#2032)
	bugfix: add needed columns for tasks when updating advanced_billing (#2031)
	bugfix: When showing error message in widgets, base it on 'total_time_estimate' (#2030)
	bugfix: Remove conditions that prevented percent complete calculations (#2027)
	bugfix: hotfix-save-detail-in-lump-sum-generation (#2025)
	bugfix: template numeric variables don't allow to enter decimals (#2022)
	bugfix: need to check if advanced_core plugin is installed before using its classes, add time from task didn't work (#2021)
	bugfix: fix-invoice-generate-fixed-fee-tasks-name-duplicated (#2018)

	Since 3.10.4.5
	-----------------------------------
	bugfix: configure ermi invoice layout (#2017)
	bugfix: not all expense types are available in qbo sync (#2016)
	bugfix: never allow expenses to be saved with currency_id=0 (#2015)
	bugfix: fix-permission-can-manage-email-template (#2014)
	bugfix: update default Feng Office logo and favicon (#2013)
	bugfix: add margin to task list 'show all/more' actions to prevent overlap with scrollbar (#2012)

	Since 3.10.4.4
	-----------------------------------
	bugfix: disable deprecated mandatory expense custom properties once used for qbo sync (#2009)
	bugifx: fix permissions check in weekly view controller before making any change to times (#2005)
	Bugfix: add total cost and price columns to budgeted exp reports (#1998)
	bugfix: fix-notifications (#2003)
	Bugfix: overtime make same calculations for billing as for cost (#2010)
	bugfix: if task list date filter is empty then use empty datetime constant to prevent wrong filter initialization with now() (#2011)
	bugfix: fix context manager function that gets member node, don't assume the tree is always present (#2004)
	bugfix: add get/set for cost currency in timeslot model (#2008)
	bugfix: fix-invoice-duplicate-taks-name-for-fixed-fee-tasks (#2007)

	Since 3.10.4.3
	-----------------------------------
	fix-langs-invoices (#2002)
	bugfix: rollback batch invoicing functions to previous code and check if project member exists before building lines data (#2001)
	Bugfix: Check if 'income' plugin is installed (#2000)
	fix-bill-to-invoice-order (#1996) 
	Stop adding already existing column in the NM installer (#1988)

    Since 3.10.4.2
	-----------------------------------
	bugfix: don't select current context when adding new expense to expense templates
	bugfix: Improve the project financial calculations when expense is assigned to the task
    bugfix: ensure that tables involved in update calculations have all the columns
	bugfix: fix langs invoices
	bugfix: fix installer in community edition
	bugfix: fix invoice layout for Kjolhaug
	bugfix: fix save invoice after editing
	bugfix: add condition to member search query to filter unconsistent data if exists
	bugfix: fix client number on invoices

    Since 3.10.4.1
	-----------------------------------
	bugfix: fix langs in invoices(#1982)
	bugfix: Save 'Group by' preference of the member listing as contact config option (#1983)
	bugfix: fix report project invoice by contract stage (#1981)
	bugfix: fix mark as paid feature (#1984)
	bugfix: change overtime calculations listeners to ensure that they are executed after any timeslot save (#1979)
    bugfix: fix addu expenses module errors (#1978)

	Since 3.10.4.0
	-----------------------------------
	feature: add upload receipt button to expenses quickadd (#1957) 
	bugfix: projects not filtered by manager in time form (#1977) 
	* bugfix: don't reload dimension lists when changing timeslot's task, keep current filtered list (#1977) 
	* bugfix: dont reload act.expense projects list after changing budgeted expense, keep already filtered list, also filter bud.expenses list by selected pm or project (#1977) 
	fix-dimension-amp-quot (#1976) 
	bugfix: Apply correct formating for the money values for time, tasks, expenses (#1974)
	bugfix: the can_add hook to add project_phase members to check add permissions generates error because bad iteration of project members (#1973)
	bugfix: fix-joint-invoices (#1972) 
	bugfix: Add new calculated project financial columns to the custom report (#1971)
	bugfix: check if advanced_blling is active before using task invoice_id column (#1970)
	bugfix: Fix contact export (#1969) 
	bugfix: qbo sync expenses description must go to item description and not into memo (#1968)
	bugfix: use member display name in overview header (#1967)
	bugfix: Improvement/budget by tasks report add time expenses info (#1965) 
	bugfix: In custom reports remember selections for multiple similar combos (#1963)
	bugfix: Use project financials in the 'Project earnings' report calculations (#1961)
	bugfix: syntax errors in expenses2 installer queries (#1922)
	bugfix: Use permissions to show/hide project financial columns in the custom report (#1814)

	Since 3.10.4.0-rc2
	-----------------------------------
	bugfix: fix-summary-without-currency (#1964) 
	bugfix: recover invoie print projects table format (#1966)

	Since 3.10.4.0-rc1
	-----------------------------------
	bugfix: fix-subtotal-invoices (#1962) 
	bugfix: fix-po-number-project-invoice (#1960)
	bugfix: time entry billing rate and cost were not recalculated after drag and drop classify (#1959)
	bugfix: fix advanced_core update function name so it can be executed (#1955)
	bugfix: emtpy error message deleting part paid invoice (#1954)
	bugfix: fix amount cell widths and text overflow in kjolhaug invoice print template (#1958)

	Since 3.10.4.0-beta6
	-----------------------------------
	feature: allow project list (and any other member list) to have horizontal scroll (#1939)
	feature: add new columns to projects list (#1941) 
	improvement: weekly view set current week when switched from list (#1950 
	improvement: Add lang updates (#1948)
	improvement: Weekly view: UX improvements (#1940) 
	bugfix: fix the function that verifies integration status before voiding an invoice. (#1935)
	bugfix: in weekcly view add time members must be sent in the main 'members' parameter so 'closed project' permissions can be checked (#1953)
	bugfix: fix the request parametrs of ObjectGrid when requesting totals in a separate request (only used in time module) (#1952)
	bugfix: fix-editing-invoices-in-feng (#1951)
	bugfix: weekly view billable add ability to focus (#1949) 
	bugfix: fix app log query to add new type of logs in upgrade script
	bugfix: Hide project manager selector, make project selector mandatory (#1947)
	bugfix: fix instantiate templates button (#1946) 
	bugfix: fix-rn-invoices (#1945) 
	bugfix: cant edit task templates (#1944) 
	bugfix: Weekly view fix: allow entering time using comma as the decimal separator (#1943)
	bugfix: invoices-generation-fix-lumpsum-without-timeslots (#1942)
	bugfix: tab-billing-info-get-country-and-number (#1938)
	bugfix: some app log detials are not formatted correctly (#1936) 
	bugfix: Remove fixed fee condition when calculating task's financials (#1934)
	bugfix: fix income update 31-32, it fails when updating from 3.8.1

	Since 3.10.4.0-beta5
	-----------------------------------
	feature: project task is manual percent completed (#1931) 
	feature: invoices-subtotal-discounts (#1929)
	feature: Improvement/budget by tasks report show time expenses (#1923) 
	feature: time-entry-make-mandatory-task (#1886)
	feature: allow to receive date parameter when add/edit timeslot from mobile (#1932)
	bugfix: fix-invoices-templates-footer-increase-length (#1901)
	bugfix: member selector list hidden when is too close to the bottom (#1921)
	bugfix: in-task-can-reminder-start-date (#1933) 
	bugfix: dont trigger product type selector onchange if product is the same (#1930)
	bugfix: time weekly view add time without task issues (#1928) 
	bugfix: fix-master-income-old-versions (#1927)
	bugfix: when adding node to member selector, dont update existing one, replace it so it renders without error (#1925)
	bugfix: error in time module quick add when advanced_billing plugin is not active
	bugfix: ensure received amount for invice payment is formatted as a number (#1916)
	bugfix: can't edit time after adding from quick add and invoicing status is empty (#1918)

	Since 3.10.4.0-beta4
	-----------------------------------
	bugfix: Set percent complete to 100 if user entered more than 100 (#1920)
	bugfix: Recalculate the previous task's financials when actual expense is reassigned (#1917)
	bugfix: Fix calculation of the project's estimated price for labor and expenses (#1917)
	bugfix: fix-triplo-layout (#1915) 
	bugfix: ensure received amount for invice payment is formatted as a number (#1916)
	bugfix: can't edit time after adding from quick add and invoicing status is empty (#1918)

	Since 3.10.4.0-beta3
	-----------------------------------
	bugfix: When recalculating tasks' financials, stop getting cache subtasks #1912
	
	Since 3.10.4.0-beta2
	-----------------------------------
	bugfix: Remove conditions that hide the percent completed input (#1911) 
	bugfix: permission when deleting time in weekly view (#1910) 
	bugfix: Update the description in the task financials part of the edit form (#1909)

	Since 3.10.4.0-beta1
	-----------------------------------
	feature: add-condition-in-CC-fields-on-email-templates (#1899)
	bugfix: project financials calculations (#1906) 
	bugfix: fix-totasl-invoices (#1904)
	bugfix: error saving log details when adding expense (#1902) 
	bugfix: fix-fixed-fee-invoices-task-name. (#1898)
	bugfix: estimated time presentations and remove profitability values (#1895)
	bugfix: recover secondary emails input for users (#1905)
	bugfix: fix-invoices-take-billing-from-address-batch-generation (#1908)
	bugfix: issues in time quick add (#1907)
	bugfix: fix amount formatting at invoice payment form to prevent wrong numbers (#1903)
	bugfix: fix-permission-tasks-users-different-companies (#1876)

	Since 3.10.4.0-alpha1
	-----------------------------------
	feature: improve log details time expenses tasks (#1897) 
	feature: Api - first version for mobile mvp (#1870) 
	bugfix: can't remove subscribers from template tasks (#1888)
	bugfix: fix-lump-sum-invoices (#1893)
	bugfix: get-billing-address-from-client (#1879)
	bugfix: don't check workflow permissions after classification, they were alredy checked (#1877)
	bugfix: when using mail form dom object, dont use it by name, get it using the id and genid (#1874)
	bugfix: Increase the width of the project selector (#1896)
	bugfix: Fix lump sum templates (#1894) 
	bugfix: fix-invoice-old-installations (#1887)
	bugfix: weekly-view-visual-issues-from-beta-branch (#1883) 
	bugfix: features-new-calculations-on-financials-tab (#1880) 
	bugfix: Fix modal expenses (#1875) 

	Since 3.10.3.0-beta15
	-----------------------------------
	feature: show project's profitability (#1890) 
	feature: new report project budget by tasks (#1878) 
	feature: recalculate percent complete task add to project (#1872) 
	bugfix: fix amount formatting before filling the b.epenses form in bexpenses templates (#1885)
	bugfix: fix projects tree order by (#1884)

	Since 3.10.3.0-beta14
	-----------------------------------
	bugfix: rollback timeslot user validation in subscribers function
	bugfix: Add custom property code for "PO #" in projects (#1871) 

	Since 3.10.3.0-beta13
	-----------------------------------
	feature: improve layouts information lines (#1862)
	feature: overtime payroll report improvements2 (#1854) 
	feature: add project billing contact email and Client billing Email in invoice (#1853)
	feature: set permissions to contract phase dim (#1852) 
	feature: link expenses to tasks (#1847)
	language: apply new translations from translations instance (#1861) 
	bugfix: control that labor cat plugin is installed before using categories (#1867)
	bugfix: fix tasks assign when unassing (#1856)
	bugfix: Weekly view: Use labor category member from the active context when needed (#1850)
	bugfix: overview list type filter must only include content object types and only from active plugins (#1865)
	bugfix: apply user tz offset when formatting date group title (#1868)
	bugfix: exp quick add, when selecting exp cat. the prod types were not filtered (#1848)
	bugfix: fix js syntax error in reload subscribers call at time entry form (#1864)
	bugfix: disable by default the user preference "hide_quoted_text_in_emails" (#1858)
	bugfix: ensure that project managers and other project related dimensions have is_manageable=1 so we can put them in forms (#1857)
	bugfix: conditions that checks permissions to add task (#1851)
	bugfix: fix assigned to in tasks (#1849)
	bugfix: fix error in app log details when cp does not exist (#1863)
	bugfix: invoicing history report performance improvements (#1855) 

	Since 3.10.3.0-beta12
	-----------------------------------
	bugfix: fix Prop's invoices templates and apllied new controlls (#1843)
	bugfix: fix-task-edit-with-taskTemplates_RC (#1841)
	bugfix: Fix discounts totals when editing invoices (#1824)
	bugfix: Notification summary: Fix the bug that broke the notification system (#1845)
	bugfix: Fix bug that caused cost and billing permission malfunction (#1844)
	bugfix: Fix users that not have permission to the project on task and times (#1842)  
	bugfix: fix-billing-address (#1839)
	bugfix: fix-mails-sending-automatico-in-new-tab (#1838)

	Since 3.10.3.0-beta11
	-----------------------------------
	feature: payroll overtime reports improvements (#1823) 
	feature: Weekly view frontend design (#1805)
	feature: invoice-template-add-labels-to-totals (#1834)
	bugfix: fix-invoice-preview-in-keefer (#1837)
	bugfix: time off description not updated in tsheets (#1835)
	bugfix: performance issues when deleting sync invoice (#1832) 
	bugfix: fix-invoice-not-have-billing-address (#1827) 
	bugfix: remove-generate-invoice-button-from-task (#1825)
	bugfix: add more size to imap folder columns to prevent duplicate key error (#1836)
	bugfix: improve performance in quick-adds and initial load (#1833) 
	bugfix: fix invoice list reload when deleting (#1831) 
	bugfix: Project JDT report: fix the query that gets objects for the report (#1829)
	bugfix: fixes to project billing excel export (#1826)

	Since 3.10.3.0-beta10
	-----------------------------------
	bugfix: don't verify workflow permissions when indirect action triggers the status change, like invoicing
	
	Since 3.10.3.0-beta9
	-----------------------------------
	bugfix: fix-invoices-without-projects (#1821)
	bugfix: add query to fill client property group with new association with busines company (#1820)
	bugfix: Fix new templates default values (#1819) 
	bugfix: set-default-yes-for-allowing-multiple-lines-at-invoice-detail (#1812)
	bugfix: robinson-noble-invoice-column-employee-too-long (#1816)
	bugfix: fix-milestones-in-templates-tasks (#1815)
	bugfix: fix-billing-address in invoice form (#1822)
	bugfix: Add 'period billing' option to the alternative project billing report (#1818)
	bugfix: Weekly view: support adding time to the subproject's task (#1817)
	bugfix: Project list: check cost/billing permissions for project financials (#1813)
	bugfix: Check cost permissions for the expense's value "total cost without taxes" (#1811)

	Since 3.10.3.0-beta8
	-----------------------------------
	bugfix: add missing translations to es_la language (#1806)
	bugfix: cant-save-an-invoice (#1810)
	bugfix: use decimals from the config option in the actual expense list in totals (#1809)
	bugfix: invoice-take-the-billing-address-for-the-client (#1808)
	bugfix: Use persisted financial values instead of calculating project estimated price (#1807)

	Since 3.10.2.0-beta7
	-----------------------------------
	feature: dynamic project table for invoice templates (#1756) 
	feature: default-settings-on-invoice-templates (#1795) 
	bugfix: aging report: include invoices without due date in the 'current' period only (#1800)
	bugfix: fixed-fee-task-make-stable (#1801) 
	bugfix: show employee input when expense type is rentals/consumables (#1804)
	bugfix: show invoice not synchronized not trashed (#1802) 
	bugfix: Update the message to show in the widget (#1799)
	bugfix: Prevent errors when quotes are present in the description in weekly view (#1798)
	bugfix: Avoid double click to change from weekly view to list view (#1797)
	language: add new translations for it_it (#1803)

	Since 3.10.3.0-beta6
	-----------------------------------
	bugfix: fix-separator-lines (#1793)
	bugfix: invoice-templates-initials (#1791)
	bugfix: timeslot billing recalculation (#1794)
	bugfix: fix several issues at workflow permissions verifications for time and expenses (#1792)

	Since 3.10.3.0-beta5
	-----------------------------------
	feature: add more payment term options to invoice terms property (#1786)
	feature: tsheets sync time off entity (#1785) 
	feature: fixed-fee-task-improvents (#1774)
	bugfix: fix-lump-sum-view-combo (#1790)
	bugfix: inlcude "object prefixes" feature to the member display name calculation (#1789)
	bugfix: delete css classes that removes all scrollbars in the system (#1784)
	bugfix: Weekly view: css alignment and column width changes (#1782)
	bugfix: weekly view project's tooltip show project name (#1781)
	bugfix: Mobile API: Assign expense category using product type of the expense (#1780)

	Since 3.10.2.0-beta4
	-----------------------------------
	feature: invoice from multiple companies (#1775) 
	feature: invoice layouts structure in database (#1775) 
	feature: integrate e-invoice qr code with invioce layouts (#1775) 
	bugfix: invoice-list-totals-line-at-the-bottom-disappears (#1773) 
	bugfix: qbo sync time in background when using weekly timesheet (#1776)
	bugfix: Use project's display name in the weekly view (#1779)
	bugfix: update financials after timer stopped (#1778) 
	bugfix: billing-category-must-be-left-aligned (#1777) 
	bugfix: Improvements in the 'Worked hours' widget (#1768) 

	Since 3.10.2.0-beta3
	-----------------------------------
	feature: show one line per each ratainer on invoice lines (#1767) 
	feature: Improvement/weekly view enable decimal time input (#1765)
	bugfix: Project billing report: exclude running and stopped timers (#1769)
	bugfix: Invoice summary breaks if dates includes hours in format (#1772) 
	bugfix: Increase size for the total due in the quinn's invoices (#1771) 
	bugfix: Weekly view: when description is edited sync with quickbooks #1770
	bugfix: fix-discounts-types-on-invoice-lines (#1766)
	bugifx: Invoice form broken when not using labor and/or expense dimensions (#1766)

	Since 3.10.2.0-beta2
	-----------------------------------
	feature: Automatically calculate earned revenue based on % complete of a task (#1762) 
	feature: Config option for invoicing fixed fee tasks based on total-discount or invoicing delta only (#1762) 
	bugfix: widget executed values recalculations include manual values (#1763) 
	bugfix: Weekly view: Edit a time entry is not updating the info in Tsheets (#1761)
	bugfix: Improve logic that filters tasks for widget; improve messages (#1760)

	Since 3.10.3.0-beta1
	-----------------------------------
	bugfix: enable the time input, when time is deleted in multientry box (#1759)
	bugfix: Weekly view: select all text when time input is clicked (#1758)
	bugfix: Earned value widget: add to estimated totals task's estimated value with missing info (#1757)
	bugfix: Weekly view: sync edited time with quickbooks/tsheets (#1755)
	bugfix: rollback of invoice list totals row fix

	Since 3.10.2.0-rc1
	-----------------------------------
	feature: new-invoice-line-type-retainer (#1746) 
	feature: add options to the 'Aging invoices' report (#1738)
	bugfix:custom-report-with-design-applied-incorrectly (#1736) 
	bugfix: Add 'unclassified' section to the 'Project billing' report (#1749) 
	bugfix: task-remove-paragraph-indentation-in-excel-exported-reports (#1745)

	Since 3.10.2.0-beta7
	-----------------------------------
	feature: Add project table on invoice layout for NAtional Econ (#1751)
	feature: Automatically-calculate-earned-revenue-based-on-%-complete-of-a-task-… (#1735)
	feature: Invoice-templates-allow-to-configure-line-separator (#1734)
	bugfix: we must allow the expense type to init with empty value when type is mandatory (#1753)

	Since 3.10.1.1
	-----------------------------------
	bugfix: expenses in mobile version: improve api responses (#1752)
	bugfix: If labor or expense are disabled, not show invoice line (#1750)
	bugfix: project-billing-report-not-exporting-correctly-excel (#1747) 
	bugfix: Custom report: support ordering by number with decimals as custom property (#1743)
	bugfix: Project billing report: exclude running timers from the report (#1742)
	bugfix: when attachment doesn't have a name don't put always ForwardedMessage.eml, use a default name accoridng to the file type (#1741)
	bugfix: Use fixed value when appropriate in the calculate_timeslot_rate_and_cost function (#1740)
	bugfix: Invoice-lines-negatives-are-not-be-treated-as-discounts (#1739)
	bugfix: Totals-line-at-the-bottom-disappears-when-we-scroll-up (#1705) 

	Since 3.10.2.0-beta6
	-----------------------------------
	feature: Invoice-template-fixed-fee-taks_v1 (#1719)
	feature: National econ invoice print settings (#1721)
	feature: Add project financials to custom report, support report & print, other fixes (#1723)
	feature: new config option to make actual expense type mandatory or not (#1732)
	bugfix: project list support order for all columns including financial (#1728)
	bugfix: cant generate invoice using api (#1727)
	bugfix: increase 100 times mail body size limit to show when reply/forward an email (now in 20 MB) (#1731)
	bugfix: event reminders were saving wrong column so they couldn't be sent (#1730)
	bugfix: issues-with-email-templates (#1722)
	Bugfix: fix template subtasks critical issues (#1718)
	bugfix: weekly-view-change-button-for-anchor-the-memo-modal (#1716)
	bugfix: ar-ledger-report-project-name-overrides-the-total-lines (#1712)
	bugfix: direct-indirect-labor-report-excel-export-green-background-fixed (#1700)
	bugfix: ensure that all member total columns are present before saving values in upgrade script (#1725)

	Since 3.10.2.0-beta5
	-----------------------------------
	bugfix: Use different logic when contact is changed in time entry view and quick add (#1726)
	bugfix: fixd DocNumber assignation in qbo expense sync (#1717)
	bugfix: ignore deactivated plugins when update_all is called (#1715)

	Since 3.10.2.0-beta4
	-----------------------------------
	feature: add financial values to template tasks (#1703)   
	feature: invoices layout changes national econ (#1694)
	bugfix: expense quick-add: when selecting budgeted expense => autoselect its project (#1713)
	bugfix: Apply different sorting for custom report columns without groups (#1704)
	bugfix: fix performance when parent task change task financial (#1702) 

	Since 3.10.2.0-beta3
	-----------------------------------
	bugfix: Add expenses to lump sum invoices (#1690)
	bugfix: Sort columns with respect of option groups in the custom report column options section (#1689)
	feature: task-support-multiple-invoicing-firms-and-layouts (#1676)
	
	Since 3.10.2.0-beta2
	-----------------------------------
	Fixes added to master since 3.10.0.6

	Since 3.10.2.0-beta
	-----------------------------------
	bugfix: Fix bill to invoice batch generation (#1675) 
	bugfix: fix invoice templates for rc version (#1681)

	Since 3.10.1.x
	-----------------------------------
	bugfix: Add contact's full name to the custom report columns (#1671)
	bigfix: remove-blank-space-in-tasks-form (#1644) 
	
	Since 3.10.1.0
	-----------------------------------
	bugfix: increase 100 times mail body size limit to show when reply/forward an email (now in 20 MB) (#1731)
	bugfix: event reminders were saving wrong column so they couldn't be sent (#1730)
	bugfix: issues-with-email-templates (#1722)
	Bugfix: fix template subtasks critical issues (#1718)
	bugfix: weekly-view-change-button-for-anchor-the-memo-modal (#1716) 
	bugfix: ar-ledger-report-project-name-overrides-the-total-lines (#1712) 
	bugfix: direct-indirect-labor-report-excel-export-green-background-fixed (#1700)
	bugfix: ensure that all member total columns are present before saving values in upgrade script (#1725)
	bugfix: ignore deactivated plugins when update_all is called (#1715)

	Since 3.10.1.0-rc4
	-----------------------------------
	bugfix: fix subscribers component for events (#1708) 
	bugfix: after removing the name calculation from the save() function of members the upgrade didn't calculate it (#1698)
	bugfix: weekly view -> let time inherit project members when task not selected (#1706)
	bugfix: AR ledger report: Exclude trashed and archived invoices (#1707)
	bugfix: in qbd export person name in memo field only for credit card expenses (#1711)
	bugfix: fix-Labor-lang (#1710)

	Since 3.10.1.0-rc3
	-----------------------------------
	bugfix: fix-get-custom-property-homeowner-from-project-for-guzi (#1697)
	bugfix: Actual expenses form display issues (#1696)
	bugfix: Exclude trashed and archived payment receipts from AR ledger report (#1695)
	bugfix: Load task from DB instead of cache to get the updated financials info (#1693)
	bugfix: direct_indirect labor report issues with the excel export (#1692) 
	bugfix: Save time changes to prevent bug when adding a new row in the weekly view (#1691)
	bugfix: Fix invoice line date job scheduled in lump sum invoice (#1688) 
	bugfix: Rewrite time interval hook to calculate the time rounding (#1687)
	bugfix: Fix/format task financials calculate totals (#1682) 
	bugfix: Excel report is not adding the totals (#1673) 

	Since 3.10.1.0-rc2
	-----------------------------------
	bugfix: add more qbd export expenses values (#1686)

	Since 3.10.1.0-rc
	-----------------------------------
	bugfix: fix invoice templates for rc version (#1681)
	bugfix: replace member selectors in time quick add to improve navigation with tab key (#1674)

	Since 3.10.1.0-beta
	-----------------------------------
	feature: first version of member selector with simple extjs combobox (#1665)
	feature: actual expense form: when changing prod type and amounts are different, ask the user if he wants to change them (#1647)
	feature: expenses quick add improvements (#1643) 
	feature: apply new UI design for invoice templates (#1641) 
	bugfix: fix error in display name calculation when adding new member (#1654)
	bugfix: Remove error that checks if time has task assigned in the weekly view (#1648)
	bugfix: fix "now" date to show in excel exported report
	bugfix: include-exact-time-for-executed-on-export-excel-reports (#1634) 

	Since 3.10.0.x
	-----------------------------------
	feature: Invoice template for Guzi - add the "Job Schedule date" option to the "assign date based on" combo (#1622)
	feature: Add option 'Show project's client and start date' to project management reports (#1621)
	feature: ability-to-customize-columns-name (#1614)
	feature: Project financials: add billable and non-billable worked time (#1606) 
	feature: improve excel export (#1521) 
	feature: quick add of actual expenses (#1521) 
	feature: store member display name, use it in system (#1551) 
	feature: autoselect product type if there is only one(#1504)
	bugfix: Escape quotes when creating subtasks in the task form (#1613) 
	bugfix: make task required in weekly time view add function (#1549)
	
	Since 3.10.0.6
	-----------------------------------
	bugfix: gnb-out-of-the-box-invoices-after-upgrade-not-working (#1685) 
	bugfix: Fix/calculate project financial totals in the project list (#1683) 
	bugfix: Add "homeowner" field to Guzi invoice from custom property (#1680)

	Since 3.10.0.5
	-----------------------------------
	bugfix: Inherit 'billable' and 'invoicing_status' from task when starting timer (#1679)
	bugfix: invoicing history customized for kjolhaug excel issues (#1677) 

	Since 3.10.0.4
	-----------------------------------
	feature: Highlight each project in reports when exporting to excel (#1650) 
	bugfix: fix column order in time custom reports with task columns (#1672)
	bugfix: fix Invoice templates - "Include product type" in Detail column not working (#1670)  
	bugfix: project id unicity check must only include existing members cp values (#1669)
	bugfix: improve time list query to allow ordering by task name (#1668)
	bugfix: fix actual expense list query that overrides name with product name when ordering by product (#1667)
	bugfix: totals are broken in two lines rn invoices (#1662) 
	bugfix: check library compatibility before using classes (#1660) 
	bugfix: fix invoice line ordering to use mysql format (#1658)
	bugfix: display of filters in PDF reports is cut off (#1657) 
	bugfix: Fix send mails when object template is not acivate, also always shows attachments. (#1655)
	bugfix: fix show attach section only if it's has an attach (#1655)
	bugfix: at qbo sync billing email, dont send more than 100 chars in that field (#1652)
	bugfix: actual expense classification form position has to be in the right (#1651) 
	bugfix: fix invoice template selector when names have special characters (#1508)
	bugfix: fix format of 'Executed on' date in custom reports (#1286)

	Since 3.10.0.3
	-----------------------------------
	bugfix: overdue filter in TASK module is also retrieving tasks without due date (#1631)
	bugfix: when adding time to the task added via row adder in the weekly view (#1615)
	bugfix: fix translations in report: "Presupuesto" (#1642)
	bugfix: use default labor category for time when non selected in the task (#1620)
	bugfix: es_la translations at for overview widgets (#1608)
	bugfix: Add project number and name in email templates (#1640)
	bugfix: fix Invoice lines ordered and Lump sum + expenses template (#1646) 

	Since 3.10.0.2
	-----------------------------------
	Weekly view: set correct invoicing status based on members or task (#1638)
	bugfix: Fix project list (#1635)
	bugfix: Improve excel export (#1628) 
	bugfix: fix Start date in Project Billing report (WIP) (#1624)
	bugfix: fix-templates-labor-categories-and-order (#1627)
	bugfix: fix bad control value on a probably null variable, caused to break print invoice (#1637)

	Since 3.10.0.1
	-----------------------------------
	bugfix: fix alignment in invoice totals
	bugfix: Add billing to data at feng invoice
    bugfix: display the "Client number" set in the client form in invoices

	Since 3.10.0.0
	-----------------------------------
	bugfix: fix-invoice-agrupations-by-name-and-description (#1619)
	bugfix: Exclude projects without due invoices from the aging invoices report (#1603)
	bugfix: Changes to the Bill to section of the invoice in Guzi west template (#1600)
	bugfix: qbo-sync time - only send date and amount of hours, withour start and end time (#1609)
	bugfix: remove typo from variable usage when printing invoice
	bugfix: fix custom property padding in tasks view
	bugfix: remove deprecated report from old expenses plugin if exists (#1610)
	bugfix: gesl remote dir creation: clean folder name to remove special chars (#1510)
	bugfix: Configure the page break on the invoices for Guzi (#1605) 

	Since 3.9.3.0-rc11
	-----------------------------------
	bugfix: update expenses total cost column to allow 3 decimals (#1602) 
	bugfix: expense amounts badly formatted and saved (#1601) 
	bugfix: fix-project-name-all-agrupations (#1597) 
	bugfix: Fix generation of reptitive invoices when its payed (#1599)

	Since 3.9.3.0-rc10
	-----------------------------------
	bugfix: fix invoice's column names for RN (#1593) 
	bugfix: fix invoice's bill to customer name (#1594) 
	bugfix: improve performance when executing member and task templates (#1595)
	bugfix: when resuming time from task list the other time entries were not paused despite the config option says to pause them
	feature: Add option 'Show invoice details' to the project budget status report (#1591)  

	Since 3.9.3.0-rc9
	-----------------------------------
	bugfix: fix-invoices-preview (#1592)
	bugfix: make time module actions column width adjustable
	
	Since 3.9.3.0-rc8
	-----------------------------------
	feature: Project billing report: add option 'Display grand total' (#1588)
	bugfix: Account summary is retrieving current invoice amount instead of last invoice amount (#1589)
	bugfix: invoice lines table class (#1590)   
	
	Since 3.9.3.0-rc7
	-----------------------------------
	bugfix: In invoice lines discounts now accept decimals after the Zero
	bugfix: In invoices change getemailbytype to getmainemail
	bugfix: Update earned value for task after adding timer in the task view
	bugfix: Get summary form parent project in joint invoice

	Since 3.9.3.0-rc6
	-----------------------------------
	bugfix: Fix batch invoicing when grouping expenses by name or description (#1579)
	bugfix: Improve the calculation of the worked_time for the timeslot in module level (#1578)
	bugfix: Sometimes when creating joint invoices don't get the parent project (#1581)
	bugfix: bugfix: default client image is not present in theme images directory (#1580)
	bugfix: Hide executed billable, show estimated, etc, columns from projects list (#1566)
	bugfix: Improve the function that gets projects for the project billing report (#1582)

	Since 3.9.3.0-rc5
	-----------------------------------
	bugfix: tasks multiassignment js file is not loaded when editing first task and causes error (#1576)
	bugfix: Adjustment/aging invoices add adjustments (#1575)   
	bugfix: fix pay for non recurring invoices (#1573) 
	bugfix: for invoices in bill to section prevent to click the same contact that is selected (#1577)
	bugfix: fix-totals-and-columns-order-invoices-for-props (#1574)   
	bugfix: fix-decimals-on-budget-expeneses-form (#1572)
	
	Since 3.9.3.0-rc4
	-----------------------------------
	bugfix: Fix/timer new config option bugs (#1570) 
	bugfix: fix/always-show-main-project-in-invoice-split (#1569)
	bugfix: Use CKEditor in the subtask section of the edit task form (#1561) 
	bugfix: Fix the aging invoices report bugs (#1560) 
	bugfix: fix error when editing subtask and reload task line in task list
	bugfix: clean thousand separator in expense line amounts before loading input… #1568

	Since 3.9.3.0-rc3
	-----------------------------------
	bugfix: Multi-currency - Total in invoices is not being properly calculated
	bugfix: After paying a recurring invoice the next invoice is not generated automatically
	bugfix: Subtotal is wrong when you use taxes + lump sum invoice template
	bugfix: Default "Lump sum" type template not working properly with taxes

	Since 3.9.3.0-rc2
	----------------------------------  
	bugfix: change-method-array_key_last-to-count (#1564) 
	bugfix: move function definition to helper, so we avoid 'redeclaration' error if that php file is included more than once

	Since 3.9.3.0-rc
	----------------------------------  
	bugfix: for multiple invoices show parent invoice in print preview (#1555) 
	bugfix: fix errors when sync invoice with reimbursable charges in lines (#1554)
	bugfix: Add 'expense type' support to the import tool (#1553)
	bugfix: bug at invoice discount issues (#1550) 
	bugfix: fixes to ERML invoice template (#1557) 
	bugfix: fix js error when loading categories for invoice line selectors

	Since 3.9.3.0-beta3
	----------------------------------  
	feature: sort total rows by currency in the aging invoices report (#1542)
	feature: qbo-sync - new config option to deactivate incoming changes synchronization (#1540) 
	feature: add the invoice date to actual expense in the custom report (#1509)
	bugfix: b.exp templates: for expense lines clean the amount columns so the ',' character doesn't break the sql or the js logic (#1543)
	bugfix: hide project financial values by default (#1541) 
	bugfix/invoice-print-dialog-box-every-time-edit-invoices-robinson-noble (#1539)
	bugfix: hide completed tasks in weekley timesheets task selector (#1528) 

	Since 3.9.3.0-beta2 
	----------------------------------
	feature: Show aging invoices for the projects without project manager (#1535)
	feature: use complex formulas for variables at generic object templates (#1534)
	feature: new config option to sync objects only in the parent project of the project's hierarchy (#1522)
	bugfix: Invoice line creator is not adding totals (#1536) 
	bugfix: Fix showstoppers at expenses module (#1527)

	Since 3.9.3.0-beta1
	-------------------
	bugfix: fix advanced billing plugin update
	feature: generic object templates
	feature: allow user to see and create invoice lines in the right place
	bugfix: fix time and task reports fatal error
	bugfix: Display amount due instead of total in aging invoices report
	bugfix: change label on not syncronized invoices with QB
	bugfix: fix translation on reports
	feature: add tooltip in task name weekly
	
	Since 3.9.2.7
	-----------------------------------
	bugfix: get-invoice-template-issue-and-props-settings (#1571) 
	bugfix: address-block-on-invoice-exact-measures (#1562) 
	bugfix: hide-unit-value-when-discount-percentage-is-selected (#1558)

	Since 3.9.2.6
	-----------------------------------
	bugfix: Multi-currency - Total in invoices is not being properly calculated
	bugfix: After paying a recurring invoice the next invoice is not generated automatically
	bugfix: Subtotal is wrong when you use taxes + lump sum invoice template
	bugfix: Default "Lump sum" type template not working properly with taxes

	Since 3.9.2.5
	-----------------------------------
	bugfix: fixes to ERML invoice template (#1557) 

	Since 3.9.2.4
	-----------------------------------
	bugfix: Fix calculations in the project billing report for the expenses summary (#1545)
	bugfix: Include active context when filter dimension panel for non admin users (#1503)
	bugfix: fix guzi west invoice print due date (#1547) 
	bugfix: don't show print window when viewing an invoice (#1547) 
	bugfix: evx widgets were broken due to php opening short tags usage '<?'

	Since 3.9.2.3
	-----------------------------------
	bugfix: fix-print-preview-with-no-project (#1538)
	bugfix: fix print preview (#1533)
	bugfix: fix critical issues when creates invoices (#1532)
	bugfix: Fix the bug that didn't show description modal when close timer (#1530)
	bugfix: fix upgrade script version in the paella script to include new development (#1529)
	bugfix: fix error when executing time/task custom reports (#1526)

	Since 3.9.2.2
	-----------------------------------
	Bugfix: Remove call to IncomeLinesController that not exists
	Bugfix: Not change status when editing invoices if not corresponds
    
	Since 3.9.3.0-beta
	-------------------
	bugfix: BioLogical - Setup invoice templates
	bugfix: Fix Repeated Invoices with same pay information
	feature: Joint invoice preview and Generate one invoice for each sub-project

	Since 3.9.2.x
	-------------------
	feature: persist project totals (#1438) 
	feature: add a config option 'default country in address' (#1460) 
	feature: actual expense add deposit day custom property (#1457) 
	feature: Project billing report improvements (#1498) 
	feature: persist project totals (#1438) 
	bugfix: sorting by client doesnt work at invoices module (#1466)
	
	
	Since 3.9.2.0
	-------------------
	bugfix: errors when printing invoices in feng

	Since 3.9.2.0-rc6
	-------------------
	bugfix: remove-modal-window-to-print-in-invoice-details (#1475) 
	bugfix: Fix bugs aging invoices (#1502) 
	bugfix: Remove invoice id from time and expenses after trashing invoice (#1491) 
	bugfix: fix header guzi west invoice (#1496) 
	bugfix: address-location-the-invoice-not-aligned-with-the-envelope (#1428 

	Since 3.9.2.0-rc5
	-------------------
	Feature/template selector within invoices template settings (#1488) 
	Feature/import tool suppot invoice lines (#1484) 
	bugfix: minor adjustments v2 (#1486) 
	bugfix: fix-invoices-projects-groups (#1485) 
	bugfix: The default invoice template with the original version (#1478)

	Since 3.9.2.0-rc4
	-------------------
	bugfix: minor adjustments to props invoice print (#1483) 
	bugfix: fix organization data edition (#1482)

	Sinec 3.9.2.0-rc3
	-------------------
	feature: Feature/invoices whit lump sum (#1463) 
	feature: Add config option to hide the financial tab in the task edit view (#1453 
	feature: Append payment_receipt to filtters (#1413)
	feature: Feature/mark invoices as paid bulk action (#1448) 
	feature: Add option to calculate 'quantity' for actual expenses in custom reports (#1445)
	feature: Append select to boolean filter (#1433)
	bugfix: When editing the time entry the billing info get deleted (#1480) 
	bugfix: hotfix/errors-when-invoicing-from-feng (#1479) 
	bugfix: add php-spreadsheet library missing 'vendor' folder (#1476)
	bugfix: fix-update-plugin-income (#1474) 
	bugfix: copy original template content to feng directory (#1473) 
	bugfix: fix-Issues-when-editing-invoice-lines-props (#1471)
	bugfix: Fix/error message in task (#1467) 
	bugfix: Fix-cannot-edit-contact-invoices-bill-to (#1465)
	bugfix: Invoice-ddresses-issues-RN (#1464)
	bugfix: hotfix/missing-langs-en-español (#1461) 
	bugfix: Remove false from subtask description in tasks form (#1459)
	bugfix: hotfix/fix-the-dividing-lines-of-the-totals-columns-in-the-reports (#1458)
	bugfix: Hotfix/improvements and adjustments in the erml invoice template (#1456) 
	bugfix: if billing address prop exists, try to get the address value from that cp when synchronizing clients (#1454)
	bugfix: append due lang (#1452)
	bugfix: append quickbooks missing lang (#1443)
	bugfix: cant-scroll-up-while-drag-and-drop-a-invoice-lines (#1451) 
	bugfix: Add support for multicurrencies in the 'Aging invoices' report (#1449)
	bugfix: Exclude subprojects from the 'Aging invoices' report (#1447)
	bugfix: Prevent a bug when deleting all selected actual expenses (#1446)
	bugfix: hotfix/rejected-multicurrency-fix-update-total (#1444) 
	bugfix: Fix/wrong dates using net 30 (#1285) 

	Since 3.9.2.0-rc2
	-------------------
	bugfix: hotfix/total-not-changing-when-modified-qty-on-invoice-lines (#1442)   
	bugfix: feature-set-product-type-category-gropuing-name-descripcion (#1440)
	bugfix: fix-income-plugins-version (#1439)
	bugfix: sort quickbooks colum (#1434)
	bugfix: fix query that retrieves report timeslots, it was using end_time in one condition and should use start_time (#1437)
	bugfix: Invoice template updates (#1436) 
	bugfix: ERML invoice templates improvements (minor) (#1435) 

	Since 3.9.2.0-rc
	-------------------
	bugfix: Invoice template critical issues
	bugfix: fix-product-type
	bugfix: fix-non-criticals-errors

	Since 3.9.2.0-beta4
	-------------------
	feature: activate-email-template-for-invoices (#1423) 
	feature: invoice-template-rename-Labels-from-date (#1421)
	bugfix: rejected invoice view critical improvements (#1425) 
	bugfix: qb desktop export: apply user time zone to timeslot date (#1424)
	bugfix: fix-expenses-2-plugin (#1419)
	
	Since 3.9.1.14
	--------------------
	bugfix: Improve the update script for advanced billing (#1489) 
	bugfix: Fix bug that didn't filter objects by selected members in object picker (#1481)

	Since 3.9.1.13
	--------------------
	bugfix: When editing the time entry the billing info get deleted (#1480) 
	bugfix: copy original invoice template content to feng directory (#1473) 
	bugfix: Fix/error message in task (#1467) 
	bugfix: Fix-cannot-edit-contact-invoices-bill-to (#1465)
	bugfix: Invoice-ddresses-issues-RN (#1464)
	bugfix: hotfix/missing-langs-en-español (#1461) 
	bugfix: Remove false from subtask descroption in tasks form (#1459)
	bugfix: hotfix/fix-the-dividing-lines-of-the-totals-columns-in-the-reports (#1458)
	bugfix: Hotfix/improvements and adjustments in the erml invoice template (#1456) 
	bugfix: if billing address prop exists, try to get the address value from that cp when synchronizing clients (#1454)
	bugfix: cant-scroll-up-while-drag-and-drop-a-invoice-lines (#1451) 
	bugfix: Add support for multicurrencies in the 'Aging invoices' report (#1449)
	bugfix: Exclude subprojects from the 'Aging invoices' report (#1447)
	bugfix: Prevent a bug when deleting all selected actual expenses (#1446)
	bugfix: hotfix/rejected-multicurrency-fix-update-total (#1444) 
	bugfix: Fix/wrong dates using net 30/60 at invoice form (#1285) 

	Since 3.9.1.12
	--------------------
	bugfix: hotfix/total-not-changing-when-modified-qty-on-invoice-lines (#1442) 
	bugfix: fix query that retrieves report timeslots, it was using end_time in one condition and should use start_time (#1437)
	bugfix: Invoice template updates (#1436) 
	bugfix: ERML invoice templates improvements (minor) (#1435) 

	Since 3.9.1.11
	--------------------
	bugfix: Invoice Aging report: show due amount in the balance column (#1429)
	bugfix: invoice payments fix thousand separator (#1427) 
	bugfix: Fix original and props invoice print templates
	bugfix: fix invoice line date when leaving input using mouse (#1418)
	bugfix: Fix order by exexution order (#1408)
	bugfix: invoice preview-not-working-for-feng (#1430) 

	Since 3.9.1.10
	--------------------
	language: update fr_fr translations
	
	Since 3.9.1.9
	--------------------
	features: In invoices templates add "Person name" in columns to display should be a combo with full name, initials or blanck for timeslot and expenses
	bugfix: In invoice line edit, adjust type field to fiil all text
	bugfix: Fix old invoices templates version
	bugfix: changes requested to the billing report
	bugfix: fix error when marking expenses as invoiced if invoice has been deleted
	bugfix: Paid invoices showing as "Partially Paid"
	bugfix: bugfix/setting-default-actual-expense-type
	feature: Include printed and partially paid invoices in the 'aging invoices' report
	bugfix: update of currency and tax lines


	Since 3.9.1.8
	--------------------
	feature: invoice with total 0 can be set as paid
	feature: Enable invoice preview to read group
	feature: sync expenses bill qbo
	feature: invoice view critical improvements
	feature: Allow to edit expense
	bugfix: In invoice preview dont show empty section in labor table
	bugfix: invoice edit lines clean unit value when change type
	bugfix: Do not synchronize time entry with QBO when timer is stared
	bugfix: Project billing report not calculating half cents - Show 3 decimals instead of 2

	Since 3.9.1.7
	--------------------
	feature: Enable invoice preview to read group
	feature: Timer: Add functionality to add description when stop button clicked
	bugfix: update invoices looks
	bugfix:  Invoice templates-  "Group by labor category / Group by expense category (show fee)" template fix
	bugfix: invoice-lines-disccounts-not-accept-decimals
	bugfix: Subscribers disappeared from Documents
	feature: Add default billing email in the main within contact of the organization-part2
	feature: default actual expense type selector
	bugfix: project billing report not calculating half cents

	Since 3.9.1.6
	--------------------
	feature: Feature/weekly view add time without task
	feature: Invoice view should same as invoice print
	feature: Improve timer config option
	bugfix: If billable property (radio-button) is set to "No", then billing set to zero
	feature: Monitor non synched invoices
	feature: In custom reports - conditions - develop three new options when filtering by a dimension (and improve UI)
	bugfix: WIPs report improvement: Start date on projects
	feature: task email reminder start date
	feature: invoice templates
	bugfix: can edit a contacts report
	bugfix: Update module expenses2 to version 22
	bugfix: Changed checkbox logic for expenses taxes
	bugfix: Fix search box in INVOICE module
	bugfix: Mark as invoiced 1 time entry
	bugfix: Fix Import payment from QBO
	bugfix: Fix the logo on the reports
    bugfix: Fix edit contact's email
	bugfix: "Billing report" is showing running time entries
	bugfix: Fix Expenses report - Excel format is being generated by de system
	bugfix: Fix email address for Guzi West on the invoices

	Since 3.9.1.5
	--------------------
	bugfix: fix unit value in invoice lines 
	bugfix: fix workflow permissions for cases when expense does not have an user
	bugfix: bad closed </div> tag caused that other tabs of the form dissapear
	bugfix: recover-macrofacultad-payment-name-field
	feature: Improve validation in email fields within the contact form
	feature: Remove 'Credit Card Purchase' expense type from RN
	bugfix: The expenses are in order based on the date
	bugfix: The invoice is showing a expense line with a wrong date
	bugfix: : The invoice is showing a red line when you print it

	Since 3.9.1.4
	--------------------
	bugfix: when og.dimensions is empty the plain member selector renders empty, in that case we must query the server for the data before rendering

	Since 3.9.1.3-bca3
	--------------------
	bugfix: qbd sync Take description from category #1339
	bugfix: fix bill address and attn fields at invoice form

	Since 3.9.1.3-bca2
	--------------------
	feature: fix_Pre-launch_improvements_invoices (#1333)
	feature: invoice templates - new group by for time: date,user,labor (#1326)
	feature: Qb desktop export expenses (#1308) 
	bugfix: when line date is item date, dont use calculated value, use timeslot date
	bugfix: fix error editing actual expense
	bugfix: fix_invoices_views (#1325)
	bugfix: fix invoice generation line cat and user when grouping by timeslots

	Since 3.9.1.3-bca
	--------------------
	bugfix: fix lump sum invoice printing

	Since 3.9.1.3
	--------------------
	feature: Qb desktop export expenses (#1308) 
	feature: Config option allow duplicated invoice number (#1335) 
	bugfix: Fix budgeted expenses totals calculations when sorted by date (#1315)
	bugfix: Fix the bug in calculations in the "Aging invoices" report (#1316)
	bugfix: fix expense class sync, use class of expense category before using pr… 
	bugfix: hotfix/guzi-west-improvements-to-the-invoice-templates (#1334) 
	bugfix: Fix/link object feature performance issue (#1331) 
	bugfix: ajustes biological Invoices (#1329) 
	bugfix: Project earnings report: improvements and bug fixes (#1324) 
	bugfix: Fix for spanish text on english dictionary (#1323)
	bugfix: 'Aging invoices' report: add the 'Project manager' dimension support (#… 
	bugfix: Hotfix/custom reports updates (#1320) 
	bugfix: hotfix/bottombar-for-scrolling-to-right-no-longer-appears (#1319) 
	bugfix: ERML Invoice template updates (#1318) 
	bugfix: Fix/contact allow duplicated emails (#1289) 
	bugfix: fix time list order by billing rate

	Since 3.9.1.2
	--------------------
	bugfix: qbd export change " character (#1312)
	bugfix: qbd export invoices: Export all pages (#1311)
	bugfix: add select main contact as billing (#1310) 
	bugfix: don't delete financials info of subtasks, when saving parent (#… 
	bugfix: Address location in the invoice is not aligned with the envelope (#1305) 
	bugfix: qbo sync: At project sync, create the whole project hierarchy when synchonizing… 
	bugfix: fix Account summary breaks between two pages (we should never split i… 
	bugfix: at member properties form don't include reverse relations, only relations from main member type
	bugfix: Fix suppliers add edit (#1291) 
	bugfix: increasing the size of the total columns so that it allows you to see the value with its currency in a line (#1284)
	bugfix: Hotfix/task more improvements on invoice lines (#1266) 

	Since 3.9.1.1
	--------------------
	feature: adjust default template with new design (#1303) 
	bugfix: fix langs at 'year to date' filter option (#1302) 
	bugfix: qbo expense sync, when changing expense type to a non-sync type then delete the already created bill at qbo (#1298) 
	bugfix: Fix the issue when the timer is paused and trashed (#1295) 
	bugfix: fix quickbooks plugin installer

	Since 3.9.1.0
	--------------------
	bugfix: Revert "Features/invoice template change columns (#1271)", this commit included an unfinished development
	bugfix: only change price inputs, not costs when changing billable property, also remember last prices (#1297)

	Since 3.9.1.0-rc6
	--------------------
	feature: New parameter on format function for datetimes when creating report conditions html (#1282)
	bugfix: Fix 'Show main project info' option for the project reports (#1294)
	bugfix: Project billing report: Add a new invoicing status option in the sele… 
	bugfix: allow EVX admin to edit QBO Id at projects form (#1292)
	bugfix: Fix the issue that didn't run the report with unfolded subprojects (#1290)
	bugfix: Fix problems with Bill to Addresses in invoices (#1288) 
	bugfix: fix BCA invoice address when printing (#1283) 
	
	Since 3.9.1.0-rc5
	--------------------
	feature: Add discard button to the timer in task and time lists (#1265)
	bugfix: restore companyid/rut field at billing info tab in invoices form (#1279)
	bugfix: Fix invoice edit when expenses2 not installed (#1275) 
	bugfix: Hotfix/invoice lines scroll macosx (#1268) 
	bugfix: Hotfix/improve render address custom properties (#1259) 
	bugfix: Fix timezone usage in queries at proj billing report (#1281) 
	bugfix: fix red line for invoices table
	bugfix: Billing yes/no updates unit ant total price in expenses (#1278)
	bugfix: fix logo/title in header, BCA invoices (#1274) 
	bugfix: Hotfix/visual errors within the task form (#1263) 
	bugfix: width max-content, users invoice line (#1260) 
	bugfix: set font size 20 in reports (#1257) 
	bugfix: Hotfix/cant edit contacts email (#1256) 
	bugfix: Improve member selector in the project billing report (#1254)
	bugfix: Save when member is not selected in the report option (#1267)

	Since 3.9.1.0-rc4
	--------------------
	Feature: Changes to the invoice templates for consistency and to support a good export to QB

	Since 3.9.1.0-rc3
	--------------------
	bugfix: Invoice lines - "Information field" - Print with line breaks

	Since 3.9.1.0-rc2
	--------------------
	bugfix: Line breack total titles
	feature:Project billing report: add option to sort projects by client name

	Since 3.9.1.0-rc
	--------------------
	bugfix: remove duplicated billing contact
	bugfix: remove amounts from information lines when printing
	bugfix: fix date when printing expense line
	bugfix: add labor information and expense information line types

	Since 3.9.1.0-beta4
	--------------------
	bugfix: fix invoice line scroll (#1249)
	bugfix: Fix 'project number' column in the custom reports (#1248)
	bugfix: Adjustments within the ERMl invoice (#1228) 

	Since 3.9.1.0-beta3
	--------------------
	feature: Qbo manage billable expense charges (#1238)
		* qbo - at invoice sync -> link related time and expenses to each line if possible, if not then link them to the whole invoice
	bugfix: Totals lables at custom reports are not shown when name column is not present (#1201) 

	Since 3.9.1.0-beta2
	--------------------
	bugfix: at invoice lines, when selecting expense categories -> filter product selector
	bugfix: invoice totals are overlapped with add new line link

	Since 3.9.1.0-beta
	--------------------
	feature: Add 'quantity', 'person', 'unit_price' columns to expenses in the project billing reports (#1224)
	feature: Options added to hardcoded payment terms calculation at invoice form (#1226)
	bugfix: invoice lines new dev bug fix (#1225) 
	bugfix: po number fixed (#1221) 
	
	Since 3.9.0.2
	--------------------
	bugfix: Hotfix/improve render address custom properties (#1259) 
	bugfix: Fix timezone usage in queries at proj billing report (#1281) 
	bugfix: fix red line for invoices table
	bugfix: Billing yes/no updates unit ant total price in expenses (#1278)
	bugfix: fix logo/title in header, BCA invoices (#1274) 
	bugfix: Hotfix/visual errors within the task form (#1263) 
	bugfix: width max-content, users invoice line (#1260) 
	bugfix: set font size 20 in reports (#1257) 
	bugfix: Hotfix/cant edit contacts email (#1256) 
	bugfix: Improve member selector in the project billing report (#1254)
	bugfix: Save when member is not selected in the report option (#1267)

	Since 3.9.0.1
	--------------------
	bugfix: Fix 'project number' column in the custom reports (#1248)
	bugfix: Adjustments within the ERMl invoice (#1228) 

	Since 3.9.0.0
	--------------------
	feature: new invoice template type 'Lump sum view' to accumulate in one line only when printing (#1247)
	feature: new dropdown menu in expenses and time list to mark expenes/time as invoiced/non_billable/unbilled, only available for our superadmin user
	bugfix: custom reports - fix column names when last group is shown as columns, fix columns and totals alignment, fix header alignment when columns are numeric (#1242)
	bugfix: remove incorrect html and symbols inside subtask description (#1240) 
	bugfix: set a minimum font size of 12 in reports (#1239) 
	bugfix: Add project number to project name and sort projects by name in 'Project billing' and 'Budget status' reports
	bugfix: dont apply timezone offset when filtering by date at invoice list, invoice dates doesnt have time. (#1246)
	bugfix: allow reports to filter by expense type (copy from Alex's branch) (#1245)
	
	Since 3.9.0.0-rc
	--------------------
	bugfix: Comment out 'time overlap' feature (#1227)
	bugfix: time expenses if non billable set rates to zero (#1218) 
	bugfix: Configuring new margin for reports (#1223) 
	bugfix: Change the report key of the 'Project billing' to fix option selections (#1222)
	bugfix: Use 'AND' condition in dimension filter when getting projects for project billing report (#1220)
	bugfix: Generate new minified css file to apply address component css (#1219)

	Since 3.9.1.0-beta
	--------------------
	feature: add a new approval status 'Processed', support bulk actions (#1216)

	Since 3.9.0.0-rc
	--------------------
	feature: At invoices form allow to select the type of each invoice line (labor, product, etc.)

	Since 3.9.0.0-beta10
	--------------------
	feature: config option to allow time entries overllpping or not (#1090)
	bugfix: Sort contacts alphabetically in the Utilization report (#1192)
	bugfix: Improve 'Utilization report' contract hours calculation (#1209)
	bugfix: grid layout for address custom properties (#1210)
	bugfix: fix user form user data tab, div close missing (#1205)
	bugfix: fix budgeted expense widgets and list (#1203)
	bugfix: Import tool: Add the 'invoicing status' field to an actual expenses form (#1206)
	bugfix: the part of the search query that looks at the project's client name was wrong and causing that every member is returned, causing front-end performance issues (#1202)
	bugfix: QB Desktop export: chage created on by invoice date (#1211)
	bugfix: ensure that actual expenses can be classified in approval status

	Since 3.9.0.0-beta9
	-------------------
	feature: Improvement/add unbilled billable only billing report (#1197) 
	feature: Added year to period filters in time module (#1189)
	bugfix: remove wrong background color for report header (#1199)
	bugfix: Fix company logo when exporting report to PDF (#1198)
	bugfix: hide print option if pdf option is available at custom reports (#1190) 
	bugfix: don't cut table at invoice print if it has too many lines (#1169) 

	Since 3.9.0.0-beta8
	-------------------
	feature: Improve project billing report add contract amount (#1185) 
	feature: Feature/weekly view add time without tasks (#1170) 
	feature: Visual adjustments for the contact form (#1096) 
	bugfix: when rendering contact cp and receiving default value, verify that is a valid id to make the query (#1183)
	bugfix: Weekly view: fix the missing newly added description when pressing ad… 
	bugfix: Check for end_time when sincronyzing timeslot to QBO on task timer start
	bugfix: Sort invoice lines by grouped name (#1186) 
	bugfix: Alphabetically order of projects inside Joint invoice (#1184) 
	bugfix: Rename custom property code from 'contract_amount' to 'budget_total' (#… 
	bugfix: documents file types icons fixed (#1180) 
	bugfix: when quering expense totals the query fails for the widgets that include expenses
	bugfix: when quering expense totals for reports the query was not adding the expense_totals table

	Since 3.9.0.0-beta7
	-------------------
	bugfix: Qbo sync tsheets ts progress Fix QB user save complete data (#1178) 
	bugfix: fix subtask classification inheritance when editing parent task (#1175) 
	feature: Add option to include signing box to pdf and excel export of custom reports (#1146) 
	Feature: Support time interval plugin in the weekly time entry (#1101) 

	Since 3.9.0.0-beta6
	-------------------
	feature: weekly view time entries improvements (#1128) 
	feature: excel export: improve the header (#1136) 
	feature: Add project number to custom reports (#1150) 
	feature: New cp type: url (#1141)
	feature: Add budgeted columns to project list and custom report (#1109) 
	bugfix: change custom report date render to prevent double timezone apply (#1167)
	bugfix: fix draft mail send process (#1166) 
	bugfix: at invoices take Po number from project (#1165) 
	bugfix: Validation on 'undefined' error when saving completed task (#1162)
	bugfix: Add line to RN invoice format (#1161)
	bugfix: when quering expense totals for reports the query was not adding the expense_totals table
	bugfix: RN print template multiple address (#1154) 
	bugfix: Fix order by newsletter subscription in member list (#1153)
	bugfix: Fix 'Financials' and 'Earned value on labor' data presentation (#1149) 
	bugfix: fix aging invoices report (#1148) 
	bugfix: fix issue when changing from weekly to list view at time module (#1144)
	bugfix: Include functions js file on time module index (#1142)
	bugfix: Quinn missing entries grouped report (#1140) 
	bugfix: tsheets add langs and test filters (#1139) 
	bugfix: exclude timeslots that are trashed (#1134) 
	bugfix: order list by price mult not working (#1133)
	bugfix: Pending work by labor category report is not working (#1131)


	Since 3.9.0.0-beta5
	-------------------
	feature: Support multicurrencies in the actual expenses list (#1121)
	feature: improve header and footer when exporting custom reports to PDF(#1111) 
	feature: QBD integration: 2 level mapping for qbd categories, billingcat/laborcat and producttype/expensecategory
	bugfix: QBD integration: fix max address date at invoice export to qbo
	bugfix: QBD integration: fix date field at invoice and invoice line level to use the object creation date always
	bugfix: QBD integration: fix 'duplicate name' error when importin time and project has same name as client
	bugfix: temporal fix for order in custom reports grouped by project with a composed name (#1127)
	bugfix: add billing contact (attn) field to the billing information section at invoices form, autocomplete it with client/contact selection
	bugfix: ensure qbo plugin update script adds all the required values at version 17

	Since 3.9.0.0-beta4
	-------------------
	bugfix: Qbo sync tsheets, fix token issues (#1116) 
	feature: Time weekly view Replace plus sign with new svg (#1098)

	Since 3.9.0.0-beta3
	-------------------
	feature: Improve the way invoices are sent via email, and add attachments
	feature: when pressing tab at the end of invoice lines table a new line is added to the form
	bugfix: before using getUsername function ensure that transport object is a Swift_SmtpTransport, other transports classes may not have this function
	
	Since 3.8.8.11
	-------------------
	bugfix: Configuring new margin for reports (#1223) 
	bugfix: Change the report key of the 'Project billing' to fix option selections (#1222)
	bugfix: Use 'AND' condition in dimension filter when getting projects for project billing report (#1220)
	bugfix: Generate new minified css file to apply address component css (#1219)

	Since 3.8.8.10
	-------------------
	bugfix: Sort contacts alphabetically in the Utilization report (#1192)  
	bugfix: grid layout for address custom properties (#1210) 
	bugfix: budgeted expense widgets and list is broken (#1203)
	bugfix: Import tool: Add the 'invoicing status' field to an actual expenses form (#1206)
	bugfix: the part of the search query that looks at the project's client name was wrong and causing that every member is returned, causing front-end performance issues (#1202)
	bugfix: ensure that actual expenses can be classified in approval status

	Since 3.8.8.9
	-------------------
	bugfix: hide print option if pdf option is available at custom reports (#1190) 
	bugfix: don't cut table at invoice print (#1169) 

	Sinec 3.8.8.8
	-------------------
	bugfix: when rendering contact cp and receiving default value, verify that is a valid id to make the query (#1183)
	bugfix: Sort invoice lines by grouped name (#1186) 
	bugfix: Rename custom property code from 'contract_amount' to 'budget_total' (#1181)
	bugfix: documents file types icons fixed (#1180) 
	bugfix: when quering expense totals the query fails for the widgets that include expenses
	bugfix: when quering expense totals for reports the query was not adding the expense_totals table

	Since 3.8.8.7
	-------------------
	bugfix: qbo sync: when importing projects fix function that queries by project number (#1177)
	bugfix: when generatin invoice grouping time dont put in the same group times with different rate (#1176) 
	bugfix: Task: bill to consider line breaks robinson noble invoices (#1173)

	Since 3.8.8.6
	-------------------
	bugfix: Fix project billing report dates issue (#1152)
	bugfix: fix fo_workflow_permission_value_pgs column type and length (#1143)
	bugfix: Fix get user initials fn at invoice line (#1137) 
		* fix invoice line get user initials funcion, it was implemented badly without verifying if objects exists before using
		* fix contact sync, if not found by email then search it by name before creating it
	bugfix: fix RN invoice template, update it to latest version
	bugfix: logo adjustments at RN invoice template (#1163) 

	Since 3.8.8.5
	-------------------
	bugfix: Fixes to grouped timeslots report (#1160) 
		* increase minimum width of columns
		* remove currency from amount columns when exporting to excel
		* new option at grouped timeslots report to use descriprive format or not in the worked time column
	bugfix: when showing utilization report date range, the user timezone is not applied and end date is shown 1 day after

	Since 3.8.8.4
	-------------------
	bugfix: fix time and invoices list error when deleting all the objects in the page
	bugfix: recover lost code to sync time and expenses to qbo
	bugfix: allow generic date filters to use genid to avoid conflicts with other list filters
	bugfix: recover period filter at invoices list
	bugfix: move filters to a second toolbar at
	bugfix: Add support for the multicurrencies in the expenses (#1118) 
	bugfix: The bca invoices are not taking the PO NUMBER from the project form (#1108) 
	bugfix: Remove a line (1 px hight) thats at the end of the bca invoice print (#1107) 
	bugfix: invoice autonumbering, when validating number exclude invoices in trash (#1106)

	Since 3.8.8.3
	-------------------
	feature: qbo sync - new config option to define the start date for sync, anythig before will not be sync
	bugfix: when getting invoice template to print, if os is windows the dir separator is wrong
	bugfix: prevent exception with time module date filters when user changes the date format preference

	Since 3.8.8.2
	-------------------
	bugfix: Fix Time list sorting by the invoicing status column (#1095)
	bugfix: when generating invoice, use client's billing address and billing contact if project doesn't have a billing contact (#1094)
	bugfix: Fix person filters for time + actual expenses (#1093)
	bugfix: Fix calculations for the widgets; improve the performance 10x (#1092) 
	bugfix: Incorporate invoice print templates to main repo (#1091) 
	bugfix: improvements to RN invoice template
	bugfix: validate the expense before firing the hook, so the qb sync will not be triggered if validation fails (#1089)
	bugfix: give more precision to unit price at invoice line level to ensure that qty*price=total (#1104)
	bugfix: add more options to the hardcoded algorithm that calculates due date using payment terms
	feature: Qb desktop integration invoices (#1102) 
	feature: Qb desktop integration improvements - time export to IIF (#1100) 

	Since 3.9.0.0-beta2
	-------------------
	feature: Feature/Weekly time entry: add multiple entries per day
	feature: Final changes for TSheets integration MVP
	bugfix: when ordering invoice lines by date, not all properties were copied to the new data array, like product_type_id, so invoice line loses relation with product.

	Since 3.9.0.0-beta
	-------------------
	feature: Weekly time entries interface
	feature: Integration with Quickbooks Tsheets
	bugfix: emails not sending when from header is not the same used to authenticate (#1069) 
	bugfix: missing icons for some document types

	Since 3.8.8.0
	-------------------
	bugfix: fix quickbooks plugin install/update scripts to fix mapping error 
	bugfix: when sinchrinizing invoice don't resinchronize the associated timeslots
	bugfix: invoice generation: lump sum not taking all expenses
	bugfix: invoice generation: order lines by date
	bugfix: fix missing lang notifications (#1048) 
	bugfix: invoice billing info: don't concatenate billing address at bill to field if there is only one billing client
	bugfix: invoice billing info: use billing contact address when printing invoice if available
	bugfix: can't view email using phone browser (#1074) 
	bugfix: disable qbo account and payment method custom properties when deactivating quickbooks plugin
	bugfix: remove additional value inside the query that caused error when upgrading(#1067) 
	bugfix: Add assigned to column to the linked objects component (#1057) 
	bugfix: Default Lump sum type template not working properly with taxes (#1029) 

	Since 3.8.8.0-beta5
	-------------------
	bugfix: expenses paid by must show only active users
	bugfix: performance improvements in every form, when autocompleting related dimensions selections after selecting project, specially for invoices
	bugfix: when autofilling invoice bill info use billing client if present
	bugfix: sort by projects is not showing time without projects
	bugfix: copy task is not working

	Since 3.8.8.0-beta4
	-------------------
	feature: drag and drop invoice lines to set the lines order
	bugfix: expenses - fix product type not null verification before asking for its properties
	bugfix: qbo sync - at expense sync don't query terms if expense doesn't have any
	bugfix: script update name config options from defaultTypeAddress to default_type_address

	Since 3.8.8.0-beta3
	-------------------
	feature: create new line with tab key after last invoice line
	feature: add different logo for clinets in contacts list
	feature: qbo sync - config option to specify which expense type will be sync, hide qbo system config options, new expense type for field lab testing
	bugfix: qbo sync - sync classes field at expense and time sync, use cp to match classes
	bugfix: qbo sync - modify qbo sync column text for expense types that don't synchronize
	bugfix: fix time toolbar, remove hardcoded rests of code of qb buttons
	bugfix: prevent errors when sync client and doesnt have a contact associated
	bugfix: invoice print - use only line id to order in every line to keep original line order

	Since 3.8.8.0-beta2
	-------------------
	feature: new expense type for Rentals/Consumables, config option to enable/disable this type, hide/show form inputs depending on expense type
	bugfix: when creating new expense select logged user by default at employee field
	bugfix: Fix: Save billable value when adding and editing time entry (#1035)
	bugfix: Fix project reports pdf print

	Since 3.8.8.0-beta
	------------------
	bugfix: in expenses hide payment account and payment method fields
	bugfix: make employee mandatory for reimbursable expenses
	bugfix: fix invoice numbering suffix calculation
	bugfix: add save buttons at the end of the invoice form to prevent scroll to top
	bugfix: When printing invoice discounts respect lines order

	Since 3.8.7.X
	------------------
	feature: Add support for negative units in invoice lines.
	feature: 3 ways to show discounts at invoice template level
	feature: MVP for account payables
	feature: sync expenses with qbo bills 
	Since 3.8.7.4
	------------------
	Fix: Save billable value when adding and editing time entry
	bugfix: Fix project reports pdf export
	bugfix: fix for duplicated invoice number issue after improvements

	Since 3.8.7.3
	------------------
	feature: add "transfer" to payment methods
	feature: change paid by label for employee label
	bugfix: fix invoice autonumering (#1015) 
	bugfix: issues with pdf export at reports (#1022) 
	bugfix: Fix general content objects listing order by sentences, after proj_id order by feature it broke all listings if that cp not exists (#1018)
	bugfix: old expenses plugin: when ordering payments by expense, the ones without expense were excluded
	bugfix: Create a new product line with the TAB key (#1013) 
	bugfix: For dates, the possibility to add just the numbers and not the / (sla… 
	bugfix: Separate QB desktop and QB online plugins (#1007) 
	bugfix: Return the project status to the 'Projects' widget (#1004)

	Since 3.8.7.2
	------------------
	feature: allow invoice templates to group or not the discount lines when printing (#1009)
	bugfix: show percentage symbol only for invoice allocation attribute
	bugfix: auto resize textarea detail input at invoice lines

	Since 3.8.7.1
	------------------
	bugfix: Fix the 'projects' widget filtering (#1002) 
	bugfix: add percentage and remove symbol from value and condition for member association attributes (#1003) 
	bugfix: Set task's name using template parameter (#1001)
	bugfix: Add 'days since created' column to the project list and projects view
	bugfix: change in the name column for the default type address setting (#998) 
	bugfix: Use invoice subtotals to avoid including taxes in revenue in 'Project earnings' (#995)
	bugfix: Disable submit button in the form when clicked, enable it if there was a problem (#994)
	bugfix: Fix the tasks' financial columns in the custom report (#993) 
	bugfix: Fix exports and improve inputs in the "Cost and billing by client" report
	bugfix: Filter projects by active context when the project is selected (#991)
	bugfix: Dont group invoice lines with different product (#987) 
	bugfix: Rename 'Unknown' to the 'No contact data' in the client form (#989) 
	bugfix: Fix duplicate key error in upgrade script (#990) 


	Since 3.8.7.0
	------------------
	bugfix: Improve total column in the Utilization report (#986) 
	bugfix: Don't allow to edit and delete invoiced time and expenses, unvoid and untrash invoices (#984)
	bugfix: Don't allow to mark as void the invoice if it is synchronized (#983) 
		* Don't allow to mark as void the invoice if it is synchronized
		* Don't void the invoice if it is synchronized and the sytem is not connected
	bugfix: Fix/Prevent setting a task to repeat forever unintentionally (#982) 
		* Set correct repeat option when user starts typing or selecting day
		* Notify user if 'repeat times' or 'repeat until' is empty
		* Don't save repeat options when creating a repetitive task via 'Repeat times'
		* Check if user can change repetitive options
		* Add langs to the error messages
	bugfix: Fix 'total budget' column name in the project earnings report (#980)
	bugfix: Make performance improvements for email widgets by removing sharing table from query (#977)
	bugfix: change of text in the label of cancel invoices (#976) 
	feature: new config option to split or not the invoice, add invoice allocation… (#978)
		* new config option to split or not the invoice
		* add invoice allocation attribute with the install/upgrade script
		* ensure that relation between projects and billing clients exists


	Since 3.8.7.0-beta3
	------------------
	feature: Invoice template group by exp name (#967)
	feature: allow to change how the automatic actual expense name is built using a config option
	feature: allow invoice templates to group expenses by expense name
	bugfix: Apply styling and use lang when rendering invoicing status for time
	bugfix: Fix last month filter in the time list (#973)
	bugfix: Add performance improvements to activity widget (#971)
	bugfix: When checking the active context add only associated to object type members (#970)
	bugfix: fix query that verifies if mail already exists, from header was not escaped before using it in the sql (#968)
	bugfix: when clients and projects are in the same dimension, a null is a result of a subquery and a value cannot be inserted at income config options table
	bugfix: Add new option "For budget, use" to the "Project budget status" (#965)
	bugfix: fix workflow permission plugin pre-conditions
	bugfix: fix newsletter plugin install queries
	bugfix: when getting inv template data to use in js, html fields (such as footer_text) must be removed, it breaks the json decode js fn (#966)
	bugfix: when saving invoice and no currency comes in the invoice data, then use the currency of the first invoice line

	Since 3.8.7.0-beta2
	------------------
	bugfix: Fix/task billable notifications (#964)
	bugfix: Add invoiced and unbilled columns to the 'Project Billing' report (#962)
	bugfix: when generating invoice with date range, the end date condition was using beggining of day instead of end of day, so last day's expenses were excluded (#961)

	Since 3.8.7.0-beta
	------------------
	feature: Fix/remember the options last selected (#958) 
	feature: Hotfix/form contact address default value (#956) 
	feature: Hotfix/larger textarea fields (#949) 
	bugfix: hotfix/advanced_core_and_expenses_plugin (#951)
	bugfix: firstCommit documents icons missing documents icons (#947) 

	Since 3.8.6.X
	---------------
	feature: Modifications to Cost/Revenue forecast report (#946) 
		* Aply changes of branch feature/revenue-forecast
		* update script must change report name in next update
	feature: sort by project within time module (#929) 
		* allow ordering by project number and remove project number column
		* requested changes to avoid errors
		* added condition to check custom prop id exist
		* Add improvements to sorting
		* Allow to sort projects in the time tab
	
	Since 3.8.6.30
	---------------
	bugfix: Fix/task billable notifications (#964)
	bugfix: Add invoiced and unbilled columns to the 'Project Billing' report (#962)
	bugfix: when generating invoice with date range, the end date condition was using beggining of day instead of end of day, so last day's expenses were excluded (#961)

	Since 3.8.6.29
	---------------
	bugfix: Fix/widget performance in the 'Tasks status' and 'Work progress' widgets
	bugfix: Inherit 'is_billable' value when creating tasks using templates (#953)  
	bugfix: Add escape character to the subtask name in the task form (#950)
	bugfix: invoice list is not working when filtering by any member, since 3.8.6.29

	Since 3.8.6.28
	---------------
	bugfix: when order criteria is the same in several invoices, we must use another default order (object_id) to apply the sorting (#945)
	bugfix: Fix invoice autonumbering and due date when batch invoicing (#942) 
		* when receiving a dummy expiration date object, the save_invoice function is not recalculating it using the payment terms
		* fix invoice autonumbering when invoice notebook numbers reach the final number, allow to use autonumbering when no notebook is used
	bugfix: In actual expense custom reports the paid_by_id column is not showing user names (#941)
	bugfix: Fix project earnings project filtering and add 'Total budget' column (#940)
		* Include projects with unset 'budget_total' custom property
		* Remove net revenue column
		* Add 'Total budget' column to the Project Earnings Report
	bugfix: when no invoice template is assinged, use the default one. (#939)

	Since 3.8.6.27
	---------------
	feature: affinity ams: when selecting inactive status change the cp listing active to false
	feature: append comment registry event (#921) 
		* Progress required field comment
		* Appned comment in attender detail
		* change width modal
	bugfix: scroll top in register event
	bugfix: Include subprojects when filtered by active context in project billing report (#936)
	bugfix: Check only users' email when validating user's email if it exists (#934)
	bugfix: Fix bug when exporting excel in Project billing report (#933)
	bugfix: Fix/labor category billable functionality (#932)
		* Get billable labor categories using custom property
		* Add 'billable' column to the time list
		* Ask user if he/she wants to change the billable value when labor cat is changed
		* Ask if user wants to change the billable of time entry when labor category is changed
	bugfix: when client uses noreply@evx or noreply@feng then we must force the 'from' header of the email to be noreply@evx or noreply@feng (#930)
	bugfix: Fix timeslot status classification after invoicing (#928) 
	bugfix: Set names for billable columns when grouping actual expenses in custom reports (#927)
	bugfix: Avoid running the unnecessary function that adds members to users (#926)
	bugfix: Fix several js errors when plugins not active
		* fix js error caused by not escaping subtasks name in tasks edit form
		* fix js error when add/edit timeslot caused by bad verifcation when checking if income plugin is active
		* fix js error caused when not checking if variable is defined when adding additional filters to expenses module
		* fix js error at core tasks toolbar caused by not checking if billbable/notbillable actions are defined, if plugin not installed the tasks list breaks

	Since 3.8.6.26
	---------------
	feature: addu account payables plugin (#922) 
	bugfix: fix time period filter for time and remove it from invoicing module
	bugfix: usage of min chars config option to trigger the search request when filtering a dimension
	bugfix: invoice print was showing wrong amounts when separating expense markup to another line (#920)
	bugfix: Improve performance when saving project with contact custom property (#923)

	Since 3.8.6.25
	---------------
	feature: listener added to persist from and to date filter on change (#915)
	feature: add product type name column and and allow sorting by that value (#914)
	feature: at invoice print markup title fixed and make bold labor cat when showing alongside with description (#913)
	bugfix: change suggested index to use at table object_members in general listing query when filtering by members (#917)
	bugfix: Fix bugs task billable (#916) 
		* Throw correct error when the required member in task form is not selected
		* Include required dimensions when checking permissions
	bugfix: fix time period filter skip values (#912)

	Since 3.8.6.24
	---------------
	feature: time period filters added to invoices module (#910)
	bugfix: fix create table query for invoice templates, there was a ',' after the last column (#911)
	bugfix: prevent errors in js syntax when printing var values from php, avoid having a result like 'var x = ;' 
	

	Since 3.8.6.23
	---------------
	feature: Add client custom properties to the contact and company view (#903)
	feature: add permissions report (#906) 
		* Add new plugin permissions_report and build framework for the report
		* Add options to the report
		* Edit options styling
		* Add an object type selector to the options
		* Add controller functions
		* Collect data in controller, render collected data in the view
		* Fix styling when the user is selected
		* Add styling to the report
		* Move 'Permissions report' to the advanced_reports plugin
		* Add styling
		* Change page margins
		* Hide excel and csv buttons
		* Add styling improvements
		* remove hardcoded table prefix 'fo_' from queries
		* add a fixed width to the permissions column so all the tables show the columns with the same width and alignment
		* commented the function to export excel because it depends on a function that is not developed yet
	bugfix: item name must be escaped when querying or creating items at qbo (#907)
		* bugfix: item name must be escaped when querying or creating items at qbo
		* bugfix: must use the clean project name function at every create call
		* bugfix: at qbo sync don't use ' char at item names when sync, it leads to errors (also when escaping it)
	bugfix: Fix invoicing date range (#905) 
		* Add fixes to successfully upgrade income plugin
		* Fix dates when generating invoices via batch invoice
		* prevent error at batch invoicing when generating dummy invoices with the split process
		* fix date range usage at batch invoicing and generate project invoice, unify criteria for tz usage and end of day
	bugfix: Fix the bug when listing clients in the client tab (#902)
	bugfix: fix error 500 when trying to add an email account


	Since 3.8.6.22
	---------------
	feature: Qbo sync add usage of sales tax (#899)
		* tmp commit - testing sales tax sync
		* qbo-sync: include invoice taxes when saving to quickbooks
		* qbo taxes mapping interface
		* bugfix: error when generating tax data to sync
	feature: uncommented existing function to export timesheets to qb iif format (#898)
	feature: Feature add billable option to labor category (#901) 
		* Fix bugs in the update script of the income plugin
		* Add 'is_billable' to labor category and project tasks
		* Fix a bug that didn't save the boolean value for the member custom property
		* Add billable radio selector to the task form
		* Change task's is_billable when labor category is changed in the task form
		* Show message when billable value is changed in the form
		* In case if labor category doesn't have the billable value set yet. Use default boolean value, which is "1"
		* Rename function
		* Transfer task financials when copying task
		* Set correct "is_billable" when the task is copied or repetitive
		* Add 'is_billable' to the template task
		* Add functionality to switch the 'is_billable' value, if labor category is changed
		* Fix the bug that showed the message when value is changed multiple times
		* Add 'is_billable' columns and custom properties when the plugin is installed
		* Set correct 'is_billable' when subtask is created
		* Add 'is_billable' to the view
		* Set time 'is_billable' when added from the task or from time list
		* Set time's 'invoicing_status' when the task is changed
		* Set time's 'is_billable' when labor category is changed
		* Remove comments
		* Add 'mark as billable' and 'mark as non-billable' to the task list
		* Add 'billable' column to the tasks' list
		* Add config option "use_is_billable_value_in_tasks"
		* Use config option to determine when task's billable should be used or shown
		* Don't use task "billable" value when config option doesn't use task info
		* Remove condition when task 'is_billable' should be used
		* Add spanish translations
		* Fix the bug when assigning the task to time
		* remove logger messages

	Since 3.8.6.21
	---------------
	feature: allow invoice tempaltes to break down expenses by expense category (#889)
	feature: invoice templates show expense markup subtotal (#893)
		* abm of invoice template property to show mark up separated
		* feature: accumulate markup amount and show in a separate line
	bugfix: Delete custom property values that belong to the deleted objects (#892)
	bugfix: address, phone and email info added to customer and project when synching with qb online (#895)

	Since 3.8.6.20
	---------------
	feature: invoice temaplates break down by subprojects (#888)
		* add functions to get members of certain type at ContentDataObject class
		* abm of new column 'break_down_by_subprojects'
		* apply break down by subproject template option when generating invoice and when printing invoice
		* bugfix: invoice template footer text must be a column at invoice templates table, and not update a generic config option
	feature: Task templates allow using numeric vars in dates (#876)
		* in task templates, allow to use numeric variables when assigning date parameters. bugfix: variable names with '-' char was breaking every calculation
		* for task template numeric variable assignations allow fixed numbers to be set as a formula term
	bugfix: remove warning popup when adding a template task (#884)
	bugfix: remove the usage of deprecated column (since Feng 1.X) display_name at the Contacts model (#883)
	bugfix: Delete object custom property values when deleting members (#882)
	bugfix: add taxes to invoice template print html generation (#881)
	bugfix: if user doesnt have enough permissions to edit an account then the credentials validation should not be executed (#879)
	bugfix: Fix a bug that prevented adding a time entry in community edition (#877)


	Since 3.8.6.19
	---------------

	Bugfix: invoice templates issues (#880)
	* fix income plugin installer and updater scripts for column include_user_name
	* when grouping by timeslot the labor category was not stored in the invoice line
	* add 'none' to the options of the 'at line description' field at invoice templates form
	* ensure 'include_product_type' is checked if grouping by prod type
	* add class to user column at the generated html from the invoice template
	* fix income plugin installer for new column include_account_summary at invoice templates


	Since 3.8.6.18
	---------------

	feature: Add cost forecast report
	feature: allow showing user initials and customized footer on invoice templates
	feature: Add cost and billing rate columns to the time tab


	Since 3.8.6.17
	---------------
	
	Bugfix: calculate estimated cost and price for template task when instantiated


	Since 3.8.6.16
	---------------

	feature: add payments to the quickbooks import tool (#869)
	feature: allow to change labor cateogry th for a custom name for the section and force centered text for columns that didn't have alignment specified (#867)
	bugfix: Prevent showing error messages in the custom reports (#871)
		* Check if the ContactCombo variable is defined in the custom reports To prevent showing error messages
	bugfix: Stop showing 'Undefined' actual expense in the expenses list (#870)
	bugfix: Fix the penging and estimated amounts in the project budget status report (#868)
	bugfix: Export Excel with correct data in kjolhaug invoicing history report (#866)
	bugfix: remove ' char from generated invoice pdf filename because it creates an error when composing the email attaching the file
	bugfix: Exclude voided invoices from the invoicing history report (#864)

	Since 3.8.6.15
	---------------

	bugfix: Splitted invoices deletion must delete twins (#861)
		* structure for linking splitted invoices
		* fix invoice untrash call
		* add parameter to trash js fn to skip confirmation
		* controller and model for splitted invoice relations and twin invoice functions
		* installer and updater functions to create new structure
		* hooks and functions to modify the trash msg prompt and the trash/untrash/void features to apply the changes to the related twin invoices
		* put the invoice split feature in a separate plugin
	bugfix: Drag drop classify dont show popup if relation not multiple (#855)
		* add js function to find object type data by type name
		* when classifying by drag and drop don't show the 'keep-replace' popup if the classification in that type of member is not multiple
	feature: objects import: don't import duplicate project/client/supplier, in case of projects verify if exists with project number when possible (#857)
	enhancement: add a new 30 days payment term to kjolhaug hardcoded due date calculation (#858)
	bugfix: adding billing contact to a project would unassing previous projects for that contact

  Since 3.8.6.14
  -----------------

  enhancement: replace the js vanilla prompt fn by a proper window with the proper options when asking to keep or replace classification when drag and drop tasks to members (#850)
  bugfix: Drag drop task classify dont apply rate sch to subtasks (#846)
    * bugfix: when editing task rate schedule (in tasks form), the subtasks keep the old and the new rate schedule, and rate values are not calculated
    * when editing task, the classification was only spread to the first level of subtasks
    * before calculating task financials, calculate it for the subtasks
    * reclassification by drag and drop was not being applied to the subtasks hierarchy
  feature: qbo import tool: when querying invoices the default limit is 100 (#849)
    * qbo import tool: when querying invoices the default limit is 100, we need to increase the limit and iterate until there are no more invoices to query
    * add comments to code
  feature: Feature allow setting projects form fields required and or multiple (#847)
    * advanced core plugin update to make rate schedule and clients fields mandatory by default for projects form
    * feature allow superadmin to select if rate schedule and clients fields are required and/or multiple in admin panel

  Since 3.8.6.13
  -----------------

  bugfix: Revert Mail related changes causing bugs (#848)
    * Revert "fix stop checking accounts after error until connection data is updated, as well as logging email errors and notifying user (#838)"
      This reverts commit 014e6df.
    * Revert "fix don't show warning when deleting draft email from secondary account (#832)"
	  This reverts commit 1a5b6f6.
    * Revert "Feature improved outbox email user feedback (#822)"
	  This reverts commit f5ffff9.

  Since 3.8.6.12
  -----------------

  feature: Related to: (Activate the Mobile Version for BCA (prod and testing)) https://c1.fengoffice.com/_feng/index.php?c=task&a=view&id=4444137
    The determination of the name of the installation was modified for the case of test installations
    Before the test installation directory was determined by the last field, obtaining the same name as for the production installation
    Now it is detected if the directory before the last one is "test_installations" and in that case the prefix "testing-" is added to the name of the installation
    This way, for the BCA client the name of the installation is "bca" for production and "testing-bca" for testing.
    The fengapps_fgsite database on C2 is named accordingly
  bugfix: fix stop checking accounts after error until connection data is updated, as well as logging email errors and notifying user (#838)
  feature: Add custom invoice history report for Kjolhaug (#833)
    * Add a new report "Kjolhaug invoicing history"
    * Add date range filter to the 'Kjolhaug invoicing history' report
    * Select projects that have invoices in the selected date range
    * Add Project Contract type filter to the report options
    * Filter projects by contract type in the back end
    * Hide options when the project is selected
    * Create a separate plugin for Kjolhaug
    * Remove code related to kjolhaug from project_reports plugin
  bugfix: fix project billing report showing wrong start date on first page (#841)
  bugfix: Set correct Manager and Executive permissions for payment receipts (#845)
  feature: Add billing info to project list and batch invoicing (#840)
    * add billing email and billing address columns to batch invoicing grid
    * when generating automatic invoice if billing contact doesn't have email use the client's email
    * add billing address and billing email to projects list
    * remove logger from code
  bugfix: remove non-sense condition at function that generates task repetitiions that excluded some rep. tasks that should be included. Also when checking if it is the last one, ensure exclude tasks in trash and archived (#844)
  bugfix: listen to the hook after drag & drop classification and recalculate the billing for reclassified tasks and timeslots (#842)

  Since 3.8.6.11
  -----------------

  bugfix: Set subtask's financials to the calculated as default (#839)

  Since 3.8.6.10
  -----------------

  enhancement: Improve template task variables formulas (#836)
    * In task templates modify estimated time varible assignation selectors to specify time units for the variable and for the additional amount
    * MVP for task templates numeric properties assignation with formulas involving the numeric variables defined in the template
  bugfix: ensure that the group by column passed to the advanced reports hook has the right table alias to avoid sql conflicts (#837)

  Since 3.8.6.9
  -----------------

  bugfix: fix don't show warning when deleting draft email from secondary account (#832)
  bugfix: show or hide add worked time option depending on if task is parent and config settings (#831)  
  bugfix: if there is an error validating the access token, catch the exception and return that it is invalid, so the user can login again to qbo (#835)  
  bugfix: Set to 'calculated' all the values in the template task's financials tab (#834)

  Since 3.8.6.8
  -----------------

  bugfix: fix billing report showing gliched date data (#827)
  feature: Task templates new variable type numeric (#830)
    * allow to add 'numeric' variables when configuring a task template
    * add 'Estimated time' to the elegible properties of a task to be assigned with the value of a template variable when configuring a task template
    * in tasks form, if a task estimated time has an amount of minutes that is not present in the selector options, then add it to the selector, to prevent losing the value after saving the form
    * use getColumnType function for the template variable type
    * refactor code for template variables instantiation
    * allow text, numeric, date and user custom properties to be used in template tasks and re-use the same functions that fixed task properties use for calculations
  bugfix: only recalculate due date (using selected payment terms) in the controller if it's not sent by the interface (#825)  
  bugfix: Fix columns layout in the expenses list for the expense items rows (#826)
    * Add column 'is_billable' to the expense items row in the expenses list
    * Remove log message
  bugfix: Inherit task's financials info when it gets cloned (#828)

  Since 3.8.6.7
  -----------------

  feature: Feature improved outbox email user feedback (#822)
    * add methods for counting total emails in outbox and showing popup to user
    * show number of emails in outbox folder and give feedback when trying to send emails in outbox
    * remove legacy method that showed outbox emails count fron cron
    * new messages for outbox emails feedback
  bugfix: fix typo on email notification messages (#801)
  bugfix: errors reported on fresh enterprise edition installation (#766)
    * add typecast to avoid warning: 'count(): Parameter must be an array or an object that implements Countable'
    * call ->database_connection instead of DB, as DB class is not available from this dir
    * add date field to insert query for fo_cron_events table to avoid error when installing crpm plugin  
  feature: when creating a project from evx to quickbooks, set the option BillWithParent (#821)
    * when creating a project from evx to quickbooks, set the option BillWithParent to true
    * put some comments at project creation in qbo

  Since 3.8.6.6
  -----------------

  feature: Add new config option in the project billing report (#818)
    * Add option to exclude subprojects info in the Project Billing report and in the Project Budget Status report
    * Bugfix: Check if dates are DateTimeValue
  bugfix: When syncronizing an invoice if a line has a date then include it in the SalesItemDetail data of the qbo line (#820)  
  qbo-sync: when importing invoices prevent the case where there are in (#815)
    * qbo-sync: when importing invoices prevent the case where there are invoice lines with quantity=0, and take into consideration that LinkedTxn sometimes is an array and sometimes is an object
    * bugfix: before checking project number uniqueness, check if it's not empty
    * fix qbo sync to prevent differences at subtotal/total calculation when numbers come from qbo rounded up
  enhancement: Improve performance of 'Generate project invoice' function (#817)
    * Update invoice status of time entry via SQL query
    * Added a comment

  Since 3.8.6.5
  -----------------

  bugfix: fix time by name report doesn't allow to select all invoice option all (#812)  
  bugfix: Remove condition that excludes timeslots assigned to the trashed task (#816)
  performance: use the 'use index' directive when joining with object_members to avoid mysql to use index_merge and filesort for mail list when filtering by any dimension member (#814)
  bugfix: Fix bugs that didn't add timeslot via api (#813)
    - Add the date to the time entry
    - Add client member associated to the project member  
  bugfix: Bugfix repetition lost after non admin edits last task (#811)
    * When an user that doesn't have the permission to edit repetition options edits the last task of a repetition then the repetition options are lost and the task's thread is cutted
    * improve code comment

  Since 3.8.6.4
  -----------------

  improvement: Improve project billing report performance (#791)
    * Check if asocciated members are selected when running Project budget status
    * Get projects that have time and expenses in given date range in Project Billing
    * Get project members using SQL query instead of Member controller
    Improves performance a lot
    * Fix bug that effected dates filter in many reports  
  feature: Microsoft Integrations - Support Multiple Clients (#796)
    * Microsoft Integrations - Support Multiple Clients
    * Used config options for ms integration configuration
  bugfix: Add missing langs (#804)
  bugfix: Check if dates are selected when batch invoicing, notify the user if not (#805)
  bugfix: when trying to sync account mailboxes, if imap connection fails, don't retry for each mailbox (#807)
  bugfix: Invoice template: don't allow to add description if grouped by labor categories (#808)
    * Don't allow to add description if grouped by labor categories
    * Don't allow to add description if grouped by tasks
  bugfix: Remove unwanted characters from client and projects when trying to create them in Quickbooks (#809)
  bugfix: Exclude non billables from the project billing report (#810)

  Since 3.8.6.3
  -----------------

  feature: add config option to prevent users from adding time to parent tasks (#798)
    * add config option to prevent users from adding time to parent tasks
    * update plugin version info
  feature: Create invoice memo cp for project (#774)
    * Add 'invoice memo' custom property to the project
    This custom property is used to autopopulate the additional info of the invoice
    * Add cp in installer
    * Remove logger message
  bugfix: format_date function last argument (timezone) had zero instead of null as default value, and that caused that the date on the exported report didn't apply user timezone (#806)  
  bugfix: add document type for invoices with facturalista (#802)
    * fix income plugin updater at version 32 to 33
    * add document type selector to invoices in facturalista plugin, to specify the client doc type (ci,rut,passport,dni)
    * bugfix: at inovoice form: replace country text input with a country selector to ensure that we use country code instead of plain text
    * remove duplicated queries in income/update.php and use raw sql instead of DataObject to prevent errors if some columns are not present
  feature: improve error message when batch invoicing can't be executed (#800)
    * improve error message when batch invoicing can't be executed
    * replace double quotes by single quotes in lang.php to avoid js errors  
  bugfix: Fix the excel export bug in the "Worked time by person" report (#794)
  bugfix: after making inv number alphanumeric, the autonumeration process returns always number 1000 after reaching num 999 (#803)

  Since 3.8.6.2
  -----------------
  bugfix: utilization report bugs (#787)
    * fix subtitle of report end date was one day behind selected end day
    * fix when selecting this month filter showing until last day of month instead of until current day
    * add variation column to utilization report
  feature: make quickbooks column of invoices sortable (#789)
    * make quickbooks column of invoices sortable
    * update controllers to allow sorting by quickbooks synch state
  feature: Don't allow deleting synchronized invoices and payments receipts when QB is not connected (#793)
    * Flash an error if user tries to delete synchronized invoice
    When the system is not connected to the Quickbooks.
    Raise an error when the user tries to delete it from the:
      - Invoice list
      - Invoice view
    * Don't allow to delete payment receipt from list if synchronized
    * Don't allow to delete payment receipt from the view if synchronized  
  feature: in customer listing add the client's related contact custom properties as columns (#795)  
  bugfix: Check if user has permissions to generate invoice (#797)
    From:
    - Tasks list
    - Task view
    - Timeslots list  
  bugfix: fix crpm plugin installer

  Since 3.8.6.1
  -----------------
  bugfix: Qbo sync issue with required props when creating member and sync invoice (#788)
    * qbo invoice import: one transaction per invoice and verify again that the invoice has not been imported in the same iteration
    * qbo invoice import: prevent duplicated payments when invoice includes payment and payment includes more invoices
  feature: show start and due date in tasks module (#765)
    * Client RN show start and due date in tasks module
    * changes in the form that seach task tring in filters object
    * don't use harcoded index when getting columns to show/hide at the ObjectPicker.js
  bugfix: Check if suppliers and income plugins are activated (#776)
  bugfix: Check if email is already assigned to another user (#783)
    * When creating or editing a user
  bugfix: fix excel and csv export not showing totals for users (#784)
    * fix excel and csv export not showing totals for users
    * add condition to check if total_keys is null before setting default value
  bugfix: Qbo sync issue with required props when creating member and sync invoice (#785)
    * ignore required member associations when creating projects from quickbooks job
    * don't allow to save invoice with more than 21 chars (qbo restriction)
    * allow to select which invoices can be imported from the list preview
    * fix error importing invoice when client is marked as 'deleted' in quickbooks
    * remove unused and commented code
  bugfix: Prevent creating duplicated project number entries (#786)
    * Flash an error message when the user tries to create a project with a project number that belongs to another project
  bugfix: errors in qbo plugin js and hooks when invoice payment receipts plugin is not installed

  Since 3.8.6.0
  -----------------
  bugfix: at project billing report when use non-standard dimensions, we need to check if they exist before using them
  bugfix: Allow invoicing only approved time and expenses via bulk actions
    * Don't allow invoice  unsubmitted time and expenses via bulk actions
    * Check if time or entry is approved, when invoicing via bulk actions
    * Update approval status of time entries when invoice is trashed/untrashed
  bugfix report showing id instead of value and excel export formatting
    * fix: show currency name instead of currency id
    * fix spanish translation of currency name and type of payment
    * fix customer_rut shown with scientific notation format on excel exports
    * fix translation net d - due on receipt
    * remove lang, check currency is not null,  show short name
    * remove currencies tranlation
    * use strval to cast to string  
  bugfix: set value to empty string instead of null when clicking "remove filter" button on date-picker
  feature: Qbo sync import tool for invoices and payments and column to show if invoice/payment is synchronized (#782)
    * feature: batch import of QBO invoices into the invoicing module
    * add action to sync invoices and payments that are not synchronized and new column at invoices and payments lists to show if they are synchronized or not

  Since 3.8.5.69
  -----------------
  bugfix: Unable to enter time for a project that has both main and billing client
  bugfix: Bug with the Grouped Timeslots Report columns
  bugfix: Time entries reseting status when being edited and task is changed

  Since 3.8.5.68
  -----------------
  feature: Add subtotal column in batch invoicing
  bugfix: Invoice print minor adjustments
    * fix error that prevented to print invoice when using multiple client allocation
    * show discount amounts between brackets instead of using the '-' sign
    * change 'Actual expense' for 'Expense' in invoice print and align header to the left
    * at print invoice, expenses subtable, always use 'Expense' as header for the description for any combination of description, product type, exp cat, etc.

  Since 3.8.5.67
  -----------------
  feature: to resolve a problem in search by project
  bugfix: Fix invoices trash error msg and reopen status
    * bugfix: don't set invoicing status to printed when reopening an invoice
    * bugfix: when trying to delete an invoice, the system always assumes that the invoice is paid when showing the error message
  bugfix: when generating an invoice from a template, don't assume that the client's associated contact always exists
  bugfix: batch invoicing fails if one of the limit dates is not specified, we must set default values for these cases  
  feature: Set by defaul Actual Expenses tab in Expenses Module
  bugfix: Fix bug: increase the update version of the script. Because the version of the script is too low, the system will not run the script.
  bugfix: Don't assign incorrect 'approval status' member to the new actual expense
    * Don't assign 'approval status' member to the new actual expense
      The system automatically assigned the approval status member to the actual expense whenever 'invoicing_status' was assigned to the actual
      expense. I added a new condition to check if the actual expense didn't have  the  'invoicing_status' set to prevent setting approval status.
    * Remove commented code
  bugfix: Add 'invoicing_status' columns to the 'expenses' and 'payment_receipts' tables
    Add this column when 'income' plugin is activated and the user tries to install and activate 'expenses2' plugin
  feature: Add a new column 'Project number' to the time module list
    * Add new column 'Project number' to the time module list
    * Update the custom property 'project_id' Make it special and set type to 'text'
  bugfix: ms calendar sync feng duplicates events after sync
    * prevent errors when using a bad token in ms integrations preferneces section
    * bugfix: prevent duplicated events creation when synchronizing events with ms calendar
    * add more verifications before importing an event from ms to prevent duplicates, also fix the issue that was not assigning the created_by_id property of the event
    * comment some parts of code

  Since 3.8.5.66
  -----------------
  bugfix: New variable in languages files to payment receipt description
  bugfix: Project billing report: bug fixes and improvements
  feature: adjusting the width of the task financial columns
  bugfix: Bugfix qbo sync duplicate item name error

  Since 3.8.5.65
  -----------------
  bugfix: Fix number formatting in the actual expenses footer
  bugfix: Fix the bug in the batch invoicing when generating the invoices list
  bugfix: Fix time module footer counter:
    - Show correct info when filter values are changed
    - Decrease second value by 1 in the "Displaying objects" info
  bugfix: Check if client and project are associated with each other (if combination project/client is valid):
    - Check it when adding or editing the time entry
  bugfix: Bugfix/gsel changes in langs files in ms calendar
  bugfix: new changes in ms integratiosn langs files
  bugfix: Changes in langs files in ms calendar

	Since 3.8.5.64
	-----------------
	bugfix: add class to totals tbody when printing invoice and generating inner details html with template
	bugfix: modify invoice template html generation for printing to render time and expenses lines in separate tables


	Since 3.8.5.63
	-----------------
	feature: qbo-sync - prevent that category-item mapping is wiped out when qb item ids are changed in qb, if that happens then try to map using item name
	feature: Allow invoice templates to group expenses by description, also when grouping expenses don't put in the same group expenses with different cost/price rate
	feature: Add columns to project billing report and consider active context members at "Project billing" and "Project budget status" reports
	feature: QBO Viewer, for debugging and testing purposes, makes queries to QBO and shows data
	bugfix: fix error when trying to edit actual expense and image does not exists
	bugfix: don't allow to change invoice template if invoice is not new
	bugfix: fix invoice template html generation at expenses, use unit price in unit cost column if the mupliplier is not used
	bugfix: improve performance of the invoice list reloading after generating invoice and sending it to qbo, make sync run in background
	bugfix: editing invoice line description not reflected in invoice print
	bugfix: qbo sync - after saving invoice user has to wait until qb saves it to continue working, the sync must be done in background
	bugfix: qbo sync - if company not found by CompanyName try using DisplayName, when quering a project by project number don't check if it is a job or not
	bugfix: when saving inv template the include exp description is not saved, also we need to force it checked if grouping by description
	bugfix: when printing invoice using template sort the invoice lines by the first column to print
	bugfix: Lump sum - Calculate previous task financials when timeentry assigned to a new task
	bugfix: Lump sum - Calculate tasks when a subtask get assigned to another task
	bugfix: Remove hardcoded 'fo_' prefix in the MemberController query
	bugfix: Fix calculation bug in the "Project budget status"
	bugfix: Fix bug in the report title in "Projec budget status" report
	bugfix: Fix bug in the query that caused fatal error when grouped by dimension
	
	Since 3.8.5.62
	-----------------
	feature: Add ability to set a lump sum in the task financials
	feature: Subproject inherits project fields
	feature: when changing payment terms recalculate the invoice's due date if term is Net 30 or Net 60
	bugfix: qbo_sync fix duplicate name error when getting/creating client
	bugfix: when generating invoice don't duplucate description if grouping expenses by category
	bugfix: at item-category mapping, add the account number to the item's account name after selecting an item and showing its account
	bugfix: fix qbo api queries result size, the amount of items and accounts by default was too small
	bugfix: Changes to set correctly decimal digits in expenses module

	Since 3.8.5.61
	-----------------
	feature: allow to show expense name for invoice expense lines in invoice templates
	feature: set position sticky to tasks list headers so they are visible when scrolling
	feature: invoice view - hide ordered by if it is equal to bill to field
	feature: qbo sync - map labor/expense categories with qbo items
	feature: add 'hide billing tab in the timeslot' option
	bugfix: Improvements to the time imports
		- Increase memory size
		- Add members to timeslots only once
		- Save timeslot only once
		- Remove error messages when importing
	bugfix: fix multiplier column calculation at invoice template html generation when it is 0
	bugfix: error when filtering labor categories member selector using rate schedule selected member, the query was not being hierarchical
	bugfix: when deleting submember and not deleting its objects the is_optimization field for the parent membe classification was not recalculated

	Since 3.8.5.60
	-----------------
	feature: Add improvements invoicing history report
	feature: Batch invoicing syncronize to qbo in background
	feature: Improvements to the estimated and executed price and cost task columns
	feature: qbo-sync: in account-category matching if a category doesn't have an account assigned then use the closest in hierarchy
	bugfix: fix upgrade script queries that gave errors in new mysql version
	bugfix: Project earnings report needs 2 more columns
	bugfix: Project earnings report pdf export cuts the report
	bugfix: Set correct paid amount and due date based on status of the invoice when importing
	bugfix: Time-Module Resolved issue in period first half of the month
	bugfix: Resolved columns issues in non billable reports
	bugfix: fix actual expenses invoicing status filter
	bugfix: when member has archived submembers the arrow to expand submembers is shown in the dimension tree
	bugfix: actual expenses filter by invoicing status is including non billable entries when filtering by 'unbilled'

	Since 3.8.5.59
	-----------------
	feature: allow QBO sync to match labor categories with quickbooks account, and when generating invoices send the account that matches each line's labor category
	feature: Persist estimated cost and executed cost task values
	feature: Project billing report: Include the quick date options in the "Project billing" report
	feature: Project billing report: Add "invoicing_status" condition to the time and expense search
	feature: Project billing report: Include only "approved" time and expenses when getting unbilled ones
	feature: Microsoft Calendar Integration for MVP
	feature: in payment receipts change billable checkbox for a radio button yes/no
	feature: re-enable the 'bill to' info override with all the billing clients when invoice has more than one bc
	bugfix: Invoice import tool: Consider thousand separator option when importing invoices
	bugfix: Invoice import tool: Set paid_amount and due_amount when importing invoices
	bugfix: Invoice import tool: Include "due date" in the invoice form in the import tool
	bugfix: fix batch invoicing to prevent that expenses out of date range be included in invoice
	bugfix: add custom macrofcultad custom translations to the macro_facultad plugin
	bugfix: when an actual expense is not billable then the invoicing status should show 'non billable' text like in timeslots list

	Since 3.8.5.58
	-----------------
	feature: Revamp automatic split invoice generation and allow to show the same invoice totals and number for the same project invoices
	codefix: consolidate repeated code in common functions for project reports plugin helper functions and js functions
	codefix: remove harcoded 'fo_' prefix in some queries

	Since 3.8.5.57
	-----------------
	feature: Project earnings report 
	feature: Import tool: Add capability to import payment receipts
	bugfix: Fix estimated cost and estimated price column for subtasks

	Since 3.8.5.56
	-----------------
	feature: Project jdt report
	feature: Import tool: add invoice imports

	Since 3.8.5.55
	-----------------
	bugfix: qbo sync, fix customer generation when name has multiple spaces between words
	bugfix: fix invoice status calculation when payment is greater than invoice total
	bugfix: qbo cron event to process pending entities is giving error because cron functions file was not included

	Since 3.8.5.54
	-----------------
	feature: allow to have alphanumeric invoice numbers and allow to disable 'Serie' field
	bugfix: quickbooks sync: don't send dummy TaxCodeRef property if we don't have a value for a tax
	bugfix: client's contact was not always classified in client's associated dimension members
	bugfix: fix deprecated usage of { and } to access array position
	bugfix: when uploading file if selected file exists and then you change the file or the name it still will be uploaded as a new revision of the first one that has the duplicated name

	Since 3.8.5.53
	-----------------
	feature: AR Ledger report
	bugfix: Fix bug in the permissions tab during drag and drop
	bugfix: repetitive task is not generating the subtasks in the next repetitions
	bugfix: fix subtasks section in tasks form that added 1 hour to current estimated time when its minutes were greater or equal than 30
	bugfix: fix project reports install and update to avoid null values in objects table for the new report

	Since 3.8.5.52
	-----------------
	feature: Utilization report
	bugfix: Refactor and improve performance in the Import Tool
	bugfix: when creating item in qb if default income account at qb preferences is not set then try with 'Sales' income account

	Since 3.8.5.51
	-----------------
	feature: add invoice template option to show date in expenses
	feature: include project contract amount custom property when generating invoice summary to print
	bugfix: fix bill to column length
	bugfix: fix bug in invoice template when printing expense employee
	bugfix: allow to order the batch invoicing list by clicking in the column headers
	bugfix: don't filter expenses widget by last year if there are very few expenses

	Since 3.8.5.50
	-----------------
	feature: Add new filters to Time Module for half month periods
	feature: Updates to Aged AR report for PMs, show client name and increase date size
	feature: change 'invoice split' feature to be more informative and not split an invoice into several clients
	bugfix: fix invoice template error when grouping by tasks and task does not exists
	bugfix: fix billing address feature that caused the error in permissions tab
	bugfix: install.php file was in wrong directory at quickbooks plugin
	bugfix: fix batch invoicing pagination toolbar info
	bugfix: fix batch invoicing request to prevent 'max url length reached' error
	bugfix: fix quickbooks integration account usage when creating invoice item

	Since 3.8.5.49
	-----------------
	bugfix: fix quickbooks sync project and client matching, store the qb object id in members table and try to match using it, if not available then use project id cp or project/client name
	bugfix: fix po number assignation when generating the invoice
	bugfix: fix batch invoicing time limits for actual expenses
	bugfix: Added a new installation print message with the faulty query when installation fails

	Since 3.8.5.48
	-----------------
	feature: improve management of invoice allocation percentage, store the applied value in the invoice
	
	Since 3.8.5.47
	-----------------
	feature: allow invoice template lines table render function to receive parameters with the labels to use in the invoice
	feature: add functions at invoice class to get a summary of the invoice's project or client (used for invoice footers)
	
	Since 3.8.5.46
	-----------------
	feature: improve invoice templates to allow grouping by project and project-laborcategory, so user can create a 'joint invoice template' for grouping lines by subproject/laborcat
	bugfix: fix payment receipt form error when invoice name has any special char
	bugfix: fix error when entering to quickbooks integration config section and access/refresh token is invalid
	bugfix: fix required custom properties control for amount cps
	bugfix: fix error when non-superadmin edits permissions and expands workspaces tree
	
	Since 3.8.5.45
	-----------------
	feature: allow to split automatic invoices into several clients depending on the invoice allocation attribute on the relation project-client
	bugfix: fix expenses2 plugin update script
	bugfix: ix payment receipts form's invoice list and client selector when selecting project with multiple clients
	
	Since 3.8.5.44
	-----------------
	feature: allow billing contact cp to have companies
	feature: show billing address in project form, depending on the selected billing contact, billing client and client
	feature: Use billing contact cp for invoice billing info if that cp is present in invoice's project or client
	bugfix: fix checkmail function to skip accounts of disabled users
	bugfix: fix hardcoded id and prefixes in objects import get task function query 
	bugfix: fix user preference for notifying subscribers, if disabled it was notifying anyways
	bugfix: fix noOfTasks user preference lang and description
	bugfix: fix error in mail controller when hook not receiving 2nd param
	bugfix: fix some issues at invoice's billing information tab
	
	
	Since 3.8.5.43
	-----------------
	feature: improve performance in activity widget 
	feature: remove an index that causes performance issues
	feature: improve performance in mail list queries
	feature: Improve timeslots import perfromance
	feature: Support task assignment to the timeslot when batch importing 
	bugfix: Add cp code 'project_manager' for custom property "Project manager" in Project
	bugfix: Show currency selector when adding or editing the invoice
	bugfix: When editing or adding an invoice, show Series and Invoice number
	bugfix: fix update script, don't create billing client config option if clients dim is the same as projects dim
	bugfix: When importing timeslots set start time to 08:00 AM
	bugfix: fix payment classification in axes and objectives when changing quota
	bugfix: fix member selectors default associated member selection when there are more than one association between the same member types
	
	Since 3.8.5.42
	-----------------
	feature: generate automatic invoices using the billing client instead of default client if it is configured
	feature: new type of config option to select dimension associations
	feature: ensure that all dim-member associations have their config options before editing dimension options
	feature: Develop timeslots batch import
	bugfix: fix issue missing subtasks tags when saving task
	bugfix: if client address is not of type work the automatic invoice didn't use it
	bugfix: align numeric dim-association attributes to the right
	bugfix: fix invoice date in custom reports, it was adding timezone
	bugfix: fix js error when adding projects
	bugfix: fix invoice templates when grouping at timeslot level the user was not saved in the invoice lines
	
	Since 3.8.5.41
	-----------------
	feature: New plugin to have attributes in dimension member associations
	feature: Build aged invoices report
	feature: modify dimension associations structure and logic to allow having multiple associations between the same member types
	feature: add new dimension association config option to set a custom name for each association
	feature: modify member report group by selector to use dimension associations custom name if defined
	feature: modify advanced reports to use the custom dimension association names in report columns and conditions
	bugfix: modify income tab name
	bugfix: prevent warnings triggered in some parts of the code 
	
	Since 3.8.5.40
	-----------------
	feature: change invoicing status combo for a radio button indicating if it is billable or not
	feature: move billing yes/no widget to main tab in timeslots add/edit form
	feature: Add config option 'disable_billing_tab_in_timeslot' in advanced_billing plugin
	bugfix: fix dimension component renderers to allow non manageable dimensions to be shown when necessary
	bugfix: set all non-billable timeslot's billing to 0 in upgrade script
	bugfix: set timeslot billing to 0 if marked as non-billable
	bugfix: fix address field display, it was not applying line breaks
	bugfix: fix billing category edition, when removing all members the save function did not remove them
	bugfix: improve billing category form langs for billing rate field
	bugfix: fix income plugin update script when getting objects and some columns are not still in db
	
	Since 3.8.5.39
	-----------------
	bugfix: Fix timesheet report bug that caused misalignment
	bugfix: Start using commas instead of semicolon in timesheet csv export
	bugfix: don't allow user to create unassigned actual expenses if doesn't have the permission to see other user's expenses
	bugfix: don't show financials and expense progress widgets if user cannot see other user's expenses
	bugfix: fix activity widget to consider the can see other user's expenses permission to show the objects
	bugfix: modify can see other user's expenses lang
	bugfix: change message when not letting the user update the repetition settings of a completed task.
	bugfix: when editing a completed task of a repetition, the rest of the tasks should not be affected, and the repetition settings must not be lost
	
	Since 3.8.5.38
	-----------------
	bugfix: Don't show task template form after batch importing project templates
	bugfix: Fix missing comma in en-us lang
	
	Since 3.8.5.37
	-----------------
	feature: Add project template support batch import
	feature: Add mobile and fax number to the client and supplier batch import form
	
	Since 3.8.5.36
	-----------------
	bugfix: Add field to expense import, add lang to expenses plugin
	bugfix: fix missing langs for all 'unsubscribed' notifications
	bugfix: fix error when expanding subtasks introduced in last version
	bugfix: fix member deletion queries
	bugfix: only numbers must be allowed in numeric custom property inputs
	
	Since 3.8.5.35
	-----------------
	feature: allow tasks list to be ordered by custom properties
	feature: Batch import improvements
	bugfix: fix actual expenses default date, it was not using user's timezone
	bugfix: fix member delete function queries to prevent execution break due to memory limit reached
	bugfix: timesheet report 'last week' and 'this week' was filtering from monday to sunday instead of sunday to saturday
	bugfix: in timesheet report the timeslots associated to trashed tasks were excluded
	bugfix: fix time quick add row member selectors
	
	Since 3.8.5.34
	-----------------
	feature: add confirmation question before applying a bulk action for time module approval status change
	feature: add action in time list to see the each timeslot's modifications history
	bugfix: fix time module quick add row dim-member selectors, it was not removing previous member after changing if it was selected by default
	bugfix: create log for each timeslot involved in any bulk action of time approval feature, store previous status and current status
	
	Since 3.8.5.33
	-----------------
	feature: Hide contact custom properties in suppliers
	feature: Add expenses to batch import tool
	feature: allow users to add and link actual expenses to a budgeted expense that they can't write
	bugfix: in time module quick add row you can't select some text from the description with the mouse
	bugfix: remove special char ':' from customer/project name before sync
	bugfix: add 'if exists' to payment receipts plugin installer queries to prevent errors
	
	Since 3.8.5.32
	-----------------
	bugfix: Stop preselecting product type in actual expense
	bugfix: improve performance of some classification and permissions functions
	bugfix: don't recalculate contact member cache for user groups if groups didn't change
	bugfix: fix alignment of workflow permissions in users form
	bugfix: minor changes to timeslots hooks to try to improve performance when saving
	bugfix: fix call to renderContactSelector, prevent js error when memberId is empty
	
	Since 3.8.5.31
	-----------------
	feature: user filter for actual expenses module
	feature: Allow to assign client to the imported contact
	bugfix: Stop preselecting product type in actual expense if no budgeted expense is selected
	bugfix: fix expenses updater to allow classification in approval status dim
	bugfix: time module quick add row dimension selectors dont't show the member list when clicked
	bugfix: Fix bug that broke widgets when different date format was selected
	bugfix: remove permissions check before adding a time entry, it was working bad
	bugfix: fix 'NaN' js error in expenses cost/price calculation
	
	Since 3.8.5.30
	-----------------
	bugfix: always show associated dimensions in newsletter's contact selector
	bugfix: fix error in tasks list
	bugfix: fix budget status report billing category column
	bugfix: member selector performance - don't request get_dimension_id if there are no members
	bugfix: fix product type combo initialization in actual expenses
	bugfix: actual expenses status filter was being initialized with time list equal filter
	bugfix: time list invoicing status filter was being reseted after any action
	bugfix: Fix bugs that prevented saving address and contact when importing
	feature: allow plugin web to decide if sends mail from @feng or @evx depending on the edition
	
	Since 3.8.5.29
	-----------------
	feature: Add dimension members batch import
	feature: redistribute project budget status report column colspans to make more room for the expenses name
	bugfix: Fix actual expense assign product type from budgeted expense
	bugfix: Fix bug in budget report labor calculations
	bugfix: change date langs in budgeted expenses form
	bugfix: show file name when adding document to an actual expense
	bugfix: fix missing lang in billing rates
	bugfix: when adding contact from inside project form, the contact is classified in random member if it is removed from selection
	bugfix: fix workflow permissions component, ensure that the amount of inputs doesn't grow exponentially
	
	Since 3.8.5.28
	-----------------
	feature: Update react widgets' javascript packages
	feature: allow to select invoice template when generating project invoice
	bugfix: fix subtask's billing calculation when creating subtasks in tasks add/edit form
	bugfix: fix contact selector quick-add, it was saving an empty contact
	bugfix: change lang in expenses section of invoice print
	bugfix: fix product type filtering in actual and budgeted expenses form
	
	Since 3.8.5.27
	-----------------
	feature: allow to put headers for labor/expense details
	feature: make tasks view more wide
	bugfix: fix invoice template html generation when printing invoice detail headers
	bugfix: subtasks estimated time selector is not using the time interval plugin to override the available minutes
	bugfix: validate additional subtask form's selectors before calling their functions
	bugfix: fix input navigation with tab in contact combo modal form to add a new contact
	
	Since 3.8.5.26
	-----------------
	feature: allow to specify description and labor cat in subtasks of task form
	feature: Add suppliers batch import
	feature: allow to use taxes inside invoice lines
	bugfix: Skip asking budgeted expense when adding actual expense from budgeted expense
	bugfix: Fix css bug in the actual expense
	bugfix: fix price multiplier usage, when changing unit cost the unit and total price were not calculated
	bugfix: fix product type filtering, when dimension is defined as 'filter up' then the child members were not included to filter
	bugfix: set default value of billable prop to true when creating a new actual expense
	
	Since 3.8.5.25
	-----------------
	feature: Import plugin - Support importing boolean custom properties via batch import
	feature: Assign country based on country name or country code in batch importing
	feature: Allow to import amounts and numbers via batch import
	feature: Allow to save addresses as custom property when batch importing
	feature: Add more options to dates format when batch importing
	bugfix: Update vulnerable javascript packages
	bugfix: fix invoice lines lost data (prod type,cat,unit_cost,price_mult,etc) after changing invoice template
	
	Since 3.8.5.24
	-----------------
	bugfix: Fix bug that when rendering amount custom property
	bugfix: Show only contact custom properties in the client form
	bugfix: Add missing empty contact type
	bugfix: Save client's address, phone, email and url when batch importing
	bugfix: ensure that trashed_on and archived_on columns are not null
	
	Since 3.8.5.23
	-----------------
	bugfix: fix project invoice generation, it was not taking in account the last day's timelots
	bugfix: performance - dont request for listing totals if already have the number
	bugfix: fix performance issue with member listings
	
	Since 3.8.5.22
	-----------------
	feature: add client batch import to objects_import plugin
	bugfix: fix member reports when showing associated dimension column and has more than one associated member
	bugfix: fix expense items currency selector, it was not enabled when 2 or more currency present
	bugfix: improve time module performance
	bugfix: improve performance in financial widgets
	bugfix: improve performance in widgets activity, financials, earned_vs_labor, estimated_vs_worked
	
	Since 3.8.5.21
	-----------------
	feature: Improve objects import plugin, extend it for members
	feature: allow member reports to have conditions by 'located under'
	bugfix: improve breadcrbums rendering performance
	bugfix: ensure that forgot password email is sent instantly
	bugfix: fix var typo in mail utilities file
	bugfix: improve page-break in project reports for project blocks
	
	Since 3.8.5.20
	-----------------
	bugfix: project budget report - fix alignment of first th
	bugfix: project budget report - allow to decide if a page-break is inserted after each subproject
	bugfix: project budget report - fix description cell width
	bugfix: project budget report - fix all tables width and cells allignment
	bugfix: fix error in confidential users functions when no logged user
	bugfix: fix error when saving template that caused data loss
	bugfix: fix product/service integration when invoice line has user and labor category
	bugfix: don't assume the existance of invoice subtypes when making verifications
	
	Since 3.8.5.19
	-----------------
	feature: custom properties only for users
	feature: create qbo items for labor categories and products when creating invoice in qbo, config option to sync only first level classes
	bugfix: set mails panel 'preventClose' to false when sending an email to prevent the 'unsaved data popup' to appear
	bugfix: fix product type selectos error when any product has a dobule quot in the name
	
	Since 3.8.5.18
	-----------------
	feature: Add 'amount' custom property type
	feature: allow to include line description when grouping by labor cat
	feature: Use expenses' price in 'Expenses' and 'Financials' widgets
	bugfix: fix page breaks and repeat table headers in every page when exporting project budget report to pdf
	bugfix: fix workflow permissions installer to add expenses workflow permissions if expenses plugin is installed
	bugfix: fix expenses2 installer and updater to allow approval dimension to be applied in actual expenses
	bugfix: fix function that gets the client of a project when it is a subproject
	bugfix: fix error when generating invoice grouped by actual expenses and exp cagtegories dim is not present
	
	Since 3.8.5.17
	-----------------
	bugfix: fix error when instantiating tempaltes that have ' in some parameter name
	bugfix: fix user creation when type is superadmin
	
	Since 3.8.5.16
	-----------------
	language: Add missing portuguese translations
	feature: Hide old instant messengers, add twitter, facebook, linkedin
	bugfix: when clients are separated from projects, the projects dimension didn't change its name
	bugfix: fix client and status widgets to support long texts, and add some pt_br missing langs
	
	Since 3.8.5.15
	-----------------
	language: Add portuguese translations
	bugfix: fix error in join clause when ordering contact reports by company id
	
	Since 3.8.5.14
	-----------------
	bugfix: Allow to autofill with emails of contacts that user cannot manage
	bugfix: Change 'Home' translation in portugese
	bugfix: Show dimension names in the print task view
	bugfix: Fix bugs that prevented change the password
	bugfix: Show more product types in an actual expense form, fix decimals in actula expenses form
	bugfix: spelling error that stopped showing gantt chart (#623)
	bugFix: project budget status didn't include projects (#622)
	bugfix: fix permissions save functions when applying to submembers
	bugfix: fix error message lang in invoices module
	bugfix: when changing a timeslot's task, the old task's worked time was not being recalculated
	bugfix: invoice lang change, use quantity instead of amount in lines
	bugfix: fixes to permissions component
	bugfix: exclude disabled users from user cp selectors
	bugfix: fix hour_types plugin installer
	bugfix: remove restriction in webfile view that was only loading contents for urls like 'docs.google.com'
	bugfix: change non-working days skip algorithm to check if it is a non working day in every day of the interval for template tasks that advance some days
	
	Since 3.8.5.13
	-----------------
	feature: new plugin for additional timeslot permission (lock, unlock time in time status dim)
	feature: make filter-selected class bold like in mails module
	feature: listing search input, highlight keywords after search, and show clear button only if there is something to clear
	feature: in actual expenses form, separate description field to a new row and enlarge it
	performance: don't download the default language pack if it is the same as the user language
	bugfix: add config option to enable/disable the feature to hide the left panel when only a client/project is there
	bugfix: fix member color component when cp values and color column are not consistent
	bugfix: task was not shown in statistics widget when due date is today
	bugfix: fix gantt js error when calling tasks list from widget
	bugfix: fix timesheet status dimension plugin installer, error if income plugin is not installed
	
	Since 3.8.5.12
	-----------------
	feature: Allow to filter tasks list by 'upcoming' and 'without due date' 
	feature: click in tasks status widget goes to filtered task list
	feature: Allow tasks to use status timesheet dimension
	feature: improve listing function performance, affects dashboard, listings and reports
	feature: add delete button to text filter input in lists
	bugfix: Decrease the size of the load image for buttons
	bugfix: remove feature that shows only first level projects, it has issues when they are grouped in folders
	bugfix: fix mail rules when applying the trash rule, it was not saving the trashed_by_id when executed by cron
	
	
	Since 3.8.5.11
	-----------------
	feature: Improve query when collecting timeslots for project billing report
	feature: Save gantt config options and use them when rendering gantt
	feature: Show message 'None selected' when no date is selected in Project Billing report
	feature: Add user config option that allows to hide the "Delete All" button in tasks
	bugfix: Apply timezone when searching and formatting dates in project billing report
	bugfix: Increase the max width of the category column in project billing report
	bugfix: Move 'Delete All' button to the right in the task view
	bugfix: fix task timeslots grid to allow right buttons definition in the toolbar
	bugfix: quickbooks integration - cut custom fields if the length is greater than 30 chars
	bugfix: remove hack in pear lib to disable verify_peer options when connecting using ssl/tls
	
	
	Since 3.8.5.10
	-----------------
	feature: Add payment button to invoice view when an external payment link is available for the account
	feature: Add price and cost columns to the tasks list
	feature: modifications to new user account creation to allow the finish sign up step
	feature: Add the invoicing status filter to the actual expenses list
	feature: Add edit button to the time list toolbar
	bugfix: dont show add/edit buttons for users that don't have permissions in invoicing module
	bugfix: Remove flash references
	bugfix: fix expenses widget amounts sum for budgeted expenses
	bugfix: fix time approval installer queruies to avoid errors with duplicated keys
	bugfix: fix timezone usage when dst changes
	
	
	Since 3.8.5.9
	-----------------
	feature: add taxes to actual expenses, and show them in listing and reports
	feature: Modify income module name when it has more subtabs
	feature: Add config options to expenses and income plugins to show/hide the subtabs for each module
	feature: hide budgeted expense data in actual expense form when budgeted expense tab is not enabled, and the same behaviour with invoices and payment receipts
	bugfix: fix invoice payment receitps installer
	
	
	Since 3.8.5.8
	-----------------
	feature: quickbooks integration - if invoice line has user and not matched with any service try to use generic service item 'Hours'
	feature: quickbooks integration - improve inv payment period sync
	bugfix: quickbooks integration - improve version management when synchronizing objects with quickbooks
	bugfix: quickbooks integration - prevent name override
	bugfix: quickbooks integration - fix payments and invoices sync, part 2: invoice number unicity, payments account, void and reopen
	bugfix: when classifying by drag and drop, don't ask to to remove previous or not if the object was not classified
	bugfix: when generating invoice from project or batch invoicing, only use approved timeslots
	bugfix: fix invoice form max width
	bugfix: fix error with workflow permissions when adding actual expense and no status dim is installed
	
	
	Since 3.8.5.7
	-----------------
	bugfix: Fixes to 'Project billing' and 'Project Budget Status' reports
	bugfix: Fix quickbooks plugin update script
	 
	
	Since 3.8.5.6
	-----------------
	feature: Several improvements to 'Project billing' and 'Project Budget Status' reports
	feature: new plugin for Expense rate schedules dimension
	feature: Two way synchronization with Quickbooks for invoices and payments
	feature: Add 'is reimbursable' to product types
	feature: Improve invoices form, allow property groups to be arranged and improve billing info tab

	
	Since 3.8.5.5
	-----------------
	feature: allow payment receipts to have greater amount than due amount
	feature: include balance in payment receipt view and form
	feature: add 'Add payment' feature to invoices and remove mark as paid feature if payment receipts is installed
	feature: move direct url to the properties box in objects view
	bugfix: adjust invoice payments table margin
	bugfix: fix dimension options for payment receitps
	bugfix: dont recalculate invoices with 0 amount when entering to the edition form of a payment
	bugfix: apply correct format fot money amounts inside payments form
	bugfix: performance improvements to tasks getArrayInfo() and more parameters to skip some information
	bugfix: Don't allow user to create unassinged tasks if user can't see other users tasks

	
	Since 3.8.5.4
	-----------------
	feature: new plugin for invoice payment receipts
	feature: several modifications to paemfe plugin reports
	feature: replace old js default prompt with a modal form when reclassifying objects using drag & drop
	feature: Add a config option to specify the invoice footer text when printing
	feature: increase config options text inputs width
	feature: quickbooks integration: add shipping info and match qbo classes with feng dimensions
	feature: new invoice cp for po number autofilled with project po number, modify lump sum generation to include involved task names
	bugfix: Don't show date in last activity column if user never used system
	bugfix: fix notes field misalignment in clients form
	bugfix: fix add_to_members function when removing members of an object
	bugfix: fix some comments and ensure that object is correctly built
	bugfix: fix tasks form subscribers component to use the new permission that checks if user can see other user's tasks
	bugfix: prevent user sharing table recalculation when deleting member
	
	
	Since 3.8.5.3
	-----------------
	bugfix: fix format date function for reports to prevent errors with emtpy or '--' strings
	bugfix: performance improvements in user edition at permissions section
	feature: Add permission "Can see other user's expenses"
	
	
	Since 3.8.5.2
	-----------------
	feature: new plugin to define permissions to property groups in forms and overview
	feature: when user can see only one client or project then select it automatically and hide dimensions panel
	feature: when member selector property is readonly in form, disable breadcrumb clicks
	bugfix: prevent error 500 when preview file is not in upload directory
	bugfix: permissions for invoice actions were not verified correctly when collab customer views an invoice
	bugfix: fix permissions for managed events dimension, they don't have to be mandatory like workspaces
	bugfix: prevent error when labor cat dim is not installed and trying to render the selector at invoice line form
	
	
	Since 3.8.5.1
	-----------------
	feature: Show YTD info in widgets for the whole system
	feature: Add contact config option 'widget_dimensions'
	feature: Increase memory in widgets
	feature: Update css for dimension configs
	bugfix: Don't save timeslot when incorrect year entered
	bugfix: Increase memory limit when listing the timeslots
	bugfix: fix custom reports dimension conditions for contact reports
	bugfix: fix quota available amount verification when adding/editing payment
	bugfix: fix quota selector in advanced expenses plugin, for quota name display
	bugfix: check if member templates plugin is installed in evx widgets before using its classes
	bugfix: remove references to og.systemSound in og.js
	bugfix: fix quickbooks plugin installer
	bugfix: Set contact config option listingContactsBy to 1 via script update
	bugfix: Improve tasks list query when filtering by subscribers
	bugfix: fix read permissions when showing linked objects in comments
	bugfix: prevent not found errors in console with mail polling requests and mail_tr
	bugfix: several fixes to paemfe plugin
	bugfix: don't assume that crpm plugin is always installed to get the projects dimension id
	bugfix: prevent errors when advanced core and status timesheet plugins are not installed
	bugfix: fix overdue_and_upcoming widget syntax error with php tag
	bugfix: Remove description of the task in 'Late and Upcoming tasks' widget
	
	Since 3.8.5.0
	-----------------
	feature: quickbooks integration improvements: match service items with feng users
	feature: use Quickbooks default Terms when saving the invoice
	feature: match Quickbooks custom fields with invoice/project custom properties
	feature: create a Job entity under the client to represent the invoice project when saving the invoice
	feature: modify show comments feature in reports to show them in separate lines below the objects
	bugfix: ensure that superadmins always have workflow permissions
	bugfix: when saving invoice use payment terms cp default value if it is defined
	bugfix: fix invoice print links
	bugfix: when user cant edit permissions the default permissions must be applied
	bugfix: Quickbooks integration - fix invoice number, due date and address
	bugfix: prevent javascript execution when viewing an email
	bugfix: ensure that all calls to getCreatedBy() returns an object before asking for its properties
	
	Since 3.8.5-rc
	-----------------
	feature: Quickbooks Online integration improvements
	feature: only show last year's information in evx widgets when not filtering by anything
	bugfix: add missing parameters to setTimeout function in PEAR/Socket class
	bugfix: fix crpm_types update script that adds the acronym custom property for pms
	
	Since 3.8.5-beta
	-----------------
	feature: add acronym custom property to project managers
	feature: allow invoice to get the client legal name
	feature: filter timesheets report by invoicing status
	feature: improvements to send object mail for api
	feature: allow to change invoice number
	bugfix: add patch to estimated and worked time widget to use only current year's tasks
	bugfix: fix widgets php syntax error when php short tags are not enabled
	bugfix: when adding an user add default workflow permissions over this user to every administrator
	bugfix: Fix 'Folders' widget: Allow to use custom object type names
	
	Since 3.8.4.x
	-----------------
	feature: remove aoac theme from core public folder
	feature: Make widgets work for the whole system
	feature: add missing information to individual task print view
	feature: modifications to evx_edition and demo plugins
	bugfix: fix issue when adding permissions to a member and applying to submembers when the logged user is a superadmin
	bugfix: fix issue with double hour type in time module quick add feature
	bugfix: fix error in notifier when object is null
	
	Since 3.8.4.3
	-----------------
	feature: New bulk action: Mark as -> Mark as void
	feature: use horizontal scroll in invoice list, batch invoicing list and time tab
	bugfix: Don't generate invoice if there is no billed worked time or actual expenses in the project
	bugfix: Budgeted expenses don't need invoicing status
	bugfix: Show the name of every generated invoice in the success message after generation
	bugfix: Show parent hierarchy in labor category select option in forms (#584)
	
	Since 3.8.4.2
	-----------------
	feature: Batch invoicing
	feature: Show labor categeries associated with rate schedules in the forms
	feature: Add project info to 'Activity' and 'Late and upcoming tasks' widgets
	feature: Rename 'project phase' to 'contract phase' in en_us translations
	feature: Add a new dimension 'Job phases'
	feature: Use 'Job phase' instead of 'Project phase' in 'Budget' and 'Invoicing history' reports
	bugfix: fix completed filter when showing tasks in calendar, completed tasks were not showing when no filter was applied
	
	Since 3.8.4.1
	-----------------
	bugfix: fix typo in function member_selector.remove_all_selections
	bugfix: Fix custom report issue with totals when exporting to excel
	bugfix: contact custom reports were not using the config option for the order of name and surname
	bugfix: fix extra hour calculations in advanced services plugin, the day separator interval was not used with correct timezone
	bugfix: fix expenses report group by status
	bugfix: fix infobar hooks http query when trial is new
	bugfix: cp date columns were not applying specific date format defined in reports
	bugfix: fix paemfe plugin function to get receipt images
	bugfix: fix js error that happens sometimes when logging in and leaves blank screen
	bugfix: several obile improvements
	bugfix: fix report header max-height
	bugfix: modify text search inside modules min character restriction to 2 chars
	bugfix: modify text search when using 'like' to use %query%
	bugfix: fix template tasks instantiation order, so subtasks can always find their parent
	
	Since 3.8.4.0
	-----------------
	feature: actual expense form improvements
	feature: invoice template changes to support lump sum
	feature: use invoice templates from tasks and time list when no bulk actions are present
	bugfix: fix bug that recreated the repetitive task with the same dates using template
	bugfix: fix percent complete calculation when reopening a task
	bugfix: fix adv services break hours calculation and fidelis contble report
	bugfix: minor feng1 - feng3 migration fixes
	bugfix: installer fixes
	
	Since 3.8.4-rc3
	-----------------
	bugfix: fix custom reports execution when user does not have the permission to see other user's timeslots
	bugfix: remove bug that prevented saving actual expense with assigned budgeted expense
	
	Since 3.8.4-rc2
	-----------------
	bugfix: fix contact email and phone getArrayInfo function to not assume that the type exists
	bugfix: fix timeslot billing recalculation when voiding an invoice
	bugfix: fix timeslots approval status after voiding an invoice
	bugfix: fix product type filtering query
	bugfix: Remove high risk javascript vulnerability
	bugfix: dont show deprecated status selector in budgeted expenses form
	bugfix: fix unchecked workflow permissions for expenses and invoice generation when no product type is present
	bugfix: fix errors in income and expenses update scripts

	Since 3.8.4-rc
	-----------------
	feature: workflow permissions and bulk actions for actual expenses, analog to timesheet approval workflow
	feature: make invoice bulk action work with invoice templates in expenses and time modules
	feature: Project management reports improvements
	bugfix: fix error when editing actual expense
	bugfix: dont try to update a plugin if not present in db
	
	
	Since 3.8.4-beta2
	-----------------
	feature: new config option to show or not company info when printing report
	feature: Show pending billable info for expenses in the PM report
	feature: Add 'Estimated billable' column to expenses summary in the PM report
	feature: Reorder and rename columns in the PM reports
	bugfix: fix documents filter by only current level and crpm installer/updater to ensure that the config option is present
	bugfix: Fix javascript vulnerabilities; update some js modules
	bugfix: Replace spaceship operator for php5 compatibility
	bugfix: fix report container classes when printing report, so the same css as html report are applied
	bugfix: fix actual expenses list and reports when date is not set
	bugfix: add report conditions and applied filters to the report print header
	bugfix: Use 'report_time_colums_display' config when exporting CSV for Timesheet report
	bugfix: Use 'report_time_colums_display' config option to format time in the Timesheet report
	bugfix: Remove bug that cause errors in the grouped custom reports
	bugfix: Don't calculate estimated and pending when without labor category

	
	Since 3.8.4-beta
	-----------------
	bugfix: dont reload product type data in actual expenses if it didn't change after selecting a budgeted expense
	bugfix: fix budgeted expense selector filters in actual expense form
	bugfix: fix invoice template custom property selector when name has a ','
    bugfix: commit css rules for invoicing status config options section
	
	Since 3.8.4-alpha
	-----------------
	feature: include actual expenses in automatic invoice generation for a project
	feature: Redesign Add/Edit view for actual expenses
	feature: Enforce project phase permissions
	feature: modify actual expenses model and form to support cost and price and auto calculation based in product types
	feature: add the new fields to actual expenses view (new cost and price fields)
	feature: add invoicing status to actual expenses and budgeted expenses
	feature: link actual expenses to invoicing status dimension
	feature: modify config section names and format for timesheet customizations
	bugfix: fix actual expenses listing totals row, format all amount columns with currency
	bugfix: fix mail tracking feature to include the alt attr in the img tag so prevent spam negative score

	Since 3.8.3.x
	-----------------
	feature: invoice templates feature

	Since 3.8.3.2
	-----------------
	bugfix: when exporting timesheets report to csv it was not filling the worked time column
	
	Since 3.8.3.1
	-----------------
	feature: Generate 'Invoicing history' report for whole system
	feature: Add the hierarchy path to members in the custom report group name
	bugfix: when imap server responds that mailbox doesn't exist then remove it from synchronization
	bugfix: fix actual expenses add/edit, it was not saving date correctly
	bugfix: fix custom report conditions for dates and <= operator
	bugfix: Fix the bug in the SQL query that caused inconsistent result in the custom reports for timesheets
	bugfix: use only timeslot's members to calculate its permissions, don't use the related task's members

	Since 3.8.3.1-beta
	-----------------
	feature: new plugin for sharepoint integration, includes a php sdk to interact wiht Sharepoint
	feature: modifications to gesl_remote_actions plugin to generate the project folders structure in a Sharepoint site
	bugfix: added missing spanish langs to expenses2 plugin
	bugfix: fix drag and drop popup when using the check all checkbox in time list
	bugfix: fix report conditions for datetime columns and timezon issues
	bugfix: fix custom report grouping when grouping by folders
	bugfix: fix timezone issue when adding timeslot from quick add
	
	Since 3.8.3.1-alpha2
	-----------------
	feature: add button to recalculate billing in timeslots list
	feature: config option to reclassify a timeslot or not when linking it to a task
	bugfix: fix error when saving timeslot and approval plugin is installed but workflow permissions is not
	bugfix: fix js errors when linking a task to a timeslot and reclssifying timeslot

	Since 3.8.3.1-alpha
	-----------------
	feature: new report for project billing
	feature: add date range to pm report
	
	Since 3.8.3.0
	-----------------
	feature: Improvements in invoice history report
	
	Since 3.8.3-rc2
	-----------------
	bugfix: fix untrash function call in object controller
	
	Since 3.8.3-rc
	-----------------
	bugfix: fix user filter in timeslot form when user does not belong to owner company
	bugfix: fix custom report excel export when last group is shown as columns, each group details for group columns were not included in the result
	bugfix: fix currencies controller plugin name, that was causing an error when including helpers
	bugfix: remove get/set functions from report category model for non exisiting columns
	bugfix: fix performance issue when using advanced dimension intersection reports
	bugfix: fix error when deleting client picture
	bugfix: dont put the remove relation link when the relation is not multiple
	bugfix: add default workflow permissions to admins and managers in time approval plugin
	
	Since 3.8.3-beta2
	-----------------
	bugfix: Fix bug: don't lose price and cost values when editing expense items
	bugfix: round up time to 15 minutes when adding time from the tasks list
	bugfix: fix the error generated when completing a repetitive task and selecting the option to complete the subtasks too
	bugfix: temporarilly hide the print button from the task view's timeslots list
	bugfix: fix client image input, the save button wasn't shown after selecting the picture
	bugfix: fix error when trying to view a budgeted expense
	bugfix: fix placeholder text in member selectors, allow usage of custom member type names
	
	Since 3.8.3-beta
	-----------------
	feature: Billing and cost permissions
	bugfix: prevent browser autocomplete in member selectors
	bugfix: when generating repetitions the first task is duplicated
	bugfix: when generating repetitions the last task is unclassified
	bugfix: when checking days to repeat a task timezone must be used
	bugfix: Always round up time when using chronometer to 15 minutes even if it is a few seconds (less than one minute)
	bugfix: fix js bug when rendering task breadcrumbs in the list
	bugfix: remove company restriction from users filter in time module
	
	Since 3.8.3-alpha
	-----------------
	feature: feature: approval workflow permissions plugin for timeslots
	bugfix: fix upgrade script to check if column exists before adding it
	bugfix: fix js error when product type name has '
	
	Since 3.8.2.x
	-----------------
	bugfix: time_intervals plugin: roundup chronometer, proper estimated minutes options in the task
	bugfix: ensure that permissions are saved before member templates plugin automatically instantiates task templates
	bugfix: separate aoac features from evx_plugin, make a new plugin for aoac features
	bugfix: fix lang for expenses options in settings
	bugfix: dont check plugin permissions in core
	bugfix: allow time approval workflow to do bulk actions when filtering by user
	bugfix: fix border misalignment in the 'Invoicing history' report
	
	Since 3.8.2.0
	-----------------
	bugfix: fix repetitive tasks generation when specifying end date for repetitions, the first one was not being generated
	
	Since 3.8.2-beta
	-----------------
	bugfix: Remove minus from the negative numbers in 'Invoicing history' report
	bugfix: Fix decimals in widgets
	bugfix: add new column to contable report
	bugfix: fix excel export for some custom reports
	bugfix: remove unsupported characters from excel export sheet titles
	bugfix: fix js money formatting to use config options for separators and ensure the sanity of the amount strings before saving them
	
	Since 3.8.2-alpha
	-----------------
	feature: Develop invoicing history report
	feature: Develop timeslot specific system permissions
	feature: allow contable report to filter active/inactive users, and modify column order
	bugfix: Set 'suppliers' dimensions to 'mandatory'
	bugfix: fix file upload error when saving searchable objects table for non txt file
	bugfix: remove some warnings from log
	bugfix: fix title property when exporting to excel
	bugfix: fix pm report intermediate subtotals for estimated and pending time
	bugfix: pm report: include the same summaries in excel as in html
	bugfix: fix drag and drop to labor categories issue introduced after last feature of d&d
	bugfix: prevent error when editing a payment receipt that has a document but it is not in the repo
	bugfix: fix error in custom report conditions sql building function
	
	Since 3.8.1.33
	-----------------
	feature: ask user to reclassify in associated dimensions when reclassifying using drag and drop
	feature: change project management report time and expenses tables order
	bugfix: Fix bug that sent email subject that user is assigned to the new task, when user was only subscribed
	bugfix: Fix add time feature
	bugfix: fix company logo url in notifications manager, it wasn't absolute
	bugfix: fix permissions errors when adding/editing/deleting actual expenses
	bugfix: fix error 500 in custom report conditions when condition is 'is user = false'
	
	Since 3.8.1.32
	-----------------
	feature: allow drag and drop in time and expenses modules
	feature: allow to modify grouped reports group order
	bugfix: modify contable report order to use padron cp
	bugfix: fix date formatting for task dates in timeslots reports
	bugfix: fix custom reports when grouping by date
	bugfix: fix fidelis report start and end time timezone
	
	Since 3.8.1.31
	-----------------
	feature: allow excel report headers to wrap text
	bugfix: several fixes to fidelis's reports and their excel export functions
	bugfix: Show correct user name in the email subject when comment is added
	
	Since 3.8.1.30
	-----------------
	feature: expenses amount inputs formatted with thousand and decimal separators
	bugfix: fix plugin queries to run with mysqli
	bugfix: dont include js flash objects
	bugfix: mail linked objects were repeating some linked objects
	bugfix: fix expenses bug introduced in last version that broke the widgets
	
	Since 3.8.1.29
	-----------------
	feature: set association between projects and clients as not multiple
	feature: Add option to select what is shown as columns in budget report: Subprojecs or Phases
	feature: Develop expenses api
	bugfix: fix money format in expenses list amounts
	bugfix: fix some upgrade scripts to check extension mysqli and not mysql
	bugfix: ensure that no user is trashed when deleting member and its objects
	bugfix: remove hardcoded table prefix from some queries
	bugfix: Don't generate budget report if project is not selected

	Since 3.8.1.28
	-----------------
	bugfix: several fixes in upgrade scripts
	bugfix: fix client creation from api, give the default permissions depending on defined settings
	bugfix: fix multiassignment when subtask name has an enter
	bugfix: fix invoices trash function warnings
	bugfix: allow to change actions column width in mail module
	bugfix: ensure that all dimension associations have their default config options after updating plugins
	
	Since 3.8.1.27
	-----------------
	feature: allow ckeditor to embeed youtube videos in documents
	feature: modify text_to_show_in_trees logic to allow associated dimension members to be shown as member name's prefix
	bugfix: Fix vulnerabilities reported by github
	bugfix: when deleting invoice related timeslot invoicing status must be rolled back 
	bugfix: remove the default_selection checkboxes from the multiple member selector selected members list
	bugfix: fix required custom properties verification
	bugfix: fix attachments popup to include all documents where the user has perissions, no matter if it came from an email, if it is classified where user has permissions it must be listed
	bugfix: fix address custom property inputs
	bugfix: fix product types table collation in the installer
	bugfix: minor fixes in customer and mail controllers

	
	Since 3.8.1.26
	-----------------
	feature: Add subscribers column to reports
	bugfix: Fix workspaces subtypes edition
	bugfix: fix mail deletion when file is not in repository
	bugfix: fix exception management when deleting file revisions
	bugfix: fix multi assignment hook, when editing a task the subtasks classification was overriden
	bugfix: fix expenses trash purge hooks
	bugfix: fix empty trash for administrators
	bugfix: prevent errors in upgrade when setting datetime columns to 0 and sql mode does not allow
	bugfix: fix automatic repetitive task generation to not notify the subscribed users to the generated tasks
	bugfix: Fix overreaching lines in the notifications
	bugfix: Reload the page when repetitive tasks are created
	bugfix: Fix bug in the calculation of repetitive task due date or start date and config option "days range to replicate tasks"
	
	Since 3.8.1.25
	-----------------
	bugfix: fix notifications manager bug, summary was still being sent for people that disabled all triggers for the summary in their user preferences
	bugfix: fix crpm installer
	bugfix: fix Contacts class getEmailAddress method query
	bugfix: Fix bug that halted sending the 'Due date reminder' notification (#514)
	bugfix: added new mail tempate variables: completed_by and completed_on to object_templates and notifications_manager plugins
	bugfix: fix task complete function, it was not using the latest object's data to generate the notification
	
	Since 3.8.1.24
	-----------------
	bugfix: exclude trashed timeslots from Timeslots class functions
	bugfix: fixes to web plugin and http requests
	bugfix: modify upgrade process to update db adapter to mysqli if needed
	bugfix: fix installer with fixed reports
	bugfix: fix core reports list for new reporting system for fixed reports
	bugfix: Show custom property name in the notifications (#506)
	bugfix: Display percent completed, re-classify the timeslot when changing timeslot's task
	bugfix: Update warnings in 'Work progress' widget, show list of linked tasks that missing info
	
	Since 3.8.1.23
	-----------------
	bugfix: fix several installer issues with default values when mysql is too strict
	bugfix: fix some installer issues after fixed reports were reconfigured in the database
	bugfix: fix several installer issues in plugins that were not using mysqli functions
	bugfix: fix automatic task repetitions generation when there are duplicated threads of tasks
	bugfix: show unclassified timeslots when grouped by workspaces
	bugfix: show tasks status, timeslot type and group by options in the timesheets report header
	bugfix: fix bugs in 'Due date reminder' notifications
	bugfix: fix notification when a new task is assigned to the user
	bugfix: consolidate all the changes and additions to the task and notify the user that new task is assigned to
	
	
	Since 3.8.1.22
	-----------------
	bugfix: fix bugs in 'Due date reminder' notifications
	bugfix: fix upgrade script for timesheets report
	bugfix: fix tasks list dimension column names when using custom names for member types
	bugfix: dont check certificate in web plugin wget command
	bugfix: fix error 500 when editing task and template no longer exist
	bugfix: fix timesheets report csv export, it was not showing workspaces column
	bugfix: fix timesheets report when using timeslots type variable
 
	
	Since 3.8.1.21
	-----------------
	bugfix: fix budget report form when expense categories plugin is not installed
	bugfix: close pdf modal form after the file is downloaded
	bugfix: Fix subtasks repetitiveness
	bugfix: fix missing lang internal projects
	bugfix: fix custom reports cp conditions when cp is user or contact
	
	Since 3.8.1.20
	-----------------
	feature: Several improvements in Budget report phase
	feature: add 'monto a rendir' column to payments list
	feature: Integration with new Excel export library PhpSpreadsheet
	feature: set member selectors as single selection in billing definitions
	bugfix: don't use phpexcel primitives directly when arranging totals, can't assume which library is being used
	bugfix: fix budget report pdf export to set the company logo as a public file so pdf lib has no problems to include it
	bugfix: Fix custom report options to format date
	bugfix: Fix css when printing reports made in plugins. Add a parameter to hide the default report header when printing a fixed report
	bugfix: fix adv reports installer to set a def value in one columns
	bugfix: Fix upgrade scripts when some columns not exist
	bugfix: Stop sending move_direction status in notifications

	
	Since 3.8.1.19
	-----------------
	bugfix: change the way that web plugin makes the requests
	bugfix: use a variable to set the page size of member selector components to improve performance
	bugfix: fix the view more tree node in member selector components
	bugfix: in function httprequest use https when no shema is sent
	bugfix: custom reports were not allowing to show contact cps columns
	bugfix: fix duplicated users in users list when they have more than one email address
	bugfix: fix tipoCambio field when sending e-invoice with currency different than uyu
	bugfix: fix get project tasks function for the budget report
	
	Since 3.8.1.18
	-----------------
	feature: New budget report
	feature: make PM report recursive to every level
	feature: support multiple projects in PM report
	feature: increase memory limit for the excel export process
	bugfix: fix timesheets report grouping when using first level tasks only and other subgroups
	bugfix: fix facturalista invoice line name length to be less than 80 chars
	bugfix: fix facturalista glosa field for discounts, max 50 chars
	bugfix: fix financials widget, it was not budgeting the tasks without due date
	bugfix: fix core upgrade script to prevent error when adv_reports is not installed
	bugfix: fix custom report edition, wrong permissions check and error message shown
	bugfix: fix newsletters recipient status lists and sending process
	
	Since 3.8.1.17
	-----------------
	feature: add "Previous tasks" as a new possible column in tasks custom reports
	feature: allow user to edit report name and change report's category
	feature: add column 'Email' to the Settings->Users list
	feature: allow to use multi assignment feature to generate subtasks when editing a task
	bugfix: fix multi assignment feature in tasks.
	bugfix: fix timesheets report alignment and structure
	bugfix: fix installer default datetime values in objects table
	bugfix: format long numbers in the react charts in Y-Axis
	bugfix: fix notification manager bug that sent 2 notification emails when the user is assigned
	bugfix: fix notification manager bug that didn't send notifications when name, classification or description were changed in the document.
	bugfix: keep the previous default behaviour when assigning country to einvoice
	bugfix: fix multiple address component renderer, it was not working correctly in cps, country and type selectors wrong
	
	Since 3.8.1.16
	-----------------
	feature: remember pdf options when exporting reports to pdf
	feature: language improvements for expenses plugin
	bugfix: fix payment receipts date filters
	bugfix: Fix start date and due date in subtasks repetitions
	
	Since 3.8.1.15
	-----------------
	feature: add client number in the destino field for facturalista class
	feature: Unify PM reports
	feature: allow to exclude internal projects in PM report
	feature: allow to group default timesheets report by only the first level tasks
	bugfix: fix PM report project information headers
	bugfix: don't include disabled dimensions in default timesheets report group by options
	
	Since 3.8.1.14
	-----------------
	bugfix: fix trash function, it was not setting trash date
	bugfix: fix tasks list group totals, dont use total_worked_time column, it is not calculated correctly in some cases
	bugfix: fix project management report totals
	feature: add language files for facturalista plugin
	feature: make references mandatory for debit notes
	
	Since 3.8.1.13
	-----------------
	feature: new type of invoice: Debit note. Also supports e-invoice
	bugfix: fix missing lang when trying to delete tickets
	bugfix: add daily and weekly summary records to the notifications manager installer
	bugfix: fix dependencies bug when completing a task and system asks to complete subtasks
	bugfix: minor change to cps included in new projects widget
	
	Since 3.8.1.12
	-----------------
	bugfix: realign and resize misaligned icons
	bugfix: update the presentation of the notification triggers
	bugfix: fix notification summaries generation queries to prevent fatal errors and error management inside the function
	bugfix: fix fatal error when assuming that the result of findOne is always a Contact
	bugfix: fix facturalista hardcoded country when generating e-invoice
	bugfix: fix notifications manager summaries generator
	bugfix: fix string formatting for reports and some special characters
	bugfix: fix error 500 when emtying trash
	
	Since 3.8.1.11
	-----------------
	feature: Add two new columns to the "Project management report"
	feature: Add config options to the general configuration for the time in tasks
	bugfix: fix date filters in pm report
	bugfix: fix night hours calculation in advanced services plugin
	bugfix: fix objective edition
	bugfix: fix code warnings in add/edit objective form and fix description component localization
	bugfix: Fix template task generator
	bugfix: Fix saving cropped picture
	bugfix: fix tasks table default datetime values to prevent query errors for too strict mysql configurations
	bugfix: when completing a repetitive task the new repetition is not loaded in the list
	
	Since 3.8.1.10
	-----------------
	bugfix: fix get next repetitions date function call
	bugfix: Remove width limitation for the breadcrumb buttons
	feature: Notification manager improvements
	
	Since 3.8.1.9
	-----------------
	bugfix: fix invoice generation from tasks and timeslots
	bugfix: fix payment receipt view error 500 when uploaded document is not in the repository
	bugfix: Fix search selector and permission group selector to flip or fit the list position when it goes beyond the screen
	bugfix: Fix permissions issue in payment's quota selector and fix custom lang of payment system permission
	bugfix: improve search for numeric values
	bugfix: add quota amounts to searchable objects
	bugfix: non-admin users add custom reports without permissions and can't see them
	
	Since 3.8.1.8
	-----------------
	feature: allow to configure format for currency amounts (decimal digits, decimal and thousand separators)
	
	Since 3.8.1.7
	-----------------
	bugfix: fix payments search
	bugfix: fix cloning or adding to a template a task with subtype
	bugfix: minor language updates
	
	Since 3.8.1.6
	-----------------
	bugfix: Fix widgets css in the dashboard
	bugfix: cron event to generate repetitive task instances was not enabled by default
	bugfix: role member was not applied to the repetitions after a template is instantiated
	
	Since 3.8.1.5
	-----------------
	feature: Add move direction to the repetitive tasks with non-working days
	feature: improvements to project management reports
	bugfix: fix repetitions issue that duplicates last task
	
	Since 3.8.1.4
	-----------------
	feature: Minor style updates for dashboard widgets
	bugfix: never send tipoCambio=0 in facturalista plugin
	bugfix: fix template instantiation for repertitve tasks when due date depends on a parameter
	
	Since 3.8.1.3
	-----------------
	bugfix: modify quota selector text to show more detailed information
	
	Since 3.8.1.2
	-----------------
	feature: remove date field from payments view, add/edit form and list in a separated plugin
	feature: new report option to show only date value for datetime columns
	bugfix: fix payments totals calculations in old expenses plugin
	bugfix: Fix generated dates in repetitive tasks created by templates
	bugfix: dont show financials and earned vs labor widgets if no context is selected, they may have performance issues with high volume of data
	bugfix: dont autoselect related members after selecting a related member in members add/edit form

	Since 3.8.1.1
	-----------------
	bugfix: assigned user's default role must be assigned when instantiating tasks from template

	Since 3.8.1.0
	-----------------
	feature: UI improvements on widgets
	
	Since 3.8.0.x
	-----------------
	feature: new project and evx widgets
	feature: integration with react
	
	
	Since 3.8.0.16
	-----------------
	feature: config option to use or not the time module's quick add row
	
	Since 3.8.0.15
	-----------------
	feature: new plugin roles_dimension
	
	
	Since 3.8.0.14
	-----------------
	bugfix: evx project widget installation query fixed
	bugfix: minor fixes to project management report
	bugfix: fix number format for expenses getArrayInfo function
	bugfix: fix excel export rounding when number string is too long
	bugfix: fix search query for non-administrators
	
	
	Since 3.8.0.13
	-----------------
	bugfix: fix general search error 500
	bugfix: Fix error message to show when user doesn't have perimssions to add an object in a context.
	bugfix: modify property groups hooks to return data as an array if needed, add project information to project management report
	feature: new expenses progress widget
	
	
	Since 3.8.0.12
	-----------------
	feature: allow project management report to be exported to pdf, excel and csv
	bugfix: fix plugin installer/updater helper query that adds the located_under property to the default group
	
	
	Since 3.8.0.11
	-----------------
	feature: new project reports plugin
	feature: Make minutes input field have bigger width depending on the font style it was cutting the number of minutes
	feature: add alignment option to property groups, to use in widgets and reports
	
	
	Since 3.8.0.10
	-----------------
	bugfix: When classifying an object in the related members of a project, check if it isn’t already classified in a member of the associated dimension, if it is classified in one then don’t reclassify.
	bugfix: fix advanced services timeslots generation, they were not using the task’s start date and that leads to errors in further timeslots hour type calculations
	bugfix: fix the user permissions popup caps when selecting the user from the searchbox
	bugfix: dont generate the next repetition when editing the last repetitive task 
	
	
	Since 3.8.0.9
	-----------------
	bugfix: Tasks list print: When filtering by pending the print view was including completed subtasks
	bugfix: Tasks list print: The print view was not including all tasks in the list
	bugfix: Tasks list print: Print view css was not the same as the tasks list
	bugfix: Tasks list print: In dimension member columns only one member per dimension was printed
	bugfix: Minor fixes in function comments and verifications in array variables before iterating or counting
	bugfix: fix contact edition: when removing all members it wasnt doing anything
	bugfix: fix members add/edit form to include the parent selector when advanced core is installed and member cps plugin is not
	bugfix: set expense item name as the description when name is empty in getArrayInfo function
	bugfix: fix custom property sum operator when making the query for report group totals
	bugfix: fix pdf generation command syntax when defining the command location in config.php
	bugfix: fix isDate helper function to check the date value with the one defined in the user preferences
	
	
	Since 3.8.0.8
	-----------------
	feature: add supplier custom properties to expenses custom reports
	feature: Make 'close' button visible in template variables window
	feature: Make list of templates sorted when user wants to add new task
	bugfix: Fix the repetitive task date generator
	bugfix: fix switch break command in advanced reports hook
	bugfix: minor syntax fixes and function documentation fixes, also removed some unexistent classes usage (legacy from Feng 1.x)
	bugfix: fix listing function when mail plugin is not installed
	bugfix: Fix advanced search query
	bugfix: fix notifications manager hook definition
	bugfix: Alert the user if the date field is empty in the template instantiation
	bugfix: fix templates task workflow section, the template tasks couldn't be added to the workflow
	bugfix: Make repetitive tasks consider working days
	bugfix: Fix filters functionality in tickets module
	
	
	Since 3.8.0.7
	-----------------
	feature: Create new sortable columns in the Contacts, Notes and Tickets
	bugfix: fix custom reports navigation when cp conditions has special characters
	bugfix: Fix template repetitie tasks due date and start date generator
	bugfix: Fix link to the google fonts
	bugfix: fix evx_edition plugin installer
	bugfix: fix user's default hour type selector, it was selecting every member of other dimension components in the form
	bugfix: fix tasks list query, bug introduced in the last release
	
	Since 3.8.0.6
	-----------------
	feature: Added new columns to notes module
	feature: Added new columns to the Contact Module
	feature: dont show the group totals for one specific report (identified by its code in the reports table)
	feature: add 'observaciones' column to one specific custom report (identified by a code defined in the reports table)
	feature: Create new columns 'Created By', 'Created on', 'Updated by', 'Updated on' in the Documents module
	feature: added hook to autoclassify in project's related members after adding timeslot from mobile
	feature: Modifications for compatibility and new features added to the mobile app
	bugfix: fix newsletter contact selector to include all the contact dimensions
	bugfix: remove 'worked time' text from intersection column headers in custom reports
	language: New translations to ru_ru language
	
	Since 3.8.0.5
	-----------------
	bugfix: filter quota selector by active context
	bugfix: fix evx projects widget table header for description column
	
	Since 3.8.0.4
	-----------------
	bugfix: remove old expense reports from new expenses plugin
	bugfix: fix user history permissions to allow other users with higher or equal rank to view it
	feature: add new bulk actions button to time module: put as pending
	feature: new plugin for calculated billing rates

	Since 3.8.0.3
	-----------------
	bugfix: fix timeslots permission validations when editing and deleting
	bugfix: fix overtime report total

	Since 3.8.0.2
	-----------------
	bugfix: fix error when executing overtime report without date, add user filter to overtime report
	bugfix: fix client members depth when removing parent
	
	Since 3.8.0.1
	-----------------
	bugfix: fix generic listing query when filtering by more than one member, was affecting listing totals
	bugfix: hide permission radio buttons if the user role doesnt allow them to be set
	bugfix: task custom reports are not filtering by status=completed
	
	Since 3.8
	-----------------
	feature: new timesheet approval plugin
	bugfix: recalculate task percent completed after adding or deleting timeslot
	bugfix: recalculate task percent completed after deleting timeslot
	
	Since 3.8-rc
	-----------------
	bugfix: modify evx project widget alignments
	bugfix: override member type name with subtype name in member forms
	bugfix: fix worked time calculation after deleting timeslots and reload the worked time summary in tasks view
	bugfix: Fix headers misalignment in custom reports pdf export
	bugfix: Fix excel export in custom reports to include the project number in project's name
	bugfix: fix duplicated project number in projects tree
	
	Since 3.8-beta
	-----------------
	bugfix: fix object prefixes in repetitve tasks, in the repetitions the prefix was being duplicated
	bugfix: fix templates instantiation to avoid forcing today date when user does not enter a date parameter
	bugfix: fix template params instantiation when dates are a non working day
	bugfix: bugfix: dont let template tasks to fall in an invalid week day (according to the enabled weekdays defined in the original task)
	bugfix: fix template objects prop comparison, fix timeslots status after generating invoice
	
	Since 3.8-alpha6
	-----------------
	feature: Added function add_custom_property, quick and simple method to add a single custom property
	feature: custom properties for tasks can now be instantiated by templates
	feature: new method to group timeslots in invoices (by person and hour type)
	feature: The name of the template properties is now calculated on the getTemplateObjectProperties function
	feature: Added a hook call to add custom properties as template properties
	bugfix: fix userbox position in header when clicking it
	bugfix: Return template properties sorted by the property name
	language: langs for new income config options
	
	Since 3.8-alpha5
	-----------------
	bugfix: improve documents widget css for new theme
	bugfix: Fix header and widget margins for new theme
	bugfix: dont override current associated members when changing the parent member
	
	Since 3.8-alpha2
	-----------------
	feature: new overtime_reports plugin
	
	Since 3.8-alpha1
	-----------------
	feature: new theme and css modifications
	feature: modifications to evx widgets
	
	Since 3.7.2.26
	-----------------
	feature: Keep reference of the original timeslot when splitting it by overtime hours dimension
	feature: Improved messages and translations for time input
	bugfix: fix the timeslots form to prevent start date reset if you modify the start date before the worked hours
	bugfix: modify the recalculate_next_days function to make the recalculations 4 weeks forward instead of 1 week
	bugfix: fix custom reports when they are grouped and show some custom properties related to the contacts
	bugfix: fix overtime calculations when paused time > 0
	bugfix: fix quick add row to reload totals row after adding a new timeslot
	bugfix: fix overtime calculations for timeslots starting at 00:00hs
	bugfix: fix get_contact_worked_time function to use the original timeslot id in related timeslots
	bugfix: Make the recalculations by merging the related timeslots and splitting only the original one
	bugfix: Fix the week filters to start on sundays and end on saturdays
	bugfix: After generating new timeslots add all of them to the list
	bugfix: fix several errors in overtime calculations due to timezone usage
	bugfix: Improved UX when entering and editing timeslots
	bugfix: Simplified the code for automatic changes to the time, start dates, and end dates in the form.
	
	Since 3.7.2.25
	-----------------
	bugfix: fix error count when selecting all timeslots and sending them to trash
	bugfix: fix timeslots quick add member selectors when autocompleting related dimensions and hooks usage to set other members
	bugfix: Improvements in the way the start date and start time are calculated and saved
	bugfix: fix automatic generation of repetitions after moving featuro to crpm plugin
	
	Since 3.7.2.24
	-----------------
	bugfix: more fixes in overtime calculations, after deleting timeslots
	bugfix: when deleting member ensure that registries in billing_definition_members are deleted too
	bugfix: fix member selectors when autoselecting related members and they are not in og.dimensions cache
	bugfix: fix overtime calculations to prevent project override
	
	Since 3.7.2.23
	-----------------
	feature: allow to choose pdf page size when exporting custom report to pdf
	
	Since 3.7.2.22
	-----------------
	bugfix: fix timezone usage in timeslots listing date filters
	bugfix: fix column width and font size when exporting custom reports to pdf
	
	Since 3.7.2.21
	-----------------
	bugfix: fix required custom property control when cp type is user and is multiple
	bugfix: fix pdf export in custom reports
	bugfix: fix permissions in timeslots when adding from a task
	
	Since 3.7.2.20
	-----------------
	bugfix: recalculate posterior timeslots when changing one before
	
	Since 3.7.2.19
	-----------------
	bugfix: fix automatic status calculation for projects
	bugfix: remove restriction of quota unicity in context
	
	Since 3.7.2.18
	-----------------
	feature: make configurable the amount of decimal digits in product types and expense totals
	bugfix: serveral fixes in payment receipt reports
	bugfix: fix expenses subtab font size bug
	
	Since 3.7.2.17
	-----------------
	bugfix: several fixes for expense items custom reports
	bugfix: trying to view an expense item (from a report) throws an error
	bugfix: fix js error when searching in left panel trees
	bugfix: use 3 decimal digits in product type amounts

	Since 3.7.2.16
	-----------------
	bugfix: fix overtime calculations to prevent double pay type member assingment
	bugfix: dont show timeslot's billing tab if overtime calculations plugin is active
	bugfix: fix installer sql syntax error
	
	Since 3.7.2.15
	-----------------
	feature: allow to sort member trees by member type before alphabetically
	bugfix: when uploading a new doc and already exists, by default select the option to upload as a new revision
	bugfix: fix permissions tree in users form to include timeslots in permissions query
	bugfix: fix error in contacts report grouped by folder and with or conditions in classification
	
	Since 3.7.2.14
	-----------------
	feature: allow to view the sent emails in newsletter view
	bugfix: verify that sharing table group_id key does not exists before executing the query to add it
	bugfix: fix newsletter sent contacts popup list
	bugfix: fix plugin folder name for additional member permissions plugin
	
	Since 3.7.2.13
	-----------------
	bugfix: fix error in custom reports with advanced billing columns
	bugfix: fix timeslots form start date calculations
	bugfix: prevent trailing/starting white spaces in template object property variable values
	bugfix: fix product types filtering
	bugfix: fix hour_types installer
	bugfix: autoselect project's client in quick-add row when filtering timeslots list by project
	bugfix: fix ignoring labor categories when creating subtasks using multi assignment
	bugfix: fix 7th day rule in overtime calculations after registering 10 overtime hours
	
	Since 3.7.2.12
	-----------------
	bugfix: ignore some properties when checking if only classification has been changed
	bugfix: ignore persons dimension in application log details
	
	Since 3.7.2.11
	-----------------
	feature: config option to ignore some dimensions when applying classification to subtasks
	feature: overtime calculations, classify as overtime the 7th consecutive day of work no matter the amount of hours
	bugfix: fix group name coulumn text when exporting to excel/csv a grouped report without details
	
	Since 3.7.2.10
	-----------------
	bugifx: fix week start/end calculation when adding a timeslot for next week or more in the future
	bugfix: when exporting to excel the time columns were not using the custom format
	bugfix: fix overtime calculations, ignore non-worked timeslots
	bugfix: when splitting timeslots the task id was not copied to the new ones
	
	Since 3.7.2.9
	-----------------
	feature: overtime calculations plugin
	bugfix: fix getting controller by classname in member controller
	bugfix: fix langs for billing category column in timeslot reports
	bugfix: fix timeslots totals query, make it independent of currency if there is only 1 currency in the system
	
	Since 3.7.2.8
	-----------------
	bugfix: fix notifications manager config options saving
	bugdix: dont send email when only description is changed if not selected in config option
	
	Since 3.7.2.7
	-----------------
	feature: when invoicing timeslots classify them in the invoiced status member
	bugfix: dont inherit members from parent task if dimension is not multiple and subtask has already a member of that dimension
	
	Since 3.7.2.6
	-----------------
	bugfix: fix task list filters when filtering by subscribed by user and completed tasks
	bugfix: fix notifications manager config options and user new template for due date reminders
	fearure: make overtime_hours dimension hierarchical
	
	Since 3.7.2.5
	-----------------
	feature: new section in dimension options to define the default value for each dimension
	feature: new user preference to define the dimension that dictates color to show for each object in the calendar
	feature: new plugin that adds the dimension "Overtime hours"
	feature: new plugin that adds the dimension "Billing rates"
	bugfix: several fixes in timesheet status dimension plugin
	bugfix: notification manager fixes in consolidation
	bugfix: dont include unsubscribed contacts when sending newsletters
	
	Since 3.7.2.4
	-----------------
	feature: config option to exclude add,open,close task from consolidation
	
	Since 3.7.2.3
	-----------------
	feature: First version of the "status dimension for timesheets" plugin aka: status_dimension_timesheet
	bugfix: modifications to evx widgets plugin
	bugfix: fix error when adding project phase
	bugfix: fixes in notifications manager
	bugfix: prevent enqueue newsletter emails for persons that are not subscribed to newsletters (using cp subs_newsletter)
	
	Since 3.7.2.2
	-----------------
	feature: default labor category for users in timestlots module
	bugfix: fix timeslots list totals when filtering by more than one member
	bugfix: autopopulate project's associated members when adding timeslots and selecting a project
	bugfix: fix column of unclassified worked time when grouping results by a dimension and showing the group in columns
	bugfix: fix sum of timeslot report custom properties when grouping by user
	bugfix: fix timeslots start time lang
	bugfix: fix email system setup config options saving

	Since 3.7.2.1
	-----------------
	bugfix: fix notification manager when consolidating comments and open/close
	
	Since 3.7.2.0
	-----------------
	feature: add contact cps to report group lines when grouping by contact
	feature: allow to add project cp columns in timeslot reports/Reports
	feature: when grouping timeslot reports by project, put the same name as in the trees (using configuration if present)
	
	Since 3.7.2-rc11
	-----------------
	feature: add invoicing status filter to time tab
	bugfix: fix report condition input for invoicing status
	
	Since 3.7.2-rc10
	-----------------
	bugfix: notifications manager fixes
	
	Since 3.7.2-rc9
	-----------------
	bugfix: notifications manager fixes
	
	Since 3.7.2-rc8
	-----------------
	feature: add contact custom properties to timeslot custom reports
	bugfix: fix expenses plugin pre requisites
	bugfix: Fixed typo
	
	Since 3.7.2-rc7
	-----------------
	bugfix: fix member add/edit when using templates and member subtypes
	bugfix: fix evx projects widget list height
	
	Since 3.7.2-rc6
	-----------------
	bugfix: fix copy task, was not copying members
	bugfix: show all users in calendar filter when not filtering by any member
	bugfix: fix auto heights in evx projects widget

	Since 3.7.2-rc5
	-----------------
	feature: evx_widgets plugin version alpha
	feature: associate member templates with member subtypes
	bugfix: use plural title for projects and clients widgets
	bugfix: fix old billing categories, they were not saving the currency
	
	Since 3.7.2-rc4
	-----------------
	bugfix: fixes in notifications manager

	Since 3.7.2-rc3
	-----------------
	feature: update notification manager
	
	Since 3.7.2-rc2
	-----------------
	bugfix: fix time module date filters
	
	Since 3.7.2-rc
	-----------------
	feature: allow getArrayInfo function to return members data for tasks
	bugfix: allow mobile to show project name with custom properties
	bugfix: list of clients should work for single or divided dimension in mobile.
	bugfix: show custom properties in timeslot lists
	bugfix: group_by_sql wasn't initialized.
	
	Since 3.7.2-beta6
	-----------------
	bugfix: fix listing query for trashed objects
	bugfix: fix permissions for internal collaborators in time module
	bugfix: fix abm of emails in client form
	bugfix: fix text custom property edition when adding zeros before the text and the text can be casted to number
	bugfix: fix height calculation in custom reports
	bugfix: fix additional text in member trees feature when expanding member childs
	
	Since 3.7.2-beta5
	-----------------
	bugfix: fix clients email edition, main email was not modified correctly
	
	Since 3.7.2-beta4
	-----------------
	feature: notifications manager improvements
	
	Since 3.7.2-beta3
	-----------------
	bugfix: dont show disabled dimensions in config option handlers
	feature: notifications manager improvements
	
	Since 3.7.2-alpha5
	-----------------
	bugfix: dont check module permissions automatically when giving permissions in a member
	bugfix: fix object billing calculations after instantiating task templates
	bugfix: fix dimension panel filtering when expanding a project and filtering by any of the associated dimensions
	bugfix: fix user selector for custom properties
	bugfix: Function had a duplicate variable '$ignored' in its parameters.
	bugfix: type was incorrectly set as dimension_object instead of dimension_group
	bugfix: Missing lang for hint
	bugfix: Improved wording for new feature.
	bugfix: Fixed. Misspelled class. Constructor wasn't working.
	bugfix: Fixed. Issues when upgrading a PHP 7 installation. 

	Since 3.7.2-alpha4
	-----------------
	change: Removed the property "status" for Payment Receipt. Replacing with a dimension.
	feature: Showing name of client for tasks in calendar view - in all views (weekly, monthly, daily).
	fix: Warnings for declarations of functions not compatible with parent Class.
	fix: Several small fixes on coding style

	Since 3.7.2-alpha3
	-----------------
	feature: update dimension association between projects and rate schedules to allow selection of related members
	feature: when selecting a member autoselect the related members if the association is marked with 'allows_default_selection'

	Since 3.7.2-alpha2
	-----------------
	bugfix: error 500 when deleting payment receipts
	bugfix: show billable column as yes/no instead of true/false
	
	Since 3.7.2-alpha
	-----------------
	feature: new billable column for expenses and receipts
	feature: system permission to enable adding hours in time tab
	
	Since 3.7.1.x
	-----------------
	feature: new plugin notifications_manager
	feature: modifications to object_templates plugin to work with notifications_manager
	
	Since 3.7.1-rc
	-----------------
	feature: allow to use "and" and "or" conditions in custom reports
	bugfix: fix remembering the option to delete objects when deleting member
	bugfix: when deleting member and not its objects they were deleted anyways
	bugfix: default value of option to delete objects when deleting member should be 'no'
	
	Since 3.7.1-beta
	-----------------
	feature: when deleting member ask user to delete objects or not
	feature: hide payment name in add/edit form only for macro facultad
	bugfix: Mobile was not working after 3.7.0 (PHP 7.0 support). 'Escape' functions 'escape_string' were not valid.
	bugfix: fix member selector hidden input format when adding and removing members from template tasks
	bugfix: Prevents an error when the DB connection is not established.
	bugfix: PHP 7.2 now reserves the word 'object', so it can be used as a class name.
	bugfix: fix getColumnType function for most of the content objects added by plugins
	bugfix: check if member can have parents before rendering the 'located under' component
	bugfix: fix typo in pear/net/socket library
	bugfix: Link to old download page was not working. Updated to new download page.
	
	Since 3.7.1-alpha4
	-----------------
	feature: add pdf support to payment receipt images in add/edit form and receipt view
	feature: show in red the payment receipts that are greater than their expense's cost
	feature: autocomplete payment receipt name using date and expense category
	feature: disable submit button while uploading payment receipt image
	bugfix: when editing expense using the 'add new item' button in the items grid, add a new empty item line
	bugfix: add link to expense in payment receipt view
	bugfix: fix feature that puts client name in calendar objects to also check member hierarchy

	Since 3.7.1-alpha3
	-----------------
	feature: Add "Description" field to payment receipts
	feature: Allow to upload image of the payment receipt
	feature: Allow to add payment receipt from expense view
	feature: Allow to add new item from expense view's items grid
	feature: Add view for payment receipts
	
	Since 3.7.1-alpha2
	-----------------
	feature: when adding/editing expenses improve the way that product types are filtered
	feature: when adding/editing expenses allow product type selection to fill member selectors
	bugfix: Fix view for expenses, including items grid
	bugfix: Improvements in format of payment receipts list view
	bugfix: Set default payment receipt date to today
	bugfix: can't edit payment receipt
	bugfix: payment receipt doesn't save custom properties, subscribers, linked objects and members
	bugfix: In payment receipt add/edit put the expense selector first
	bugfix: Several fixes in installer
	
	Since 3.7.1-alpha
	-----------------
	bugfix: fix member template quick add urls
	
	Since 3.7.0.x
	-----------------
	feature: new expenses module for evx.
	
	Since 3.7.0-beta7
	-----------------
	bugfix: tasks list doesnt stop timeslots in view, when completing
	bugfix: add not minified ext lang file for sv_se
	
	Since 3.7.0-beta6
	-----------------
	bugfix: fix add/edit client form position when hiding contact inputs
	bugfix: ensure that reporting contact config options are inserted in the upgrade script
	bugfix: fix member selectors in quota form
	bugfix: use a timeout when selecting columns to show in tasks list
	feature: initialize associated dimension selectors with active context
	
	Since 3.7.0-beta5
	-----------------
	bugfix: dont use expense column in payments if expenses are not used
	bugfix: fix content object listing query group by
	
	Since 3.7.0-beta4
	-----------------
	feature: allow to specify number of hours in invoice lines when generating invoices automatically from tasks/timeslots
	bugfix: in time tab open ts report in reports tab, to use the css of that tab
	
	Since 3.7.0-beta3
	-----------------
	bugfix: fix member form custom properties alignment
	bugfix: fix missing langs
	bugfix: disable workday cp by default
	
	Since 3.7.0-beta2
	-----------------
	bugfix: cant set or change project parent
	bugfix: cant complete tasks in php7
	
	Since 3.7.0-beta
	-----------------
	bugfix: can't save client custom properties if advanced_core plugin is not activated
	bugfix: can't instantiate templates from tasks list
	
	Since 3.7.0-alpha
	-----------------
	bugfix: override extjs datefield validation function to ensure that correct format is used when typing dates without zeros
	feature: New clients and contacts section in configuration
	feature: Allow to specify if all clients created will have contact information
	feature: Include "Located under" in dimension members properties, to change its order in add/edit form
	
    Since 3.6.3.x
    ----------------
    feature: PHP7 compatibility.
    
    Since 3.6.3.19
    ----------------
    bugfix: fix in breadcrumbs with associated dimensions
    bugfix: required dimension associations were not being checked
	
	Since 3.6.3.18
    ----------------
    bugfix: dont reload tasks list if not needed
    bugfix: improvements in payments list and view with quota
	
	Since 3.6.3.17
    ----------------
	bugfix: improvements in tasks repetition form
	
	Since 3.6.3.16
    ----------------
	bugfix: changes in tasks repetition variables
	
	Since 3.6.3.15
    ----------------
    bugfix: fix member reports conditions by associated dimension
	
	Since 3.6.3.14
    ----------------
    bugfix: fix custom reports totals for time columns when not using usual format
	
	Since 3.6.3.13
    ----------------
	bugfix: fix reports detail lines when grouping by intersection of dimensions and showing groups as columns
	bugfix: set newsletters contacts selector object type to contact
	bugfix: fix timeslot add and current_time variable
	
    Since 3.6.3.12
    ----------------
    bugfix: fix invoice logo width
	
    Since 3.6.3.11
    ----------------
    feature: new quota dropdown selector
    bugfix: disabling tasks notifications not always working
    bugfix: improve tasks list load more groups, do it when less than 20% of the scrollbar is left
    bugfix: fix persons filter in time module when no context is selected
	
    Since 3.6.3.10
    ----------------
    bugfix: several changes in advanced expenses with quota and payments
	
    Since 3.6.3.9
    ----------------
    bugfix: fix task dependency selector in templates
    bugfix: fix member templates when removing member association
	bugfix: dont remove enters in description when adding timeslot
	
    Since 3.6.3.8
    ----------------
    bugfix: performance issue in imap sync when sending to trash in bulk
	
    Since 3.6.3.7
    ----------------
    bugfix: fix grouped reports when grouping by date
	
    Since 3.6.3.6
    ----------------
    bugfix: fix calendar sync configuration
    bugfix: in grouped reports use date format to parse dates when grouping rows by a date property
    bugfix: trim web plugin notiification config connection values to ensure they are parsed correctly
    bugfix: fix time list filters
    bugfix: dont use the attachment name when wrinting to tmp folder only for view
	
    Since 3.6.3.5
    ----------------
    bugfix: improve timeslots add/edit
    bugfix: show description cols with pre-wrap in timeslots lists
	
    Since 3.6.3.4
    ----------------
	bugfix: fix scroll and resize of mail composing view
	bugfix: fix no filter in time module period filter
	
    Since 3.6.3.3
    ----------------
	bugfix: cant delete payments
	bugfix: fix tickets installer
	bugfix: add payment from inside quota does not select quota
	bugfix: new payment menu from insied quota position is wrong
	bugfix: error when executing dimension group reports
	bugfix: fix js error in risks module
	bugfix: fix risks installer

    Since 3.6.3.2
    ----------------
    bugfix: fix customer widget
    bugfix: minor adjustments in invoices module
    bugfix: persist time module filters
    bugfix: fix time module toolbar, panel height and totals row.
    bugfix: allow to delete multiple members in member list
	
    Since 3.6.3.1
    ----------------
    bugfix: escape line detail when editing invoice
    bugfix: use default value as it is in config option for task groups pagination count
    bugfix: several fixes in invoicing module
    bugfix: dont show unknown status in clients widget, dont show status if cp is disabled
    bugfix: filter disabled cps in get cp columns function
    bugfix: fix scroll pagination when scrollbar does not appear
	
    Since 3.6.3-rc7
    ----------------
    bugfix: cannot paste using right click menu in notes.
	
    Since 3.6.3-rc6
    ----------------
    bugfix: error in tasks list when no tasks present
	
    Since 3.6.3-rc5
    ----------------
    bugfix: fix tempalte task repetition when adding.
	
    Since 3.6.3-rc4
    ----------------
    bugfix: improve performance of pop3 mail download
    bugfix: generating next invoice doesn't copy company id
    bugfix: cannot edit repetitive tasks in templates
    bugfix: can't edit client organization
    feature: improve objects_import plugin
	
    Since 3.6.3-rc3
    ----------------
    bugfix: several fixes in tasks list performance
	
    Since 3.6.3-rc2
    ----------------
    bugfix: dont redraw all tasks if already rendered after loading more groups
	
    Since 3.6.3-rc
    ----------------
    bugfix: small fixes to tasks list group pagination.
    bugfix: in custom reports dont disable the toolbar in report parameters view.
	
    Since 3.6.3-beta11
    ----------------
    bugfix: cant classify mails with attachments if attachment has / in the name
	bugfix: error editing workspace if associated content data object does not exist
	bugfix: dont load all tasks when only refreshing tasks list group totals
	bugfix: not adding to sharing table unclassified objects to new users
	bugfix: reorganize custom reports header (conditions block and buttons container)
	bugfix: report and print from clients tab doesnt use the same order
	feature: when custom report data is too narrow, expand it to use all the panel width
	feature: tasks list groups pagination
	
    Since 3.6.3-beta10
    ----------------
	bugfix: Fix error in application logs query when filtering
	bugfix: Add margins to the direct-url container in object views
	bugfix: cant add name in timeslots reports
	bugfix: worked time column showing wrong data
	bugfix: dont break report title in the middle of a word
	bugfix: total estimated cost is not showing
	bugfix: Remove unnecesary paddings in custom report views
	bugfix: Fix custom report content height calculation
	feature: Remove toolbar in custom reports
	feature: Reorganize custom reports buttons
	
    Since 3.6.3-beta9
    ----------------
    bugfix: profile picture crop and upload fixes
    bugfix: cannot edit indicators
    bugfix: stop running timeslots was only affecting timeslots without task
    bugfix: cusotm reports associated dimension columns fixed to prevent duplicates
	
    Since 3.6.3-beta8
    ----------------
    bugfix: fix worked time color in tasks list
    bugfix: reminders dont tell wich date they have as reference
    bugfix: templates with lots of tasks and variables cannot be saved and gets broken if input count is greater than 1000
    bugfix: cannot modify timeslot billing if they are classified in a submember
	
    Since 3.6.3-beta7
    ----------------
    bugfix: dim columns were added twice and this causes an error when saving them in custom reports
    feature: return member id and billing data when requesting event ticket data
	
    Since 3.6.3-beta6
    ----------------
    bugfix: when generating event invoice name put a generic name if the invoice is for more than one event
	feature: disable events registration when using classes workflow
	feature: add new object template parameter: summary_of_all_events
	
    Since 3.6.3-beta5
    ----------------
    bugfix: fix searchable objects installer plugin 
    bugfix: fix config option values table in installer
    bugfix: dont use null as def value of primary keys
    bugfix: fix escape characters for users selector in custom report parameters
    feature: allow login function to receive parameters and be called by api
    feature: include billing emails in request event pricing function
	
    Since 3.6.3-beta4
    ----------------
    bugfix: ensure that invoice is classified in the event of the ticket
    bugfix: cant search members with ampersand
    bugfix: Show archived objects in the trashed objects list
    bugfix: dont show billing tab when editing ts if user doesnt have permissions
    bugfix: undefined function was being used to set billing category id
    bugfix: error redeclaring function when including twice cp table
    bugfix: duplicated tasks are loaded sometimes
    bugfix: check if member with the same member already exists before trying to create a new one
    bugfix: invoice classification fixed in class registration
    bugfix: several modifications to class registration and attendee list
    feature: include list values and list value labels in list cps definition
    feature: config option to ignore custom invoice layout when generating invoice
    
    Since 3.6.3-beta3
    ----------------
    feature: change return value of save_event_tickets_bulk
    feature: split contact address fields in reports
    feature: config option to send emails in background
    feature: email templates
    bugfix: error 500 on events views when event doesnt have end date
    bugfix: add a code for return the name of members when requesting price
    bugfix: fixes in affinity report batch
    bugfix: error in query to update invoice preview file id
    bugfix: in web plugin take the installation name from the filesystem path, not from the url
	
    Since 3.6.3-beta2
    ----------------
    feature: register attendees to multiple events
    feature: online member registration form conditional improvements
    bugfix: problems when sending invoice to billing contact
    bugfix: exclude customer_folder and project_folder from member templates
    bugfix: when sending outbox in cron send for all users with permissions in the account
    bugfix: show comments option was only working for tasks reports
    bugfix: exclude object types of disabled plugins in configuration

    Since 3.6.3-beta1
    ----------------
    bugfix: adding clients from api doesnt save custom properties
    bugfix: custom property filters are not translated in tickets module
    bugfix: fixes in affinity members report
    bugfix: cant save associated members by api
    bugfix: in tasks workflow, copy the linked objects to the next task
    bugfix: when showing comments in reports encode date comment part to to prevent errors in view
    
    Since 3.6.2.X
    ----------------
    feature: Allow to send objects by email (for api use)
    feature: Allow to create clients by api

	Since 3.6.2-beta41
    ----------------
    bugfix: allow MemberChooserTreeLoader to send parameters in post, to avoid making the url too long
    bugfix: fix custom properties add
    feature: evx_edition plugin and login page modifications
    feature: affinity reports plutin

    Since 3.6.2-beta40
    ----------------
    bugfix: fixed report totals for calculated columns
    bugfix: fix obj type hierarchy options query when saving expense
    bugfix: improved drag and drop in mails tab
    bugfix: fix table custom properties in forms and views
    bugfix: missing langs in advanced expenses
    bugfix: add zipcode to the toString function in contact addresses
    bugfix: cant add cps for member types that are not dimension objects
    feature: allow to define plural names in the dimension options section

    Since 3.6.2-beta39
    ----------------
    bugfix: Show Custom Properties with scroll mode
	bugfix: Show bill_billing_rate with currency format in reports
	bugfix: add option to mark invoice as paid in view
	bugfix: member type names fixed in task and report selectors
	bugfix: execute function to ensure that all dim associations have their custom properties after every plugin update
	bugfix: when creating user always set checked by default the option to send notfication
	bugfix: init gantt genid and preferences before response comes
	bugfix: fix verify_peer options when connecting to mail server using tls
	bugfix: company picture form fixed
	bugfix: ignore disabled dimensions in tasks list columns
	bugfix: send and save invoice fixed
	bugfix: when cp is special and is defined as id@text try to get the lang from the text part

    Since 3.6.2-beta38
    ----------------
    feature: separate labor categories dimension (hour types) to other plugin
    feature: allow to configure if the phone types are shown in reports
    bugfix: fix misspelled langs in custom property admin
    

    Since 3.6.2-beta37
    ----------------
    bugfix: Custom reports when ordering by due date and due date is the same the order is different when viewing report and when exporting to pdf
    bugfix: Custom reports export to pdf, second page header overlaps with the data rows
    bugfix: Fixed status column for projects in custom reports
    bugfix: wrong langs in projects and missing langs in task reports with show comments
    bugfix: dont draw ignored conditions info when executing custom reports
    bugfix: remove duplicated models that were in the views directory

    Since 3.6.2-beta36
    ----------------
    bugfix: reload current panel was not called after adding an invoice
    bugfix: contable report must only include users with padron
    bugfix: automatic timeslot generation must not classify in common hour type if already classified in holiday hour type.
    feature: modify new event registration to allow free registrations

    Since 3.6.2-beta35
    ----------------
    bugfix: filter member list by context
    feature: fixed report -> hours by user and intersection of dimensions from advance services plugin

    Since 3.6.2-beta34
    ----------------
    bugfix: member list order by cp
    bugfix: report ignore condition pagination
    bugfix: show more on member list group is not working

    Since 3.6.2-beta33
    ----------------
    bugfix: mail parser encoding error

    Since 3.6.2-beta32
    ----------------
    bugfix: fix searchable objects for invoices
    bugfix: put sent status as invoice sent for confirmed invoices

    Since 3.6.2-beta31
    ----------------
    feature: new project phases dimension
    bugfix: cron error with empty dates on cron events
    bugfix: task workflow not working

    Since 3.6.2-beta30
    ----------------
    bugfix: reports are displayed collapsed
    bugfix: include the mail that pays online when sending invoice


    Since 3.6.2-beta29
    ----------------
    bugfix: invoice sent status filter
    bugfix: report columns order
    bugfix: When sending invoices if to is sent using GET, check if it is an array before adding to the mail paramters

	Since 3.6.2-beta28
    ----------------
    bugfix: report columns order
    feature: change sent status to a list of values and update the invoice preview

    Since 3.6.2-beta27
    ----------------
    bugfix: search box on user list not working
    bugfix: pdf reports are collapsed
    bugfix: contact billing info on invoices

    Since 3.6.2-beta26
    ----------------
    bugfix: do not check permissions when classifying contacts from member custom property
    bugfix: check if is a valid datetime value before using the object

	Since 3.6.2-beta25
    ----------------
    bugfix: mobile not working, missing api view
    bugfix: Automatic timeslots must not check permissions when being classified
    bugfix: lang Cuentaes to Cuentas

    Since 3.6.2-beta24
    ----------------
    bugfix: add contact emails to searchable objects
    bugfix: fixes in context widget
    bugfix: do not check permissions when classifying contacts from member custom property
    bugfix: missing lang report category
    bugfix: error 500 in total worked time report
    bugfix: company billing info on invoices
    bugfix: break line on comment column

	Since 3.6.2-beta23
    ----------------
    bugfix: force add member associations to searchable objects

    Since 3.6.2-beta22
    ----------------
    feature: add member associations to searchable objects
    bugfix: billing info properties on event registration
    bugfix: prevent multiple timeslots running
    performance: performance improvements on permissions

	Since 3.6.2-beta21
    ----------------
    bugfix: invoices: migrate payment method columns to custom properties

	Since 3.6.2-beta20
    ----------------
    feature: Allow to select properties from selected contact on a custom property (for members)
    feature: new rate schedules dimension, associated to projects
    feature: allow to select list custom properties to display on contact selectors
    feature: show comments on report on a separated column
    bugfix: prevent blank screen on mail view
    bugfix: error 500 on member reports
    bugfix: get user by email function not working properly
    bugfix: return websites for each contact on the api
    bugfix: subtask display fails after expand parent task
    bugfix: report categories are filtered by permissions

    Since 3.6.2-beta19
    ----------------
	feature: SAML Single Sign On plugin.
	bugfix: invoice preview does not use custom invoice template.

    Since 3.6.2-beta18
    ----------------
    bugfix: breadcrumbs from related dimensions are not displayed on object lists
    bugfix: max amount of tickets per event calculation
    feature: custom reports, allow to define conditions using associated dimension on member reports

    Since 3.6.2-beta17
    ----------------
    bugfix: allow to change sent status of invoices on edit form
    bugfix: check multiple contacts with the same email configuration before creating new members automatically
    bugfix: wrong ticket type for non-members on event registration
    bugfix: totals on reports
    feature: properties groups for content objects view
    feature: improve contact billing information tab on invoices
    feature: display all members subtypes when creating new member from dimension tree
    feature: max amount of tickets per event

    Since 3.6.2-beta16
    ----------------
    feature: show contact columns on client reports
    feature: contact selector on "billing to" tab on invoices form
    bugfix: wrong total on new event registration
    bugfix: remove sent status from invoices and add other property for that

    Since 3.6.2-beta15
    ----------------
    bugfix: notification config override by hook
    bugfix: no space between imploded address fields when printing invoice
    bugfix: bug when saving subtype dependencies, duplicated row in table
    bugfix: fix report_category_id column, it was added as a tinyint
    bugfix: fix managed events to allow them to have empty date/time
    feature: allow more than one contact with the same email
    feature: was added the ticket type custom properties to event registration

    Since 3.6.2-beta14
    ----------------
    feature: multiple email templates for different actions
    feature: classify invoices on managed event related account
    bigfix: custom properties on object subtypes not working

	Since 3.6.2-beta13
    ----------------
    bugifx: error 500 when ticket type has been deleted
    bugifx: invoice number was not generated when creating new repetition or from new event registration
    bugfix: show date and time on field "created on" invoices

    Since 3.6.2-beta12
    ----------------
    bugfix: show date and time on field "created on" invoices
    bugfix: show date and time on field "created on" event tickets
    bugfix: secondary dimensions must filter primary dimensions
    bugfix: don't change invocie status to sent if is paid.
    security: security issues sql injections and xss

	Since 3.6.2-beta11
    ----------------
    bugfix: replay to all not working
    security: remove public/assets/javascript/ckeditor/ck_upload_handler.php
    security: security issues in api controller
    feature: allow to assign invoice number in non-pending status
    feature: new bootstrap style for members tree on "new event registration"

    Since 3.6.2-beta10
    ----------------
    bugfix: don't show mail rules recomendation configuration not working
    bugfix: managed events, filter only by member the git contact selector on "new event registration"

    Since 3.6.2-beta9
    ----------------
    feature: classify event invoices in a configurable member of the accounts dimension
    bugfix: new event registration contact selector scroll

    Since 3.6.2-beta8
    ----------------
    bugfix: error adding attendees
    bugfix: member status on members widget
    feature: generate report button on attendees tab

    Since 3.6.2-beta7
    ----------------
    bugfix: member tree selector performance

    Since 3.6.2-beta6
    ----------------
    bugfix: managed events, ticket type name and desc must be escaped before sending data to view
    bugfix: only group by o.id if query is for content_objects

    Since 3.6.2-beta5
    ----------------
    bugfix: SWIFT_DISABLE_VERIFYPEER_SOCKET_OPTION not working
    bugfix: managed events, attendees classification
    bugfix: fix custom properties view on members
    bugfix: don't use timezone in getObjectData if the object doesn't have a timezone
    bugfix: don't filter related dimensions on members tree
    bugfix: totals on reports and objects lists

	Since 3.6.2-beta4
    ----------------
    bugfix: class roster attendee report

    Since 3.6.2-beta3
    ----------------
    feature: class roster attendee report

	Since 3.6.2-beta2
    ----------------
    bugfix: error 500 on managed events tab
    feature: managed events, new contact selector on event registration process to select attendees
    feature: contact selector select wich properties display
    feature: advanced billing, new columns on task report "Estimated profit margin" and "Expected profit %"
    feature: member pricing on ticket types

    Since 3.6.2-beta
    ----------------
    bugfix: advance report installer error
    bugfix: fixes on Billing rate and Cost rate report
    bugfix: error 500 on reports when income plugin is not installed
    bugfix: reports column names break word
    bugfix: totals on reports
    bugfix: contact custom property combo langs
    bugfix: newsletter recipients selector
    feature: was added contact cps to attendees panel

    Since 3.6.1-rc
    ----------------
    feature: advance billing on tasks (estimated cost and estimated price)
    feature: billing information on reports
    feature: copy members function
    feature: extend contact custom property selector to allow selecting unclassified contacts

    Since 3.6.1-beta4
    ----------------
    bugfix: report column title words break
    bugfix: can't create timeslots for yesterday

    Since 3.6.1-beta3
    ----------------
    bugfix: send invoice doesn't refresh status in pdf

    Since 3.6.1-beta2
    ----------------
    feature: allow custom invoice mail template for different installations
    bugfix: invoices generated in background are not saving the subtype id
    bugfix: changing event subtype loses dimension associations

	Since 3.6.1-beta
    ----------------
    performance: on sharing table calculation after the user was being edited
    bugfix: move event invoice automatic generated name to event_tickets plugin
    bugfix: financial accunts has wrong table specified in model
    bugfix: new event registration member selector fix
    bugfix: managed event start-end fields fix on submembers

    Since 3.6.0-rc1
    ----------------
    feature: contact custom properties now you can add contacts from the combo
    feature: timeslots now you can select the related task
    feature: filter events by subtype on listing function
    feature: objectives description use ckeditor
    feature: member templates for all member types
    feature: send invoice by email
    feature: stop previous timeslot configuration

    Since 3.6.0-rc
    ----------------
    bugfix: error 500 on templates
    bugfix: advance reports, report categories not working
    bugfix: export reports to pdf
    bugfix: apply permissions to all sub-members on the member edit view.

    Since 3.6.0-beta.3
    ----------------
    bugfix: report list not working
    bugfix: automatic calculation of worked time on add time modal
    bugfix: time list add no filter by date option
    bugfix: task list tasks displayed twice
    bugfis: totals on grouped reports

    Since 3.6.0-beta.2
    ----------------
    bugfix: fix upgrade problem to version 3.5.3 with index in table custom_property_values. 
    
    Since 3.6.0-beta.1
    ----------------
    bugfix: invoices query failed with message 'Unknown column 'external_id' in 'field list'
    bugfix: report list not working if advance report is installed


    Since 3.6.0-beta
    ----------------
    feature: new time form


	Since 3.5.3
    ----------------
    feature: add members in object notifications subject
    feature: config generate next invoice repetition when printing.
    feature: invoice Payment Period
    feature: new linked object view
    feature: add search input to users list
    feature: fix report headers
    feature: time-entries improvements
    feature: add a new condition unclassified to reports configuration
    feature: new feature on list of report, now you can create categories for reports
    feature: new function to create contact from name and mail
    feature: added bootstrap for internal use
    feature: managed eventes plugin
    bugfix: main tabs bar resize
    bugfix: resize was added to columns assigned_to and assigned_by in the task panel
    bugfix: several missing langs
    bugfix: subtask render
    bugfix: query error when ordering tasks list by assigned to
    bugfix: error 500 fixed when listing customers in api
    bugfix: missing useHelper in contact controller function
    bugfix: modify get_public_file function to receive file id as an optional parameter
    bugfix: modify image cp value when requested using raw_data=true to return the original json value of the db
    bugfix: fix tasks list group totals when filtering by more than one dimension
    bugfix: cutom reports dont put empty th if no details are shown, icon column is not rendered
    bugfix: custom reports without details misalignments
    bugfix: fix config option handler for members to allow multiple selection
    bugfix: exclude archived members when giving automatic permissions to new users
    bugfix: in context widget dont use features of inactive plugins
    bugfix: fix broken billing tab in timeslot edit
    bugfix: cant generate first task repetition batch
    bugfix: problem of padding in button in dashboard
    bugfix: task list fix for edge cases (yesterday or tomorrow also changing the week, etc), new weeks (Mon-Sun instead of Sun-Sat)
    bugfix: add generic actions Time tab
    bugfix: fix widget expenses
    bugfix: new sharing table logic
    bugfix: time reports missing task column status
    bugfix: send mail directly when clicking send mail
    bugfix: task list filter status today.
    bugfix: mail account signature change fail
    bugfix: When edit a member if the name contains & it shows &amp
    bugfix: list cp rendered with errors.
    bugfix: template task date variable + minutes not working properly
    bugfix: invoice report expiration date timezone error
    bugfix: calendar view don't displays tasks for guest users
    performance: listing order
    performance: sharing table

    plugin advanced_core:
    feature: ticket new filter by custom property
    bugfix: Fix date format on report headers
    bugfix: object list function broken by hook custom_properties_filter_get_objects_list_extra_conditions
    bugfix: Call to a member function getFixedColumnValue on null

    plugin advanced_reports:
    feature: custom report group by intersection: allow to define specific members to intersect

    plugin advanced_services:
    bugfix: Hour type changes
    bugfix: when generating first set of repetions, dont continue after repeat_end date if it is specified
    bugfix: add transactions for generate x days tasks cron function

    plugin crpm:
    bugfix: dont create invoice customer if no customer data received

    plugin event_tickets:
    feature: new event management functions

    plugin income:
    feature: invoice send via email
    feature: new config option to make client mandatory or not in invoices
    feature: address in print view, now show the billing address
    bugfix: in print view of invoice calling to worng variable for client zip code
    bugfix: fixed message when delete a income
    bugfix: image on invoice whe generating pdf
    bugfix: error 500 when trying to delete invoice from view

    plugin newsletters:
    feature: use "To" field for each email when configuration has Persons per email: 1


	Since 3.5.2.X
    ----------------
    feature: member custom properties groups on member information widget
    feature: use property groups for member custom properties in member add/edit forms
    feature: property groups -> add default group and put all cps in that group
    feature: show previous and next invoice if they exist when viewing an invoice
    feature: text filter input in invoices module
    feature: new managed events plugin
    feature: new event tickets plugin: event tickets abm and attendees widget
    feature: modal member add/edit
    feature: config options to disable the popup dialogs when archiving or trahsing objects
    feature: allow to set a member template to an existing member
    bugfix: transform users widget in contacts widget
    bugfix: default payments report includes trashed payments
    bugfix: invoice line with 0 unit value and total>0 does not sum in general totals
    bugfix: cant link object to invoices that are not pending
    bugfix: dont show generate next invoice link if it is already generated
    bugfix: only the last invoice of the repetition should be shown as repetitive in the invoices list
    bugfix: conditional tasks new bool cp values support
    bugfix: cant generate next invoice if user does not have the can_edit_confirmed_invoices permissions and the invoice is printed
    bugfix: MAIL SIGNATURE font size 24px by default
    bugfix: invoices when adding a new line use the same currency selected for the invoice
    bugfix: dont show member history tab if member has no history


	Since 3.5.1.X
	----------------
	feature: show previous and next invoice if they exist when viewing an invoice
	feature: text filter input in invoices module
	bugfix: dont show generate next invoice link if it is already generated
	bugfix: only the last invoice of the repetition should be shown as repetitive in the invoices list

	
	Since 3.5.1.5
	----------------
	feature: when grouping reports allow to hide the total element count of each group.
	feature: allow expenses list to have horizontal scroll.
	feature: add logic to filter expenses by quarters in expenses widget.
	feature: expenses widget new configuration add filters.
	feature: allow boolean custom properties with undefined values.
	bugfix: Filter is remove on change page. Feature - Send file by email without mail plugin.
	bugfix: escape imap folder names when making queries to the db
	

	Since 3.5.1.4
	----------------
	feature: always show payment history in payment view
	feature: expenses temporal status
	feature: new config option type: date range
	feature: new config option type: general list
	feature: new column balance in advanced expenses lists and reports
	feature: Menu show for tasks is working as email accounts filter, with timeout for each click 
	feature: Added custom configuration for user for set the font-size of emails
	bugfix: imap sync, when removing from folder if we dont have the mail uid => query it to the server and perform the removal
	bugfix: in objects view, the breadcrumbs shows always one workspace in all associated dimensions
	bugfix: allowed users to assign in task controller does not return the same when called from the reasign users popup
	bugfix: js error in timeslot add when drawing user combo and logged user doesn't have "can_manage_time"
	bugfix: Change value "Serie" to plural for keys "series" and "field Object series"
	
	Since 3.5.1.3
	----------------
	feature: improve combobox on report conditions
	feature: system config option disable notifications for object type
	feature: paste email list from excel on mails
	bugfix: prevent widgets links to be opened on a new tab
	bugfix: add delete log when deleting mails
	bugfix: enabled spellcheck in description of add task

	Since 3.5.1.2
	----------------
	bugfix: Expense totals fixed when ordering by a dimension column.
	feature: imap sync plugins.
	
	
	Since 3.5.1.1
	----------------
	feature: invoicing - config option to make final consumer mandatory
	fetaure: invoicing - when changing final consumer value change company id/client document label
	feature: invoicing - when changing invoice currency to a non default one, show exchange rate input
	bugfix: invoicing,facturalista - exchange rate is not sent to DGI when currency is not UYU
	bugfix: display list custom properties in objects view fixed
	bugfix: missing langs in project widget when projects dimension is disabled
	bugfix: dont include timeslots of deleted tasks in reports
	bugfix: adv.services: sometimes night hours are generated in the night start instead of the timeslot start
	performance: tasks list and task view when system has lots of users
	
	Since 3.5.1
	----------------
	bugfix: cant add tasks with advanced_services and  not using time in date pickers.
	bugfix: dont filter files by their associated mail if they are classified.
	bugfix: dont set the font size in ckeditor's contents.css body rule.
	feature: imap synchronization plugin (alpha).
	feature: allow to reclassify mail from the list.
	
	Since 3.5.1-rc
	----------------
	feature: objects import tool
	feature: allow to type in user combo box in timeslots add/edit
	feature: plugin advanced_mail_imap_folders - new dimension for imap folders
	bugfix: permissions, users can't see documents if they can't see the mail related.
	bugfix: check that class exists before executing "eval()"
	bugfix: assigned to notifications are sent to resource users
	
	
	Since 3.5.1-beta4
	----------------
	feature: search input in tickets module.
	feature: config option to exclude associated dimensions from general breadcrumbs.
	bugfix: dont show associated dimension's member history widget.
	bugfix: unescaped names in time module.
	bugfix: task asignee notification on templates.
	bugfix: in object view the custom properties repeats last value if is empty.
	
	Since 3.5.1-beta3
	----------------
	feature: invoicing module: allow to set client address and city fields as mandatory when adding an invoice.
	bugfix: some dimensions members are not selected when clicked in breadcrumbs.
	bugfix: if dimension is hidden the members are not selected when clicked in breadcrumbs.
	bugfix: the contact associated to the client is not classified in the client's related members.
	bugfix: breadcrumbs are not showing the members of the dimensions that have its selector disabled and they are autoclassified.
	bugfix: template tasks subscribers and assign to permissions.
	bugfix: Double quotes are not correctly escaped in custom properties description.
	bugfix: member templates: ensure that new member is in context before rendering the task template paramters form.
	bugfix: timeslot quick add does not show all users with read permissions on timeslots in the user selector.
	bugfix: when assigning client permissions deleted user permission groups appears in selector.
	bugfix: when copying expenses the payments are not copied.
	feature: advanced_services: allow to define works shift days as dat off.
	feature: advanced_services: config option to define the hour type member for days off.
	feature: action to generate first repetitive instances for tasks.
	feature: users of type "resource" (only to be assignees of tasks and timeslots)
	

	Since 3.5.1-beta2
	----------------
	bugfix: attachments inside other attachments cannot be downloaded sometimes.
	bugfix: user token is not removed when disabling user.
	bugfix: in listings when adding a custom property as a column and is not visible by default the system does not remember the users' choice.
	feature: several improvements in grouped custom reports.
	advanced_reports: conditions for groups (only for contact_id and assigned_to_contact_id)
	advanced reports: when grouping by person, allow to add emtpy groups and where to put them (beggining or end)
	advanced_reports: new condition to filter by classified in or not classified in the selected members
	bugfix: tasks list group by person filters different than assigned to, are not shown when loading the list.
	feature: allow to disable custom report parameter when running the report
	bugfix: advanced_reports: timeslot totals are not correct
	bugfix: error when filtering tasks list by milestone.
	bugfuix: report group by date columns was not applying timezone.
	bugfix: when printing/exporting grouped custom reports only the current page is shown.
	performace: limit mails search critera with a minimum of 3 characters.
	feature: add calculated columns to custom reports (for now only 'status' in tasks reports)
	
	Since 3.5.1-beta
	----------------
	feature: start timeslot from without task.
	feature: allow to specify end date and time when adding a timeslot.
	feature: task list group by member type.
	feature: allow to disable custom report parameter when running the report.
	advanced reports: new group by: dimension intersection.
	bugfix: group order in tasks workflow definition.
    bugfix: not all birthdays were shown in calendar's full week view.
    bugfix: advanced_reports: timeslot totals are not correct
    bugfix: report group by date columns was not applying timezone.
    bugfix: tasks list group by person filters different than assigned to, are not shown when loading the list.
	bugfix: error when filtering tasks list by milestone.
    performance: mail search.
	
	
	Since 3.5.0.X
	----------------
	feature: new timeslot module.
	feature: new timeslot list in tasks view.
	feature: allow to specify paused time when adding a timeslot.
	bugfix: tasks that dont use time are not shown correctly in calendar
	
	
	Since 3.5.0.9
	----------------
	feature: make hierarchical all dimensions in crpm_types plugin.
	feature: member tool to change its dimension.
	
	
	Since 3.5.0.8
	----------------
	feature: advanced reports: new condition type to check the availability of users in contact custom reports.
	feature: advanced reports: grouped timeslots report.
	feature: advanced services: render time amount config options as hour:minutes.
	feature: templates: add fixed time when assigning vairable "date of task creation".
	bugfix: advanced billing categories list does not filter by context.
	bugfix: template tasks does not copy the repetition options added in plugins to instantiated tasks.
	bugfix: repetitive tasks generated from templates are not chained correctly with the rest of the repetitions of the same instantiation.
	
	
	Since 3.5.0.7
	----------------
	bugfix: prevent use of browser cache when downloading files.
	bugfix: when completing a task and the wokflow changes the project/client status the member status history is not saved.
	
	Since 3.5.0.6
	----------------
	bugfix: phone number form component shares variable with other phone components and causes malfunction when adding new phones in contacts.
	bugfix: breadcrumbs displayed near name on object lists.
	bugfix: assigned to logged user by default when creating task from email.
	bugfix: when editing timeslot starting at 12:00 am and depending in the timezone of the user, sometimes it adds 24 hours.
	feature: user preference to show the inactive users or not in the contacts list.

	
	Since 3.5.0.5
	----------------
	bugfix: permissions error when trying to view a document associated to an email.
	bugfix: when instantiating templates the permissions are not checked for the assigned person.
	
	Since 3.5.0.4
    ----------------
	bugfix: general search is checking permissions for super administrators.
    bugfix: when trying to complete task with uncompleted dependencies the notification is sent anyways.
    bugfix: object picker filters after removing the filter (continues to use the active context)
    bugfix: currency in total tasks time time report not correct
    feature: add invoice description to the preview.
    feature: mail account option to specify if the ssl certificate has to be validated
	feature: pear/net/socket connection function updated to use stream_socket_client() if needed

	Since 3.5.0.3
    ----------------
    bugfix: list custom property values are not shown in object view.
    bugfix: custom properties config to hide in listings is not working. 
	bugfix: Contact report mysql error when filtering by phone or webpage
	
	Since 3.5.0.2
	----------------
	bugfix: don't check permissions when auto classifying instantaited tasks from templates
	feature: in timeslot form optional start time field
	feature: modal form to add worked hours in tasks
	featere: config option to choose if show the name or picture of the assigned person
	
	Since 3.5.0.1
	----------------
	bugfix: template instantiation, text in variable property editor is always instantiated in lowercase
	bugfix: when tying to classify in a member where you don't have permissions the system doesn't tell you that the object was not classfied.
	bugfix: when you press 'check mail' it tells you an email has been received but it doesn't show up in your mailbox. You have to refresh the mailbox.
	
	Since 3.5.0.0
	----------------
	bugfix: disabling clients and projects lists deactivates the plugin
	bugfix: mime parser does not parse correclty pdf sent as application/octet-stream

	Since 3.5-beta
    ----------------
	bugfix: ensure that the last_uid_in_folder is updated correctly always.
	bugfix: spellcheck on mail ckeditor.
	bugfix: mail paging toolbar is disabled after search.
	bugfix: js error when custom dimension names have single quotes.
	bugfix: template tasks instantiation: conditional tasks with subtasks does not create the subtasks correctly.
	bugfix: when email attachments are parsed as "Related" they are not included when forwarding.
	bugfix: when email is classified only in dimensions that doesn't define permissions and user is not the mail account owner, the email cannot be reclassified.
	bugfix: object_id is not saved in sent_notifications table.
	feature: advanced services: config option to check the minimum percentage of the work shift that must be worked to allow night hours classification
	feature: advanced services: work shift break configuration and usage when calculating automatic timeslots.

	Since 3.5-alpha
	----------------
	feature: separate timeslots classification from its task
	bugfix: click in mail subject, from, to and open link in new tab doesn't open the email.
	bugfix: when downloading image it is an inline image of another document or an email attachment then check the permissions of the container doc or email.
	
	Since 3.4.4.X
	----------------
	feature: advanced reports allow to include comments below each object line.
	feature: member relations history, tab in edition and widget.
	feature: put client/project/workspaces/tags lists in a fixed tab.
	feature: separate timeslots classification from its task.
	feature: timeslot custom reports new column "paused time".
	feature: group reports by date does: put the dates of the same day in the same group
	feature: advanced services: repetitive tasks by fixed days
	feature: advanced services: generate first repetitive instnaces when creating a repetitive task.
	feature: advanced services: config option to define the minimum amount of night hours to classify the timeslots
	feature: advanced services: night hours administration section, work shifts administration section, contact work shifts assignation
	feature: advanced services: new member type - station in crpm dim.
	feature: advanced services: allow custom reports to group by hour types and service types and show these dims as columns
	feature: advanced services: automatic split of timeslots by hour types.
	feature: advanced services: config options for normal, extra and holiday hours
	feature: advanced billing: allow to specify more than one member for each billing category dimensions.
	feature: advanced billing: modifications for timeslot reports and generation.
	feature: advanced billing: billing cat with conflict timeslots in a separate section and allow to make custom reports.
	feature: custom report groups ordered alphabetically
	feature: set color to mails panel filter buttons when filtering mail listing.
	feature: set color to current mail folder in mail listing.
	feature: adv. mail: allow to select more than one account in the mail account filter menu.
	feature: add/edit timeslot reports: separate task and timeslot columns
	feature: css improved in grouped reports
	feature: new redundant column "worked_time" in timeslots
	feature: config handler to select members
	feature: marketing channels dimension.
	feature: config option to allow or not multiple lines in invoice line description.
	
	language: nl_nl fixed for "first name" and "surname".
	language: missing langs in tickets module.
	
	bugfix: when email type is text/calendar the ical file is not parsed.
	bugfix: editors line breaks fixed.
	bugfix: reload all tasks modified when editing a task and affecting other instantiated repetitions.
	bugfix: member listing group by "located under" column fixed
	bugfix: sometimes associated member selectors are filtered by current context.
	bugfix: timeslot reports replicates task columns.
	bugfix: reporting timeslots - if ts has no name or desc, use associated task name
	bugfix: reporting - group by three criterias, in the third sometimes generates separated groups for timeslots in the same group.
	bugfix: sometimes click on email opens it in a new tab.
	bugfix: contacts search does not find if searching by name and surname together
	bugfix: click in mail subject, from, to and open link in new tab doesn't open the email
	bugfix: in gantt if a task is in more than one group it is displayed only once
	
	
	Since 3.4.4.63
	----------------
	bugfix: mail rules that forward text mails does the forwarding as html.
	
	Since 3.4.4.62
	----------------
	bugfix: sent emails imap synchronization fixed.
	
	Since 3.4.4.61
	----------------
	bugfix: gantt task dependencies not rendered.
	bugfix: ensure that the last_uid_in_folder is updated correctly always
	bugfix: delete mails from server fixes.
	
	Since 3.4.4.60
	----------------
	bugfix: when email attachments are parsed as "Related" they are not included when forwarding.
	bugfix: mysql scape on mail list query.
	bugfix: prevent sending e-invoice twice.
	
	Since 3.4.4.59
	----------------
	bugfix: templates with tasks and subtasks that are generated through conditional actions does not generate the subtasks correctly.
	
	Since 3.4.4.58
	----------------
	bugfix: export ticket custom reports to csv and excel does not change the html entities for the characters in the description.
	bugfix: missing lang in tickets custom report columns.
	
	Since 3.4.4.57
	----------------
	bugfix: in contacts view - don't show job title data if the custom property is disabled.
	
	Since 3.4.4.56
	----------------
	bugfix: sometimes when send email btn is pressed it saves a draft.
	
	Since 3.4.4.56
	----------------
	bugfix: contacts search does not find if searching by name and surname together
	
	Since 3.4.4.55
	----------------
	bugfix: email download performance when trying to request emails that are not in server.
	bugfix: sent email message_ids are not saved in mail_contents_imap_folders.
	
	Since 3.4.4.54
	----------------
	bugfix: when viewing mail its read status is not always reloaded until the complete list is reloaded. 
	
	Since 3.4.4.53
	----------------
	bugfix: missing langs in tickets notifications.
	bugfix: ticket description spacing fixed.
	
	Since 3.4.4.52
    ----------------
    bugfix: added constant to disable verify_peer and verify_peer_name when sending emails using ssl/tls and using php >= 5.6.
	
	Since 3.4.4.51
    ----------------
    bugfix: in gantt if a task is in more than one group it is shown only once.
    bugfix: missing lang in project types when the association is multiple.
    bugfix: member selectors order.
	bugfix: associated member selectors js error.
	feature: config option to allow or not multiple lines in invoice line description.
	
	Since 3.4.4.50
    ----------------
    bugfix: after selecting first multiple associated member you need to click outside and then inside the textbox to load the list.
    bugfix: disable multiple lines in invoice line description.   
    feature: invocing module: credit notes must have at least one reference.
	
	Since 3.4.4.49
    ----------------
    bugfix: change delete member js prompt for a custom modal to avoid the browser to remember the last answer.
    bugfix: make advanced_core a pre-requisite for expenses plugin.
    bugfix: member listing group by "located under" column fixed.
	
	Since 3.4.4.48
    ----------------
    bugfix: when clasifying emails it was always clasifying all the conversation without looking the preference that defines that.
    bugfix: breadcrumbs in emails must not be shown next to the subject.
    bugfix: 'created_by_id' in where clause is ambiguous' on calendar widget.
	
	Since 3.4.4.47
    ----------------
    bugfix: email parser was not recognizing attachments in multipart/related content.
	bugfix: missing lang in deleted emails notifications.
	
	Since 3.4.4.46
    ----------------
	feature: create member templates from received emails.
	bugfix: performance of search component within the mail tab.
	
	Since 3.4.4.45
    ----------------
	bugfix: error in advanced reports when grouping by created by.
	
	Since 3.4.4.44
    ----------------
	bugfix: imap check mail function fail when last received mail was deleted from the mail server.

    Since 3.4.4.43
    ----------------
    bugfix: imap checking fixed when last received mail is not in server.
    feature: new plugin "working_cycles"
    feature: new plugin "modules"

    Since 3.4.4.42
    ----------------
    feature: hierarchical client types dimension.
    bugfix: custom dimension name not used in associated dimensions.
    bugfix: send mail sometimes saves draft.

    Since 3.4.4.41
    ----------------
    bugfix: mail tracking.

    Since 3.4.4.40
    ----------------
    bugfix: error parsing Message-ID from mail.

    Since 3.4.4.39
    ----------------
    bugfix: unable to set can_update_other_users_invitations and can_link_objects permissions value.
    bugfix: imap download mails from specific date.
    bugfix: ambiguous column error when object subtype plugin is active and user group tasks by a date field.

	Since 3.4.4.38
    ----------------
    bugfix: imap mail download

	Since 3.4.4.37
    ----------------
	feature: mobile api updated
	
	Since 3.4.4.36
    ----------------
	bugfix: disable mail track
	
	Since 3.4.4.35
    ----------------
    bugfix: assigned to component does not fill with users
	bugfix: when no context selected, the add task form is not shown

	Since 3.4.4.34
    ----------------
    bugfix: mail contents - imap folder association normalized.
	
	Since 3.4.4.33
    ----------------
	feature: gantt chart includes dimension columns, custom property columns and basic properties columns
	
	Since 3.4.4.32
    ----------------
	bugfix: task member group breadcrumb shows wrong parent
	
	Since 3.4.4.31
    ----------------
    feature: action in document view - send document by email
    bugfix: cannot open weblinks files from internal network
    bugfix: tasks drag and drop between milestone groups
	
	Since 3.4.4.30
    ----------------
    bugfix: if sandbox defined then don't use the purifier to edit html documents.
	bugfix: when downloading an email whose "from" is marked as "no spam" it is sent to spam folder if has spam level headers greater than the configured level.
	bugfix: crpm types dimensions are not shown in separate columns.
	feature: allow to separate clients and projects columns in tasks list.
	
	Since 3.4.4.29
    ----------------
	bugfix: not all tasks are included in monthly view if they are too many.

	Since 3.4.4.28
    ----------------
	bugfix: newsletter cp filters not working

	Since 3.4.4.27
    ----------------
	bugfix: don't save same email more than one time if it belongs to more than one imap folder.
	bugfix: don't send reminders for template objects.
	
	Since 3.4.4.26
    ----------------
    bugfix: member custom reports pagination when hiding details
    bugfix: total of rows is not shown in reports when grouping by and hiding details
	bugfix: name column not forced in reports when grouping by and hiding details
    bugfix: contact links cannot be clicked
	bugfix: if contact/company links doesn't have scheme they are treated as relative urls.
	
	Since 3.4.4.25
    ----------------
    bugfix: check negative spam score when saving mail.
	bugfix: in notifications subscribers section, the last two users are not separated by spaces.
	feature: add column status in users list
	feature: use different background color for inactive users in users list
	
	Since 3.4.4.24
    ----------------
	bugfix: don't show company name between brackets for companies.
	bugfix: mail content fix when replying
	bugfix: error when editing timeslot with advanced billing
	bugfix: don't show add task form if user doesn't have permissions to add.
	feature: migrate payment types to object subtypes
	feature: add checkbox to select all object types in permissions definition interface.
	
	Since 3.4.4.23
    ----------------
	bugfix: custom reports not filtering by created on.
	
	Since 3.4.4.22
    ----------------
    bugfix: contacts listing order by last updated fixed.
	
	Since 3.4.4.21
    ----------------
	bugfix: when user triggers email dowload and has no permissions over the email rule's members the email is not classified there.
	
	Since 3.4.4.20
    ----------------
	bugfix: grid toolbar dissapears when entering an object, collapsing left panel and then closing the object view
	
	Since 3.4.4.19
    ----------------
    bugfix: in object view, changes in css of classification widget to use all available width 
    bugfix: when deleting mail it is not removed from list until reloading
	
	Since 3.4.4.18
    ----------------
    bugfix: custom reports error when conditions uses "is_user".
    bugfix: email addresses are not shown in custom reports.
    bugfix: performance upgraded when grouping custom reports by dimension member types.
    bugfix: in contacts list when clicking a company goes to other view.
    bugfix: email custom reports fails when using "to" in conditions.
    change: dont show payments table when editing expense.
	
	Since 3.4.4.17
    ----------------
    bugfix: in template instantiation error when calculating date from creation plus more days
	
	Since 3.4.4.16
    ----------------
    bugfix: when filtering object picker by object type the dimension filter is not applied
    bugfix: performance issue in permissions table
    bugfix: grouped reports should put the "unclassified" group in the last place
    feature: reminders post due date
    feature: config option to autoselect if milestone should be applied to subtasks
	
	Since 3.4.4.15
    ----------------
	bugfix: invoice updated_on column empty
	bugfix: tree panels wrong ordering with lower/upper case members, also with characters with accents. 
	bugfix: notifications from cron incorrect hostname
	bugfix: pdf export with accents in filename fixed
	feature: new dimension "Account executives"
	feature: show custom properties in tasks list
	
	Since 3.4.4.14
	----------------
	feature: new address type: postal
	feature: adminsitration users list using paginated grid filtering by active, inactive or all.
	feature: new action "Classify only attachments" in email view actions panel.
	
	Since 3.4.4.13
	----------------
	bugfix: don't filter users without permissions in root in tasks user's filter
	bugfix: permissions check when adding or removing task dependency
	bugfix: error 500 logged_user()->getgetUserTimezoneHoursOffset();
	bugfix: subtype assignation in invoicing module when generating next repetition
	bugfix: reports pagination when grouped and not showing details
	feature: more calculated columns in expenses/payments custom reports
	
	Since 3.4.4.12
	----------------
	bugfix: mail plugin installer and updater must put the max_spam_level in 5
	bugfix: fixed created date display for objects created before dst change
	feature: In event view, if event time zone is different than logged user time zone then show the original time with timezone below the calculated time for the logged user.
	feature: allow to change the time zone of tasks and events
	
	Since 3.4.4.11
    ----------------
    feature: timezone improvements, dst usage.
	
	Since 3.4.4.10
    ----------------
	bugfix: user without delete permissions cannot edit file revision comment.
	bugfix: file revision comment form is not modal
	bugfix: several expenses fixes and upgrades
	bugfix: invoice preview include expiration date
	bugfix: resize objectGrid when resizing containers
	feature: expenses cache tables for totals

	Since 3.4.4.9
    ----------------
	config: notification from name
	
	Since 3.4.4.8
	----------------
	bugfix: when user does not have the "can manage tasks" permission and completes a task an error message is shown after the completion.
	bugfix: automatic project status calculation not working
	bugfix: member selector, when getting child if it has to be replaced sometimes it is not added again
	
	Since 3.4.4.7
	----------------
	feature: multiple currency integration with invoicing, expenses and advanced billing.
	feature: electonic-invoice plugins.
	feature: spam score verification upgraded.
	feature: mail list - icon to show if it has been replied or forwarded.
	feature: add fixed totals for invoices.
	feature: allow to totalize content object's fixed numeric properties.
	feature: separated list totals for each defined currency in expenses and invoicing.
	bugfix: expense list and payment list totals does not use standard functions.
	bugfix: expense and payment reports are not including totals.
	
	Since 3.4.4.6
	----------------
	bugfix: in member custom reports, when adding a list custom property condition the possible values are not loaded correctly.
	
	Since 3.4.4.5
	----------------
	bugfix: don't show object print options if object subtypes plugin is not installed
	bugfix: don't use noreply@fengoffice.com as the from email address in notificacions/reminders
	bugfix: check if object subtypes plugin is activated before using it in expenses
	
	feature: object type hierarchy options
	feature: when custom properties are more than 10 then split them in two columns in the add/edit form
	feature: ensure that notifications always has something in the from name.
	feature: dont send notifications if demo_web plugin is active.
	feature: add content object id to sent_notifications table
	feature: dimension association config to autoclassify in parent member's associated member
	
	Since 3.4.4.4
	----------------
	bugfix: in grouped custom reports when a task is in more than one group then the hours are modified twice by the timezone.
	bugfix: check that parent exists before copying task info from parent
	
	Since 3.4.4.2
	----------------
	bugfix: object picker for mail attachments is not filtering correctly
	bugfix: group by parameters are not shown when adding a custom report
	bugfix: when adding custom properties, show in lists and show in main tab should be selected by default
	bugfix: list custom properties edition fails when values have '
	bugfix: breadcrumbs in object view are not using the standard function
	bugfix: expenses widget graph

	feature: show payment history in payment edit form and view
	feature: text filters in expense and payments list
	feature: expense and payment listings double toolbar
	feature: add custom property attribute to specify if the property is shown as a column in listings by default


	Since 3.4.4.1
	----------------
	feature: inherit company addresses and phones in contact reports if contact doesn't have any.
	bugfix: emails with no message_id header are not downloaded when connecting through imap
	
	Since 3.4.4
	----------------
	bugfix: upgrade script was setting version to beta.
	
	Since 3.4.3.35
	----------------
	bugfix: member name length is limited to 160 chars.
	bugfix: after searching in the member selector, the child nodes are not indented.
	bugfix: dont show disabled dimensions as listing columns
	bugfix: in invoicing module when hiding a column, the payments columns are wrong
	bugfix: after adding member it is always appended to the end of the left panel.
	
    language: de_de, fr_ca updated
    
    
    Since 3.4.3.34
    ----------------
	feature: copy expense action in expense's view
	
	Since 3.4.3.33
    ----------------
    bugfix: limit repetitive tasks shown in calendar.

	Since 3.4.3.32
    ----------------
    bugfix: executive users cannot edit expenses or link to other objects.

	Since 3.4.3.31
    ----------------
    bugfix: when editing a client, the custom properties inputs are duplicated.

	Since 3.4.3.30
    ----------------
    bugfix: error message appears when trying to reload uninitialized panel.
    bugfix: mail account dropdown menu doesn't have scroll.
	
	Since 3.4.3.29
    ----------------
    bugfix: list bullets not shown in tasks description in IE 11.

	Since 3.4.3.28
    ----------------
    bugfix: order of groups in task list when grouped by members.

	Since 3.4.3.27
    ----------------
	bugfix: inherit parent milestone and classification when creating sub tasks

    Since 3.4.3.26
    ----------------
    bugfix: In contact reports address conditions are not working.

    Since 3.4.3.25
    ----------------
    bugfix: task assigned to filter error when "Let users create objects without classifying them" is false.

	Since 3.4.3.24
    ----------------
	feature: breadcrumb on task group name for member groups.

    Since 3.4.3.23
    ----------------
	bugfix: confidential users are shown in mail account permissions section.
	
    Since 3.4.3.22
    ----------------
	bugfix: mails signatures were not editable.
	bugfix: subtasks use parent dates.
	
    Since 3.4.3.21
    ----------------
    bugfix: when changing member parent the expand/collapse element is not updated.
    bugfix: if og.openlink function returns an object then when it is called from an "href" in Firefox attribute if fails and no action is performed

    Since 3.4.3.20
    ----------------
    bugfix: when writing an email if content is put inside the signature div then it is not saved.

    Since 3.4.3.19
    ----------------
    bugfix: sql error when grouping by milestone and filtering by dates on the task list.

    Since 3.4.3.18
    ----------------
    bugfix: report pagination does not work when report has conditions.
    bugfix: client and project list tab title.
    bugfix: error printing advanced reports.
    bugfix: inherit parent dates when creating sub tasks.
    bugfix: milestone order in tasks list.
    feature: gantt weekend color.
    feature: allow to order member listings by associated dimensions

    Since 3.4.3.17
    ----------------
    bugfix: widget client statistics langs.
    bugfix: spanish lang 'in'  was  'En'.
    bugfix: client and project list tab title.
    bugfix: estimated and worked time widget language fixes.
	bugfix: completed tasks with worked time message.
	bugfix: special custom properties langs in member information widget.
	bugfix: lang fixes in member listing buttons.
	feature: dont show dimensions that are not selectable in object breadcrumbs.
	feature: show default color icon in activity widget for members that doesn't have icons.
	feature: show status with colors in clients list.

    Since 3.4.3.16
    ----------------
    config: header texture config.
    bugfix: notification logo width.
    bugfix: customer and project widget "view all" not working
    plugin demo_web: send all spam mails to inbox.
    bugfix: unread mails widget
    bugfix: widget late and upcoming tasks add new tasks.
    bugfix: widget: latest comments.
    bugfix: clients widget does not show tasks graph
    bugfix: project and clients statistics widget does not show status meter
    bugfix: member listing shows custom properties without localization settings
    bugfix: dont show status property column if status dimension is being used.

    Since 3.4.3.15
    ----------------
    bugfix: calendar print

    Since 3.4.3.14
    ----------------
    bugfix: performance fixed in tasks list when using subtasks structure and no filters or groups are used.

    Since 3.4.3.13
    ----------------
	feature: user preference to select the time column format in custom reports

    Since 3.4.3.12
    ----------------
    bugfix: prevent double request when double clicking the export to csv/excel button in custom reports.

    Since 3.4.3.11
    ----------------
    bugfix: member templates dont override the associated member with the one defined in the template initial data if the user has changed it when adding a member.
    
    Since 3.4.3.10
    ----------------
    bugfix: when saving suppliers the secondary email addresses are not saved.

    Since 3.4.3.9
    ----------------
    bugfix: mail account filter css error.

    Since 3.4.3.8
    ----------------
    bugfix: task and time reporting errors
    bugfix: css fixes to prevent vertical scroll in body element when using browser zoom

	Since 3.4.3.7
    ----------------
	bugfix: mail rules action foward mail.

	Since 3.4.3.6
	----------------
	bugfix: custom report reorder fixed when clicking in column header.
	bugfix: dont process the member search response if it is not from the last request.
	bugfix: users with mail account permissions cannot see unclassified emails if they are not the account owners.
	bugfix: cant add clients because of contact custom properties.
	
	Since 3.4.3.5
	----------------
	feature: cancel prevous search request when the search criteria has changed and the response has not returned yet.
	feature: tasks workflow - config option to select which dimensions should be copied from the completed task to the next task in workflow.
	
	Since 3.4.3.4
	----------------
	bugfix: tree nodes show more does not apply associated dimension filters
	feature: more custom login customizations
	
	Since 3.4.3.3
	----------------
	bugfix: member reports fixed when using column object_subtype_id and no object subtype is defined.
	bugfix: fix pdf export process when the exported report is too large.
	bugfix: dimension search cache is not cleared when associated dimension changes.
	bugfix: associated dimensions are fully loaded when selecting a member with no associations.

	Since 3.4.3.2
	----------------
	bugfix: template tasks conditional actions edition fixed.

	Since 3.4.3.1
	----------------
	bugfix: when disabling all the dimension selectors for an object type and editing an object of this type, the members were removed.

	Since 3.4.3.0
	----------------
	bugfix: after deleting mail account the account filter of the listing must remove the deleted id
	bugfix: google calendar sync, exception management.
	bugfix: classification component for single selection dimensions fixed.
	bugfix: permission error over file revisions.
	bugfix: when reopening a task, only the first level of dependencies are reopened.
	bugfix: task conditional actions are not replicated if the task is generated later in the workflow.
	bugfix: task workflow boolean conditions fixed.
	bugfix: when saving template task, the members and workflow should not be copied to subtasks.
	
	Since 3.4.3-rc
	----------------
	bugfix: if user does not manage permissions then don't let to create members without parent, otherwise no one will see it as user cant assign its permissions.
	language updates: fr_fr, fr_ca, tr_tr, nl_nl
	
	Since 3.4.3-beta
	----------------
	bugfix: cut user permissions when changing role must be done only if downgraded and executed in background when possible.
	bugfix: main custom properties are not shown in main tab
	
	Since 3.4.2.x
	----------------
	feature: allow to select for every dimension if its selector is shown when adding/editing an object
	feature: allow associated dimension columns in member reports
	feature: filter classification components with related dim-members when selecting the main dimension member
	feature: preload classification components with related dim-members when selecting the main dimension member
	feature: member listing group by
	feature: member custom reports group by
	feature: add relation between samples and countries
	feature: tasks workflow - new action to change project status
	feature: expenses plugin: show previous status amounts in listing and view, show payment history in expense view.
	
	bugfix: export to excel: sheet title length must have less than 31 characters
	bugfix: report totals row data alignment fixes
	bugfix: remove spaces between parameter brackets in translation tool
	bugfix: cannot delete object subtypes
	bugfix: Reload dimensions when selecting a member of an associated dimension fixed.
	bugfix: when ids are stored as string instead of int in the tree the associated dimensions does not filter other trees
	bugfix: remove strtolower usage from custom property views

	Since 3.4.2.20
    ----------------
    bugfix: When completing a repetitive task, sometimes not all original subscribers were added to the next task.
    bugfix: mail plugin installer does not add default permissions in members where users already have permissions.
	bugfix: new addToSharingTable function does not adds to the table the mail account owners when adding an email.
	bugfix: when viewing e-mail company addresses are not recognized when the company is in the system.

	Since 3.4.2.18
    ----------------
    feature: document types plugin.

	Since 3.4.2.17
    ----------------
	bugfix: updateContactMemberCache prevent error 500 on this function if the parameter user is not a contact.
	bugfix: dont use member cache for tags listing.
	
	Since 3.4.2.16
    ----------------
    bugfix: js error when printing total worked time report
    bugfix: actions button in tasks list does not work in firefox

	Since 3.4.2.15
    ----------------
	language: fr_ca language puts the same month abbreviation for June and July. 

	Since 3.4.2.14
    ----------------
	bugfix: Mail module not compatible with uuencoeded attachments.

	Since 3.4.2.13
    ----------------
	bugfix: if user cannot manage permissions, update contact member cache after adding new member.

	Since 3.4.2.12
    ----------------
    bugfix: Cannot select logged user in tasks widget configuration if user doesn't have permissions for tasks without classifying.
    bugfix: Prevent multiple submit when downloading an email attachment.

	Since 3.4.2.11
    ----------------
    bugfix: Language fixes for Nederlands (nl_nl).

	Since 3.4.2.10
    ----------------
    bugfix: upgrade from 3.4.1 fixed.

	Since 3.4.2.9
    ----------------
    bugfix: table prefix error when sending mails.

	Since 3.4.2.8
    ----------------
    bugfix: Reload dimensions when selecting a member of an associated dimension fixed.
    bugfix: check file extension before download file revision.
	
	Since 3.4.2.7
    ----------------
    bugfix: google calendar sync error when event doesn't have a name.

	Since 3.4.2.6
    ----------------
    bugfix: deleted emails are shown in trash panel.

	Since 3.4.2.5
    ----------------
    bugfix: check file extension before download file.

	Since 3.4.2.4
    ----------------
    feature: Allow to define additional member permissions in user and group edition.

	Since 3.4.2.3
    ----------------
    bugfix: User groups permissions not loaded when editing a member.
    bugfix: Cannot add additional member permissions to user groups.
    bugfix: Additional member permissions were not applied to children when "Apply to submembers" is checked.

	Since 3.4.2.2
    ----------------
    bugfix: newsletter default account not working.

	Since 3.4.2.1
    ----------------
    bugfix: overview error after selecting a member if the data base table prefix is different than fo.

	Since 3.4.2
    ----------------
    bugfix: company name length restriction.

	Since 3.4.2-rc2
	----------------
	bugfix: dont use JSON_NUMERIC_CHECK constant if not defined (is a php predefined constant).
	language: nl_nl updated.
	
	Since 3.4.2-rc
	----------------
	bugfix: member cache construction function fixed.
	
	Since 3.4.2-beta2
	----------------
	feature: Export custom reports to excel
	bugfix: fixed object classification after changing member parent.
	
	Since 3.4.2-beta
	----------------
	bugfix: custom reports pdf export compatibility in windows (need to install wkhtmltopdf)
	bugfix: custom report print view only shows first page
	bugfix: custom reports print and csv/pdf export adjustments
	bugfix: replicate purchase orders permissions over suppliers in the other content objects
	bugfix: assigned user cannot complete task from task view if doesn't have write permissions
	feature: filter member reports by associated dimensions (selected in left panel)
	
	Since 3.4.1.13
    ----------------
    language: fr_fr updated.

    Since 3.4.1.12
    ----------------
    bugfix: users widget not working properly.

	Since 3.4.1.11
    ----------------
	feature: search filter on mails

	Since 3.4.1.10
    ----------------
	performance: email recommendations when sending emails
	bugfix: active context information widget is not shown when more than one member is in context.

	Since 3.4.1.9
	----------------
	bugfix: disabled custom properties are shown in contact form
	bugfix: cannot export contact/company custom properties
	bugfix: scroll on mail panel after delete mails
	
	Since 3.4.1.8
	----------------
	feature: new system permission can instantiate template
	
	Since 3.4.1.7
    ----------------
    bugfix: prevent infinite loop when calculating repetitive tasks instances for calendars.

	Since 3.4.1.6
    ----------------
    bugfix: cannot complete task from task view when user does not have write permissions.
    language: nl_nl updated.

	Since 3.4.1.5
    ----------------
    bugfix: when adding permissions to user by a group in one dimension and by personal permissions in other, the task list is not correct.
    bugfix: fixed email decoding when entire email is an inline attachment.
    performance: task assigned selector, subscribers selector, other user selectors.

	Since 3.4.1.4
    ----------------
    bugfix: add unique index in object prefixes to avoid duplicated prefixes
    bugfix: object selector, if custom property filters not defined the list is not initialized
    bugfix: when checking email prevent querying all the mailbox mails when some mails are not found in server.
    feature: newsletter errors management improved
	private plugins updates

	Since 3.4.1.3
    ----------------
    bugfix: attached eml attachments are not parsed, and attached .eml cant be downloaded.

	Since 3.4.1.2
    ----------------
    bugfix: encoding problem on mail attachments names.

	Since 3.4.1.1
	----------------
	bugfix: cannot delete client email.
	bugfix: client email format is not controlled when editing client.
	
	Since 3.4.1
	----------------
	bugfix: several reference errors when using previous task on task edit view (affect reminders, multi assignment, repetition, assign to, custom properties, subscription, subtasks, linked objects)
	bugfix: edit tasks does not send notifications
	bugfix: when editing mail account, the signature of other users with permissions in the account are deleted.
	feature: user config option to show/hide date filters in tasks list
	css updates: key-value object properties input css modifications
	language: fr_fr language updates
	
	Since 3.4.1-rc
	----------------
	bugfix: error when adding task with more than 10 custom properties
	bugfix: error when changing user role and permissions in the same submit.
	bugfix: timeslot report has wrong time format
	bugfix: when editing a task and adding subtasks notifications are not sent if parent task assigned to is not changed.
	language: Türkçe (tr_tr) language updates
	
	Since 3.4.1-beta
	----------------
	bugfix: in firefox monthly calendar view does not expand the boxes if there are more events to show.
	bugfix: duplicate mails when to and from are the same mail.
	bugfix: google calendar sync permission issue when importing events on root.
	bugfix: in advanced search when cutting results name substr_utf function must be used.
	bugfix: missing config option for amount of events shown in monthly view.
	bugfix: events repeating by fixed day aren't shown correctly in monthly view.
	bugfix: events repeating until date doesn't include the last day in monthly view.
	
	Since 3.4.0.x
    ----------------
	feature: new calculated column total_worked_time in tasks
	feature: Tasks list drag & drop between task groups.
	feature: Tasks list drag & drop to dimension members.
	feature: Newsletters plugin.
	feature: Include member custom properties in member reports.
	feature: Make client sectors dimension multiple and hierarchical.
	feature: add address and phones info to client dashboard information
	feature: imap config option preserve emails state (read, unread)
	feature: Newsletters plugin.
	
	Since 3.4.0.29
    ----------------
	bugfix: several reference errors when using previous task on task edit view (affect reminders, multi assignment, repetition, assign to, custom properties, subscription, subtasks, linked objects)
	css updates: object properties input.

	Since 3.4.0.28
	----------------
	Config option to use or not the dates filters in tasks 
	
	Since 3.4.0.27
	----------------
	bugfix: Error when sending notifications after editing tasks
	
	Since 3.4.0.26
	----------------
	bugfix: Error when upgrading to 3.3.2 with fixed table prefix.
	
	Since 3.4.0.25
	----------------
	bugfix: Error when changing permissions and role.
	
	Since 3.4.0.24
	----------------
	bugfix: Non-working days calculation in template instantiation fixes.
	bugfix: search associated members was being filtered by current context.
	
	Since 3.4.0.23
	----------------
	bugfix: Private plugins modifications.
	
	Since 3.4.0.22
	----------------
	bugfix: Contact member cache generation fixed for new users.
	
	Since 3.4.0.21
	----------------
	bugfix: When editing user and removing a group the member cache was not deleted correctly for some submembers.
	
	Since 3.4.0.20
	----------------
	bugfix: when searching a child member and then searching the parent member, only the other child members are loaded after expanding the parent.
	
	Since 3.4.0.19
	----------------
	bugfix: gantt tasks order is not correct.
	
	Since 3.4.0.18
	----------------
	bugfix: import google calendar event classification fixed.
	
	Since 3.4.0.17
	----------------
	bugfix: export reports to pdf is adding html content to the file.
	
	Since 3.4.0.16
	----------------
	bugfix: check mail error when emails uid change on the mail server.
	bugfix: some user properties are set when adding contact
	
	Since 3.4.0.15
	----------------
	bugfix: cannot enable email module if it has been disabled.
	bugfix: email address length is too short.
	
	Since 3.4.0.14
	----------------
	bugfix: users cannot see other users contact cards if they have a higher role.
	
	Since 3.4.0.13
	----------------
	bugfix: mail list is not reloading if viewing an email and changing context.
	bugfix: export contacts to csv;
	bugfix: create contact from email when sending email was setting user fields.
    bugfix: additional member permissions - sometimes the checkboxes are checked and the permissions is not set.
   	
	Since 3.4.0.12
	----------------
	bugfix: get_member_childs function for member tree.
	bugfix: javascript errors when moving to trash tasks on tasks list.
	performance: multi tasks action remove member aditional data calculation.
	
	Since 3.4.0.10
	----------------
	bugfix: instantiate template from email is not working properly.
	bugfix: javascript error when completing subtask on the tasks list.
	
	Since 3.4.0.9
	----------------
	performance: remove member aditional data from member lists (project and clients lists)
	
	Since 3.4.0.8
	----------------
	bugfix: add member custom properties tab render error.
	
	Since 3.4.0.7
	----------------
	bugfix: error on object list when sorting by custom properties or dimensions.
	bugfix: comments widget not working.
	bugfix: prevent json errors on members list. 
	
	Since 3.4.0.6
	----------------
	bugfix: do not check permissions for super admin on archived objects list.
	bugfix: users can view other users with lower rol if they don't have can_manage_security.
	bugfix: root objects permissions.
	bugfix: classification error on user edit form.
	
	Since 3.4.0.5
	----------------
	feature: allow to edit precharged subejct and body variables when instantiating a template from an email.
	feature: allow all day events container to be expanded to show all items.
	feature: mails panel, when filtering by unread, dont remove mails immediately after marking as read, remove them when leaving the tab or opening an email.
	feature: constant to remove help links in settings.
	
	bugfix: in mail list when several requests are sent to load the list (different filters) only the last response must be loaded.
	bugfix: when classifying from mail view always return to first page.
	bugfix: if template parameters has name with capital letters and are saved in lowercase (if browser sent them in lowercase) the parameter is not applied.
	bugfix: if sunday is a working day, 5 days calendar view should start in sunday.
	bugfix: encoding error in general search.
	bugfix: show all childs in memeber trees after expand.
	bugfix: cannot advance to second page of projects list.
	bugfix: mail panel grid selections doesn't work fine after marking mail as read and filtering by unread.
	
	Since 3.4.0.4
	----------------
	bugfix: single member selector does not show filter input in chrome.
	bugfix: performance improved when checking for new emails.
	bugfix: tasks toolbar complete button does not prompt to complete subtasks.
	bugfix: remove rows from mail list when mail is deleted. archived, classified in other place, etc.
	bugfix: only admins can change timeslot person.
	bugfix: if pdf file doesn't have extension the preview is not displayed
	bugfix: when clients and projects are in different dimensions the tasks cannot be grouped by projects
	
	Since 3.4.0.3
	----------------
	feature: massive task reassignation button in tasks list
	feature: allow to add/subtract minutes of date variables in task templates.
	
	bugfix: member custom properties of type user does not load all users with permissions
	bugfix: email panel, new emails must be loaded only in first page
	bugfix: members list custom properties columns, disabled custom properties must not be shown.
	bugfix: member templates add/edit render empty divs for selectors of disabled dimensions
	bugfix: let superadmins view and modify confidential users
	bugfix: when template adds more than one day to a date variable and the resulting date is a non-working day then that amount must be added to keep the gap.
	bugfix: add subtask from task view.

	Since 3.4.0.2
	----------------
	bugfix: when submitting comment disable the button to prevent double comment if pressed twice.
	bugfix: email list performance improvements.

	Since 3.4.0.1
	----------------
	feature: email polling check if there are new mails and add them to the list instead of reloading all the panel
	feature: in lists allow checkboxes to work with shift key
	bugfix: breadcrumbs does not appear in expenses report the first time it is executed.

	Since 3.4
	----------------
	bugfix: some non-standard characters cause that some workspaces are not shown unless the searchbox is used.
	bugfix: companies csv export does not use .csv as file extension.
	bugfix: sql modified for superamins in clients and projects listings.
	bugfix: in permissions "all" checkbox doesn't work if user is collaborator or guest.
	bugfix: user subscribers are deleted after user edition.
	bugfix: mail report fields to, cc, bcc and body doesn't allow 'like' condition.	
	
	Since 3.4-rc
	----------------
	bugfix: webpages display issue in contacts view.
	bugfix: tasks list grouped by milestones, if showing empty milestones, when adding/editing task, it is replicated in all emtpy milestones.
	bugfix: change 'null' for '0' in plugin_id column in installer initial data quieries.
	
	Since 3.4-beta
	----------------
	bugfix: cannot instantiate template from mail if template has no variables.
	bugfix: in some languages somtimes the messages widget crashes the overview.
	
	Since 3.3.0.11
	----------------
	feature: filter by type in archived objects, trash panel and overview list.
	feature: member info widget improved.
	feature: objects subtypes plugin, allow to define different types of notes, tasks, etc., each one with different sets of custom properties.
	feature: custom properties interface improved.
	feature: expenses - allow expenses report to be exported to csv.
	feature: expenses - definition of different type of payments, each one with different sets of attributes.
	feature: expenses - config option to allow to add negative amounts.
	feature: advanced billing plugin.
	feature: add to listings the possibility to order by custom properties and dimension members.
	feature: don't persist attached documents after sending the email.
	feature: allow to filter time report by associated task status.
	feature: when completing a task show popup to ask if user wants to complete subtasks too.
	feature: allow to set user when adding worked time from tasks list.
	
	bugfix: when editing an object, don't modify linked objects for objects that logged user doesn't have permissions.
	bugfix: when editing an object, linked objects are not instantly loaded in the form.
	bugfix: add/edit mail account - sent emails imap sync fields are not shown in a tab.
	bugfix: don't show non manageable dimensions in object view (except for clients).
	bugfix: view/edit company without logo shows person default image.
	bugfix: when changing company logo it doesn't refresh the preview.
	bugfix: company logos are not shown in contacts tab list.
	bugfix: delete contact picture does not delete all sizes
	bugfix: when all sizes pictures are generated by the upgrade process, they cannot be edited (medium and small sizes).
	bugfix: plugin installer does not update version if plugin is already scanned.
	bugfix: single member selector style adjusted to look like multiple member selector.
	
	languages updated: tr_tr and fr_ca.
	
	
	Since 3.3.0.10
	----------------
	bugfix: pagination in archived and trashed panels.
	bugfix: user and date information in archived and trashed panels is not shown.
	bugfix: ckeditor sometimtes puts an overlay that cannot be removed (when pasting links).
	bugfix: when viewing emails don't show attachments container if there are only inline attachments that are shown in mail body.
	bugfix: linked objects js error when creating task from email
	bugfix: repeat by fixed date events sometimes are shown in the next day (when timezone > 0)
	bugfix: when viewing emails don't show attachments container if there are only inline attachments that are shown in mail body.
	bugfix: comments text area has a max of 13 lines.
	bugfix: cannot remove tags from users.
	bugfix: cannot edit some picture files.
	bugfix: non administrators can't link objects to template tasks when editing.
	bugfix: give a proper message when imap extension is not installed.
	bugfix: permissions components does not have horizontal scrollbar.
	
	Since 3.3.0.9
	----------------
	bugfix: member panel children display issue fixed.
	
	Since 3.3.0.8
	----------------
	bugfix: cannot download emails with .docx attachments if zip php extension is not installed.
	bugfix: when filtering by another dimension member the members that have parents are not included.
	
	Since 3.3.0.7
	----------------
	bugfix: reminders are always saved to apply all the subscribers.
	bugfix: reminders are sent to non-subscribed users (when reminders are copied in repetitive tasks)
	language updates: fr_ca and tr_tr

	Since 3.3.0.6
	----------------
	bugfix: Deleting from project listing is not using the member controller delete function.
	
	Since 3.3.0.5
	----------------
	bugfix: don't add contact member permissions for dimension_object and dimension_group object types.
	
	Since 3.3.0.4
	----------------
	bugfix: single member selector on firefox.
	bugfix: send mail error when is called by cron and the sender is not the account owner. 
	bugfix: email address support.
	
	Since 3.3.0.3
	----------------
	bugfix: member list order by parent name.
	bugfix: search on member trees is not working if the node name have html entities.
	
	Since 3.3.0.2
	----------------
	bugfix: web documents only working for super admins.
	bugfix: workspaces and tags list not working.
	bugfix: add new contact/user/company picture error
	
	Since 3.3.0.1
	----------------
	bugfix: when adding/editing suppliers the associated contact custom properties are not rendered and if there is any required cp the supplier cannot be added.
	bugfix: clear filter after select on member selectors.
	bugfix: single member selector
	bugfix: breadcrumbs js error
	bugfix: ensure to archive-unarchive associated object or associated member
	bugfix: filter dimensions by other dimensions does not get the filtered members always.
	bugfix: ensure that deprecated gantt overview widget is not rendered.
	bugfix: custom properties address fields renderization crashes when it has enters.

	performance: member tree search.
	performance: member trees.
	
	
	Since 3.3
	----------------
	
	bugfix: js error and new tab opens when trying to add new invoice and module is not configured.
	bugfix: tasks list group by dimension not showing tasks without member.
	bugfix: when no formula status is matched then the last one is assigned.
	bugfix: super admin can not see other users attachments if the mail is not classified.
	bugfix: repeating template tasks not working with config option repeating_task.
	bugfix: purchase orders "ordered by" field must be only users.
	bugfix: expenses payments numeration
	
	lang: Turkish translation


	Since 3.3-rc
	----------------
	bugfix: contact list image width.
	bugfix: settings icons misalignments when language is es_es or es_la
	bugfix: search results not always shows where is the match (e.g.: if match is in a comment)
	bugfix: template tasks parent id is not set in some cases.
	bugfix: dont delete .htaccess when cleaning tmp folder using cron events
	bugfix: set "attach document to notification" default value to "false"
	bugfix: filters bug in linked objects picker.
	bugfix: custom report dates are not correct.
	bugfix: search results not always shows where is the match (e.g.when matched word is in a comment)
	bugfix: report is shown in permissions screen when reports tab is disabled
	bugfix: sharing table not updated for user when permissions changed.
	bugfix: birthday query not correct for leap-years
	bugfix: dont set user images to expire today.
	bugfix: incorrect error message when user cannot upload document
	
	
	Since 3.3-beta
	----------------
	bugfix: breadcrumbs on mail list.
	bugfix: template tasks depth.
	bugfix: some template tasks disappear from template view.
	bugfix: contact member cache is not recalculated when enabling or disabling modules.
	bugfix: weblinks names.
	bugfix: non working days - remove option "leave as they are" and set min amount of days to 1.
	bugfix: user birthday
	bugfix: administrator cannot edit user in some cases
	bugfix: in template user variables: dont filter fixed user variables by context or company
	bugfix: all prev tasks completed notification cant be sent if company logo file does not exists
	bugfix: dont show member custom properties if plugin not installed.

	
	Since 3.2.3.2
	----------------

	bugfix: dont show object in listings if it is only classified in a person member.
	bugfix: resize members panel.
	bugfix: multiline text on street address.
	bugfix: missing contact data inputs in clients edition when client is a person.
	bugfix: langs in tasks groups.
	bugfix: user's widget in dashboard showing contacts.
	bugfix: php execution in tmp folder must be disabled
	bugfix: no message is shown when tasks report does not have tasks
	bugfix: change project_manager to custom property
	bugfix: related dimensions are reloaded with selected node object type not having an association.
	bugfix: edit button on members trees
	bugfix: after change client parent classify contact 
	bugfix: update parent after adding child node on members trees 
	bugfix: print tasks list
	bugfix: don't allow duplicated username
	bugfix: Error when filtering tasks by tag.
	bugfix: dont show object in listings if it is only classified in a person member
	bugfix: do not reload tasks list after complete tasks
	bugfix: javascript infinite loop on tasks list
	bugfix: dont force repository files download, keep cache for these files
	bugfix: mail rules mark as read fail when is called from cron
	bugfix: when filtering members by another dimension, cannot view filtered member childs
	bugfix: autoclassify components misaligned in mail account edition
	bugfix: drag and drop on member tree node
	bugfix: zip code is not shown in user/contact/company card
	bugfix: cannot download attachments if email has inline images before the attachments
	bugfix: checkbox to send notification to assignee does not appear
	
	
	feature: resize columns on tasks list.
	feature: separate dimensions columns on tasks list.
	feature: general member listings.
	fetaure: allow to reorder and disable description and color fields (in custom properties administration).
	feature: custom properties of type user
	feature: table to add options for different member types
	feature: dimension object type option to decide if member is selected or not after its creation
	feature: description field for all type of members.
	feature: lab samples plugin and object prefixes modifications
	feature: dimension options normalization
	feature: dimension options settings section
	feature: allow to set custom dimension names
	feature: allow to enable/disable dimension member types
	feature: show or hide overview action links checking by current context (e.g.: dont show clients list link if a client is selected)
	feature: sent notifications history log
	feature: ability to log in filesystem the error details when sending a notification fails
	feature: dont reload all panel when task workflow generates new tasks, only add new tasks to the list
	
	perfromance: mobile member listing
	performance: remove object timeslots permissions 
	performance: close timeslots when completing tasks
	
	language: non working days traductions for es_es and es_la
	
	usability: time report using 100% width, min-width:750
		
	
	Since 3.2.3.1
	----------------
	bugfix: Error when filtering tasks by tag.
	
	Since 3.2.3
	----------------
	feature: view object history paginated.
	
	Since 3.2.3-beta
	----------------
	bugfix: fixed addToSharingTable when called from rebuild_sharing_table.php
	bugfix: unclassified emails are not added to sharing table.
	bugfix: group totals on tasks list.
	bugfix: new mail old view from contact list.
	bugfix: blocking file for uploading new revision.
	bugfix: when instantiating template from email, assigned users should be filtered using email's members
	bugfix: ensure that member template tasks are classified in the recently created member.
	bugfix: when viewing mails, container must be bigger before the resize
	bugfix: view more french.

	
	Since 3.2.2
	----------------
	bugfix: object prefix is not deleted after deleting object.
	bugfix: removed trailing commas from javascript objects.
	bugfix: classify contact after change client parent.
	bugfix: mail view doesn't reload after classifying mail.
	bugfix: remember notify my self checkbox in add and edit file. 
	bugfix: notify myself checkbox view in add and edit file.
	bugfix: notifications to asignee when creating and editing tasks.
	bugfix: update parent after adding child node on members trees.
	bugfix: mail rules history view.
	bugfix: not possible to upload picture when creating contact or user.
	bugfix: not showing which tab is selected in the mail panel.
	bugfix: phone number name with special characters. 
	bugfix: language and css of installed plugins that are not activated are not included.
	bugfix: template milestone add is not modal.
	bugfix: when modifying/trashing several tasks don't make all client/project calculations foreach task, do it once after all modifications
	bugfix: calculate percent completed on tasks.
	bugfix: group totals on tasks list.
	bugfix: Superadmin cannot edit other superadmins.
	
	feature: generate template from mail - edit email fields - link mail to generated tasks.
	feature: calendar new event view when clicking on the calendar.
	feature: when creating subsecuent tasks (tasks workflow) also creates a task dependency between the completed task and the new ones
	
	performance: member trees paginated (left panel and selectors).
	performance: remove object timeslots permissions.
	performance: close timeslots when completing tasks.
	
	config option: number of previous pending tasks showing next to each task.
	config option: notification to subscribers when creating and editing tasks.
	
	Since 3.2.2-rc
	----------------
	bugfix: object prefix is not deleted after deleting object
	
	Since 3.2.2-beta
	----------------
	bugfix: breadcrumbs on mail list.
	bugfix: calculate percent completed on tasks.
	bugfix: group totals aligned on tasks list.
	bugfix: error when instantiating template milestones.
	bugfix: showing which tab is selected in mail panel.
	bugfix: prevent "duplicate key" message in permission groups table when creating an user.
	bugfix: some checkboxes are not submitted correctly.
	
	
	Since 3.2.2-alpha
	----------------
	fetaure: When editing a task and start or due date is changed, ask the user to advance/rewind the subtasks dates.
	feature: new config option to configure which address fields are mandatory.
	
	performance: breadcrumbs.
	performance: tasks list.
	
	bugfix: when clicking home, the panel is reloaded once per enabled dimension.
	bugfix: when filtering tree by another dimension and selecting the node it is reloading again unnecessarilly.
	bugfix: remove comment and template objects types from role_object_type_permissions, max_role_object_type_permissions and contact_member_permissions.
	bugfix: old breadcrumbs.
	bugfix: object prefices - setting object name must escape characters.
	bugfix: linked objects component always filters by context, and intersects with the own member pickers selections.
	bugfix: when editing object, when cleaning object_members before adding to members, members of non-manageable dimensions must not be cleaned.
	bugfix: template instantiation with non-working days does not leave task dates as they are if advanced days are 0.
	bugfix: don't classify "inline" attachments when classifying emails.
	bugfix: auto_classify_attachments config option is not included in mail plugin installer.
	bugfix: color input does not render "light grey" color.
	bugfix: project manager selector changes.
	bugfix: csv contact/company import encoding problem with iso-8859-1.
	bugfix: csv contact/company import fails if some fields has apostrophe.
	bugfix: csv contact/company import duplicates addresses, phones and webpages if import is executed twice.
	bugfix: custom properties with apostrophe can't be added to searchable objects.
	bugfix: some checkboxes are not submitted correctly.
		
	
	Since 3.2.1
	----------------
	feature: user picture files are scaled in 3 sizes for performace
	
	Since 3.2.1-rc
	----------------
	bugfix: linked objects and view as list in dashboard does not filter.
	bugfix: notification of previous task completed is the same as task completed.
	bugfix: template instantiation with non-working days does not leave task dates as they are if advanced days are 0
	bugfix: contact selector langs are always in spanish.
	bugfix: tasks not showing in dashboard calendar widget.
	
	
	Since 3.2.1-beta
	----------------
	bugfix: mobile client's list.
	bugfix: uploading file with empty name.
	bugfix: mail panel navigation history.
	bugfix: when instantiating tasks from template in second step, subscribers are not copied changes: new function to centralize the copy of the subscribers, reminders, linked objs, members and custom props.
	bugfix: lang unknown group on tasks list for dates groups.
	bugfix: instantiate template parameters is not filtering by recently created member (when using member templates).
	bugfix: lang nl_nl contains enters inside the langs and that causes javascript to be broken.
	bugfix: when adding/editing an email account, if a blank is after mail server address then cannot connect to mail server.
	
	
	Since 3.2.0.4
	----------------	
	bugfix: google calendar sync permissions errors
	
	Since 3.2.0.3
	----------------	
	language: language fr_ca updated.
	
	Since 3.2.0.2
	----------------	
	bugfix: member custom properties values close div on active feed.
	
	Since 3.2
	----------------
	bugfix: feng1 upgrade
	bugfix: mail listing must not show emails that are not of my account and are not classified.
	bugfix: config option notify myself when uploading documents. 
	bugfix: total pending time in tasks list. 
	bugfix: add timeslot on task view not visible in root.
	bugfix: expenses, objectives and purchase orders where sending notifications before commit.
	bugfix: input type checkbox in modals
	bugfix: in community editions (tasks does not use time in dates) the reports are adding the timezone.
	bugfix: projects list and clients list links are not included in dashboard list view
	bugfix: google calendar sync.
	
	performance: linked objects list
	performance: index by object_type_id, trashed_on, archived_on in objects table
	
	Since 3.2-rc2
	----------------
	bugfix:	ie fixes.
	bugfix: custom properties description in forms is not aligned.
	
	
	Since 3.2-rc
	----------------
	feature: allow to have companies and contacts with the same email address.
	feature: clients list.
	feature: clients and projects listings allows to show custom properties and order the list by them.
	feature: check attach word with langs.
	feature: listing contacts with picture.
	feature: config option to allow choosing in which email field (to,cc,bcc) will the recipients be put when sending a notification.
	feature: config option changing name order to name surname.
	feature: config option remember working days preference in task's push.
	
	bugfix: updating percentage completed in tasks list.
	bugfix: returning to company view after editing contact picture.
	bugfix: number of archived objects.
	bugfix: member selector overflow.
	bugfix: style when changing order of deleted custom property. 
	bugfix: custom property order. 
	bugfix: remembering checkboxes in reports. 
	bugfix: order by start date in tasks list.
	bugfix: saving documents without name.
	bugfix: refreshing read/unread ball colour in tasks list.
	bugfix: alignment in add event.
	bugfix: number of elements in trash.
	bugfix: when sending email to contact with comma in their name. 
	bugfix: showing milestones options when disable in task panel.
	bugfix: remembering gantt zoom.
	bugfix: classify modal autofocus.
	bugfix: plugin installers: dont give permissions in members that users doesn't have permissions for tasks (expenses, objectives, purchase_orders, income).
	bugfix: plugin installers: dont give permissions in members that users doesn't have permissions for tasks.
	bugfix: members of dimensions that dont use permissions are not shown in listings.
	bugfix: tags on memeber selectors.
	bugfix: contact custom properties on client edit.
	bugfix: when "adding objects without classifying": cannot create task if not filtering by member.
	bugfix: when grouping tasks by dim-members unclassified group is shown always however it has no tasks.
	bugfix: in additional member permissions.
	bugfix: when setting a project's client, the client contact is not classified into the project.
	bugfix: tab panel permission was not set when installing income plugin.
	bugfix: tasks report headers are wrong when grouping by priority.
	bugfix: when creating task repetition the subscribers are not copied from the original task.
	bugfix: when creating new client classify contact or company on the client.
	bugfix: error 500 when adding expense without payments.
	bugfix: when adding timeslot in "view all" no user is available.
	bugfix: when creating new client classify contact or company on the client.
	bugfix: events without invitation are not shown.
	bugfix: tasks workflow - only second step inherits templates variables.
	bugfix: prevent adding duplicated nodes in left panel trees.
	bugfix: task templates, only the first step works.
	bugfix: error 500 when colaborator edit a timeslot.
	bugfix: cannot edit root permissions when changing user type.
	bugfix: notes widgets is not cutting note text correctly. 
			
	
	Since 3.2-beta2
	----------------
	feature: new custom property type : address
	feature: suppliers dimension (for purchase orders)
	feature: user config option check attach word on mails.
	feature: modal view for classify email.
	feature: more read permissions for guest customers.
	feature: custom reports for dimension members.
	feature: confirm delete in members.
	feature: time on report dates (crpm)
	
	bugfix: don't close modal on overlay click.
	bugfix: users can see all users profiles.
	bugfix: adapting image size on contact view.
	bugfix: user disappear from other users view after edit.
	bugfix: dynamic message when adding linked objects.
	bugfix: editting web document from view.
	bugfix: checking url when adding web document.
	bugfix: in income table definitions and queries.
	bugfix: save user permissions in background twice.
	bugfix: converting a contact to user loses custom properties and linked objects.
	bugfix: when editing an user and changing its user groups the member cache is not recalculated.
	bugfix: when creating user from contact, if all system permissions are removed, the default are added.
	bugfix: subscribers are lost if object is saved before the reload subscribers request returns.
	bugfix: duplicate phone on edit contact view.
	bugfix: mysql reconnect on executeAll.
	bugfix: "Cannot read property createChild of null" when adding a task and closing the modal form quickly.
	bugfix: in user groups permissions when applying permissions to all submembers of a workspace that has no children, the workspace is removed from the tree.
	bugfix: search input in listings has many bugs.
	bugfix: do not check feng_persons dimensions when checking for email classification (for listing icon and classification filter)
	bugfix: create user from contact view.
	bugfix: cannot export to csv custom reports with date_time custom properties.
	bugfix: draw located under selector only if member can have parent.
	bugfix: workspace description value is not rendered when editing a workspace.
	bugfix: breadcrumbs asking for members where user don't have permissions.
	bugfix: after add a contact on root.
	bugfix: feng 3 mobile.
	bugfix: can't remove "only working days for the tasks" on templates.
	bugfix: client fee is not saved correctly
	bugfix: deleting permission for single object types removes all permissions for member (when changing permissions from user form)
	
	
	member custom properties: use "visible_by_default" column to show cp in main tab or "custom props" tab
	
	performance: get imap mail function optimization.
	performance: update timeslots billing values is using too many objects and memory runs out if there are a lot of timeslots to update.
		
		
	Since 3.2-beta
	----------------
	feature: php path config.
	
	bugfix: can't subscribe users.
	bugfix: upgrade from versions lower than 3.1.5.3
	bugfix: after add a contact in root.
	bugfix: after updating permissions for a user on a member, all objects on sub-members are deleted from sharing_table for that user.
	
	Since 3.1.5.3
	----------------
	feature: dont use sharing table to check canView for individual objects.
	feature: instantiate task templates from email.
	feature: upgrade script change searchable_objects table to InnoDB if mysql version is  5.6 or greater.
	feature: checkbox to apply same permissions to submembers in edit member form, permissions modal form.
	feature: new table to define max member permissions by role.
	feature: send notifications grouped by language and timezone, max of 20 users x group.
	feature: invoicing repetition - repeat by fixed date.
	feature: When calculating task dates in templates and task push, do not count non-working days.
	feature: google calendar sync api v3.
	feature: notify assigned user of task A when A has a previous task B and B has been completed.
	feature: allow time inputs in date custom properties, only if crpm plugin is installed.
	feature: when instantiating template tasks check dates and if it is a non-working day execute the action defined in the template.
	feature: non working days abm.
	
	permissions: give read permissions over reports to all roles as default permissions 
	permissions: hide "templates" object type radio buttons in permissions forms
	
	bugfix: when member filters associated dimension, get_child_members is not filtering. 
	bugfix: when member filters associated dimension, if filtered member is clicked then the other dimensions are cleared and no member is selected.
	bugfix: when changing user role to a lower role, max permissions are not updated.
	bugfix: root permission are set to ext. collaborators.
	bugfix: nested transactions when saving mail.
	bugfix: contact export reloads the page. 
	bugfix: export all contacts to vcard does not filter by context.
	bugfix: pagination in invoices listing.
	bugfix: config option "inherit_permissions_from_parent_member" was not inserted in installer.
	bugfix: when conditional task is automatically instantiated it is non added to sharing table.
	bugfix: purge trash performance.
	bugfix: if role cannot read object type the permission radio button is not hidden.
	bugfix: create task from email form is not modal and  does not autoselect the email's context.
	bugfix: template workflow - when tasks are automatically created, the parameters are not instantiated in the new tasks.
	bugfix: use post for get_members function.
	bugfix: breadcrumbs sometimes shows parent members that doesn't have permissions.
	
	Since 3.1.5.2
	----------------
	bugfix: on overview member selectors not working.
	bugfix: remove all members from contact.
	
	Since 3.1.5.1
	----------------
	usability: dont remove all items when reloading mail list.
    bugfix: mail panel is reloaded foreach email received (only when checking mail manually)
	bugfix: push tasks dates.
	
	Since 3.1.5
	----------------
	bugfix: in member permissions radiobuttons that cannot be selected (depending on role) must not be shown.
	bugfix: collaborator user can't view tasks where is the assigned user.
	bugfix: when editing objects and changing context, subscribers are reloaded and users without permissions are not removed if they were subscribed previously.
	bugfix: permission trees loading concurrence fixes.
	bugfix: user can edit other users with higher role.
	bugfix: collaborator cannot add worked time from tasks list if no member selected.
	bugfix: adding folders from quick add does not inherit permissions from parent member.
	language: ja_jp updated
	
	Since 3.1.4.3
	----------------
	permissions: give guests and collaborators read permissions over reports by default.
	bugfix: error sending mail.
	
	feature: cron function send outbox mail.
	
	Since 3.1.4.2
	----------------
	bugfix: email download count fixed.
	bugfix: error when creating tasks.
	
	Since 3.1.4.1
	----------------	
	bugfix: contact export to csv.
	
	Since 3.1.4
	----------------
	bugfix: performance when saving tasks, if it has subtasks can produce timeout.
	bugfix: if role cannot read object type the permission radio button is not hidden.	
	
	Since 3.1.3.8
	----------------
	feature: System permission to let some users link objects.
	
	Since 3.1.3.7
	----------------	
	bugfix: notify myself when uploading files.
	
	Since 3.1.3.6
	----------------
	bugfix: only context permissions are checked when adding a timeslot.
	bugfix: can_add function is checking for disabled dimensions.
	
	Since 3.1.3.5
	----------------		
	bugfix: missing linked objects types on the selctor.
	bugfix: add billing on timeslots when select stop clock on a task. 
	bugfix: repeat event by fixed day is showing wrong dates for some events.
	bugfix: only context permissions are checked when editing a timeslot.
	bugfix: multiple file upload transaction broken by notifications.
	
	Since 3.1.3.4
	----------------	
	bugfix: at least one dimension must be selected on system modules.
	
	Since 3.1.3.3
	----------------	
	bugfix: add timeslot on tasks for user with permissions on the task context.
	
	Since 3.1.3.2
	----------------
	bugfix: when instantiating templates if subtask is instantiated before parent task the instantiation fails because parent instance is not found
	
	Since 3.1.3.1
	----------------
	bugfix: show tasks dependencies on gantt.
	bugfix: in edit task form, not all ' are escaped when drawing subtasks form.
	bugfix: task reminders are not sent for non administrators, task canView function was asking for logged user instead of using the user parameter.
	
	Since 3.1.3
	----------------
	bugfix: Mobile login fixed.
	bugfix: dates variables on tasks templates timezone error.
	bugfix: can_manage_contacts is checking permissions for super admins
	bugfix: logo_empresa.png limit size
	bugfix: cannot save "can_edit_completed_payments" system permission	
	
	Since 3.1.2.8
	----------------
	bugfix: if user has no permissions to see assigned to other user tasks he/she can view them in the calendar.
	bugfix: super admin cannot view all files.
	bugfix: task list print does not work
	bugfix: upgrade fix if cron event already exists
	bugfix: remove from sharing table objects when user has no permissions to access objects without classification.
	bugfix: when adding a workspace, client, project or folder without parent and changing the parent in the form, the permissions are not inherited.
	feature: scroll to comment after adding one.
	language: updaed ru_ru, fr_ca and fr_fr
	
	Since 3.1.2.7
	----------------
	bugfix: in user edition, user group names are not escaped and the form crahsed depending on the content.
	
	Since 3.1.2.6
	----------------
	perfomance: user selector on add/edit task view.
	performance: add to sharing table
	bugfix: add index member_id in object_members
    bugfix: get max uid for imap folder
	bugfix: when saving an email don't begin the transaction if it is not needed

	Since 3.1.2.5
	----------------	
	bugfix: upgrade scripts for expenses and objectives plugins, add max permissions for executives.
	
	Since 3.1.2.4
	----------------	
	bugfix: can not select empty milestones on tasks list.
	bugfix: when uploading an existing document, the list of files shows each file date 1 day after the real date.
	bugfix: email due date reminders shows company name instead of due date.
		
	Since 3.1.2.3
	----------------	
	bugfix: sql error when upgrading from feng 1.7.
	bugfix: on tasks drag and drop the task loses its description.
	bugfix: load gantt if user config option is set to do that.
	bugfix: show subtasks on gantt.
	bugfix: show milestone due date on task list.
	bugfix: show empty milestones on task list fails when filtering by dates ranges.
	bugfix: cannot delete user groups.
	bugfix: cannot edit user data.
	bugfix: document is always attached to notifications.
	bugfix: email due date reminders does not show the date correctly.
	language: russian translations updated.

	feature: template tasks workflow (in conditional_tasks plugin)

	Since 3.1.2.2
	----------------	
	bugfix: forward mail not working in some cases.
	
	Since 3.1.2.1
	----------------
	bugfix: Users without permissions to add timeslots can add timeslots if the task is assigned to him/her.
	bugfix: In tasks list, user has no time permissions for a task but time options are shown.
	bugfix: In tasks list, if action popover button has no actions, it is shown with an empty menu.
	bugfix: on template tasks add dependant task not working.
	bugfix: after edit member update all childs depths.
	bugfix: when deleting emails the register in objects table was not deleted.
	bugfix: document level filter is not set with its current value when logging in.
	
	feature: in single member selector when no member is selected show root node's text.
	feature: upgrade by console - no need to pass the version from and version to parameters.
	
	
	Since 3.1.2
	----------------
	bugfix: When checking mail, check for spam level in headers improved.
	bugfix: Error when adding tasks.
	bugfix: Cannot delete user group.
	bugfix: Feng1 to Feng3 upgrade script does not fill the "enabled_dimensions" config option.

	
	Since 3.1.1
	----------------
	feature: Cron process to reprocess last objects' sharing table entries
	
	
	Since 3.1
	----------------
	bugfix: Sql error in tasks list.
	bugfix: In upgrader script, if DEFAULT_LOCALIZATION not defined then define it with value "en_us".
	bugfix: After adding tasks, actions buttons not working.
	bugfix: Reminders on task templates are not saved.
	bugfix: Can't see subtasks if parent task  is not displayed.
	bugfix: If email account is set as default, then the "Sender name" field is ignored.
	bugfix: After change group by on tasks list the groups are still the same.
	bugfix: Timezones on tasks list groups.
	bugfix: When adding an event checkboxes "subscribe invited users" and "send email notifications" are not working.
	bugfix: Duplicated tasks on tasks list in last month and last week when this groups are overlapping.
	bugfix: Sql error table missing prefix. table im_types.
	bugfix: Can't add tasks in french.
	
	feature: in contact csv import allow to match custom properties
	
	language: fr_ca updated.
	
	
	Since 3.1-beta
	----------------	
	bugfix: Template tasks subscribers were not copied when instantiating the tasks.
	bugfix: Remove contextmenu from the email editor.
	bugfix: Autoclassifying email fix in query.
	bugfix: Refresh the task row after adding timeslots to tasks.
	bugfix: Sql query malformed on tasks list.
	bugfix: Duplicate signature sometimes when replying emails.
	bugfix: When creating collaborators positioned in a workspace, the workspace is not added to the member cache, permissions are fine.
	bugfix: Javascript eerror "member is undefined" in member cache js file.
	
	
	Since 3.0.8
	----------------
	feature: hierarchy filter on documents tab.
	feature: in custom reports if object name is printed now it is a link to the object.
	feature: when classifying users using drag and drop the system asks if you want to add the default permissions for the users in the workspace where they are being classified.
	feature: add tags selector in user add/edit form.
	
	performance: tree node asks for childrens to the server twice after click .
	
	bugfix: reminders on template tasks.
	bugfix: after adding a client, the client tree shows the client twice.
	bugfix: do not reload member trees after editing a member.
	bugfix: use current time when adding timeslots from tasks list.
	bugfix: permission errors when adding timeslots from tasks list.
	bugfix: on tasks list after add the first task remove  "There are no tasks in".
	bugfix: wrong order when grouping by priority on tasks list.
	bugfix: wrong signature when replying mail from a non default account.
	bugfix: after add subtasks send assignment notifications.
	bugfix: when dragging members to no-permissions tree children are not moved.
	bugfix: cannot edit user tags.
	bugfix: select milestones on templates.
	bugfix: when composing an email with other email address the autosave asks if you want to send with that adddess (it must ask only when sending or saving draft).
	bugfix: collaborators should not have access to mail tab
	
	language: fr_ca updated.

	Since 3.0.7
	----------------
	
	
	Since 3.0.6
	----------------
	feature: in custom reports, show name column as a link to the listed object and open the link in a new feng tab.
	feature: add projects to available object types when configuring autonumeric prefixes.
	feature: crpm types plugin - new dimension Client type, Project type and Project status.
	feature: when replying an email of other account, a warning must appear telling that email will be sent using that account and give the posibility to change the account before sending the email.
	
	bugfix: upgrade script to 3.0 fails when inserting in tab_panels if not all columns are specified depending in mysql server configuration.
	bugfix: dont use the same "from name" when sending mails with different account.
	bugfix: cannot autoclassify mails in more than one workspace.
	bugfix: checklang translation tool does not show plugin missing/incomplete translation files.
	bugfix: non-exec directors should not be task assingable.
	bugfix: header breadcrumbs are not reloaded when deleting a workspace.
	bugfix: when reordering workspaces, tags, clients and projects columns in any listing (notes, documents, etc), the values of these columns are lost for all rows, must reload the list to reappear.
	bugfix: send notification when a task is assigned.
		
	performance: ajax load on tasks list.

	Since 3.0.5.1
	----------------
	feature: multiple status factors for each status.
	feature: add/edit billing category in modal form.
	feature: cancel button added to permissions popup in member edition
	
	performance: on tasks list view.
	
	bugfix: cannot upload logo when adding company or client.
	bugfix: cannot add company when creating a client with type=person.
	bugfix: unclassify user from members where permissions have been removed.
	bugfix: when replying an email and changing the "from" the mail content is lost.
	bugfix: calendar events only shows one member color (fixed for week and day views, must be activated in user preferences).
	bugfix: expenses plugin - totals row doesn't check permissions.
	bugfix: csv export should only export the contacts, users or companies if they are being shown in the contacts tab.
		
	Since 3.0.5
	----------------
	bugfix: Error when creating user from company view
	bugfix: Error when viewing empty custom property if it is of type=contact.
	bugfix: Installer error, missing column 'can_update_other_users_invitations'.
	
	Since 3.0.4.1
	----------------
	bugfix: Performance issue when changing workspace parent.
	bugfix: Cannot add user from exisiting contact.
	
	feature: System permission to let some users change event invitations state for other users.
	
	Since 3.0.3.1
	----------------
	bugfix: Performance issue when ordering documents list by size.
	
	Since 3.0.3
	----------------

	bugfix: do not show trashed comments on mails view.
	bugfix: member tree filter not working properly in some cases.
	bugfix: revision number in file view header shows the number including trashed revisions
	bugfix: show more on users selector not working.
	
	Since 3.0.2
	----------------
	feature: Choose if you want to exclude a client or project from automatic status changes.
	feature: Added custom properties to choose a default status when creating a new project or client. 
	feature: When creating a new client, you can now choose if its a company, a contact, or nothing. 
	
	bugfix: Pending factor removed from automated status formulas. 
	bugfix: Dimension Members with no creation application log were not displayed on the Dimension member list in the Administration panel. 
	bugfix: Contact emails are not being displayed on the suggested emails. 
	bugfix: Dimension member selectors were not functioning on the contacts module. 
	bugfix: Object members were not being displayed correctly, and when more than possible to display were added, "and 1 more" was not displayed.
	bugfix: cannot set permissions for users with the same user type to a project.
		
	
	Since 3.0.1
	----------------
	bugfix: after removing a member from a task, refresh member status.
	bugfix: after ading a task, refresh member status.
	bugfix: after editing status formulas, refresh all members statuses.
	bugfix: add billing view.
	bugfix: performance problem displaying contacts birthday
	
	feature: push tasks dates.
	
	Since 3.0
	----------------
	bugfix: members permissions on breadcrumbs .
	bugfix: located under selector on add tags view.
	bugfix: custom properties with multiple values are not saving properly.
	bugfix: files edit is not validating fields, and causing transaction rollback.
	bugfix: error editing web document.
	bugfix: undefined variable on filescontroller
	bugfix: check mail function make a lot of work if the last mail on the system is not on the server.
	bugfix: templates sub tasks.
	bugfix: templates view.
	bugfix: task titles on task list.
	bugfix: mails view.
	bugfix: in member permissions, don't allow to modify permissions of superior users
	
	performance: download imap mails function.
	
	
	Since 3.0-rc
	----------------
	bugfix: Enabled_dimensions where not inserted correctly by plugins installation.
	bugfix: When uploading a file, blue button moves left and then returns to its original place.
	bugfix: Logo is not clickable.
	bugfix: When modal form is rendered and controller sends an error, screen is masked anyways.
	bugfix: Don't show radio buttons to delete/write if role cannot delete or write (e.g.: collaboratos cannot delete som object types).
	bugifx: Collaborators and guests should not have root permissions.
	
	Since 3.0-beta
	----------------
	feature: Check max permissions per user role when adding/editing permissions.
	bugfix: Do not show active context members on activity widget breadcrumbs.
	bugfix: Activity widget says that some users have been unsubscribed.
			
	Since 2.7.1.9
	----------------
	feature: Several improvements in user interface, experience and looks.
	feature: New ���Getting Started Wizard���.
	feature: New workspaces selector.
	feature: Improved user creation and management.
	feature: Improved Task  Management.
			
	Since 2.7.1.8
	----------------
	feature: Allow to configure if parent permissions are inherited when creating a new workspace, client or project.
	
	Since 2.7.1.7
	----------------
	bugfix: Active context member info widget reactivated.
	bugfix: Permissions not saved when applying to all submembers and permissions tree is collapsed.
	
	Since 2.7.1.6
	----------------
	bugfix: do not show trashed emails from other accounts.
	bugfix: when adding a new member, inherit parent permissions.
	bugfix: When saving permissions for a workspace, client or project, the mandatory dimensions were not being analyzed.
	
	Since 2.7.1.5
	----------------
	bugfix: 'Unexpected token' error in tasks list.
	bugfix: 'after_contact_view' hook was lost when contact view was changed.
	
	Since 2.7.1.4
	----------------
	bugfix: task list actions
	bugfix: missing config option auto_classify_attachments
	
	Since 2.7.1.3
	----------------
	bugfix: repeating events not displayed correctly
	bugfix: checkmail function from cron fail if the mail have attachments
	
	Since 2.7.1.2
	----------------
	feature: Use "wkhtmltopdf" to convert custom reports to pdf
	
	Since 2.7.1.1
	----------------
	bugfix: When uploading a file greater than max size, the file is created but without content.
	bugfix: If error occurs when uploading file always shows the same message 'Error uploading file'.
	
	Since 2.7.1
	----------------
	bugfix: removed "fo_" prefix from queries.
	
	
	Since 2.7.1-beta
	----------------
	bugfix: quick edit task timezone errors.
	bugfix: performance improvement on object listing query.
	bugfix: performance improvement on mail listing query.
	bugfix: superadmins should not see unclassified emails of other people.
	bugfix: when selecting a workspace after searching it, it is not focused
	
	language: languages fr_ca, ar_sa updated
	

	Since 2.7.0.4
	----------------
	feature: Workspaces, tags, etc. selectors upgraded.
	
	bugfix: Performance upgrade in initial loading.
	bugfix: When editing permissions don't load information for hidden components, load data when expanding them.
	bugfix: date custom properties does not allow blank values, and cannot be deleted.
	bugfix: in custom reports remove text styles and leave new lines.
	bugfix: enters in timeslots descriptions are skipped in time report and task view.
	bugfix: after add new members on the tree select them.
	bugfix: can not print monthly calendar.
	bugfix: expenses and objectives installer: new users permissions.

	language: translation tool upgrade: Link to checklang tool to list missing langs foreach language file.
	language: translation tool upgrade: Added search functionality (searchs in keys and values)
	
	Since 2.7.0.3
	----------------
	bugfix: Performance upgrade in initial loading.
	bugfix: Workspaces, tags, etc. selectors upgraded.
	bugfix: When editing permissions don't load information for hidden components, load data when expanding them.
	
	Since 2.7.0.2
	----------------
	bugfix: email components in contact and company edition
	bugfix: show all emails in contact and company views
	bugfix: required list custom properties with default value are not filled with it when creating a new object
	
	Since 2.7.0.1
	----------------
	bugfix: date format selector saves incorrect date format when selecting m.d.Y or m-d-Y
	
	
	Since 2.7
	----------------
	bugfix: UTF-8 issue in tasks descriptions in the tasks widget.
	bugfix: Transactions reorganized when saving permissions.
	bugfix: Required custom properties control fixed.
	
	
	Since 2.7-rc
	----------------
	bugfix: Previous XSS fixes broke some post parameters (e.g. contact form).
	bugfix: If using a custom favicon, it is not used when printing an email.
	bugfix: Don't show "expiration_time" attribute in files reports (deprecated column).
	bugfix: Task descriptions cannot save character "|".
	bugfix: When composing an email and changing "from" sometimes the signature does  not refresh correctly.
	bugfix: Filtering tasks list by date, inputing the date manually does not filter the list.
	bugfix: Task descriptions overflow fixed.
	bugfix: Cannot import ical in a workspace if user doesn't have permissions in root.
	bugfix: Cannot print calendar month view.
	bugfix: Cannot export total tasks times report to csv.
	
	language: fr_ca updated.
	
	Since 2.7-beta
	----------------
	bugfix: performance when changing permissions.
	bugfix: XSS prevention fixes.
	bugfix: login screen broken in IE 7.
	bugfix: Viewing email with images like <img src="data:image/png;base64......."/> does not show images
	bugfix: Composing emails with images like <img src="data:image/png;base64......."/> are blank after sending them.
	
	
	Since 2.6.3
	----------------
	bugfix: Template tasks names overflow.
	bugfix: Calendar views event overlapping fixes.
	bugfix: If tmp/logo_empresa.png exists, then use it in all notifications.
	bugfix: When configuring widgets, changing to other section resets options.
	bugfix: When creating admins permissions were not saved in background and not using cache for dimension object type contents.
	bugfix: Permission issue when disabling a tab and then removing permissions for some workspaces.
	bugfix: Tasks "assign to" was not filtering by task context.
	bugfix: Performance in get mails query.
	bugfix: Remove "related to" option when adding a user.
	bugfix: Clients and projects widgets fixes.
	bugfix: Task quick-edit workspace, clients and projects selectors fixed.
	bugfix: Task drag & drop error after deleting a task.
	bugfix: Ical feed for calendar does not work.
	bugfix: Superadmin not viewing all elements.
	bugfix: Cannot upload file in Internet Explorer when a filtering by a member.
	bugfix: When uploading files, use a generated name instead of filename to save it in tmp.
	
	feature: Added a permissions cache to improve left panel trees load times. 
	feature: Initial loading performance improved.
	feature: Performance improved in "People widget" in overview tab.
	feature: Performance improved in "Activity widget" in overview tab.
	feature: Add flag by object for sharing table healing process.
	feature: Gantt full screen option.
	feature: Toolbar option to show/hide birthdays in caledar views.
	feature: Upgrade plugins after upgrading core using html interface (only if 'exec' php function is available).
	feature: Config option to allow (or not) new users to be granted with permissions over all existent workspaces depending on the user type.
	feature: Don't show popup when adding a new member.
	feature: Contact form upgraded.
	feature: Contact card upgraded.
	fetaure: Config option to decide if mail attachments are classified with the email.
	feature: Config option to show expanded/collapsed the attachments in mail view
	feature: Add 'name' field to telephone numbers (to support assistant name and assistant number)
	
	language: Languages fr_fr (French France) and fr_ca (French Canada) improved.
	language: Added language en_gb (English U.K.).
	
	
	Since 2.6.3-beta
	----------------
	bugfix: send email inline images attached instead of the link to tmp folder.
	bugfix: superadmin not viewing all elements.
	bugfix: task drag & drop error after deleting a task.
	bugfix: ical feed does not work.
	bugfix: cannot upload file in IE when a filtering by a member.
	bugfix: when uploading files, use a generated name instead of filename to save it in tmp.
	bugfix: send email inline images attached instead of the link to tmp folder.
	bugfix: task drag & drop error when grouping by milestone.
	bugfix: view day in calendar is not showing some tasks names.
	bugfix: line break in timeslots comments.
	bugfix: quick add task in milestone.
	bugfix: time report does not list results if a cp condition is added.
	bugfix: day calendar view show all task duration.
	bugfix: time reports grouping by clients & projects fixed.
	bugfix: time report - missing langs in group names when grouping by task.
	bugfix: edit person and company notes text box size.
	bugfix: when upload a new revision of a file keep old linked objects.
	bugfix: dont allow guest users to see other guest users.
	
	
	Since 2.6.2.1
	----------------
	bugfix: download actions were logged as 'read' instead of 'download'.

	feature: user group config handler	
	feature: permissions save in background process updated.
	feature: hook to override action veiw in ajax requests.
	feature: hook to edit application logs when saving them.
	feature: hook to edit template properties when instantiating.
	feature: allow to invoke hooks with dynamic list of parameters.
	
	Since 2.6.2
	----------------
	feature: save permissions in background upgraded.
	bugfix: custom report export to csv.
	bugfix: gantt undefined alert when a link task target is not present.
	bugfix: gmt errors in calendar when displaying tasks or dragging tasks.
	bugfix: when upload a new revision of a file keep old linked objects.
	bugfix: edit person and company notes text box size.
	bugfix: members selectors combos breadcrumbs errors.
	bugfix: milestones gmt problems in calendar view date.
	bugfix: don't use gmt in task dates if config option Use time in task dates is  disabled.
	bugfix: day calendar view show all task duration.
	bugfix: quick add task in milestone.
	bugfix: line break in timeslots comments.

	Since 2.6.2-rc
	----------------
	bugfix: javascript error when editing a completed task.
	bugfix: migration from feng version 1 to feng version 2
	fetaure: timeslot custom reports, including task columns
	
	Since 2.6.2-beta
	----------------
	language: Swedish
	bugfix: error when converting odt or docx to text with tabs
	
	Since 2.6.1.6
	----------------
	feature: gantt - new GUI
	feature: gantt - zooming by day, week, month, year
	feature: gantt - exporting to pdf
	feature: gantt - exporting to png
	feature: gantt - editable progress
	feature: gantt - task completition
	feature: gantt - groups show their entire progress
	feature: gantt - major performance improvements
	feature: gantt - being able to collapse or expand all groups
	feature: gantt - being able to show or not the tasks names
	bugfix: link objects list wrong total
	bugfix: sometimes new mail no signature shown
		
	Since 2.6.1.5
	----------------
	bugfix: cannot save client permissions

	Since 2.6.1.4
	----------------
	bugfix: error when converting odt or docx to text with tabs

	Since 2.6.1.3
	----------------
	bugfix: method_exists not working properly depending on php version if using a class method. 
	
	Since 2.6.1.2
	----------------
	bugfix: cannot download csv report in Internet Explorer.
	bugfix: time report date range shows the first day of actual month if "last month" is selected (data is correct, only date range info is showing this error).
	bugfix: check if mail plugin is installed before making queries in mail tables
	feature: allow to choose if "estimated time" column is shown in time report

	Since 2.6.1.1
	----------------
	bugfix: refresh sharing table when root permissions are updated.
	bugfix: dont call mb_string library functions if mb_string is not installed
	
	Since 2.6.1
	----------------
	bugfix: permissions in attachments when not classified
	feature: add 'workspaces' column to cvs export of total tasks times report

	Since 2.6.0.2
	----------------
	bugfix: disabled users are not loaded in tasks list.
	bugfix: add clients and projects column to total tasks times csv export.
		
	Since 2.6.0.1
	----------------
	bugfix: gantt crashes in ie.
	bugfix: function add_file_form_multi() doesn't exist, it should be add_file_from_multi().
	bugfix: default contact config options with wrong values after upgrade.
	
	Since 2.6
	----------------
	bugfix: task dependencies in templates did not show up correctly.
	bugfix: in calendar view the time line has a gmt error.
	bugfix: task quick add/edit alignment.
	bugfix: importing a contact csv does not send context when sending the file and permission query fails.
	bugfix: js error in save as button for documents, when comments are mandatory.
	
	Since 2.6-rc
	----------------
	bugfix: report date parameters are not formatted correctly the first time if they are not saved in contact config options
	
	
	Since 2.6-beta
	----------------
	
	bugfix: after contact csv inport, msg says "Company NULL" if contact has no comapany
	bugfix: templates import tasks, milestones (and several templates bugs)
	bugfix: activity widget fixed
	bugfix: email plugin upgrade script fixed
	bugfix: missing langs in advanced reports (es_es, es_la)
	bugfix: don't add the object to personal workspace if no workspace is selected
	bugfix: city is not imported if contact has no address when importing companies or contacts from csv.
	bugfix: if config option 'checkout_for_editing_online' is enabled don't show button to upload new revision when editing a document.
	bugfix: cannot link objects when editing tasks
	bugfix: after adding previous tasks in a template task the icon is broken
	bugfix: several fixes in templates and tasks dependencies.
	bugfix: do not notify my self quick add/edit tasks
	bugfix: if gantt plugin is installed there are restrictions with start date in task dependencies.
	bugfix: delete task dependencies after delete task.
	bugfix: don't show trashed or archived milestones in milestone combo.
	bugfix: performance in activity widget for members query
	bugfix: company name in person view is not a link to the company

	
	Since 2.5.1.5
	----------------
	
	feature: easier and faster way to classify tasks from the quick add/edit view
	feature: easier way to manage timeslots
	feature: multiple tasks drag and drop
	feature: custom properties can be added for Projects, Clients and other dimension members in the Professional Edition
	feature: Projects, Clients (and other dimensions) can now be colour coded
	feature: IMAP support has been developed
	feature: listings now show may show the breadcrumbs (workspace, client, project, etc.) in separate columns
	feature: templates now support subscribing users to the tasks, and take them into account
	feature: config option to select which milestones are shown in milestone selectors 
	 
	perfomance: cleaner load process
	performance: improved for most widgets
	 
	translations update: fr_fr, fr_ca, nb_no, tr_tr
	 
	bugfix: cannot print report when report title has a "'", it exports to csv
	bugfix: when saving clients using the complete form and permissions are saved in background the creator user is not associated
	bugfix: when note description is html and has no enters text overflow is visible and goes over other html, must break word.
	bugfix: search results shows html content and must show only text
	bugfix: when viewing tasks or notes with big font, the lines overlap
	bugfix: when deleting a Google calendar, events and invitations were not deleted correctly
	bugfix: import deleted external events (and try to delete in server again)
	bugfix: editing a user from contact tab redirected to edit company
	bugfix: permissions group were not working correctly for contacts
	bugfix: import companies from .csv file error
	bugfix: marking as read a document from an email resulted in an error
	bugfix: mails pagination bug
	bugfix: not all config options were set
	bugfix: show feng version for all users
	bugfix: tasks subscribers were receiving notifications even though they were not supposed to (check was disabled)
	bugfix: clients list fixes, view all links fixed in widgets
	bugfix: client, project, folder and workspace widgets fixed
	bugfix: client widget doesn't show all clients
	bugfix: in contacts tab users are not shown by default
	bugfix: assignees comboboxes selects and replace the input when it finds any match
	bugfix: dimension config handler alignments
	bugfix: when a document is blocked, it was not showing the right username
	bugfix: if a link href contains character "#" it is cut if sent in an email or in the html description of a task (e.g. feng wiki links)
	bugfix: other users can edit file properties of a checked out file
	bugfix: workspace quick add does not inherit color
	bugfix: tasks disappear when grouping by workspace and adding a new workspace
	bugfix: dont hide selected members in breadcrumbs
	bugfix: when attaching files to send mails
	bugfix: custom properties values are 256 chars length, now they have been changed to Text
	bugfix: several "undefined variable" fixes
	bugfix: missing lang when classifying emails (for "es_es" languages)
	bugfix: non utf-8 characters in custom reports produces an error when rendering
	bugfix: some folder icons are shown as '?' in activity widget.
	bugfix: file quick-add doesn't change the file modification date if it is a new revision. 
	bugfix: file quick-add doesn't create a log entry. 
	bugfix: file quick-add doesn't notify subscribers.
	bugfix: check if exec is available before using it in FileRepository
	bugfix: when loading lots of tasks and changing workspace before loading ends, two error messages appear.
	bugfix: after deleting a folder the top breadcrumb is not refreshed correctly
	
	
	Since 2.5.1.4
	----------------
	bugfix: Import companies from .csv file error
	bugfix: can't add subscribers in template task.
	
	
	Since 2.5.1.3
	----------------
	bugfix: when viewing a note or task and text is html and has no enters, text overflow is visible and goes over other html, it should break word.
	bugfix: search results shows html content and should show only text
	bugfix: application header default color changed to white
	bugfix: member tree and general breadcrumb are not reloaded correctly when adding a person
	bugfix: if a new member is added it is not added in real time to member selectors
	bugfix: in contacts tab users are not shown by default
	bugfix: performance in workspace, clients, projects and folders widgets
	bugfix: use 'exec' function only if it is enabled in the environment
	
	usability: put object breadcrumbs to the right
	
	language update: fr_ca
	
	Since 2.5.1.2
	----------------
	bugfix: When edit timeslots show all members.
	bugfix: Error occurs sometimes when attaching files to send mails.
	bugfix: Several "undefined variable" fixes.
	bugfix: Non utf-8 characters in custom reports produces an error when rendering.
	bugfix: After deleting a folder the top breadcrumb is not refreshed correctly.
	bugfix: Missing langs in document report columns.
	bugfix: When classifying emails a new revision is generated always for every attachment.
	bugfix: Attribute title not escaped in reports when renedering object links.
	bugfix: Workspace selectors are not preloaded in time panel.
	bugfix: add web document  fail if have http in the url.
	bugfix: add web document  fail if have http in the url.
	bugfix: search query problem with members.
	
	performance: When classifying emails classify attachments in background.
	
	Since 2.5.1.1
	----------------
	bugfix: gantt subtasks of subtasks not displayed.
	bugfix: mysql error when edit mail account.
	bugfix: search on dimensions doesn't work with files. 
	bugfix: getDaysLate and getDayLeft tasks functions fixed.
	bugfix: delete repeat number of times if "This Day Only" is selected on repeatinig tasks.
	bugfix: fo_ prefix table hardcoded.
	bugfix: templates errors when edit tasks.
	bugfix: milestones problems on template task.
	bugfix: can't add milestones in template task from edit.
	bugfix: mail plugin update failed if column conversation_last exists.
	bugfix: task dates are not shown with user timezone in custom reports.
	bugfix: logged user is not subscribed when uploading a file in the object picker (linked objects).
	bugfix: tasks widget shows tasks 1 day earlier for some user timezones > 0.
	bugfix: reload view (to show checkout information) when download and checkout in the same request.
	bugfix: in extjs when reloading combos.
	bugfix: if no member selected, in total tasks times report parameters, show all users.
	bugfix: separate transactions for saving user permissions.
	bugfix: multiple file upload enabled when uploading a new version.
	
	Since 2.5.1
	----------------
	bugfix: Cannot edit unclassified timeslot.
	bugfix: Bug when classifying email and attachments has no name.
	bugfix: Add weblinks in documents panel.
	bugfix: French langs fixed.
	bugfix: Total task times performance improvements.
	bugfix: Message changed when trying to add objects in root with no permissions.
	bugfix: If member name contains "'" advanced reports are broken.
	bugfix: Allow managers and administrators to add reports in root even if the don't have permissions to add reports in root.
	bugfix: Wrong date on activity widget if there are several changes on an object.
	bugfix: Selecting contact in contact tab must not filter by the contact dimension member.
	bugfix: Users and contact have the same icon.
	bugfix: Breadcrumbs are not shown for users.
	bugfix: Encoding fixed when saving files to filesystem.
	bugfix: When changing dimension member parent, breadcrumbs are not reloaded correctly.
	bugfix: Performance improvements in activity widget.
	bugfix: Cannot delete task description from quick-edit.
	
	feature: Add 'can_manage_tasks' permission to executive users.
	feature: Use "on duplicate key update" in DataObject insert queries, if specified.
	
	
	Since 2.5.1-rc
	----------------
	bugfix: can't view object link in notification when an email does not have a subject.
	
	
	Since 2.5.1-beta
	----------------
	bugfix: cannot add milestones in templates
	bugfix: when adding template, after adding milestone cannot select it when adding a task
	
	
	Since 2.5.0.6
	----------------
	bugfix: Template view broken by single quote in property name.
	bugfix: when edit a template if have milestones you can't see tasks.
	bugfix: don't show members that cannot be used in member selector.
	
	feature: Dashboards can be customized by user, and so can their widgets.
	
	Since 2.5.0.5
	----------------
	bugfix: Tasks grouping by dimension fixed.
	
	
	Since 2.5.0.4
	----------------
	performance: Issue when loading persons dim.
	bugfix: Imap folders are not saved when editing an email account.
	bugfix: Cannot unclassify mails from classify form.
	bugfix: Emessage not shown when inputing dates with incorrect format.
	bugfix: Add start date to task view.
	bugfix: Get tasks by range query does not include logged user's timezone.
	bugfix: In task complete edition form, assigned to are not displayed correctly.
	bugfix: Issue in include myself in document notifications.
	bugfix: Set db charset when reconnecting in abstract db adapter.
	
	
	Since 2.5.0.3
	----------------
	bugfix: Add attachments column in queued_emails in upgrade scripts.
	bugfix: Set db charset when reconnecting in abstract db adapter.
	
	
	Since 2.5.0.2
	----------------
	bugfix: Render member selectors with preloaded member info.
	bugfix: Order by name doesn't work on object list.
	bugfix: People widget only display users.
	
	
	Since 2.5.0.1
	----------------
	bugfix: on mysql 5.6 have_innodb variable is deprecated
	
	
	Since 2.5
	----------------
	
	feature: Allow to configure dashboard widget position and order for each user.
	feature: Allow to configure default dashboard widget position and order for all users.
	feature: Comments dashboard widget.
	feature: Email dashboard widget.
	feature: choose to filter calendar widget or not.
	feature: choose the user to filter the tasks widget.
	
	bugfix: when add a timeslot by clock on tasks update the percent complete.
	bugfix: if a file doesn't have revision when classify create one.
	bugfix: several minor fixes of undefined variables, missing langs, etc.
	bugfix: when disabling or reactivating users from company view, users list is not reloaded.
	bugfix: member selector displayed wrong data
	bugfix: on task add/edit view, assignee combo displayed wrong data
	bugfix: subscribers and invited people were not shown correctly
	bugfix: encoding when receiving emails
	bugfix: when editing a classified timeslot, its context was not shown
	bugfix: in file upload, the name is not changed if a new name is entered
	bugfix: missing langs and sql changes for email user config options
	
	
	Since 2.5-rc
	----------------
	
	bugfix: general search form submitted by enter key doesn't work in Google Chrome
	bugfix: links are now saved as such when using WYSIWYG
	bugfix: primary-breadcrumb show exact context
	bugfix: mysql transaction problem when sending emails without using a cronjob
	bugfix: when making a new installation, users were not shown by default
	
	
	Since 2.5-beta
	----------------
	
	bugfix: if a file doesn't have a revision, when classifying an email create one.
    bugfix: when adding a timeslot by clock on tasks, task progress bar was not updated correctly.
    bugfix: fixed custom reports using boolean conditions in false.
    bugfix: problems with paging on the overview list.
    bugfix: on activity widget, when clicking on a member, change dimension.
	
	Since 2.4.0.6
	----------------
	
	plugin: new Advanced Reports plugin for the Professional and Enterprise Edition

	feature: multiple files upload support has been added
	feature: cleaner reports tab
	feature: time reports can be filtered by custom properties
	feature: document notification improvements: allow notifying oneself, changing the default subject, choose whether to attach the document to its notification
	fetaure: unclassify objects dragging them to the "view all" node of a dimension tree
	feature: file revisions are easier to access
	feature: new button to add a template from the task tab
	
	security: XSS issue prevention fixed in login form
	
	performance: mail autocomplete, when contacts > 1000 don't load them all, make a query filtering when user begins to type (after 3 chars)
	performance: subscribers are rendered using ajax in add/edit forms
	performance: member selectors loaded using ajax in add/edit forms 
	performance: render subscribers queries optimized (index added) 
	performance: add index by member_id in contact_member_permissions in upgrade scripts
	perfomance: permissions checks in the advanced search take less time so the search is faster
	perfomance: permissions checks in the general search take less time so the search is faster
	perfomance: context checks in the general search take less time so the search is faster
	perfomance: when listing objects, permissions check query has been improved
	
	bugfix: remember columns selection in mail list
	bugfix: set 777 permission to autoloader to prevent future issues
	bugfix: drag and drop on mails panel don't uncheck selected mails
	bugfix: when instantiating a template, create logs for its tasks
	bugfix: on contact list, ordering by email failed
	bugfix: mysql transaction problem when getting emails
	bugfix: on template view, refresh view after editing a template task
	bugfix: when selecting a parent task in template tasks, only show template tasks from that template
	bugfix: repeating tasks don't allow selecting which days to repeat unless using specific repetition
	bugfix: attachments that start with "#" are not sent
	bugfix: max-height for attachments div when composing an email
	bugfix: undefined variable in message controller
	bugfix: when loading advanced search view, displays search results for empty string search 
	bugfix: when using Google Chrome, advanced search submit button breaks
	bugfix: hardcoded table prefix "fo_" in 2.4 upgrade script
	bugfix: when creating a superadmin, system permissions are not set
	bugfix: being able to order by custom property on notes list
	bugfix: search button disabled until results get displayed
	bugfix: mails in outbox alert show archived emails, it should not
	bugfix: don't send reminders for trashed or archived objects
	bugfix: date custom properties default value does not use user's timezones
	bugfix: when editing an email account do not synchronize folders with the mail server until user chooses to do it
	bugfix: email error reporting hook, for log errors on notifications delivery
	bugfix: when instantiating a template, do not display companies in assign combo unless config option allows so
	bugfix: fails when adding subtasks from task view
	bugfix: sending notifications from inside create log function, breaks the mysql transactions
	
	
	Since 2.4.0.5
	----------------
	
	bugfix: Don't send notification when add mail.
	
	
	Since 2.4.0.4
	----------------
	
	bugfix: Deprecated functions usage.
	bugfix: Emtpy trash can was using a deprecated function with performance issues.
	bugfix: Missing parameters in function invocation.
	
	
	Since 2.4.0.3
	----------------
	
	bugfix: can't delete template task, permission denied.
	
	
	Since 2.4.0.2
	----------------
	
	bugfix: langs customer_folder and project_folder.
	bugfix: can't add contacts from mail.
	bugfix: on activity widget move action don't display.
	bugfix: when create user, notifications break mysql transaction.
	
	
	Since 2.4.0.1
	----------------
	
	bugfix: cron process to emtpy trash can does not delete members asociated to contacts.
	
	
	Since 2.4.0
	----------------
	
	bugfix: tab order fix in quick add task; 
	bugfix: issue when create a subtask from task view;
	
	
	Since 2.4-rc
	----------------
	
	fetaure: error message improved when upload limit is reached.
 
	bugfix: on gantt, names of the tasks were not displayed completely.
	bugfix: on gantt, the time estimation for tasks was not displayed correctly.
	bugfix: date custom properties default value does not use user's timezone.
	bugfix: on people widget add user combo is not ordered by name.
	bugfix: on activity widget dates have gmt errors.
	bugfix: general search allways search for empty string.
	bugfix: url files are not saved correctly when url is not absolute.
	bugfix: imap fetch fixed when last email does not exists in server.
	bugfix: only invite automatically the "filtered user" when adding a new event, not when editing an existing one.
	bugfix: variable member_deleted uninitialized in a cycle, maintains the value of previous iterations and fills the log warnings.
	bugfix: don't display group-mailer button if user doesn't have an email account.
	bugfix: allow mail rules for all incoming messages, useful for autoreplies.
	bugfix: the invitations of the events created on google calendar will have the same special ID of the event.
		
	
	Since 2.4-beta
	----------------
	
	feature: alert users if they have mails in the outbox
	feature: contact custom reports - added columns for address, phones, webpages and im.
	feature: display time estimation in time reports when grouping by tasks
	feature: config option to add default permissions to users when creating a member.
	
	system: upgrade Swift Mailer from version 4.0.6 to 5.0.1, this improves and solves some issues when sending emails with exchange servers
	
	bugfix: on user login when save timezone don't change the update_on value
	bugfix: solved an issue when editing a repetitive task and changing its previous repetition value
	bugfix: solved when editing a template task can't remove a dimension member
	bugfix: solved using repeating tasks when applying a template
	bugfix: on tasks and timeslots reports, if grouped by task it diplay milestones
	bugfix: allow the creation of templates in the root (View all)
	bugfix: Users are now shown by default in the People tab.
	bugfix: when printing the task list view, tasks now display their progress and estimation
	bugfix: on general search prevent multiple form submit.
	
	
	Since 2.3.2.1
	----------------
	
	feature: templates have been greatly improved: they have changed completely for good!
	feature: remember selection on total task execution time report
	feature: when sending an email, if a word containing attach is found and no attachment if found, it triggers an alert.
	feature: new user config option to set how many members are shown in breadcrumbs
	feature: update plugins after running upgrade from console.
	feature: add root permission when creating executive or superior users.
	feature: contact edit form has been improved
	
	bugfix: when uploading avatars, if it is .png and its size is smaller than 128x128 the image is not resized
	bugfix: when sending an mail, the sender is now subscribed to it
	bugfix: when adding a file from an email attachment, its now set to be created by the account owner
	bugfix: reporting pagination fixed 
	bugfix: custom reports, csv and pdf export only exports the active page..now it exports everything!
	bugfix: don't collapse task group after performing an action to the task when group is expanded.
	bugfix: email parsing removes enters and some emails were not shown correctly
	bugfix: people widget in french used to cause a syntax error
	bugfix: don't classify email in account's member if conversation is already classified.
	bugfix: task filtering by user has been improved: it loads faster and more accurate
	bugfix: the users selectbox for assignees has been improved: it loads faster and more accurate
	bugfix: check for "can manage contacts" in system permissions if column exists
	bugfix: email parsing does not fetch addresses when they are separated by semicolon
	bugfix: tasks assigned to filter doesn't filter correctly when logged user is an internal collaborator and users 	can add objects without classifying them.
	bugfix: search result pagination issue
	bugfix: search results ordered by date again
	bugfix: add to searchable objects failed for some emails
	bugfix: custom properties migration fix
	bugfix: feng 1 to feng 2 upgrade improved
	bugfix: style fixes in administration tabs
	bugfix: checkbox in contact tab now is working properly. initially it does not show the users
	bugfix: google calendar sync issue for events with over 100 chars has been solved
	bugfix: contact csv export fixed: when no contact is selected => export all contact csv export fixed
	bugfix: some undefined variables have been defined
	bugfix: some translations that were missing were added
	
	security: remove xss from request parameters
	
	performance: search engine has been greatly improved
	
	other: the search button is disabled until returns the search result
	other: when upgrading to 2.4 the completed tasks from feng 1 will change to 100% in completed percentage
	
	
	Since 2.3.2
	----------------
	
	bugfix: When creating members, do not assign permissions for all executives (or superior users) if member has a parent.
	
	
	Since 2.3.2-rc2
	----------------
	
	bugfix: Cannot filter overview by tag.
	bugfix: Tasks tooltip in calendar views shows description as html.
	bugfix: Permissions issue when editing and subscribing for non-admins for not classiffied objects.
	
	
	Since 2.3.2-rc
	----------------
	
	bugfix: Show can_manage_billing permission.
	bugfix: Missing lang on javascript langs. 
	bugfix: Javascript plugin langs are not loaded.
	bugfix: When requesting completed tasks for calendar month view, it does not filter by dates and calendar hangs if there are too much tasks.
	bugfix: Administration / dimensions does not show members for dimensions that don't define permissions.
	bugfix: Permissions fix when email module is not installed.
	bugfix: Company object type name fixed.
	bugfix: Try to reconect to database if not conected when executing a query (if connection is lost while performing other tasks).
	bugfix: When users cannot see other user's tasks they can view them using the search.
	bugfix: Group permissions not applied in assigned to combo (when adding or editing tasks).
	bugfix: Minor bugfixes in 1.7 -> 2.x upgrade.
	bugfix: Activity widget: logs for members (workspaces, etc.) were not displayed.
	bugfix: General search sql query improved.
	bugfix: Don't include context in the user edited notification.
	bugfix: Don't show worked hours if user doesn't have permissions for it.
	bugfix: Don't send archived mails.
	
	feature: Only administrators can change system permissions.
	feature: Users can change permissions of users of the same type (only dimension member permissions).
	feature: Set permissions to executive, manager and admins when creating a new member.
	
	
	Since 2.3.2-beta
	----------------
	
	bugfix: Archiving a submember does not archive its objects.
	bugfix: Error 500 when adding group.
	bugfix: Installer fixes.
	bugfix: Modified the insert in read objects for emails.
	bugfix: Minor bugfixes in document listing.
	bugfix: Sql error when $selected_columns ins an empty array in ContentDataObjects::listing() function
	bugfix: root permissions not set when installing new feng office.
	bugfix: Person report fixed when displaying email field.
	bugfix: contacts are always created when sending mails.
	bugfix: Tasks list milestone grouping fixed.
	
	preformance: Search query improved.
	performance: Insert/delete into sharing table 500 objects x query when saving user permissions.
	
	
	Since 2.3.1
	----------------
	
	bugfix: When ordering tasks and subtasks and grouping by some criterias.
	bugfix: ul and ol (list) on task description doesn't show number or bullet.
	bugfix: Don't update email filter options when reloading email list if they are not modified.
	bugfix: Eail polling only when browser tab is active.
	bugfix: Wait time interval to check an email account.
	bugfix: Session managment fix.
	bugfix: Workspaces widget to the left.
	bugfix: When creating workspace it is not selected if it isn't a root workspace.
	bugfix: Update objects when linking to others, from user_config_option to config_option.
	bugfix: Calendar dalily view puts other days tasks.
	bugfix: Fixes of undefined variables logged in cache/log.php.
	bugfix: Call popup reminders only from active browser tab.
	bugfix: Format date funcions did not use config option for format.
	bugfix: Username is not remembered when creating a new user.
	bugfix: People widget is not displayed.
	bugfix: When unzipping a file the name has the url first.
	bugfix: On Trashed Objects breadcrumbs are not displayed if members are archived.
	bugfix: When add a timespan on a task was always taking logged user id for billing.
	bugfix: Time zone bug on list task in a range of dates.
	bugfix: Last login not saved into data base.
	bugfix: Google calendar synchronization bug fixes.
	
	performance: Save permissions asyncronically when saving member to improve performance.
	
	feature: New login form.
	feature: Field "Is user" added for people custom reports.
	feature: Users permissions can be configured to leave objects unclassified and choose the users that can read/write/delete these objects.
	feature: Tasks view improved.
	feature: People widget improved.
	feature: Improved member panels loading.
	
	language: Several language updates.	
	
	Since 2.3.1-beta
	----------------
	feature: View Contacts direct url if config option is enabled.
	feature: The system now remembers whether you are displaying the Overview as dashboard or as list.
	    
	bugfix: Duplicate key inserting read objects solved.
	bugfix: When writing an email from email tab, bcc was always displayed.
	bugfix: Events report end date did not show the time, now they do.
	bugfix: Objects history was not displaying linked objects logs.
	bugfix: On task list when you filter by a range of dates permissions filtering were not applied.
	bugfix: Exchange compatibility option has been removed.
	bugfix: When listing tasks timezones were not being taken into account.
	bugfix: Last login field was not being updated.
	bugfix: Gantt chart was showing some tasks as completed when their percentage was over 100% and they were not completed
	bugfix: When adding a timeslot for someone else within a task, the billing value was not being taken into account
	bugfix: Gantt chart tasks resizing has been improved 
	
	Since 2.3
	----------------
	feature: In the contact panel you can choose contacts in order to send a group mail
	feature: New user config option, updating an object��������s date when it is linked to another object
	feature: Gantt sub tasks can be out of range of parent task dates.
	feature: Gantt chart and Task List can be filtered by period.
	feature: Comments are now displayed on Activity Widget.
	feature: Gantt Chart now displays tasks with only start or due date
	feature: archive/unarchive dimension members from administration->dimensions.
	feature: when uploading a file with the same name as another that has been uploaded by other user and you don't have permissions over it, don't show as if exists.
	feature: New Projects by default will start with �������good�����? status
	feature: Listing function does not use limit if start parameter is not specified
	feature: When adding a client/project the initial focus is on name
	 
	performance: tasks list performance has been greatly enhanced by loading the descriptions afterwards through Ajax
	performance: when saving members save permissions using an async request.
	 
	bugfix: Users invited to an event can view/edit their invitation on Google Calendars
	bugfix: Editing  e-mail accounts correctly by administrator or user with permissions
	bugfix: Export only visible contacts/companies in contact panel
	bugfix: User e-mail duplication upon creation
	bugfix: Completing  tasks with child tasks error
	bugfix: Contact who trashed a document now shown in history
	bugfix: Worked time was not always displayed.
	bugfix: There were empty logs in the Activity widget
	bugfix: Group by on tasks lists, subtasks displayed in wrong place.
	bugfix: Sort listings by custom properties(contact, document) .
	bugfix: Activity Widget broken on small screens.
	bugfix: Activity Widget time zone issue.
	bugfix: Custom property, escape commas.
	bugfix: Contact custom reports now show their email addresses
	bugfix: Search contacts by phone number, email , im and by address.
	bugfix: add_to_members when no permissions over parent .
	bugfix: Duplicate key when adding emails to searchable objects.
	bugfix: User with permissions to edit account cannot delete unclassified emails.
	bugfix: projects widget does not show projects.
	bugfix: Sql was not using "select distinct" on searchable objects().
	bugfix: add task dependency js error .
	bugfix: ObjectController::list_objects malformed sql error.
	bugfix: Now all users can sync Feng Calendar with Google Calendar.
	bugfix: Google Calendar is no longer trashing old events
	bugfix: Trash fails when mail plugin is not installed
	bugfix: Member selector fixed for IE
	bugfix: Some permissions were not set when adding new member
	bugfix: Creating reports of �������grouping by user�����? and �������members�����? at the same time issue fix
	bugfix: Report not showing correct Date in condition legend
	bugfix: SQL issue in Report fixed
	bugfix: Description not set for all tasks when listing.
	bugfix: Left menu expands after adding first client or project
	
	Since 2.3-rc
	----------------
	
	bugfix: Tasks don't show custom properties when printing one.
	bugfix: Rearranged task name and remove button in template edition to avoid overlapping.
	bugfix: Edit task sometimes opens new tab when saving.
	bugfix: Several report grouping bugfixes.
	bugfix: Advanced search by custom property doesn't find the object.
	bugfix: Classify weblinks by drag & drop does nothing.
	bugfix: Event notifications broken with large user names.
	bugfix: IE crashes when loading member trees.
	bugfix: Bugfix when timeslot has no user.
	bugfix: Export to csv, escaped commas and semicolons, improved time management.
	bugfix: Workspace and tags added for new event and task notifications.
	bugfix: Company logo is not displayed well on IE.
	bugfix: Breadcrumbs for the search results.
	bugfix: Improved task list grouping when grouping by date fields.
	bugfix: Overview calendar widget "view more" link broken.
	bugfix: Show main dashboard when clicking on "View all" of People dimension.
	bugfix: Show "save with new name" button after saving a new document.
	bugfix: Object picker pagination shows wrong total.
	bugfix: Several missing langs fixed (en_us, es_la).
	
	Since 2.3-beta
	----------------
	
	feature: Action prompt after workspace creation.
	feature: Advanced search improved.
	feature: Improved export to csv in total tasks time report.
	feature: People panel, move to trash button (only for companies without contacts and for contacts that are not users).
	feature: People panel, improved filter by type (users, companies and contacts as checkboxes).
	feature: Task and event reminders improved.
	feature: Double clicking a workspace takes you to the workspace edition form.
	feature: Custom reports can be ordered by external columns (e.g. milestones, assigned to, etc).
	feature: add/edit template - can specify milestone for each task.
	feature: Height adjustment document preview.
	feature: New buttons to add workspaces and other objects in dashboard widgets.	
	
	bugfix: When uploading files: detect file type from extension when browser sends 'application/x-unknown-application' as file type.
	bugfix: Add to searchable objects doesn't add special characters correctly.
	bugfix: read_objects insert query reimplementation.
	bugfix: Parent workspace not passed when adding workspace from widget button.
	bugfix: Duplicated config option in upgrade script.
	bugfix: Advanced search: sql security issues fixed.
	bugfix: Render "table custom properties" fixed when object has no values for the property.
	bugfix: Error in activity widget for some comments.
	bugfix: Plugin installer returns 'duplicate key' when executing it twice for the same plugin.
	bugfix: Login layout broken for some languages.
	bugfix: Add milestones for tasks in templates: when editing template milestones combos are unselected.
	bugfix: Prevent user deletion from object listings (dashboard).
	bugfix: Workspaces plugin update 4 -> 5 fixed.
	bugfix: Event reminders don't show event name in popup.
	bugfix: Emails addToSharingTable() fixed.
	bugfix: Custom reports malformed conditions when using boolean fields (e.g. is_company).
	bugfix: Search pagination fixed for advanced search results.
	bugfix: Changing assinged to in tasks edition sometimes does not show the notification checkbox.
	bugfix: Member selector fix when not is_multiple.
	bugfix: sql injection in advanced search.
	bugfix: Cannot upload files if no workspace or tag is selected.
	bugfix: Template instantiation does not puts the objects in original members if instantiation is made with no member selected.
	bugfix: Don't save email if cannot save the email file in the filesystem.
	bugfix: csv export fix when & and enters are present in task names or descriptions.
	bugfix: Tasks status filters fixced.
	bugfix: When the whole mail is an attachment, it is not shown.
	bugfix: Show users and people lists expanded in company view.
	bugfix: Object picker pagination shows wrong total.
	
	language: Several language updates.
	
	
	Since 2.2.4.1
	----------------
	
	bugfix: Add permissions over timeslots, reports and templates for user in user's person member when creating the user.
	bugfix: Assigned to combo does not show users when filering by tag.
	bugfix: First person added not shown in tree.
	bugfix: Add object_id to searchable objects.
	bugfix: Empty trash will try to delete deleted emails.
	bugfix: Trash can shows deleted emails.
	bugfix: Create email account gives permissions to it to other users.
	bugfix: Cannot add user if any dimension is required.
	bugfix: Comments are not added to sharing table.
	
	feature: Config option to enable assign tasks to companies.
	
		
	Since 2.2.4
	----------------
	
	bugfix: Add/edit member form permissions goes down if screen is not wide enough.
	bugfix: Member selector onblur must select one of the list if there is any match and there is at least one character written.
	bugfix: Object picker: do not show object types not allowed for the user in the left panel
	bugfix: D&D classify is allowing to classify in read only members.
	bugfix: Do not show parent members in member selector if user has no permissions over them.
	bugfix: Upgrade 1.7 -> 2.X: give permissions over timeslots, reports and templates in all workspaces where the user can manage tasks.
	bugfix: Non admin users cannot delete timeslots.
	
	feature: Can define required dimension without specifying object types.
	feature: Option to view members in a separate column.
	
	
	Since 2.2.4-beta
	----------------
	
	bugfix: Cannot delete user with no objects associated.
	bugfix: Javascript error when loading and change logo link does not exists.
	bugfix: plugin administration fixes.
	bugfix: Email content parts that come in attachments are not shown.
	bugfix: Tasks edition in gantt chart loses task description.
	bugfix: Adding client or project under another member does not remember selected parent when using quickadd and details button.
	
	feature: More options for tasks edition.
	feature: More options for composing emails.
	
	language: Languages updated: German, French, Japanese, Polski.
	
	
	Since 2.2.3.1
	-------------
	
	bugfix: Cannot add user without password if complex passwords are enabled.
	bugfix: Include ";" as metacharacter for complex password validations.
	bugfix: Member name is username when adding a contact (editing contact fixes member).
	bugfix: Change logo link does not work.
	bugfix: Repetitive tasks fix.
	bugfix: fo_ table prefix hardcoded one time.
	bugfix: Calendar tasks display fixed.
	bugfix: Always check if member can be deleted.
	bugfix: Cannot delete mail account with mails.
	bugfix: Add contact was checking if user has can_manage_security.
	bugfix: Cannot select parent member using checkboxes.
	bugfix: Error 500 in some notifications.
	bugfix: New client/project from overview fixed.
	bugfix: Breadcrumbs only show 2 members x dimension.
	bugfix: Total tasks time reports csv export does not work.
	bugfix: Fix en c������lculo de porcentaje de avance de tareas.
	bugfix: Forwarding or replying mails in German only prints "null".
	bugfix: Function getCustomPropertyByName fixed.
	bugfix: Activity widget popup wider to put all buttons in one line.
	bugfix: Users in assign_to combo are not ordered.
	bugfix: 1.7 -> 2.x upgrade does not create table mail_spam_filters.
	bugfix: Tags are lost when dragging a task to another workspace.
	
	performance: Delete account emails performance and memory usage improvements.
	
	feature: Compose mail get contacts by ajax.
	feature: Custom properties columns in documents tab.
	feature: No breadcrumbs for users in activity widget.
	feature: Ckeditor option added: remove html format.
	
	language: Deutch, Russian, Ukranian, Portuguese and Indonesian language updates.
	language: Several language fixes.
	
	
	Since 2.2.3.1-beta
	------------------
	bugfix: Search in a member does not find file contents.
	bugfix: Click on "search everywhere" does not find file contents.
	bugfix: Groups listed alphabetically in the Administration Panel.
	bugfix: Monthly view calendar print shows empty calendar.
	bugfix: Improvements in performance of overview widgets.
	bugfix: Timeslots are not reclassified reclassifying tasks.
	bugfix: Cannot delete members if it has objects.
	bugfix: Member deletion does not clean all related tables.
	bugfix: Only managers or superior roles can change other user passwords.
	bugfix: Several missing langs and undefined variables warnings clean.
	bugfix: Db error when adding two workspaces with the same name.
	bugfix: Quick add files - all radio buttons can be selected.
	
	system: Russian translations updated.
	
	
	Since 2.2.2
	----------------
	bugfix: Owner company cannot be classified.
	bugfix: Task list group by user fix.
	bugfix: Add pdf and docx files to searchable objects.
	bugfix: js managers bugfixes.
	bugfix: Cannot edit/delete mails from deleted accounts.
	bugfix: Error in tasks reports when ordering by 'order' column.
	bugfix: Fixes in migration from 1.X of custom properties.
	
	usability: Reports can be edited to allow execution in every context.
	usability: Performance improved in tasks list.
	usability: Users are filtered by permissions in 'People' dimension when filtering by a workspace.
	usability: Contacts are filtered in 'People' dimension when filtering by a workspace if they belong to the workspace.
	
	system: Portuguese language updated.
	
	
	Since 2.2.1
	----------------
	bugfix: logged_user fix when classifying attachments
	bugfix: go back instead of redirect when editing file properties.
	bugfix: chmod after mkdir when repository file backend creates directory
	bugfix: Several template instatiation fixes
	bugfix: mail classification bugfix
	bugfix: allow to classify mails in workspaces,tags
	bugfix: administration/users: 10 users per page fix
	bugfix: do not use objects in estimated-worked widget, use raw data for better performance
	bugfix: language fixes
	bugfix: cannot use assigned_to combo when adding tasks in ie
	bugfix: ie compatibility fix in calendar toolbars
	bugfix: enable/disable cron events for calendar export/import when adding/deleting accounts
	bugfix: html tags in task tooltip description at calendar
	bugfix: cvs export prints html tags
	bugfix: users with can_manage_security cannot manage groups
	bugfix: view week calendar views don't show tasks all days if task starts or ends in another week
	bugfix: dont show timeslots of other users if cannot see assigned to other's tasks
	bugfix: ext buttons hechos a lo chancho
	bugfix: patch if not exists function array_fill_keys (para php < 5.2)
	bugfix: break large words in task description
	bugfix: administrator cannot log in to admin panel when asking for credentials
	bugfix: cannot edit file after uploaded from object picker
	bugfix: getTimeValue when 12:XX AM
	bugfix: bugfix in custom reports with boolean conditions on custom properties
	bugfix: admin users paging fix
	bugfix: migration companies comments fix

	
	
	Since 2.2.1-rc
	----------------
	bugfix: Cannot manage plugins if no super admin.
	bugfix: Reports were not grouping unclassified objects.
	bugfix: Reports grouping misses a group.
	bugfix: Fixed findById function in ContentDataObjects.
	bugfix: Fixed Email plugin installation.
	bugfix: Fixed translations for dimension names.
	bugfix: Error with company logo when sending notifications.
	bugfix: Time report fix when selecting custom dates and listing paused timeslots.
	bugfix: Fix when getting plugin's javascript translations.
	
	
	Since 2.2
	----------------
	bugfix: Calendar monthly view bugs with repeating events.
	bugfix: Permissions system fix.
	bugfix: Projects appear in object picker.
	bugfix: language fixes (en_us, es_la, es_es).
	bugfix: Error in calendar when user has timezone=0.
	bugfix: Formatted tasks description and notes content does not shows italics and quotes when viewing.
	bugfix: Compressing files does not create compressed file in the current context.
	bugfix: Sometimes can subscribe users with no permissions to the object.
	bugfix: Activity widget bug with general timeslots.
	bugfix: Error when selecting default workspace in mail account edition.
	bugfix: User types are not transalted.
	bugfix: Prevent double generation of tasks when completing a repetitive task instance (double click on complete link).
	bugfix: CSV export fixes at Total tasks times report.
	
	usability: Create events according the filtered user.
	usability: Config option to show tab icons.
	usability: Config option to enable/disable milestones.
	
	
	Since 2.2-rc
    ----------------
    bugfix: calendar monthly view performance upgrades.
    bugfix: translation tool for plugins fixed.
    bugfix: email html signature puts br tags when composing email.
    bugfix: Person email modification does not work.
    bugfix: Prevent double task completion (when double clicking on complete link).
    bugfix: Fixed company edit link from people tree.
    
	
	Since 2.2-beta
	----------------
	bugfix: several fixes in custom reports display.
	bugfix: custom reports csv/pdf export always show status column.
	bugfix: dashboard activity widget does not control permissions correctly.
	bugfix: dashboard activity widget shows username instead of person complete name.
	bugfix: subworkspace creation does not inherit color.
	bugfix: email autoclassification does not classify attachments.
	bugfix: email view shows wrong "To" value when "To" field is empty or undefined.
	bugfix: unclassified mails allows to subscribe other users.
	bugfix: error when forwarding another user's account emails with attachments.
	bugfix: several fixes in email classification functions.
	bugfix: company comments are not displayed.
	bugfix: dashboard's tasks widget breaks right widgets when scrolling (only in chrome).
	bugfix: permissions check in Administration/Dimensions.
	bugfix: css is being printed in csv exported reports.
	bugfix: error subscribing users when instantiating templates with milestones and subtasks.
	bugfix: don't use $this in static functions.
	bugfix: archiving and unarchiving members is not done in a transaction.
	bugfix: permissions in dimension member selectors.
	bugfix: cannot set task's due date to 12:30 PM, always sets the same time but AM.
	bugfix: tasks drag and drop losses some attributes.
	
	usability: mouseover highlight on member properties/restrictions tables.
	
	
	Since 2.1
	----------------
	bugfix: several fixes in repetitive tasks.
	bugfix: quick add of tasks does not subscribe creator.
	bugfix: google calendar import fixed.
	bugfix: fixed event deletion.
	bugfix: fixed email account sharing.
	bugfix: fixed AM/PM issue when selecting task's dates.
	bugfix: special characters in workspace when adding from quick add.
	bugfix: error 500 in workspaces dashboard.
	bugfix: error when searching emails by "From" field in advanced search.
	bugfix: 1.7 -> 2.x upgrade fixed subtasks.
	bugfix: permissions in user's card.
	bugfix: task's drag and drop edition bugfixes.
	bugfix: task's quick add does not keep the task name when switching to complete edition.
	bugfix: several LDAP integration fixes.
	bugfix: fixed contact phones display in list.
	bugfix: config option descriptions added.
	bugfix: user email is not required.
	bugfix: milestone selector does not show all available milestones.
	bugfix: person email cannot be edited.
	bugfix: disabled users are shown in subscribers and invited people.
	bugfix: permission groups upgrade does not set type.
	bugfix: Javascript problems in IE.
	bugfix: issues with breadcrumbs with special characters.
	bugfix: VCard import/export fixed.
	bugfix: cannot delete workspace with apostrophe.
	bugfix: fixed "enters" issue in tasks description wysisyg editor.
	bugfix: File copy makes two copies.
	bugfix: permissions fixed for submembers.
	bugfix: when updating a file, does not subscribe the updater user.
	bugfix: milestones display diferent dates in milestone view and task list.
	bugfix: "assigned to" filter in tasks does not work properly.
	bugfix: cannot archive dimension members.
	bugfix: cannot archive several tasks at once.

	feature: activity widget.
	feature: new workspace and tag selectors.
	feature: add timeslot entries to application_logs.
	feature: complete parent tasks asks to complete child tasks.

	usability: sort email panel by "to" column.
	usability: changes in advanced search for email fields.
	usability: can change imported calendar names.
	usability: email with attachments classification process upgraded.
	usability: linked objects selector can filter by workspace and tags.

	system: CKEditor updated.
	system: translation module upgraded - translate plugins files.
	system: German, Russian and French languages upgraded.

    
    Since 2.0.0.8
    ----------------
    bugfix: Google Calendar issues solved
	bugfix: 'Executive' users not being able to assign tasks to themseleves at some places
	bugfix: Admins and Superadmins may now unblock bloqued documents
	bugfix: Subscriptions and permissions issues solved
	bugfix: Solved some issues when editing objects
	bugfix: Solved issue when classifying emails and then accesing them
	bugfix: Solved issue when adding timeslots
	bugfix: Assigned to alphabetically ordered
	bugfix: Solved issue when editing email accounts
	bugfix: Custom properties were not appearing in weblinks
	bugfix: Solved issue when sending an email
	bugfix: Solved issue where Milestones were showing wrong data
	bugfix: File revisions were not being deleted
	bugfix: Timeslots were not able to be printed
	bugfix: Issues when retrieving passwords solved
	bugfix: Solved issue when deleting timeslots
	bugfix: Solved some permissions issues
	bugfix: Solved issue when adding pictures to documents
	bugfix: Solved issues with paginations
	bugfix: Solved some compatibility issues with IE
	bugfix: People profiles can be closed
	bugfix: Trashed emails not being sent
	bugfix: Repetitive tasks issues solved
	bugfix: Solved workspace quick add issue
	bugfix: Dimension members are now searchable
	 
	usability: Sent mails synchronization reintroduced
	usability: Selecting if repetitive events should be repeated on weekends or workdays
	usability: Templates now take into account custom properties
	usability: Dimension members filtering improvements
	usability: New & improved notifications
	usability: Adavanced search feature
	usability: Added quick task edition, and improved quick task addition
	usability: Improvements when linking objects
	usability: Improvements in task dependencies
	usability: Warning when uploading different file
	usability: Google Docs compatibility through weblinks
	usability: Improved the templates usability
	usability: Workspace widget introduced
	usability: Improvement with estimated time in reports
	usability: Added estimated time information in tasks list
	usability: Deletion from dimension member edition
	usability: Archiving dimension members funciton introduced
	usability: File extension prevention upload
	usability: WYSIWYG text feature for tasks descriptions and notes
	usability: View as list/panel feature reintroduced
	usability: .odt and .fodt files indexing
	 
	system: Improved upgrade procedure
	system: Improved the sharing table
	system: Improved performance when checking emails through IMAP
	system: Improved performance within tasks list
	system: Improved performance when accessing 'Users'
	system: Improved performance with ws colours
	system: Improved performance when loading permissions and dimensions
	system: Improvements within the Plugin system
	system: Major performance improvements at the framework level
	    

	Since 2.0 RC 1
	----------------
	bugfix: Uploading files fom CKEditor.
	bugfix: Some data was not save creating a company.
	bugfix: Error produced from documents tab - New Presentation.
	bugfix: Problems with task dates in some views.
	bugfix: Fatal error when you post a comment on a task page.
	bugfix: Generation of task repetitions in new tasks.
	bugfix: Do not let assign tasks (via drag & drop) to users that doesn't have permissions.
	usability: Interface localization improvements.
	system: Performance improvements.


	Since 2.0 Beta 4
	----------------
	bugfix: Extracted files categorization
	bugfix: When adding workspaces
	bugfix: Breadcrumbs were not working fine all the time 
	bugfix: Being able to zip/unzip files
	security: JS Injection Slimey Fix
	system: .pdf and .docx files contents search
	system: Improvement when creating a new user
	system: Plugin update engine
	system: Plugin manager console mode 
	system: Search in file revisions
	system: Import/Export contacts available again
	system: Import/Export events available again
	system: Google Calendar Sync 	
	system: Improvement on repeating events and tasks
	system: Cache compatibility (i.e.: with APC)
	usability: Completing a task closes its timeslots
	usability: Task progress bar working along the timeslots
	usability: Being able to change permissions in workspaces when editing
	
	
	Since 2.0 Beta 3
	----------------
	
	bugfix: Several changes in the permissions system
	bugfix: Invalid sql queries fixed
	bugfix: Issues with archived and trashed objects solved
	bugfix: Issues with sharing table solved
	bugfix: Improved IE7 and IE9 compatibility
	bugfix: Several timeslots issues solved
	bugfix: IMAP issue solved at Emails module
	bugfix: Solved issue with templates
	bugfix: Added missing tooltips at calendar 
	bugfix: Issue when completing repetitive task solved
	bugfix: Solved some issues with the Search engine
	bugfix: Solved issue with timezone autodetection
	buffix: Solved 'dimension dnx' error creating a workspace
	usability: Permission control in member forms
	usability: Disabling a user feature
	usability: Resfresh overview panel after quick add
	usability: Langs update/improvement
	usability: Drag & Drop feature added 	
	usability: Quick add task added, and improved
	usability: Slight improvement with notifications
	usability: Avoid double click at Search button (which caused performance issues)
	usability: Permissions by group feature added
	usability: Simple billing feature added
	system: Security Fixes
	system: Mail compatibility improved for different email clients 	 
	system: Feng 2 API updated
	system: General code cleanup
	system: Widget Engine
	system: Performance improvements in custom reports
	system: Print calendar
	system: Custom Properties

	Since 2.0 Beta 2
	----------------
	bugfix: Fixed problem uncompressing files
	bugfix: Loading indicator hidden
	bugfix: Search in mail contents
	bugfix: Mail reply js error
	bugfix: Filter members associated with deleted objects
	bugfix: Fixed permission error creating a contact
	usability: Contact View Improvements
	usability: Navigation Improvements
	system: Permission system fixes
	system: Performance issues solved. Using permission cache 'sharing table' for listing
	system: Weblinks module migrated
	
	
	Since 2.0 Beta 1
	----------------

	bugfix: Fixed problem with context trees when editing content objects
	bugfix: Fixed template listing
	bugfix: Fixed issues when instantiating templates with milestones
	bugfix: Fixed issue deleting users from 'people' and 'users' dimension.
	bugfix: Fixed 'core_dimensions' installer
	bugfix: Z-Index fixed in object-picker and header
	usability: Selected rows style in object picker
	system: General code cleanup
	
	
	Since 1.7
	-----------
	
	system: Plugin Support
	system: Search Engine performance improved
	system: Multiple Dimensions - 'Workspaces' and 'Tags' generalization
	system: Database and Models structure changes - Each Content object identified by unique id 
	system: Email removed from core (Available as a plugin)
	system: User Profile System
	feature: PDF Quick View - View uploaded PDF's
	usability: Default Theme improved
	usability: Customizable User Interface
	
