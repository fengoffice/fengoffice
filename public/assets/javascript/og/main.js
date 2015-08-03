$(function(){
	
	/**
	 * Close quick add boxes
	 */
	$('#quick-form .close').click(function(){
		$(this).parent().slideUp();
	});
	
	/**
	 * Hide floating boxes clicking ouside them
	 * @deprecated - Too dificult to do in thes level. Each box must implement this behavoir
	 */
	$('body').click( function (e) {
		var toClose = ['#quick-form' , '.user-box-actions'   ] ;
		var ignoreClickIds = { 
			'userboxWrapper' : 'userboxWrapper', 
			'modcoder_colorpicker': 'modcoder_colorpicker', 
			'modcoder_picker' :'modcoder_picker',
			'modcoder_ok': 'modcoder_ok',
			'modcoder_ok': 'modcoder_ok',
			'modcoder_close': 'modcoder_close',
			'modcoder_hue_wrap': 'modcoder_hue_wrap',
			'modcoder_sample': 'modcoder_sample',
			'quick-form-3' : 'quick-form-3',
			'modcoder_grad' : 'modcoder_grad',
			'modcoder_r' : 'modcoder_r',
			'modcoder_g' : 'modcoder_g',
			'modcoder_b' : 'modcoder_b',
			'modcoder_hex' : 'modcoder_hex'
		};
		var clicked =  $(e.target) ;
		if (!clicked[0] || ignoreClickIds[clicked[0].id]) return;
		var doClose = true ;
		main_loop: 
		for (var  i in ignoreClickIds ) {
			if (  i == "remove" || !ignoreClickIds[i] ) continue ;
			var toIgnore = $("#"+ignoreClickIds[i]);
			if (!toIgnore.length) {
				//alert("#"+ignoreClickIds[i]);
				continue ;
			}else if (jQuery.contains(toIgnore[0],clicked[0])){
				//alert("else");
				doClose = false ;
				break main_loop ;
			}
		}
		
		// Custom code for specific objects without id 
		
		if (  $("#modcoder_grad div").length  && jQuery.contains( $("#modcoder_grad div") ,clicked[0])){
			doClose = false ;
		}
		
		if (doClose){
			
			for ( var i in toClose ) {
				if (  i == "remove" || !toClose[i] ) continue ;
				var box = $(toClose[i]) ; 
				if ( box[0] && !jQuery.contains(box[0],clicked[0]) ){
					box.slideUp();
				}
			}
		}
	});
	
	
	og.eventManager.addListener('before tab panel construct',function(tabConfig){
		if (og.queryString != "" && og.queryString != "c=access&a=index"  ){
			tabConfig.initialContent = {
				type:"url", 
				data: og.initialURL 
			};
			// remove query string to avoid opening the same link in every tab
			og.queryString= "";
		}
	});
	
});