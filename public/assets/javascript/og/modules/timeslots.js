timeslots = {};

// ── Preference constants ──────────────────────────────────────────────────────
// Values for the `automatic_calculation_time` preference
// (controls which field is recalculated when the worked duration changes)
timeslots.PREF_DURATION_RECALC_START = 1; // keep end fixed, recalculate start
timeslots.PREF_DURATION_RECALC_END   = 2; // keep start fixed, recalculate end
timeslots.PREF_DURATION_ASK_USER     = 3; // prompt the user every time

// Values for the `automatic_calculation_start_time` preference
// (controls which field is recalculated when a start or end date/time changes)
timeslots.PREF_DATE_RECALC_OPPOSITE  = 1; // adjust the opposite date/time
timeslots.PREF_DATE_RECALC_DURATION  = 2; // adjust the worked duration
timeslots.PREF_DATE_ASK_USER         = 3; // prompt the user every time
// ─────────────────────────────────────────────────────────────────────────────

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

	// Check if the date object is valid
	if (isNaN(date_object.getTime())) {
		return 'hh:mm';
	}

	var hours = date_object.getHours();
	var minutes = date_object.getMinutes();

	// Double-check for NaN values
	if (isNaN(hours) || isNaN(minutes)) {
		return 'hh:mm';
	}

	var formatted_minutes = minutes;

    if(minutes < 10){
    	formatted_minutes = '0' + minutes;
    }

	if(og.preferences.time_format_use_24 == "0") {
        return_string = hours + ':' + formatted_minutes + ' AM';

	    if(hours > 12){
	        return_string = (hours - 12) + ':' + formatted_minutes + ' PM';
	    }

	    if(hours == 12){
	    	return_string = hours + ':' + formatted_minutes + ' PM';
	    }

	    if(hours == 0){
	    	return_string = '12:' + formatted_minutes + ' AM';
	    }

	}else{
        return_string = hours + ':' + formatted_minutes;
	}
	return return_string;
}

/**
 * Converts a "HH:MM" string to total minutes since midnight.
 * Returns null if the string is invalid or empty.
 *
 * @param {string} hhmm - e.g. "09:30"
 * @return {number|null}
 */
timeslots.hmToMinutes = function (hhmm) {
	if (!hhmm || typeof hhmm !== 'string') return null;
	var parts = hhmm.trim().split(':');
	if (parts.length < 2) return null;
	var h = parseInt(parts[0], 10);
	var m = parseInt(parts[1], 10);
	if (isNaN(h) || isNaN(m)) return null;
	return h * 60 + m;
};

/**
 * Converts a total-minutes value back to a zero-padded "HH:MM" string.
 *
 * Negative values wrap around (e.g. -60 → "23:00") because they arise from
 * backwards-computed start times that are valid same-day clock values.
 *
 * Positive overflow (>= 1440) is intentionally NOT wrapped: values like 1500
 * are displayed as "25:00" to signal an overnight/next-day end time.
 * hmToMinutes() handles h > 23 correctly on the way back in, and end_time is
 * never submitted to the server, so the display-only value is safe.
 *
 * @param {number} totalMinutes
 * @return {string}
 */
timeslots.minutesToHm = function (totalMinutes) {
	if (isNaN(totalMinutes)) return '';
	if (totalMinutes < 0) {
		totalMinutes = ((totalMinutes % 1440) + 1440) % 1440; // wrap negatives to 0-1439
	}
	var h = Math.floor(totalMinutes / 60);
	var m = totalMinutes % 60;
	return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
};

/**
 * Returns which field should be recalculated when the worked duration changes,
 * based on the `automatic_calculation_time` user preference.
 *
 * Mirrors the logic in og.onchangeTimesInputs (edit_timeslot.php).
 *
 * @param {boolean} startKnown - whether start time/date is currently set
 * @param {boolean} endKnown   - whether end time/date is currently set
 * @param {string|number} pref - value of automatic_calculation_time preference
 * @return {string} 'end' | 'start' | 'none'
 */
timeslots.whatChangesOnDurationChange = function (startKnown, endKnown, pref) {
	if (pref == timeslots.PREF_DURATION_RECALC_END) {
		return startKnown ? 'end' : 'none';
	}
	if (pref == timeslots.PREF_DURATION_RECALC_START) {
		return endKnown ? 'start' : 'none';
	}
	// Default: prefer adjusting end (if start is known), otherwise adjust start
	if (startKnown) return 'end';
	if (endKnown)   return 'start';
	return 'none';
};

/**
 * Returns which field should be recalculated when the start time/date changes,
 * based on the `automatic_calculation_start_time` user preference.
 *
 * Mirrors the logic in og.onchangeStartDate (edit_timeslot.php).
 * Note: preference '3' (ask user) is not handled here — callers must check for it first.
 *
 * @param {boolean} endKnown      - whether end time/date is currently set
 * @param {boolean} durationKnown - whether duration is currently set
 * @param {string|number} pref    - value of automatic_calculation_start_time preference
 * @return {string} 'end' | 'duration' | 'none'
 */
timeslots.whatChangesOnStartChange = function (endKnown, durationKnown, pref) {
	if (pref == timeslots.PREF_DATE_RECALC_DURATION) {
		return endKnown ? 'duration' : 'none';
	}
	// PREF_DATE_RECALC_OPPOSITE and default: adjust end to keep duration intact
	return durationKnown ? 'end' : 'none';
};

/**
 * Returns which field should be recalculated when the end time/date changes,
 * based on the `automatic_calculation_start_time` user preference.
 *
 * Mirrors the logic in og.onchangeEndDate (edit_timeslot.php).
 * Note: preference '3' (ask user) is not handled here — callers must check for it first.
 *
 * @param {boolean} startKnown    - whether start time/date is currently set
 * @param {boolean} durationKnown - whether duration is currently set
 * @param {string|number} pref    - value of automatic_calculation_start_time preference
 * @return {string} 'start' | 'duration' | 'none'
 */
timeslots.whatChangesOnEndChange = function (startKnown, durationKnown, pref) {
	if (pref == timeslots.PREF_DATE_RECALC_DURATION) {
		return startKnown ? 'duration' : 'none';
	}
	// PREF_DATE_RECALC_OPPOSITE and default: adjust start to keep duration intact
	return durationKnown ? 'start' : 'none';
};

/**
 * Safe date formatting function that handles invalid Date objects
 *
 * @param {Date} date_object - The Date object to format
 * @param {string} format - The format string (ExtJS format)
 * @return {string} - Formatted date string or fallback placeholder
 */
timeslots.safe_date_format = function (date_object, format) {
	// Check if the date object is valid
	if (!date_object || isNaN(date_object.getTime())) {
		return og.preferences.date_format_tip || 'dd/mm/yyyy';
	}

	// Check individual date components for NaN
	var year = date_object.getFullYear();
	var month = date_object.getMonth();
	var day = date_object.getDate();

	if (isNaN(year) || isNaN(month) || isNaN(day)) {
		return og.preferences.date_format_tip || 'dd/mm/yyyy';
	}

	// If the date is valid, use the original dateFormat function
	try {
		return date_object.dateFormat(format);
	} catch (e) {
		return og.preferences.date_format_tip || 'dd/mm/yyyy';
	}
}
