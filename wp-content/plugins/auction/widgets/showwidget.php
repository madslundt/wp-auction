<?php
/**
 * @package Auction search
 * @version 1.0
 */

/**
 * WordPress Widget that makes it possible to style
 * and display a search tool for program listings using elasticsearch
 */
class ShowWidget extends WP_Widget {
    /**
     * Constructor
     */

    public function __construct() {
        
        parent::__construct(
            'show-auctions-widget',
            __('Show auctions',Auction::DOMAIN),
            array( 'description' => __('Get a list of auctions',Auction::DOMAIN) )
        );

        $this->fields = array(
            array(
                'title' => __('Title'),
                'description' => __('Description')
            )
        );
    }

    private function getNewest($template = 'newest', $max_auctions = 10) {
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/' . $template . '.php';
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
        $title = apply_filters( 'widget_title', $instance['title'] );
        $max_auctions = $instance['max_auctions'] ? $instance['max_auctions'] : 10;

        echo $args['before_widget'];
        echo '<div class="box new-auctions">';
        echo $args['before_title'];
        if ($instance['title']) {
        	echo '<h3>' . $instance['title'] . '</h3>';
        }
        echo $args['after_title'];
        echo $this->getNewest($instance['mode'], $max_auctions);
        echo '</div>';
        echo $args['after_widget'];
    }

    // Widget Backend 
    public function form($instance) {
        $title = '';
        if (isset($instance['title'])) {
            $title = $instance['title'];
        }
        $max_auctions = 10;
        if (isset($instance['max_auctions'])) {
            $max_auctions = $instance['max_auctions'];
        }
        $mode = '';
        if (isset($instance['mode'])) {
            $mode = $instance['mode'];
        }
    ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'max_auctions' ); ?>"><?php _e('Number of auctions:', Auction::DOMAIN); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'max_auctions' ); ?>" name="<?php echo $this->get_field_name( 'max_auctions' ); ?>" type="number" value="<?php echo esc_attr( $max_auctions ); ?>" />
        </p>
        <p>
			<label for="<?php echo $this->get_field_id('mode'); ?>"><?php _e('Auction mode:', Auction::DOMAIN); ?>
				<select class='widefat' id="<?php echo $this->get_field_id('mode'); ?>" name="<?php echo $this->get_field_name('mode'); ?>" type="text">
					<option value='latest'<?php echo ($mode=='latest')?'selected':''; ?>>
					Latest auctions right now
					</option>
					<option value='hottest'<?php echo ($mode=='hottest')?'selected':''; ?>>
					Hottest auction right now
					</option> 
					<option value='newest'<?php echo ($mode=='newest')?'selected':''; ?>>
					Newest auctions right now
					</option> 
				</select>                
			</label>
        </p>
    <?php 
    }
        
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $updated_instance = $new_instance;
        return $updated_instance;
    }
}