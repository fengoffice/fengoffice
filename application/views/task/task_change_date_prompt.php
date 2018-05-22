<script id="change-tasks-date" type="text/x-handlebars-template"> 
<div id="change-tasks-date{{genid}}" class="modal-container" style="background-color: white;padding: 10px;">

<form id="change-tasks-date-modal-form-{{genid}}" style="min-width: 340px;">
	<div style="display: inline-block; width:100%;" id="{{genid}}_container">

		<div style="margin-left:10px;display: inline-block;">
			<label for="closeTimeslotTotalTime" class="coInputTitle" style="min-width: 10px;">{{title}}</label>	
			<div class="desc" style="margin-top:5px;">{{description}}</div>
		</div>

		<div style="margin:10px 10px 0;">
			<table><tr><td>
				<label>{{lang 'new date'}}:</label>
			</td><td style="padding-left:20px;">
				<span id="{{genid}}_date_picker_container"></span>
			</td></tr></table>
		</div>

		<div style="float:right; margin-left:10px;">
			<button class="submit blue" tabindex="90" style="">{{lang 'change date'}}</button>
		</div>
	</div>
</form>	
</div>
</script>
