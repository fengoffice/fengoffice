og.sendComment=function(id,ele){
	var comms = $("."+ele).parent().find('textarea').val();
	if(comms!=''){
		var args=[];
		args['object_id']=id;
		jQuery.ajax({
			    url: og.getUrl('comment','add',args),
		    	type: "POST",
                data: {'comment':{'text': comms}},
                success: function(){
                    var path = og.getUrl('dashboard', 'activity_feed', {ajax: true});
                    $.getJSON(path, function(obj)
                        {
                            $('div.activity_feed_loader').html(obj.current.data);
                        }
                    ) 
				}
			});
	}
}
og.sendCommentFromTextBox=function(id,ele,e){
	 var code = (e.keyCode ? e.keyCode : e.which);
	 if(code == 13 && !e.shiftKey) {
		 $(ele).attr("disabled", "disabled"); 
		 var msgid=$(ele).attr("class");
		 var comms =$(ele).val();
		 	comms=comms.replace(/\n/g, "<br />");
			if(comms!=''){
				var args=[];
				args['object_id']=id;
				jQuery.ajax(
                    {
					    url: og.getUrl('comment','add',args),
				    	type: "POST",
		                data: {'comment':{'text': comms}},
		                success: function(data)
                        {
                            var path = og.getUrl('dashboard', 'activity_feed', {ajax: true});
                            $.getJSON(path, function(obj)
                                {
                                    $('div.activity_feed_loader').html(obj.current.data);
                                    $("div.x-panel-body").scrollTo($("."+msgid).siblings('div').children(':last'),800);                                    
                                }
                            )                        
						}
					}
                );
			}
	 }
}
og.commentBoxKeyDown=function(ele,e,id){
	og.sendCommentFromTextBox(id,ele,e);
	if (e.keyCode == 13 && e.shiftKey) {
		 ele.value=(ele.value + "\n");// use the right id here
		 newHeight=parseInt($(ele).css('height'))+15;
		 $(ele).css('height',newHeight+"px");
         return true;

	}else if(e.keyCode==8){
		if($(ele)[0].value.charAt($(ele)[0].value.length-1)=="\n"){
			 newHeight=parseInt($(ele).css('height'))-15;
			 $(ele).css('height',newHeight+"px");
			 ele.rows-=1;
		}
	}
}
