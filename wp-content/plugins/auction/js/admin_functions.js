jQuery(function($) {
    $( "#end_date_js" ).datepicker({
    	firstDay: 1,
    	minDate: 1,
    	maxDate: auction_admin.max_duration,
    	dateFormat: auction_admin.date_format
    });
});