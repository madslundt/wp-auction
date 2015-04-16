jQuery(function($) {
    var auction_admin_functions = {
        init: function() {
            this.setStartDatepicker($('.js-start-datepicker'));
            this.setEndDatepicker($('.js-end-datepicker'));
            this.dateListen();
            this.countryListen();
            this.addDate();
            this.removeDate();
            this.selectAddress();
        },
        setRegions: function(country_short_name) {
            $.ajax({
                url: auction_admin.ajaxurl,
                data: {
                    action: 'auction_get_regions',
                    token: auction_admin.token,
                    country_short_name: country_short_name
                },
                dataType: 'JSON',
                type: 'POST',
                success: function(data) {
                    regions = data;
                    if (regions) {
                        $('.js-auction-region').removeClass('disabled').removeAttr('disabled');
                        $('.js-auction-region').html('');
                        for (region in regions) {
                            $('.js-auction-region').append('<option name="' + regions[region].name + '">' + regions[region].name + '</option>');
                        }
                    }
                },
                error: function(errorThrown) {
                    alert(errorThrown.responseText);
                }
            });
        },
        countryListen: function() {
            $('.js-auction-country').change(function () {
                auction_admin_functions.setRegions($(this).val());
            });
        },
        setStartDatepicker: function(dp) {
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
        },
        setEndDatepicker: function(dp) {
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
        },
        dateListen: function() {
            $('.dates').on('focus', '.js-start-datepicker', function() {
                auction_admin_functions.setStartDatepicker($(this));
                return false;
            });
            $('.dates').on('focus', '.js-end-datepicker', function() {
                auction_admin_functions.setEndDatepicker($(this));
                return false;
            });
        },
        addDate: function() {
            $('.js-add-dates').click(function (e) {
                e.preventDefault();
                dates = $('.dates li:last-child').clone();
                dates.children('input').removeClass('hasDatepicker').removeAttr('id').val('');

                $('.dates').append(dates);
                $('.dates li .remove').css({'display': 'inline'});
                auction_admin_functions.setStartDatepicker(dates.children('.js-start-datepicker'));
                auction_admin_functions.setEndDatepicker(dates.children('.js-end-datepicker'));
                return false;
            });
        },
        removeDate: function() {
            $('.dates').on('click', '.remove', function (e) {
                e.preventDefault();
                $(this).parent('li').remove();
                var length = $('.dates').children('li').length;
                if (length <= 1) {
                    $('.dates li .remove').css({'display': 'none'});
                }
                return false;
            });
        },
        selectAddress: function() {
            $('.auction-address .js-auction-preaddresses').change(function () {
                var selected = $(this).find(':selected');
                
                var street_name = selected.data('street-name');
                var street_number = selected.data('street-number');
                var zip_code = selected.data('zip-code');
                var city = selected.data('city');
                var region = selected.data('region');
                var country = selected.data('country');

                if (country) {
                    $('.auction-address .js-auction-country').val(country);
                } else {
                    $('.auction-address .js-auction-country').val(
                        $('.auction-address .js-auction-country option:first-child').val()
                    );
                }
                if (region) {
                    $('.auction-address .js-auction-region').val(region);
                } else {
                    $('.auction-address .js-auction-region').val(
                        $('.auction-address .js-auction-region option:first-child').val()
                    );
                }
                $('.auction-address .js-auction-city').val(city);
                $('.auction-address .js-auction-zip-code').val(zip_code);
                $('.auction-address .js-auction-street-name').val(street_name);
                $('.auction-address .js-auction-street-number').val(street_number);
            });
        }
    };

    $(document).ready(function(){ auction_admin_functions.init(); });
});