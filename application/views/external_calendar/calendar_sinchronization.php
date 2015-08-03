<?php
set_page_title(lang('calendar sinchronization'));
$genid = gen_id();
if(array_var($user, 'id')){
    add_page_action(lang('delete'), "javascript:if(confirm('".lang('confirm delete permanently sync')."')) og.openLink('".get_url('external_calendar', 'delete_calendar_user')."');", 'ico-delete');
}
?>

<div id="<?php echo $genid ?>sincrhonization" style="height: 100%; background-color: white">
    <div class="coInputHeader">
	  <div class="coInputHeaderUpperRow" style='border-bottom: 2px solid #ccc;'>
		<div class="coInputTitle" style='font-size: 200%;'><?php echo lang('calendar sinchronization') ?></div>
	  </div>	
	</div> 
	<div class="coInputMainBlock adminMainBlock">
		<div class="google-cal-account">
			<?php if(array_var($user, 'auth_user',false)){?>
			<label style="min-width: 0px; margin-right: 5px;"><?php echo lang('account').": "; ?></label>
			<div class="google-cal-account-name"><?php echo array_var($user, 'auth_user') ?></div>
			
			<label style="min-width: 0px; margin-right: 5px;"><?php echo lang('status').": "; ?></label>
			<?php }	?> 
			<div class="google-cal-account-status">
				<?php if(isset($auth_url)){?>	
					<?php if(array_var($user, 'auth_user',false)){?>				
					<div style="float:left;">
						<img style="float:left;" class="ico-color6" src="s.gif">
						<?php echo lang('out of sync'); ?>
					</div>
					<?php }	?> 
					<a style="margin-left: 10px; text-decoration: underline;" target="_blank" href='<?php echo $auth_url ?>'>
						<span style="margin: 0px 4px; padding: 0px 0px 0px 16px;" class="ico-recurrent"></span> 
						<?php echo lang('connect with google'); ?>
					</a>										
				<?php }else{?> 					
					<img style="float:left;" class="ico-color11" src="s.gif">
					<?php echo lang('in sync'); ?>					
				<?php }	?> 
			</div>     
       	</div>
       	
       	<?php if(array_var($user, 'auth_user',false)){?>
       	
       	<div class="google-cal-calendars">
       		<div class="google-cal-title"><?php echo lang('sync from feng office to google') ?></div>
       		<div style="margin-right: 5px;margin-bottom: 15px;margin-left: 5px;"><?php echo lang('sync all events from feng office'); ?>
	       		<button onclick="<?php echo $sync_from_feng_action?>('<?php echo array_var($user, 'id')?>')" class="google-cal-action-btn google-cal-action-btn-color<?php echo $sync_from_feng_color?>" type="button">
	             				<?php echo $sync_from_feng_text?>
	    		</button>
    		</div>
       	</div>
       	
       	<div class="google-cal-calendars">
       		<div class="google-cal-title"><?php echo lang('sync from google') ?></div>
       		<table style="width:100%">
				<tr class="google-cal-row google-cal-row-title">
					<td>Name</td>
				    <td>Status</td> 
				    <td>Actions</td>
				    <td>Classified under</td>
				</tr>
			  	<?php if(count($external_calendars) > 0){?>            
	               <?php foreach ($external_calendars as $e_cal){
	               		$cal_status = $e_cal['calendar_status'];
	               		$cal_id = $e_cal['original_calendar_id'];
	               ?>                        
	               <tr class="google-cal-row">
	               	<td><?php echo $e_cal['title']?></td>
				    <td><?php echo $calendars_status[$cal_status]['text']?></td> 
				    <td>
				    	<?php foreach ($calendars_status[$cal_status]['actions'] as $action){ ?> 
				    		<button onclick="<?php echo $calendars_actions[$action]['function']?>('<?php echo $cal_id?>')" class="google-cal-action-btn google-cal-action-btn-color<?php echo $action?>" type="button">
             				<?php echo $calendars_actions[$action]['text']?>
    						</button>				    		
	               		<?php }?> 
				    </td>
				    <td id="google_cal_breadcrumb_container_<?php echo isset($e_cal['calendar_id'])?$e_cal['calendar_id']:0?>" class="google-cal-breadcrumb-container">
				    					    		
				    </td>	                  
	               </tr>
	               <?php }?>                       
	        	<?php }?>
			</table>
       	</div>
       	<?php }?>
	</div>   
</div>

