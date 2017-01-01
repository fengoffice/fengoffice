<script id="task-timespan-template" type="text/x-handlebars-template"> 
<div id="task-timespan-modal{{genid}}" class="modal-container" style="background-color: white;padding: 10px;">

<form id="task-timespan-modal-form{{genid}}" style="min-width: 340px;">
	<div style="display: inline-block; width:100%;" id="{{genid}}addwork">
		<input type="hidden" value="{{taskId}}" name="object_id">

		<div style="margin-bottom:10px;display:none;" id="{{genid}}tsUserContainer">
			<label for="{{genid}}closeTimeslotDescription">{{lang 'person'}}:</label>
			<select name="timeslot[contact_id]" tabindex="60" id="{{genid}}tsUser">
			</select>
		</div>
	
		{{#if showDesc}}
		<div>
			<label>{{lang 'description'}}:</label>
			<textarea cols="40" rows="10" name="timeslot[description]" tabindex="70" class="short"></textarea>
		</div>
		 {{/if}}

		<div style="float:left;">
			<label for="datepicker">{{lang 'date'}}:</label>
			<span id="datepicker{{genid}}"></span>
		</div>

		<div style="float:left;margin-left:10px;">
			<label for="closeTimeslotTotalTime">{{lang 'total time'}}:</label>	<div style="float:left;">
				<span>{{lang 'hours'}}:&nbsp;</span>
				<input type="text" value="" name="timeslot[hours]" tabindex="80" style="width:30px">	</div>
			<div style="float:left;margin-left:10px;">
				<span>{{lang 'minutes'}}:&nbsp;</span>
				<select tabindex="85" size="1" name="timeslot[minutes]" class="TMTimespanRealSelector">
				{{#each minutes}}
		  			<option value="{{minute}}">{{minute}}</option>
		  		{{/each}}		
				</select>				
			</div>
		</div>

		<div style="float:right;margin-left:10px;clear: left;">
			<button class="submit blue" tabindex="90" style="">{{lang 'add timeslot'}}</button>
		</div>
	</div>
</form>	
</div>
</script>


<script id="small-task-timespan-template" type="text/x-handlebars-template"> 
<div id="small-task-timespan-modal{{genid}}" class="modal-container" style="background-color: white;padding: 10px;">
<form id="small-task-timespan-modal-form{{genid}}" style="min-width: 200px;">
	<div style="display: inline-block;" id="{{genid}}addwork">
		<input type="hidden" value="{{taskId}}" name="object_id">
	
		<div>
			<label>{{lang 'description'}}:</label>
			<textarea cols="40" rows="10" name="timeslot[description]" tabindex="70" class="short"></textarea>
		</div>		

		<div style="float:right;width:70px;margin-left:10px;">
			<button class="submit blue" tabindex="90" style="">{{lang 'add'}}</button>
		</div>
	</div>
</form>	
</div>
</script>
