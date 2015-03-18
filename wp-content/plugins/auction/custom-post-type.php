<?php
/**
 * @package Auction custom post type
 * @version 1.0
 */

$GLOBALS['custom_post_type_name'] = 'auction';

function auction_register_post_type() {
    global $custom_post_type_name;
    $rewrite_slug = $custom_post_type_name;

    register_post_type( $custom_post_type_name, array(
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
        'query_var'          => true,
        'taxonomies'         => array($custom_post_type_name . '_categories'),
        'rewrite'            => array('slug' => $custom_post_type_name . '/%'. $custom_post_type_name . '_categories%', 'with_front' => false),
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
        'has_archive'        => $custom_post_type_name,
        'hierarchical'       => true,
        'menu_position'      => 28,
        'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' ),
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
function auction_taxonomy() {
    global $custom_post_type_name;
    register_taxonomy(
        $custom_post_type_name . '_categories',  //The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces).
        $custom_post_type_name,            //post type name
        array(
            'hierarchical'      => true,
            'label'             => __('Categories'),
            'query_var'         => true,
            'rewrite'           => array(
                'slug'          => $custom_post_type_name, // This controls the base slug that will display before each term
                'with_front'    => false // Don't display the category base before
            )
        )        
    );
}
add_action( 'init', 'auction_taxonomy');

function filter_post_type_link( $link, $post) {
    global $custom_post_type_name;
    if ( $post->post_type != $custom_post_type_name )
        return $link;

    if ( $cats = get_the_terms( $post->ID, $custom_post_type_name . '_categories' ) )
        $link = str_replace( '%' . $custom_post_type_name . '_categories%', array_pop($cats)->slug, $link );
    return $link;
}
add_filter('post_type_link', 'filter_post_type_link', 10, 2);

/*function auction_taxonomies_categories() {
    global $custom_post_type_name;
    register_taxonomy('category', $custom_post_type_name, array(
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
            'rewrite'       =>  array('slug' => $custom_post_type_name ),
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
    global $custom_post_type_name;
    register_post_status( 'closed', array(
        'label'                     => _x( 'Closed', $custom_post_type_name ),
        'public'                    => true,
        'show_in_admin_all_list'    => false,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>' )
    ) );
}
add_action( 'init', 'auction_custom_post_status' );

function auction_append_post_status_list() {
    global $custom_post_type_name;
    global $post;
    $complete = '';
    $label = '';
    if($post->post_type == $custom_post_type_name) {
        if($post->post_status == 'closed'){
           $complete = ' selected="selected"';
           $label = '<span id="post-status-display"> ' . __('Closed', Auction::DOMAIN) . '</span>';
        }
        echo '
        <script>
        jQuery(document).ready(function($){
           $("select#post_status").append("<option value="closed" '.$complete.'>' . __('Closed', Auction::DOMAIN) . '</option>");
           $(".misc-pub-section label").append("'.$label.'");
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
    global $post, $post_ID, $custom_post_type_name;
    $messages[$custom_post_type_name] = array(
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
    "start_price" => __("Start price", Auction::DOMAIN),
    "date" => __("Date"),
    "comments" => __("Comments"),
    "thumbnail" => __("Thumbnail", Auction::DOMAIN)
  );
 
  return $columns;
}
add_filter("manage_auction_posts_columns", "auction_edit_columns");

function auction_post_columns($columns) {
    $columns['start_price'] = __('Start price', Auction::DOMAIN);
    return $columns;
}
add_filter('manage_auction_post_columns', 'auction_post_columns');

function auction_render_post_columns($column, $id) {
    global $post;
    switch ($column) {
        case 'start_price':
            echo get_post_meta( $id, 'start_price', true);
            break;
        case "thumbnail":
            $attachment_ids = explode( ',', get_post_meta( $id, '_easy_image_gallery', true ));
            $attachment_id = $attachment_ids[0];
            $image = wp_get_attachment_image( $attachment_id, apply_filters( 'easy_image_gallery_thumbnail_image_size', 'thumbnail' ), '', array( 'alt' => trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ) ) );
            echo $image;
            break;
    }
}
add_action('manage_auction_posts_custom_column', 'auction_render_post_columns', 10, 2);

function custom_meta_box() {
    global $custom_post_type_name;
    add_meta_box( 
        'start_price',
        __( 'Start price', Auction::DOMAIN ),
        'start_price_box_content',
        $custom_post_type_name,
        'side',
        'low'
    );
}
add_action( 'add_meta_boxes', 'custom_meta_box' );

function save_custom_fields($post_id) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return $post_id;

    // Start price
    if (isset($_POST['start_price'])) {
        update_post_meta($post_id, 'start_price', $_POST['start_price']);
    }

}
add_action('save_post', 'save_custom_fields');


function start_price_box_content($post) {
    ?>
        <input type="number" name="start_price" id="start_price" value="<?php echo get_post_meta( $post->ID, 'start_price', true ); ?>" /> DKK
    <?php
}


function Auction_rewrite_flush() {
    Auction_register_exhibition_type();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'auction_rewrite_flush' );

//eol