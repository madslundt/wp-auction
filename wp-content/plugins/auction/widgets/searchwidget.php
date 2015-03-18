<?php
/**
 * @package Auction search
 * @version 1.0
 */

/**
 * WordPress Widget that makes it possible to style
 * and display a search tool for program listings using elasticsearch
 */
class SearchWidget extends WP_Widget {
    /**
     * Constructor
     */
    public function __construct() {
        
        parent::__construct(
            'search-widget',
            __('Search box',Auction::DOMAIN),
            array( 'description' => __('Search for auctions',Auction::DOMAIN) )
        );

        $this->fields = array(
            array(
                'title' => __('Search box',Auction::DOMAIN),
                'description' => __('Search description',Auction::DOMAIN)
            ),
        );
    }

    private function getSearchBox() {
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/search.php';
        return ob_get_clean();
    }

    /**
     * GUI for widget content
     * 
     * @param  array $args Sidebar arguments
     * @param  array $instance Widget values from database
     * @return void 
     */
    public function widget( $args, $instance ) {
        // $title = apply_filters( 'widget_title', $instance['title'] );
        echo $args['before_widget'];
        // echo $args['before_title'];
        // echo $args['after_title'];
        echo $this->getSearchBox();
        echo $args['after_widget'];
    }

    // Widget Backend 
    public function form($instance) {
        $title = '';
        if (isset($instance['title'])) {
            $title = $instance['title'];
        }
    ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
    <?php 
    }
        
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $updated_instance = $new_instance;
        return $updated_instance;
    }
}