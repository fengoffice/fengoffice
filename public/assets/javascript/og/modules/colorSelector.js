
og.color_selector = {};

og.color_selector.close_palette = function(input_id) {
	$("#"+input_id+"_sample").popover('hide');
}

og.color_selector.select = function(color_id, input_id) {
	var prev_color = $("#"+input_id).val();
	$("#"+input_id).val(color_id);
	
	$("#"+input_id+"_sample").removeClass("color-"+prev_color);
	$("#"+input_id+"_sample").addClass("color-"+color_id);

	$("#"+input_id+"_sample").popover('hide');
}
