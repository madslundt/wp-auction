<?php
    add_action( 'show_user_profile', 'extra_user_profile_fields' );
    add_action( 'edit_user_profile', 'extra_user_profile_fields' );
    function extra_user_profile_fields( $user ) {
        $address   = Auction::get_user_address();
        $countries = Auction::get_countries();
        $regions   = $address ? Auction::get_regions($address->country_short) : Auction::get_regions($countries[0]->short_name);

        $city = isset($_POST['country']) ? $_POST['country'] : isset($address->city) ? $address->city : '';
        $zip_code = isset($_POST['zip_code']) ? $_POST['zip_code'] : isset($address->zip_code) ? $address->zip_code : '';
        $street_name = isset($_POST['street_name']) ? $_POST['street_name'] : isset($address->street_name) ? $address->street_name : '';
        $street_number = isset($_POST['street_number']) ? $_POST['street_number'] : isset($address->street_number) ? $address->street_number : '';
?>
        <h3><?php _e("Address", Auction::DOMAIN); ?></h3>
        <table class="form-table">
            <tr class="user-auction-country-wrap">
                <th><label for="country"><?php _e('Country', Auction::DOMAIN); ?></label></th>
                <td>
                    <select name="country" id="country">
                        <?php foreach ($countries as $country): ?>
                            <option value="<?php echo $country->short_name; ?>" <?php if ($address) {selected(isset($_POST['country']) ? $_POST['country'] : $address->country_short, $country->short_name);} ?>><?php echo $country->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="user-auction-region-wrap">
                <th><label for="region"><?php _e('Region', Auction::DOMAIN); ?></label></th>
                <td>
                    <select name="region" id="region">
                        <?php foreach ($regions as $region): ?>
                            <option value="<?php echo $region->ID; ?>" <?php if ($address&&$address->region_id) {selected(isset($_POST['region']) ? $_POST['region'] : $address->region_id, $region->ID);} ?>><?php echo $region->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="user-auction-streetname-wrap">
                <th><label for="street_name"><?php _e('Street', Auction::DOMAIN); ?></label></th>
                <td>
                    <input type="text" name="street_name" id="street_name" maxlength="100" value="<?php echo $street_name; ?>" class="regular-text" /><br />
                    <span class="description"><?php _e('Please enter your street name.', Auction::DOMAIN); ?></span><br />
                    <div>
                        <input type="text" name="street_number" id="street_number" maxlength="20" value="<?php echo $street_number; ?>" /><br />
                        <span class="description"><?php _e('Please enter your street number.', Auction::DOMAIN); ?></span>
                    </div>
                </td>
            </tr>
            <tr class="user-auction-city-wrap">
                <th><label for="city"><?php _e('City', Auction::DOMAIN); ?></label></th>
                <td>
                    <input type="text" name="city" id="city" value="<?php echo $city; ?>" maxlength="100" class="regular-text" /><br />
                    <span class="description"><?php _e('Please enter your city.', Auction::DOMAIN); ?></span><br />
                    <div>
                        <input type="text" name="zip_code" id="zip_code" maxlength="15" value="<?php echo $zip_code; ?>" /><br />
                        <span class="description"><?php _e('Please enter your zip code.', Auction::DOMAIN); ?></span>
                    </div>
                </td>
            </tr>
        </table>
<?php 
    }
    add_action( 'personal_options_update', 'save_extra_user_profile_fields' );
    add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields' );
    function save_extra_user_profile_fields( $user_id ) {
        if (!current_user_can('edit_user', $user_id)) { 
            return false; 
        }
        $address = array(
            'street_name'   => ucwords($_POST['street_name']),
            'street_number' => $_POST['street_number'],
            'country'       => $_POST['country'],
            'region'        => $_POST['region'],
            'city'          => ucwords($_POST['city']),
            'zip_code'      => $_POST['zip_code']
        );

        Auction::set_user_address($address);
    }
?>