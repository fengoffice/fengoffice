timeslots = {};

timeslots._minutes_multiplier = 1000 * 60;
timeslots._hours_multiplier = timeslots._minutes_multiplier * 60;
timeslots._days_multiplier = timeslots._hours_multiplier * 24;
timeslots._years_multiplier = timeslots._days_multiplier * 365;
		
/**
 * This function receives the worked (and paused) hours and minutes for a timeslot, and adds them up as milliseconds
 * 
 * @return integer
 *  
 */
timeslots.turn_into_total_milliseconds = function (worked_hours, worked_minutes, paused_hours, paused_minutes) {
	
	// This was used for degbugging. Left it here as an example:
	//console.log("worked_hours, worked_minutes, paused_hours, paused_minutes = ");
	//console.log(worked_hours + ", " + worked_minutes + ", " + paused_hours + ", " + paused_minutes);
		    
    var total_worked_hours = 0;
    if(worked_hours > 0 && worked_hours != ''){
    	total_worked_hours = timeslots._hours_multiplier * worked_hours;
    }
    if(paused_hours > 0 && paused_hours != ''){
    	total_worked_hours += timeslots._hours_multiplier * paused_hours;		    	
    }
    
    var total_worked_minutes = 0;
    if(worked_minutes > 0 && worked_minutes != ''){
    	total_worked_minutes = timeslots._minutes_multiplier * worked_minutes;
    }
    if(paused_minutes > 0 && paused_minutes != ''){
    	total_worked_minutes += timeslots._minutes_multiplier * paused_minutes;
    }	
    
    total_work_in_milliseconds = total_worked_hours + total_worked_minutes;
    
    return total_work_in_milliseconds;
}

/**
 * This function receives an object of type Date the worked (and paused) hours and minutes for a timeslot, and adds them up as milliseconds
 * 
 * @return string
 *  
 */
timeslots.format_hours_and_minutes = function (date_object) {

	var return_string = '';
	
	var formatted_minutes = date_object.getMinutes();
	
    if(date_object.getMinutes() < 10){
    	formatted_minutes = '0'+date_object.getMinutes();
    }

	if(og.preferences.time_format_use_24 == "0") {
        return_string = date_object.getHours()+':'+formatted_minutes+' AM';

	    if(date_object.getHours() > 12){
	        return_string = (date_object.getHours()-12)+':'+formatted_minutes+' PM';
	    }

	    if(date_object.getHours() == 12){
	    	return_string = (date_object.getHours()+':'+formatted_minutes+' PM');
	    }

	    if(date_object.getHours() == 0){
	    	return_string = '12:'+formatted_minutes+' AM';
	    }

	}else{
        return_string = date_object.getHours()+':'+formatted_minutes;
	}
	return return_string;
}