<style>
.google-cal-breadcrumb-container{
	max-width: 150px;
}
.google-cal-action-btn{
	color: #333;
	background-color: #fff;
 	border-color: #ccc;
	border-radius: 2px;
    border-style: solid;
    border-width: 1px;
    box-shadow: 1px 1px 2px 0 rgba(0, 0, 0, 0.22);
    cursor: pointer;
    font-size: 13px;
    height: 26px;
}
.google-cal-action-btn-color1, .google-cal-action-btn-color3{
	color: #fff;
	background-color: #5cb85c;
 	border-color: #4cae4c;
}
.google-cal-action-btn-color2{
	color: #fff;
	background-color: #f0ad4e;
  	border-color: #eea236;
}
.google-cal-action-btn-color4{
	color: #fff;
	background-color: #d9534f;
  	border-color: #d43f3a;
}
.google-cal-calendars{
	clear: left;
	margin-top: 10px;
}
.google-cal-account{
	padding: 5px;
	font-size: 14px;
}
.google-cal-account-name{
	float: left; 
	margin-right: 20px;
	font-weight: bold;
}
.google-cal-account-status{
	font-weight: bold;
}
.google-cal-title{	               
	border-bottom: 2px solid #ccc;
    color: #666;
    font-size: 140%;
    font-weight: bold;
    margin-bottom: 10px;
}
.google-cal-row-title{	               
	border-bottom-width: 2px !important;   
    font-weight: bold;   
}
.google-cal-row{	               
	border-bottom: 1px solid #aaaaaa;   
}
.google-cal-row td{	               
	padding: 5px;
  	vertical-align: middle;
}
.google-cal-classify-modal{	               
	background-color: white;
    padding: 10px;
    border-radius: 5px;
    width: 530px;
    overflow: auto;
    min-height: 250px;
    font-size: 14px;
}
.google-cal-classify-modal .submit-btn{	               
	float:right;
	width:70px;
	margin-left:10px;
	clear: left;
}

</style>

<script>
og.google_calendar_start_sync_feng_calendar  = function(ext_cal_user_id){
	var post = [];
	post.ext_cal_user_id = ext_cal_user_id;
	og.openLink(og.getUrl('externalCalendar', 'start_sync_feng_calendar'), {
		hideLoading: false,
		scope: this,
		post: post,
		callback: function(success, data) {
			
		}
	});
}

og.google_calendar_stop_sync_feng_calendar  = function(ext_cal_user_id){
	var post = [];
	post.ext_cal_user_id = ext_cal_user_id;
	og.openLink(og.getUrl('externalCalendar', 'stop_sync_feng_calendar'), {
		hideLoading: false,
		scope: this,
		post: post,
		callback: function(success, data) {
			
		}
	});
}

og.google_calendar_start_sync_calendar  = function(original_calendar_id){
	var post = [];
	post.original_calendar_id = original_calendar_id;
	og.openLink(og.getUrl('externalCalendar', 'start_sync_calendar'), {
		hideLoading: false,
		scope: this,
		post: post,
		callback: function(success, data) {
			
		}
	});
}

og.google_calendar_stop_sync_calendar  = function(original_calendar_id){
	var post = [];
	post.original_calendar_id = original_calendar_id;
	og.openLink(og.getUrl('externalCalendar', 'stop_sync_calendar'), {
		hideLoading: false,
		scope: this,
		post: post,
		callback: function(success, data) {
			
		}
	});
}

og.google_calendar_delete_calendar  = function(original_calendar_id){
	var post = [];
	post.original_calendar_id = original_calendar_id;
	og.openLink(og.getUrl('externalCalendar', 'delete_calendar'), {
		hideLoading: false,
		scope: this,
		post: post,
		callback: function(success, data) {
			
		}
	});
}

og.google_calendar_classify = function(original_calendar_id) {
	//re render member selectors
	  
		og.openLink(og.getUrl('externalCalendar', 'get_rendered_member_selectors', {id: original_calendar_id,genid: '<?php echo $genid?>', ajax: true}), {
			callback: function(success, data) {
				var modal_params = {
						'escClose': true,
						'minWidth' : 550,
						'minHeight' : 300,
						'overlayClose': true,
						'closeHTML': '<a id="ogTasksPanelAT_close_link" class="modal-close modal-close-img"></a>'						
					};
				
				var form = document.createElement('form');
				form.id = 'googleCalClassifyModal<?php echo $genid?>';
				form.className = 'google-cal-classify-modal';
				form.innerHTML = data.htmlToAdd;
				form.innerHTML +='<div class="submit-btn"><button class="submit" style="" tabindex="90"><?php echo lang('add')?></button></div>';
				form.innerHTML +='<input id="<?php echo $genid?>original_calendar_id" type="hidden" value="'+original_calendar_id+'" name="original_calendar_id">';
				$.modal(form,modal_params);			
				
				$( "#googleCalClassifyModal<?php echo $genid?>" ).submit(function( event ) {
					var parameters = [];
					var form_params = $( this ).serializeArray();
					
					for (i = 0; i < form_params.length; i++) { 
						    parameters[form_params[i].name] = form_params[i].value;
					}

					og.openLink(
							og.getUrl('externalCalendar','classify_calendar'),
							{ method:'POST' , 
								post:parameters,
								callback:function(success, data){
									if (!success || data.errorCode) {
									} else {
										ogTasks.closeModal();										
									}						
								}
							}
					);	
					
					event.preventDefault();
				});						
			},
			scope: this
		});
	
}


$(function() {
<?php if(count($external_calendars) > 0){            
	foreach ($external_calendars as $e_cal){
		if(isset($e_cal['members'])){
		?>
		var crumb_html = og.getEmptyCrumbHtml(<?php echo $e_cal['members']?>,".google-cal-breadcrumb-container");
		$("#google_cal_breadcrumb_container_<?php echo $e_cal['calendar_id']?>").html(crumb_html);
		<?php
		}
	}
} ?>
og.eventManager.fireEvent('replace all empty breadcrumb', null);
});
</script>



