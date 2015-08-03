/**
 * CHANGES
 * v.2.1.4 - By PHPepe.com - Start collapsed when 'collapsed' class. Cursor Ponter 
 * v.2.1.3 - Made it $.noConflict() compatible
 * v.2.1.2 - Fixed bug in which nested fieldsets do not work correctly.
 * v.2.1.1 - Forgot to put the new filter from v.2.1 into the if (settings.closed)
 * v.2.1 - Changed jQuery(this).parent().children().filter( ELEMENTS HERE) to jQuery(this).parent().children().not('label').  Prevents you from having to guess what elements will be in the fieldset.
 * v.2.0 - Added settings to allow a fieldset to be initiated as closed.
 *
 * This script may be used by anyone, but please link back to me.
 *
 * Copyright 2009-2010.  Michael Irwin (http://michael.theirwinfamily.net)
 */
       
jQuery.fn.collapse = function(options) {
	var defaults = {
		closed : false
	}
	settings = jQuery.extend({}, defaults, options);

	return this.each(function() {
		var obj = jQuery(this);
		
		//v.2.1.4:
		if (obj.hasClass('collapsed')){
			obj.children().not('legend').hide();
		}
		
		obj.find("legend:first").addClass('collapsible').css("cursor", "pointer").click(function() {
			
			
			
			if (obj.hasClass('collapsed')) {
				obj.removeClass('collapsed').addClass('collapsible');
			}	
				
			jQuery(this).removeClass('collapsed');
	
			obj.children().not('legend').toggle("slow", function() {
			 
				 if (jQuery(this).is(":visible"))
					obj.find("legend:first").addClass('collapsible');
				 else
					obj.addClass('collapsed').find("legend").addClass('collapsed');
			 });
		});
		
		if (settings.closed) {
			obj.addClass('collapsed').find("legend:first").addClass('collapsed');
			obj.children().not("legend:first").css('display', 'none');
		}
	});
};