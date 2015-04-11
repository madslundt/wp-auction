jQuery(function($) {
    setStartDatepicker($('.js-start-datepicker'));
    setEndDatepicker($('.js-end-datepicker'));
    $('.dates').on('focus', '.js-start-datepicker', function() {
    	setStartDatepicker($(this));
        return false;
    });
    $('.dates').on('focus', '.js-end-datepicker', function() {
    	setEndDatepicker($(this));
        return false;
    });

    $('.js-add-dates').click(function (e) {
    	e.preventDefault();
    	dates = $('.dates li:last-child').clone();
    	dates.children('input').removeClass('hasDatepicker').removeAttr('id').val('');
    	dates.children('.remove').css({'display': 'inline'});

    	$('.dates').append(dates);
        setStartDatepicker(dates.children('.js-start-datepicker'));
        setEndDatepicker(dates.children('.js-end-datepicker'));
    	return false;
    });

    $('.dates').on('click', '.remove', function (e) {
    	e.preventDefault();
    	$(this).parent('li').remove();
    	var length = $('.dates').children('li').length;
    	if (length > 1) {
    		$('.dates li:last-child .remove').css({'display': 'inline'});
    	} else {
            $('.dates li .remove').css({'display': 'none'});
        }
    	return false;
    });
});

function setStartDatepicker(dp) {
    dp.datepicker({
        firstDay: 1,
        minDate: 0,
        changeMonth: true,
        dateFormat: auction_admin.date_format,
        onClose: function( selectedDate ) {
            var next_datepicker = dp.next('.js-end-datepicker');
            next_datepicker.datepicker('option', 'minDate', selectedDate );
            if (dp.datepicker('getDate') > next_datepicker.datepicker('getDate')) {
                next_datepicker.datepicker('setDate', dp.datepicker('getDate'));
            }
        }
    });
}

function setEndDatepicker(dp) {
    dp.datepicker({
        firstDay: 1,
        minDate: 0,
        changeMonth: true,
        dateFormat: auction_admin.date_format,
        onClose: function( selectedDate ) {
            var prev_datepicker = dp.prev('.js-start-datepicker');
            prev_datepicker.datepicker('option', 'maxDate', selectedDate );
            if (dp.datepicker('getDate') < prev_datepicker.datepicker('getDate')) {
                prev_datepicker.datepicker('setDate', dp.datepicker('getDate'));
            }
        }
    });
}