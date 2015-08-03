<?php
$page_title = "Translate Feng Office" . ($to != "" ? " to $to" : "");
set_page_title($page_title) ?>
<style>
body {
	padding: 5px 30px;
	font-family: Arial, sans-serif, serif;
	font-size: 12px;
}
table.lang {
	width: 100%;
	border-collapse: collapse;
}
table.lang th {
	background-color: #DDD;
}
table.lang td.key {
	background-color: #EEE;
}
table.lang th, table.lang td {
	vertical-align: top;
	padding: 5px;
	border: 1px solid #888;
}
table.filters td {
	vertical-align: top;
	padding: 5px 10px;
}
th.key {
	width: 20%;
}
th.from, th.to {
	width: 40%;
}
table.lang td.from, table.lang td.to {
	padding: 0px;
}
table.lang td.from textarea, table.lang td.to textarea {
	width: 100%;
	background: white;
	border: 0px;
	margin: 0px;
	color: black;
	overflow-y: auto;
}
td.empty {
	text-align: center;
	font-style: italic;
}
table.lang td.from textarea.focus, table.lang td.to textarea.focus {
	background-color: #EEFFEE;
}
#moreOptions {
	margin-bottom: 20px;
	margin-left: 20px;
}
label {
	font-weight: bold;
}
table.options td {
	vertical-align: middle;
	padding: 5px 10px;
}
table.lang em {
	background-color: yellow;
	font-style: normal;
}
div.msg {
	padding: 10px;
	font-style: italic;
}
</style>
<script>
function addLangs(langs) {
	locales[locale][file] = {};
	for (var k in langs) {
		locales[locale][file][k] = langs[k];
	}
}
function escLang(text) {
	return text.replace(/'/g, "\\'").replace(/\n/g, "\\n").replace(/</g, "&lt;");
}
function showMoreOptions() {
	if (this.optionsVisible) {
		this.optionsVisible = false;
		this.innerHTML = 'More options';
		document.getElementById('moreOptions').style.display = 'none';
	} else {
		this.optionsVisible = true;
		this.innerHTML = 'Hide options';
		document.getElementById('moreOptions').style.display = 'block';
	}
}
var locales = {};
</script>
<?php
if (!isset($from) || $from == "") $from = "en_us";
if (!isset($to)) $to = ""; ?>
	
<!-- h1><?php echo $page_title ?></h1-->

<p>This tool allows you to translate Feng Office to a locale other than en_us. Your webserver needs permissions to write on the 'language' folder.</p> <?php

?>
<table class="filters"><tbody>
<tr><td>
	<label>Choose a locale:</label>
	<form action="index.php" method="get" onsubmit="return localeChosen.call(this)">
		<input type="hidden" name="c" value="tool">
		<input type="hidden" name="a" value="translate">
		<input type="hidden" name="from" value="<?php echo $from ?>" />
		<input type="hidden" name="pagesize" value="<?php echo $pagesize ?>" />
		<input type="hidden" name="search" value="<?php echo $search ?>" />
		<script>
			function localeChosen() {
				var select = this.getElementsByTagName("select")[0];
				if (select.value == "new") {
					var locale = prompt("Enter a new locale:");
					if (locale) {
						this.to.value = locale;
						this.submit();
					}
				} else if (select.value != "") {
					this.to.value = select.value;
					this.submit();
				}
				return false;
			}
		</script>
		<input type="hidden" name="to" value="" />
		<select onchange="localeChosen.call(this.parentNode)">
			<option value="" <?php if ($to == "") echo ' selected="selected"' ?>>-- Choose a locale --</option> <?php
			$exists = false;
			foreach ($languages as $language) { ?>
				<option value="<?php echo $language?>"<?php if($to == $language) echo ' selected="selected"' ?>>
					<?php echo $language ?>
				</option> <?php
				if ($language == $to) {
					$exists = true;
				}
			}
			if ($to != "" && !$exists) { ?>
				<option value="<?php echo $to ?>" selected="selected"><?php echo $to ?></option> <?php
			}
			?>
			<option value="new">&lt;New&gt;</option>
		</select>
		<button type="submit">Go</button>
	</form>
<?php if ($to != '') { ?>
	<div id="checklang-div" style="max-width:200px; margin-top:5px;">
		<a href="index.php?c=tool&a=checklang&to=<?php echo $to ?>" target="_blank">Show all "Missing langs" for <?php echo $to?></a>
	</div>
<?php } ?>
</td><?php

if ($to != "") { 
	// load translation files
	$locales[$from] = array();
	$locales[$to] = array(); ?>
	<script>
		locales['<?php echo $from ?>'] = {};
		locales['<?php echo $to ?>'] = {};
		base = '<?php echo $from ?>';
	</script> <?php
	foreach ($from_files as $fromFile) {
		$locales[$from][$fromFile] = array();
	}
	// finished loading translation files
	 ?>
	<td>
	<label>Choose a file:</label>
	<form action="index.php" method="get">
		<input type="hidden" name="c" value="tool">
		<input type="hidden" name="a" value="translate">
		<input type="hidden" name="from" value="<?php echo $from ?>" />
		<input type="hidden" name="to" value="<?php echo $to ?>" />
		<input type="hidden" name="filter" value="<?php echo $filter ?>" />
		<input type="hidden" name="pagesize" value="<?php echo $pagesize ?>" />
		<input type="hidden" name="search" value="<?php echo $search ?>" />
		<script>
			function fileChosen() {
				if (this.value != "") {
					this.parentNode.submit();
				}
			}
		</script>
		<select name="file" onchange="fileChosen.call(this)">
			<option value="" <?php if ($file == "") echo ' selected="selected"' ?>>-- Choose a file --</option> <?php
			foreach ($locales[$from] as $fromFile => $fromLangs) { ?>
				<option value="<?php echo $fromFile?>"<?php if($file == $fromFile) echo ' selected="selected"' ?>>
					<?php echo $fromFile ?>
				</option> <?php
			} ?>
		</select>
		<button type="submit">Go</button>
	</form>
	</td>
	
	<td>
	<label>Search:</label>
	<form action="index.php" method="get">
		<input type="hidden" name="c" value="tool">
		<input type="hidden" name="a" value="translate">
		<input type="hidden" name="from" value="<?php echo $from ?>" />
		<input type="hidden" name="to" value="<?php echo $to ?>" />
		<input type="hidden" name="pagesize" value="<?php echo $pagesize ?>" />
		<input type="text" name="search" id="search_field" value="<?php echo $search ?>" placeholder="Enter search criteria..." />
		<button id="search_submit" type="submit">Search</button>
	</form>
	<script>
		function clearSearchField(link) {
			var f = document.getElementById("search_field");
			if (f) {
				f.value = '';
				if (link) link.style.display = 'none';
				var ss = document.getElementById("search_submit");
				if (ss) ss.click();
			}
		}
	</script>
	<?php if ($search != '') { ?>
	<div style="max-width:200px; margin-top:5px;">
		<a href="#" onclick="clearSearchField(this);">Clear search criteria</a>
	</div>
	<?php } ?>
	</td>
	
	<?php
	if ($file != "" || $search != '') { 
		if ($start >= $added && $filter == "missing") $start -= $added; ?>
		<td>
		<label>View:</label>
		<form action="index.php" method="get">
			<input type="hidden" name="c" value="tool">
			<input type="hidden" name="a" value="translate">
			<input type="hidden" name="from" value="<?php echo $from ?>" />
			<input type="hidden" name="to" value="<?php echo $to ?>" />
			<input type="hidden" name="file" value="<?php echo $file ?>" />
			<input type="hidden" name="search" value="<?php echo $search ?>" />
			<input type="hidden" name="pagesize" value="<?php echo $pagesize ?>" />
			<script>
				function filterChosen() {
					this.parentNode.submit();
				}
			</script>
			<select name="filter" onchange="filterChosen.call(this)">
				<option value="missing" <?php if ($filter == "missing") echo ' selected="selected"' ?>>Missing</option>
				<option value="all" <?php if ($filter == "all") echo ' selected="selected"' ?>>All</option>
			</select>
			<button type="submit">Go</button>
		</form>
		</td>
		<td style="text-align:right">
			<br />
			<button style="margin-left:50px" type="submit" onclick="saveClick()">Save</button>
			<a href="#" onclick="showMoreOptions.call(this);return false;" style="margin-left:10px">More options</a>
		</td>
		</tr></tbody></table>
		
		<script>
		function textChange() {
			window.onbeforeunload = function() {
				return "You have done some changes. If you leave you'll lose all changes you have made";
			};
		}
		
		function textFocus() {
			this.select();
			this.className += " focus";
		}
		
		function textBlur() {
			this.className = (" " + this.className + " ").replace(/\s+focus\s+/g, " ");
		}
		
		function saveClick() {
			var form = document.getElementById('langs');
			form.action += '&start=<?php echo $start?>';
			formSubmit();
			form.submit();
		}
		
		function pagesizeChange() {
			formSubmit();
			var form = document.getElementById('langs');
			form.action += '&pagesize=' + form.pagesize.value;
			form.submit(); 
		}
		
		function formSubmit() {
			window.onbeforeunload = null;
		}
		
		</script>
		<form id="langs" onsubmit="formSubmit()" action="index.php?c=tool&a=translate&from=<?php echo $from ?>&to=<?php echo $to ?>&file=<?php echo $file ?>&filter=<?php echo $filter ?>" method="post">
			<div id="moreOptions" style="display:none">
				<table class="options"><tbody><tr><td>
					<label>Page size:</label>
					<select name="pagesize" onchange="pagesizeChange.call(this)" value="<?php echo $pagesize ?>">
						<option value="5"<?php if ($pagesize == 5) echo ' selected="selected"'; ?>>5</option>
						<option value="10"<?php if ($pagesize == 10) echo ' selected="selected"'; ?>>10</option>
						<option value="20"<?php if ($pagesize == 20) echo ' selected="selected"'; ?>>20</option>
						<option value="50"<?php if ($pagesize == 50) echo ' selected="selected"'; ?>>50</option>
						<option value="100"<?php if ($pagesize == 100) echo ' selected="selected"'; ?>>100</option>
					</select>
				</td><td>
					<a href="index.php?c=tool&a=translate&download=<?php echo $to ?>">Download zipped translation files for <?php echo $to ?></a>
				</td></tr></tbody></table>
			</div>
			<input type="hidden" name="locale" value="<?php echo $to ?>" />
			<input type="hidden" name="file" value="<?php echo $file ?>" />
			<input type="hidden" name="search" value="<?php echo $search ?>" />
			<table class="lang"><tbody>
			<tr>
				<th class="key">Key</th>
				<th class="from"><?php echo $from ?></th>
				<th class="to">
					<?php echo $to ?>
				</th>
			</tr><?php
			$locales[$from][$file] = $from_file_translations;
			$locales[$to][$file] = $to_file_translations;
			$count = 0;
			foreach ($locales[$from][$file] as $key => $value) {
				if ($filter == "all" || $filter == "missing" && !isset($locales[$to][$file][$key])) {
					$key_str = $key;
					if ($search != '') {
						$key_str = str_replace($search, "<em>$search</em>", $key_str);
					}
					$count++;
					if ($count > $start && $count <= $start + $pagesize) { ?>
					<tr>
						<td class="key"><?php echo $key_str ?></td>
						<td class="from"><textarea readonly="readonly" tabindex="-1"><?php echo $value ?></textarea></td> <?php
					if (!isset($locales[$to][$file]) || !isset($locales[$to][$file][$key])) { ?>
						<td class="to"><textarea name="lang[<?php echo $key ?>]" onfocus="textFocus.call(this)" onblur="textBlur.call(this)" onchange="textChange()"></textarea></td> <?php
					} else { ?>
						<td class="to"><textarea name="lang[<?php echo $key ?>]" onfocus="textFocus.call(this)" onblur="textBlur.call(this)" onchange="textChange()"><?php echo $locales[$to][$file][$key] ?></textarea></td> <?php
					} ?>
					</tr> <?php
					}
				}
			}
			if ($count == 0) {
				if ($filter == "missing") { ?>
					<tr><td class="empty" colspan="3">No <b>missing</b> translations to display in <b><?php echo $file ?></b>. Choose "All" in the "View" combobox if you want to see all translations in <b><?php echo $file ?></b> or choose another file in the "Choose a file" combobox.</td></tr> <?php
				} else { ?>
					<tr><td class="empty" colspan="3">No translations to display in <b><?php echo $file ?></b>. Try choosing a different file in the "Choose a file" combobox.</td></tr> <?php
				}
			} ?>
			</tbody></table> <br /> <?php
			if ($start > 0) {
				$remaining = min($start, $pagesize); ?>
				<button onclick="this.parentNode.action += '&start=<?php echo ($start - $remaining) . ($search != '' ? '&search='.$search : '')?>'" type="submit">Previous <?php echo $remaining  ?></button><?php
			}
			// when filter is "missing" start was already calculated to reflect the langs that were added
			$nextstart = $start + $pagesize;
			$remaining = min(array($pagesize, $count - $nextstart));
			if ($remaining > 0) { ?>
				<button onclick="this.parentNode.action += '&start=<?php echo $nextstart . ($search != '' ? '&search='.$search : '')?>'" type="submit">Next <?php echo $remaining  ?></button><?php
			}
			if ($count > 0) { ?>
				Showing <?php echo $start + 1 ?> to <?php echo min($start + $pagesize, $count) ?> of <?php echo $count ?> <?php
			} ?>
		</form><?php
	} else { ?>
		</tr></tbody></table>
		<div class="msg">Select a file or use the search to list the translations.</div><?php
	}
} ?>
