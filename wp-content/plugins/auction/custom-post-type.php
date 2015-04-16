<?php
/**
 * @package Auction custom post type
 * @version 1.0
 */

function auction_register_post_type() {
    $rewrite_slug = Auction::CUSTOM_POST_TYPE;

    register_post_type( Auction::CUSTOM_POST_TYPE, array(
        'labels'             => array(
            'name'               => __('Auctions',Auction::DOMAIN),
            'singular_name'      => __('Auction',Auction::DOMAIN),
            'add_new'            => _x('Add New','auction',Auction::DOMAIN),
            'add_new_item'       => __('Add New Auction',Auction::DOMAIN),
            'edit_item'          => __('Edit Auction',Auction::DOMAIN),
            'new_item'           => __('New Auction',Auction::DOMAIN),
            'all_items'          => __('All Auctions',Auction::DOMAIN),
            'view_item'          => __('View Auction',Auction::DOMAIN),
            'search_items'       => __('Search Auctions',Auction::DOMAIN),
            'not_found'          => __('No Auctions found',Auction::DOMAIN),
            'not_found_in_trash' => __('No Auctions found in Trash',Auction::DOMAIN),
        ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => true,
        'query_var'          => Auction::CUSTOM_POST_TYPE,
        //'taxonomies'         => array(Auction::CUSTOM_POST_TYPE . '_categories'),
        'taxonomies'         => array('category'),
        'rewrite'            => array('slug' => Auction::CUSTOM_POST_TYPE . '/%category%', 'with_front' => false),
        'menu_icon'          => 'dashicons-cart',
        'capabilities'       =>     array(
            'edit_post'      => 'read',
            'read_post'      => 'read',
            'delete_post'        => 'read',
            'edit_posts'         => 'read',
            'edit_others_posts'  => 'moderate_comments',
            'publish_posts'      => 'read',
            'read_private_posts'     => 'moderate_comments',
            'delete_posts'         => 'moderate_comments',
            'delete_private_posts'   => 'moderate_comments',
            'delete_published_posts' => 'moderate_comments',
            'delete_others_posts'    => 'moderate_comments',
            'edit_private_posts'     => 'read',
            'edit_published_posts'   => 'read',
        ),
        'has_archive'        => Auction::CUSTOM_POST_TYPE,
        'hierarchical'       => true,
        'menu_position'      => 28,
        'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
        'status' => array(
            'draft' => array(
                'label' => __('New')
            ),
            'closed' => array(
                'label' => __('Closed')
            )
        )
    ));
}
add_action( 'init', 'auction_register_post_type', 0 );
/*function auction_taxonomy() {
    register_taxonomy(
        Auction::CUSTOM_POST_TYPE . '_categories',  //The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces).
        Auction::CUSTOM_POST_TYPE,            //post type name
        array(
            'hierarchical'      => true,
            'label'             => __('Categories'),
            'query_var'         => true,
            'rewrite'           => array(
                'slug'          => Auction::CUSTOM_POST_TYPE, // This controls the base slug that will display before each term
                'with_front'    => false // Don't display the category base before
            )
        )        
    );
}
add_action( 'init', 'auction_taxonomy');*/

function filter_post_type_link( $link, $post) {
    if ( $post->post_type != Auction::CUSTOM_POST_TYPE )
        return $link;

    if ( $cats = get_the_terms( $post->ID, 'category' ) )
        $link = str_replace( '%category%', array_pop($cats)->slug, $link );
    return $link;
}
add_filter('post_type_link', 'filter_post_type_link', 10, 2);

/*function auction_taxonomies_categories() {
    register_taxonomy('category', Auction::CUSTOM_POST_TYPE, array(
            'labels' => array(
                'name'               => __('Categories'),
                'singular_name'      => __('Category'),
                'add_new'            => _x('Add New','category',Auction::DOMAIN),
                'add_new_item'       => __('Add New Category',Auction::DOMAIN),
                'new_item_name'      => __('Category name',Auction::DOMAIN),
                'update_item'        => __('Update Categories',Auction::DOMAIN),
                'parent_item'        => __('Parent Category',Auction::DOMAIN ),
                'edit_item'          => __('Edit Category',Auction::DOMAIN),
                'new_item'           => __('New Category',Auction::DOMAIN),
                'all_items'          => __('All Category',Auction::DOMAIN),
                'view_item'          => __('View Category',Auction::DOMAIN),
                'search_items'       => __('Search Category',Auction::DOMAIN),
                'not_found'          => __('No Category found',Auction::DOMAIN),
                'not_found_in_trash' => __('No Category found in Trash',Auction::DOMAIN),
                'menu_name'          => __('Category')
            ),
            'hierarchical'  => true,
            'public'        => true,
            'query_var'     => 'category',
            'rewrite'       =>  array('slug' => Auction::CUSTOM_POST_TYPE ),
            '_builtin'      => false,
        )
    );
}
add_action( 'init', 'auction_taxonomies_categories', 0 );*/

/*function category_permalink($permalink, $post_id, $leavename) {
    if (strpos($permalink, '%category%') === FALSE) return $permalink;
        // Get post
        $post = get_post($post_id);
        if (!$post) return $permalink;

        // Get taxonomy terms
        $terms = wp_get_object_terms($post->ID, 'category');
        if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0]))
            $taxonomy_slug = $terms[0]->slug;
        else $taxonomy_slug = 'no-category';

    return str_replace('%category%', $taxonomy_slug, $permalink);
}
add_filter('post_link', 'category_permalink', 1, 3);
add_filter('post_type_link', 'category_permalink', 1, 3);*/

function auction_custom_post_status() {
    register_post_status( 'closed', array(
        'label'                     => _x( 'Closed', Auction::CUSTOM_POST_TYPE ),
        'public'                    => true,
        'show_in_admin_all_list'    => false,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>' )
    ) );
}
add_action( 'init', 'auction_custom_post_status' );

function auction_append_post_status_list() {
    global $post;
    $complete = '';
    $label = '';
    if($post->post_type == Auction::CUSTOM_POST_TYPE) {
        if($post->post_status == 'closed'){
           $complete = ' selected="selected"';
           $label = '<span id="post-status-display"> ' . __('Closed', Auction::DOMAIN) . '</span>';
        }
        echo '
        <script>
        jQuery(document).ready(function($){
           $(\'select#post_status\').append(\'<option value="closed"'.$complete.'>' . __('Closed', Auction::DOMAIN) . '</option>\');
           $(\'.misc-pub-section label\').append(\''.$label.'\');
        });
        </script>
        ';
    }
}
add_action('admin_footer-post.php', 'auction_append_post_status_list');

function auction_display_archive_state( $states ) {
     global $post;
     $arg = get_query_var( 'post_status' );
     if($arg != 'closed'){
          if($post->post_status == 'closed'){
               return array('Closed');
          }
     }
    return $states;
}
add_filter( 'display_post_states', 'auction_display_archive_state' );


/**
 * Product custom post type update labels.
 */
function updated_auction_messages( $messages ) {
    global $post, $post_ID;
    $messages[Auction::CUSTOM_POST_TYPE] = array(
        0 => '', 
        1 => sprintf( __('Auction updated. <a href="%s">View auction</a>'), esc_url( get_permalink($post_ID) ) ),
        2 => __('Custom field updated.'),
        3 => __('Custom field deleted.'),
        4 => __('Auction updated.'),
        5 => isset($_GET['revision']) ? sprintf( __('Auction restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
        6 => sprintf( __('Auction published. <a href="%s">View auction</a>'), esc_url( get_permalink($post_ID) ) ),
        7 => __('Auction saved.'),
        8 => sprintf( __('Auction submitted. <a target="_blank" href="%s">Preview auction</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
        9 => sprintf( __('Auction scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview auction</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
        10 => sprintf( __('Auction draft updated. <a target="_blank" href="%s">Preview auction</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    );
    return $messages;
}
add_filter( 'post_updated_messages', 'updated_auction_messages' );


function auction_edit_columns($columns) {
  $columns = array(
    "cb" => "<input type='checkbox' />",
    "title" => __("Title"),
    "categories" => __("Categories"),
    "type" => __("Type", Auction::DOMAIN),
    "date" => __("Date"),
    "active" => __("Active", Auction::DOMAIN),
    "comments" => __("Comments"),
    "thumbnail" => __("Thumbnail", Auction::DOMAIN)
  );
 
  return $columns;
}
add_filter("manage_auction_posts_columns", "auction_edit_columns");

function auction_post_columns($columns) {
    $columns['price'] = __('Price', Auction::DOMAIN);
    return $columns;
}
add_filter('manage_auction_post_columns', 'auction_post_columns');

function auction_render_post_columns($column, $id) {
    global $post;
    switch ($column) 
{        case 'type':
            $type = get_post_meta( $id, Auction::PRICE_POST_META, true) ? __('Lend', Auction::DOMAIN) . ' (' . get_post_meta( $id, Auction::PRICE_POST_META, true) . ')' : __('Lease', Auction::DOMAIN);
            echo '<a href="edit.php?post_type=' . Auction::CUSTOM_POST_TYPE . '&amp;type=' . strtolower($type) . '">' . $type . '</a>';
            // TODO: Filter search results
            break;
        case 'active':
            if (Auction::get_dates($id, true)) {
                echo '<a href="edit.php?post_type=' . Auction::CUSTOM_POST_TYPE . '&amp;active=1">' . __('Active', Auction::DOMAIN) . '</a>';
            } else {
                echo '<a href="edit.php?post_type=' . Auction::CUSTOM_POST_TYPE . '&amp;active=0">' . __('Not active', Auction::DOMAIN) . '</a>';
            }

            break;
        case 'thumbnail':
            echo '<style>.fixed .column-comments{width: 9em !important;}</style>';
            Auction::printThumbnail($id);
            break;
    }
}
add_action('manage_auction_posts_custom_column', 'auction_render_post_columns', 10, 2);

function custom_meta_box() {
    add_meta_box( 
        'price',
        __( 'Price', Auction::DOMAIN ),
        'price_box_content',
        Auction::CUSTOM_POST_TYPE,
        'side',
        'low'
    );
    add_meta_box( 
        'dates', 
        __('Dates', Auction::DOMAIN), 
        'dates_box_content', 
        Auction::CUSTOM_POST_TYPE,
        'normal',
        'high'
    );
    add_meta_box( 
        'address', 
        __('Address', Auction::DOMAIN), 
        'address_box_content', 
        Auction::CUSTOM_POST_TYPE,
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'custom_meta_box' );

function save_custom_fields($post_id) {
    /*if (!isset($_POST['start_price'])) return $post_id;
    if ( !wp_verify_nonce( $_POST['start_price'], plugin_basename(__FILE__) ) )
        return $post_id;
    if (!isset($_POST['end_date'])) return $post_id;
    if ( !wp_verify_nonce( $_POST['end_date'], plugin_basename(__FILE__) ) )
        return $post_id;
    if (!isset($_POST['address'])) return $post_id;
    if ( !wp_verify_nonce( $_POST['address'], plugin_basename(__FILE__) ) )
        return $post_id;*/

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return $post_id;

    // Price
    if (isset($_POST['price']) && !empty($_POST['price'])) {
        if (!is_numeric($_POST['price']) || $_POST['price'] < 0) {
            add_settings_error(
                'start_praice',
                '',
                __('Price can not be less than 0', Auction::DOMAIN),
                'error'
            );
            set_transient( 'settings_errors', get_settings_errors(), 30 );
            return false;
        }
        if ($_POST['price'] > 0) {
            update_post_meta($post_id, Auction::PRICE_POST_META, $_POST['price']);
        } else {
            delete_post_meta($post_id, Auction::PRICE_POST_META);
        }
    }

    if (isset($_POST['start_dates']) && isset($_POST['end_dates'])) {
        $start_dates = $_POST['start_dates'];
        $end_dates   = $_POST['end_dates'];
        if (count($start_dates) != count($end_dates)) {
            add_settings_error(
                'dates',
                '',
                __('A start- or end date seems to be empty', Auction::DOMAIN),
                'error'
            );
            set_transient( 'settings_errors', get_settings_errors(), 30 );
            return false;
        }
        $dates_length = count($start_dates);
        for ($i = 0; $i < $dates_length; $i++) {
            $start_date = date_format(date_create_from_format(Auction::DATE_FORMAT_PHP, $start_dates[$i]), 'Y-m-d');
            $end_date = date_format(date_create_from_format(Auction::DATE_FORMAT_PHP, $end_dates[$i]), 'Y-m-d');
            $start_dparse = date_parse($start_date);
            $end_dparse = date_parse($end_date);
            if (!empty($start_dparse) && !empty($end_dparse) && $start_dparse['error_count'] === 0 && $end_dparse['error_count'] === 0) {
                if (strtotime($end_date) < strtotime($start_date)) {
                    add_settings_error(
                        'dates',
                        '',
                        __('End date has to be atleast or equal to start date', Auction::DOMAIN),
                        'error'
                    );
                    set_transient( 'settings_errors', get_settings_errors(), 30 );
                    return false;
                }
            } else if ($i === 0) {
                add_settings_error(
                    'dates',
                    '',
                    __('Wrong date', Auction::DOMAIN),
                    'error'
                );
                set_transient( 'settings_errors', get_settings_errors(), 30 );
                return false;
            }
            $start_dates[$i] = $start_date;
            $end_dates[$i]   = $end_date;
        }

        if (!Auction::set_dates($post_id, $start_dates, $end_dates)) {
            add_settings_error(
                'dates',
                '',
                __('Wrong dates', Auction::DOMAIN),
                'error'
            );
            set_transient( 'settings_errors', get_settings_errors(), 30 );
            return false;
        }
    } else {
        add_settings_error(
            'dates',
            '',
            __('No dates', Auction::DOMAIN),
            'error'
        );
        set_transient( 'settings_errors', get_settings_errors(), 30 );
        return false;
    }

    if (empty($_POST['country']) ||
        empty($_POST['region']) ||
        empty($_POST['street-name']) ||
        empty($_POST['street-number']) ||
        empty($_POST['city']) ||
        empty($_POST['zip-code'])) {
        add_settings_error(
            'address',
            '',
            __('Invalid address', Auction::DOMAIN),
            'error'
        );
        set_transient( 'settings_errors', get_settings_errors(), 30 );
        return false;
    } else {
        $address = array(
            'country' => $_POST['country'],
            'region' => $_POST['region'],
            'street_name' => $_POST['street-name'],
            'street_number' => $_POST['street-number'],
            'city' => $_POST['city'],
            'zip_code' => $_POST['zip-code']
        );
        if (!Auction::set_product_address($address, $post_id)) {
            add_settings_error(
                'address',
                '',
                __('Invalid address', Auction::DOMAIN),
                'error'
            );
            set_transient( 'settings_errors', get_settings_errors(), 30 );
            return false;
        }
    }
    // TODO: Save address to a city, zip code, region etc.
}
add_action('save_post_' . Auction::CUSTOM_POST_TYPE, 'save_custom_fields');


function price_box_content($post) {
    wp_nonce_field( plugin_basename(__FILE__), 'price' );
    ?>
        <input type="number" name="price" value="<?php echo get_post_meta( $post->ID, Auction::PRICE_POST_META, true ); ?>" /> DKK
    <?php
}

function dates_box_content($post) {
    wp_nonce_field( plugin_basename(__FILE__), 'dates' );
    $dates = Auction::get_dates($post->ID);
    ?>
    <ul class="dates">
    <?php
    if ($dates):
        foreach ($dates as $key => $date):
            $start_date = date_format(date_create_from_format('Y-m-d', $date->start), Auction::DATE_FORMAT_PHP);
            $end_date   = date_format(date_create_from_format('Y-m-d', $date->end), Auction::DATE_FORMAT_PHP);
        ?>
            <li>
                <input type="datetime" name="start_dates[]" class="date-input js-start-datepicker" value="<?php echo $start_date; ?>" placeholder="<?php _e('Start date', Auction::DOMAIN); ?>"/>
                <input type="datetime" name="end_dates[]" class="date-input js-end-datepicker" value="<?php echo $end_date; ?>" placeholder="<?php _e('End date', Auction::DOMAIN); ?>"/>
                <a class="remove" href="#"<?php if (count($dates) === 1): ?> style="display: none;"<?php endif; ?>>&times;</a>
            </li>
        <?php
        endforeach;
    else:
    ?>
        <li>
            <input type="datetime" name="start_dates[]" class="date-input js-start-datepicker" value="" placeholder="<?php _e('Start date', Auction::DOMAIN); ?>"/>
            <input type="datetime" name="end_dates[]" class="date-input js-end-datepicker" value="" placeholder="<?php _e('End date', Auction::DOMAIN); ?>"/>
            <a class="remove" href="#" style="display: none;">&times;</a>
        </li>
    <?php
    endif;
    ?>
    </ul>
    <a class="button-secondary js-add-dates" href="#"><?php _e('Add one more date', Auction::DOMAIN); ?></a>
    <?php
}

function address_box_content($post) {
    wp_nonce_field( plugin_basename(__FILE__), 'address' );
    $user_address = Auction::get_user_address();
    $product_addresses = Auction::get_product_addresses();
    $countries = Auction::get_countries();
    $set_region = true;
    $is_new = get_post_meta($post->ID, Auction::ADDRESS_USER_META, true);
    $is_new = empty($is_new);

    $user_country       = isset($user_address) ? $user_address->country_short : '';
    $user_region        = isset($user_address) ? $user_address->region_id : '';
    $user_street_name   = isset($user_address) ? $user_address->street_name : '';
    $user_street_number = isset($user_address) ? $user_address->street_number : '';
    $user_city          = isset($user_address) ? $user_address->city : '';
    $user_zip_code      = isset($user_address) ? $user_address->zip_code : '';
    ?>
    <div class="auction-address">
        <div>
            <select class="js-auction-preaddresses">
                <option data-street-name="<?php echo $user_street_name; ?>" 
                        data-street-number="<?php echo $user_street_number; ?>"
                        data-zip-code="<?php echo $user_zip_code; ?>"
                        data-city="<?php echo $user_city; ?>"
                        data-region="<?php echo $user_region; ?>"
                        data-country="<?php echo $user_country; ?>"
                        value="user">
                            <?php _e('Use own address', Auction::DOMAIN); ?>
                        </option>
                <?php foreach ($product_addresses as $address): ?>
                    <option
                        data-street-name="<?php echo $address->street_name; ?>" 
                        data-street-number="<?php echo $address->street_number; ?>"
                        data-zip-code="<?php echo $address->zip_code; ?>"
                        data-city="<?php echo $address->city; ?>"
                        data-region="<?php echo $address->region_id; ?>"
                        data-country="<?php echo $address->short_name; ?>"
                        value="<?php echo $address->ID; ?>" <?php selected($post->ID, $address->ID); ?>>
                            <?php printf('%s: %s %s', $address->name, $address->street_name, $address->street_number); ?>
                        </option>
                        <?php
                        if ($post->ID === $address->ID) {
                            $set_region = false;
                            $user_country = $address->short_name;
                            $user_region  = $address->region_id;
                            $user_street_name = $address->street_name;
                            $user_street_number = $address->street_number;
                            $user_city = $address->city;
                            $user_zip_code = $address->zip_code;
                        }
                        ?>
                <?php endforeach; ?>
                <optgroup label="--------------"></optgroup>
                <option data-street-name="<?php echo isset($_POST['street-name']) ? $_POST['street-name'] : ''; ?>" 
                        data-street-number="<?php echo isset($_POST['street-number']) ? $_POST['street-number'] : ''; ?>"
                        data-zip-code="<?php echo isset($_POST['zip-code']) ? $_POST['zip-code'] : ''; ?>"
                        data-city="<?php echo isset($_POST['city']) ? $_POST['city'] : ''; ?>"
                        data-region="<?php echo isset($_POST['region']) ? $_POST['region'] : ''; ?>"
                        data-country="<?php echo isset($_POST['country']) ? $_POST['country'] : ''; ?>"
                        value="custom" <?php selected($set_region && !$is_new, true); ?>>
                            <?php _e('Enter new address..', Auction::DOMAIN); ?>
                        </option>
            </select>
        </div>
        <div>
            <label for="country"><?php _e('Country', Auction::DOMAIN); ?>
            <select name="country" class="js-auction-country">
                <?php foreach ($countries as $country): ?>
                    <option value="<?php echo $country->short_name; ?>"<?php selected($country->short_name, $user_country); ?>><?php echo $country->name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <?php 
            if ($set_region) {
                $regions = Auction::get_regions(isset($user_address) ? $user_address->country_short : $countries[0]->short_name);
            } else {
                $regions = Auction::get_regions($user_country);
            }
            ?>
            <label for="region"><?php _e('Region', Auction::DOMAIN); ?></label>
            <select name="region" class="js-auction-region">
                <?php foreach ($regions as $region): ?>
                    <option value="<?php echo $region->ID; ?>"<?php selected($region->ID, $user_region); ?>><?php echo $region->name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <label for="street-name"><?php _e('Street name', Auction::DOMAIN); ?></label><br />
        <input type="text" name="street-name" class="js-auction-street-name" value="<?php echo $user_street_name; ?>" /><br />
        <label for="street-number"><?php _e('Street number', Auction::DOMAIN); ?></label><br />
        <input type="text" name="street-number" class="js-auction-street-number" value="<?php echo $user_street_number; ?>" /><br />
        <label for="city"><?php _e('City', Auction::DOMAIN); ?></label><br />
        <input type="text" name="city" class="js-auction-city" value="<?php echo $user_city; ?>" /><br />
        <label for="zip-code"><?php _e('Zip code', Auction::DOMAIN); ?></label><br />
        <input type="text" name="zip-code" class="js-auction-zip-code" value="<?php echo $user_zip_code; ?>" /><br />
    </div>
    <?php
}

function _location_admin_notices() {
    // If there are no errors, then we'll exit the function
    if ( ! ( $errors = get_transient( 'settings_errors' ) ) ) {
    return;
    }

    // Otherwise, build the list of errors that exist in the settings errores
    $message = '<div id="acme-message" class="error below-h2"><p><ul>';
    foreach ( $errors as $error ) {
    $message .= '<li>' . $error['message'] . '</li>';
    }
    $message .= '</ul></p></div><!-- #error -->';

    // Write them out to the screen
    echo $message;

    // Clear and the transient and unhook any other notices so we don't see duplicate messages
    delete_transient( 'settings_errors' );
    remove_action( 'admin_notices', '_location_admin_notices' );
}
add_action( 'admin_notices', '_location_admin_notices' );

function Auction_rewrite_flush() {
    Auction_register_exhibition_type();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'auction_rewrite_flush' );

//eol