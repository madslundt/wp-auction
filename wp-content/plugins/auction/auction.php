<?php
/*
Plugin Name: Auction settings
Plugin URI: 
Description:
Version: 1.0
Author: Mads Lundt
Author URI: 
License: 
*/
class Auction {

    const DOMAIN = 'auction';
    const CUSTOM_POST_TYPE = 'auction';

    const LOGIN_PAGE = 'login-page';
    const SIGNUP_PAGE = 'signup-page';
    const SEARCH_PAGE = 'search_results-page';

    const DATE_FORMAT = 'dd/mm/yy';
    const DATE_FORMAT_PHP = 'd/m/Y';


    const QUERY_KEY_FREETEXT = 'text';
    const QUERY_KEY_PAGE = 'side';
    const QUERY_KEY_SORT = 'sorteret-efter';
    const QUERY_KEY_CATEGORY = 'i';
    const QUERY_PREFIX_CHAR = '-';
    const QUERY_DEFAULT_POST_SEPERATOR = '-';

    const FLUSH_REWRITE_RULES_OPTION_KEY = 'auction-flush-rewrite-rules';
    const FILTER_PREPARE_RESULTS = 'auction-prepare';

    const DATES_TABLE_PREFIX     = 'auction_dates';
    const REGIONS_TABLE_PREFIX   = 'auction_regions';
    const COUNTRIES_TABLE_PREFIX = 'auction_countries';
    const ADDRESS_TABLE_PREFIX   = 'auction_address';
    const ZIPCODES_TABLE_PREFIX  = 'auction_zipcodes';

    const PRICE_POST_META        = 'auction_price';

    public static $search_results;
    public static $search_query_variables = array();


    /**
     * Name for setting page
     * @var string
     */
    protected $menu_page = 'auction-settings';

    /**
     * Settings
     * @var array
     */
    protected $settings;

    /**
     * List of attributes that has a filter
     * @var array
     */
    public static $attributes;

    public function __construct() {
        $this->load_dependencies();

        add_action('init', array(&$this, 'create_tables'));
        
        if(is_admin()) {
            add_action('admin_menu', array(&$this,'create_submenu'));
            add_action('admin_init', array(&$this,'register_settings'));
            add_action('admin_init', array(&$this,'settings_updated'));
            add_action('admin_print_styles-post.php', array(&$this, 'load_admin_dependencies'));
            add_action('admin_print_styles-post-new.php', array(&$this, 'load_admin_dependencies'));
        }
        add_action('template_redirect', array(&$this, 'register_a_user'));

        add_action('template_redirect', array(&$this, 'get_search_page'));
        add_action('template_redirect', array(&$this, 'get_login_page'));
        add_action('template_redirect', array(&$this, 'get_signup_page'));

        self::register_search_query_variable(1, self::QUERY_KEY_FREETEXT, '[^/&]*?', false, null, '', '/');
        self::register_search_query_variable(4, self::QUERY_KEY_CATEGORY, '[^/&]+?', true);
        self::register_search_query_variable(5, self::QUERY_KEY_SORT, '[^/&]+?', true);
        self::register_search_query_variable(6, self::QUERY_KEY_PAGE, '\d+?', true);

        add_action('init', array(&$this, 'handle_rewrite_rules'));

        add_action('plugins_loaded',array(&$this,'load_textdomain'));

        add_filter('widgets_init',array(&$this,'register_widgets'));
    }

    public function load_textdomain() {
        load_plugin_textdomain( self::DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/');
    }

    public function register_widgets() {
        register_widget('SearchWidget');
        register_widget('ShowWidget');
    }

    private function load_dependencies() {
        require('widgets/searchwidget.php');
        require('widgets/showwidget.php');
        require('custom-post-type.php');
    }

    private function create_table($table, $sql) {

    }

    public function create_tables() {
        global $wpdb;

        /*===================================
        =            Dates table            =
        ===================================*/
        $dates_table = $wpdb->prefix . self::DATES_TABLE_PREFIX;

        if($wpdb->get_var("show tables like '$dates_table'") !== $dates_table) {
            $sql = "CREATE TABLE " . $dates_table . " (
                    `post_id` BIGINT(20) UNSIGNED NOT NULL,
                    `start` DATE NOT NULL,
                    `end` DATE NOT NULL,
                    PRIMARY KEY (`post_id`, `start`, `end`),
                    INDEX `fk_dates_post_idx` (`post_id` ASC),
                    CONSTRAINT `fk_dates_product`
                        FOREIGN KEY (`post_id`)
                        REFERENCES `$wpdb->posts` (`ID`)
                        ON DELETE CASCADE
                        ON UPDATE NO ACTION
                    ) ENGINE = InnoDB;
                ";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            if (!dbDelta($sql)) {
                return;
            }
        }
     
        if (!isset($wpdb->auction_dates)) {
            $wpdb->auction_dates = $dates_table;
            $wpdb->tables[] = str_replace($wpdb->prefix, '', $dates_table);
        }
        /*-----  End of Dates table  ------*/


        /*=====================================
        =            Countries table            =
        =====================================*/
        $countries_table = $wpdb->prefix . self::COUNTRIES_TABLE_PREFIX;
        if($wpdb->get_var("show tables like '$countries_table'") !== $countries_table) {
            $sql = "CREATE TABLE " . $countries_table . " (
                    `short_name` VARCHAR(3) NOT NULL,
                    `name` VARCHAR(100) NOT NULL,
                    PRIMARY KEY (`short_name`)
                    ) ENGINE = InnoDB;
                ";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            if (!dbDelta($sql)) {
                return;
            }
        }
     
        if (!isset($wpdb->auction_countries)) {
            $wpdb->auction_countries = $countries_table;
            $wpdb->tables[] = str_replace($wpdb->prefix, '', $countries_table);
        }

        // Inserting Denmark in countries
        $wpdb->query(
            "
            INSERT IGNORE INTO $wpdb->auction_countries
            (short_name, name)
            VALUES
            ('DK', 'Danmark')
            "
        );
        /*-----  End of Country table  ------*/
        

        /*=====================================
        =            Regions table            =
        =====================================*/        
        $regions_table = $wpdb->prefix . self::REGIONS_TABLE_PREFIX;
        if($wpdb->get_var("show tables like '$regions_table'") !== $regions_table) {
            $sql = "CREATE TABLE " . $regions_table . " (
                    `name` VARCHAR(100) NOT NULL,
                    `countries_short_name` VARCHAR(3) NOT NULL,
                    PRIMARY KEY (`name`, `countries_short_name`),
                    INDEX `fk_regions_countries1_idx` (`countries_short_name` ASC),
                    CONSTRAINT `fk_regions_countries1`
                        FOREIGN KEY (`countries_short_name`)
                        REFERENCES `$wpdb->auction_countries` (`short_name`)
                        ON DELETE RESTRICT
                        ON UPDATE NO ACTION
                    ) ENGINE = InnoDB;
                ";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            if (!dbDelta($sql)) {
                return;
            }
        }
     
        if (!isset($wpdb->auction_regions)) {
            $wpdb->auction_regions = $regions_table;
            $wpdb->tables[] = str_replace($wpdb->prefix, '', $regions_table);
        }

        // Inserting regions
        // Bornholm
        // Fyn
        // København
        // Københavns omegn
        // Nordjylland
        // Nordsjælland
        // Sydjylland
        // Vest- og Sydsjælland
        // Vestjylland
        // Østjylland
        // Østsjælland

        $wpdb->query(
            "
            INSERT IGNORE INTO $wpdb->auction_regions
            (countries_short_name, name)
            VALUES
            ('DK', 'Bornholm'),
            ('DK', 'Fyn'),
            ('DK', 'København'),
            ('DK', 'Københavns omegn'),
            ('DK', 'Nordjylland'),
            ('DK', 'Nordsjælland'),
            ('DK', 'Sydjylland'),
            ('DK', 'Vest- og Sydsjælland'),
            ('DK', 'Vestjylland'),
            ('DK', 'Østjylland'),
            ('DK', 'Østsjælland')
            "
        );
        /*-----  End of Regions table  ------*/


        /*=====================================
        =            Zip codes            =
        =====================================*/
        $zipcodes_table = $wpdb->prefix . self::ZIPCODES_TABLE_PREFIX;
        if($wpdb->get_var("show tables like '$zipcodes_table'") !== $zipcodes_table) {
            $sql = "CREATE TABLE " . $zipcodes_table . " (
                    `zip_code` VARCHAR(15) NOT NULL,
                    `regions_name` VARCHAR(100) NOT NULL,
                    `regions_countries_short_name` VARCHAR(3) NOT NULL,
                    `city` VARCHAR(100) NOT NULL,
                    PRIMARY KEY (`zip_code`, `regions_name`, `regions_countries_short_name`),
                    INDEX `fk_zip_codes_regions1_idx` (`regions_name` ASC, `regions_countries_short_name` ASC),
                    CONSTRAINT `fk_zip_codes_regions1`
                        FOREIGN KEY (`regions_name` , `regions_countries_short_name`)
                        REFERENCES `$wpdb->auction_regions` (`name` , `countries_short_name`)
                        ON DELETE RESTRICT
                        ON UPDATE NO ACTION
                    ) ENGINE = InnoDB;
                ";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            if (!dbDelta($sql)) {
                return;
            }
        }
     
        if (!isset($wpdb->auction_zipcodes)) {
            $wpdb->auction_zipcodes = $zipcodes_table;
            $wpdb->tables[] = str_replace($wpdb->prefix, '', $zipcodes_table);
        }
        /*-----  End of Zip codes  ------*/


        /*=====================================
        =            Address table            =
        =====================================*/
        $address_table = $wpdb->prefix . self::ADDRESS_TABLE_PREFIX;
        if($wpdb->get_var("show tables like '$address_table'") !== $address_table) {
            $sql = "CREATE TABLE " . $address_table . " (
                    `ID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `zip_codes_zip_code` VARCHAR(15) NOT NULL,
                    `zip_codes_regions_name` VARCHAR(100) NOT NULL,
                    `zip_codes_regions_countries_short_name` VARCHAR(3) NOT NULL,
                    `street_name` VARCHAR(100) NOT NULL,
                    `street_number` VARCHAR(20) NOT NULL,
                    PRIMARY KEY (`ID`),
                    INDEX `fk_address_zip_codes1_idx` (`zip_codes_zip_code` ASC, `zip_codes_regions_name` ASC, `zip_codes_regions_countries_short_name` ASC),
                    CONSTRAINT `fk_address_zip_codes1`
                        FOREIGN KEY (`zip_codes_zip_code` , `zip_codes_regions_name` , `zip_codes_regions_countries_short_name`)
                        REFERENCES `$wpdb->auction_zipcodes` (`zip_code` , `regions_name` , `regions_countries_short_name`)
                        ON DELETE RESTRICT
                        ON UPDATE NO ACTION
                    ) ENGINE = InnoDB;
                ";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            if (!dbDelta($sql)) {
                return;
            }
        }
     
        if (!isset($wpdb->auction_address)) {
            $wpdb->auction_address = $address_table;
            $wpdb->tables[] = str_replace($wpdb->prefix, '', $address_table);
        }
        /*-----  End of Address table  ------*/
    }

    public function settings_updated() {
        global $pagenow;
        $on_options_page = ($pagenow == 'options-general.php');
        $on_plugins_page = (isset($_GET['page']) && $_GET['page'] == $this->menu_page);
        $just_updated = (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true');
        
        if($on_options_page && $on_plugins_page && $just_updated) {
            do_action('auction-settings-updated');
        }
    }

    public function register_settings() {

        $pages = array();
        foreach(get_pages() as $page) {
            $pages[$page->ID] = $page->post_title;
        }

        // This include creates a variable called $settings.
        $settings = array(
            array(
                /*Sections*/
                'name'      => 'default',
                'title'     => __('General Settings', self::DOMAIN),
                'fields'    => array(
                    array(
                        'name' => self::LOGIN_PAGE,
                        'title' => __('Page for log in', self::DOMAIN),
                        'type' => 'select',
                        'list' => $pages,
                        /*'precond' => array(array(
                            'cond' => (get_option('permalink_structure') != ''),
                            'message' => __('Permalinks must be enabled for login page to work properly', self::DOMAIN)
                        ))*/
                    ),
                    array(
                        'name' => self::SIGNUP_PAGE,
                        'title' => __('Page for sign up', self::DOMAIN),
                        'type' => 'select',
                        'list' => $pages,
                        /*'precond' => array(array(
                            'cond' => (get_option('permalink_structure') != ''),
                            'message' => __('Permalinks must be enabled for sign up page to work properly', self::DOMAIN)
                        ))*/
                    ),
                    array(
                        'name' => self::SEARCH_PAGE,
                        'title' => __('Search page', self::DOMAIN),
                        'type' => 'select',
                        'list' => $pages,
                        /*'precond' => array(array(
                            'cond' => (get_option('permalink_structure') != ''),
                            'message' => __('Permalinks must be enabled for search page to work properly', self::DOMAIN)
                        ))*/
                    ),
                    array(
                        'name' => 'max_duration',
                        'title' => __('Maximum duration, in days, for an auction', self::DOMAIN),
                        'type' => 'number'
                    ),
                    array(
                        'name' => 'max_search_results',
                        'title' => __('Maximum number of auctions per page', self::DOMAIN),
                        'type' => 'number'
                    )
                )
            )
        );
        $this->settings = apply_filters('auction-config', $settings);

        foreach($this->settings as $section) {

            //Validate
            if(!isset($section['name'],$section['title'],$section['fields'])) 
                continue;

            //Add section to WordPress
            add_settings_section(
                $section['name'],
                $section['title'],
                null,
                $this->menu_page
            );

            foreach($section['fields'] as $setting) {
                //Validate
                if(!isset($setting['title'],$setting['name'],$setting['type']))
                    continue;

                //Are there any preconditions for this field to work properly?
                if(isset($setting['precond'])) {
                    foreach($setting['precond'] as $precondition) {
                        if(!$precondition['cond'])
                            add_action( 'admin_notices', function() use(&$precondition) { echo '<div class="error"><p>'.$precondition['message'].'</p></div>'; },10);
                    }               
                }

                // Add field to section
                add_settings_field($setting['name'],
                    $setting['title'],
                    array(&$this,'create_setting_field'),
                    $this->menu_page,
                    $section['name'],
                    $setting);

                // Register field to be manipulated with
                register_setting($this->menu_page,$setting['name']);
            }

        }       
        
    }

    public function create_submenu_page() {
        echo '<div class="wrap"><h2>'.get_admin_page_title().'</h2>'."\n";

        echo '<form method="POST" action="options.php">'."\n";
        settings_fields($this->menu_page);
        do_settings_sections($this->menu_page);
        submit_button();
        echo '</form></div>'."\n";
        
    }

    /**
     * Render field according to its type
     * @param  array $args Setting array
     * @return void
     */
    public function create_setting_field($args) {
        $class = isset($args['class'])?$args['class']:'regular-text';
        $current_value = get_option($args['name'], '');
        switch($args['type']) {
            case 'textarea':
                echo '<textarea class="'.$class.'" name="'.$args['name'].'" >'.$current_value.'</textarea>';
                break;
            case 'select':
                if(!is_array($args['list']))
                    $args['list'] = array();
                echo '<select class="'.$class.'" name="'.$args['name'].'">';
                foreach($args['list'] as $key => $value) {
                    echo '<option value="'.$key.'" '.selected( $current_value, $key, false).'>'.$value.'</option>';
                }
                echo '</select>';
                break;
            case 'password':
                echo '<input class="'.$class.'" name="'.$args['name'].'" type="password" value="'.$current_value.'" />';
                break;
            case 'number':
                echo '<input class="'.$class.'" name="'.$args['name'].'" type="number" value="'.$current_value.'" />';
                break;
            case 'text':
            default:
                echo '<input class="'.$class.'" name="'.$args['name'].'" type="text" value="'.$current_value.'" />';
        }
    }

    /**
     * Create submenu and call page for settings
     * @return void 
     */
    public function create_submenu() {
        add_submenu_page(
            'options-general.php',
            __('Auction', self::DOMAIN),
            __('Auction', self::DOMAIN),
            'manage_options',
            $this->menu_page,
            array(&$this,'create_submenu_page')
        ); 
    }

    public static function handle_rewrite_rules() {
        self::add_rewrite_tags();
        self::add_rewrite_rules();
        if(get_option(self::FLUSH_REWRITE_RULES_OPTION_KEY)) {
            delete_option(self::FLUSH_REWRITE_RULES_OPTION_KEY);
            if(WP_DEBUG) {
                add_action( 'admin_notices', function() {
                    echo '<div class="updated"><p><strong>'.__('WordPress auction Search',self::DOMAIN).'</strong> '.__('Rewrite rules flushed ..',self::DOMAIN).'</p></div>';
                }, 10);
            }
            flush_rewrite_rules();
        }
    }

    public static function get_search_vars($urldecode = true) {
        global $wp_query;
        $variables = array();
        foreach(self::$search_query_variables as $variable) {
            if(array_key_exists($variable['key'], $wp_query->query_vars)) {
                $value = $wp_query->query_vars[$variable['key']];
                if(gettype($value) == 'string') {
                    if($urldecode) {
                        $value = urldecode($value);
                    }
                    
                    $value = str_replace("\\\"", "\"", $value); // Replace \" with "
                    $value = str_replace("\\'", "\'", $value); // Replace \' with '
                    if(isset($variable['multivalue-seperator'])) {
                        if($value == '') {
                            $value = array();
                        } else {
                            $value = explode($variable['multivalue-seperator'], $value);
                        }
                    }
                }

                $variables[$variable['key']] = $value;
            }
            if($variable['default_value'] !== null && empty($variables[$variable['key']])) {
                $variables[$variable['key']] = $variable['default_value'];
            }
        }
        return $variables;
    }

    public static function get_search_var($query_key, $escape = false, $urldecode = true, $default = '') {
        $query_vars = self::get_search_vars($urldecode);
        if(array_key_exists($query_key, $query_vars)) {
            if($escape !== false) {
                $escape = explode(',', $escape);
                $result = $query_vars[$query_key];
                foreach($escape as $e) {
                    if(function_exists($e)) {
                        $result = $e($result);
                    } else {
                        throw new InvalidArgumentException('The $escape argument must be false or a 1-argument function.');
                    }
                }
                return $result;
            } else {
                return $query_vars[$query_key];
            }
        } else {
            return $default;
        }
    }
    
    public static function register_search_query_variable($position, $key, $regexp, $prefix_key = false, $multivalue_seperator = null, $default_value = null, $post_seperator = self::QUERY_DEFAULT_POST_SEPERATOR) {
        self::$search_query_variables[$position] = array(
            'key' => $key,
            'regexp' => $regexp,
            'prefix-key' => $prefix_key,
            'multivalue-seperator' => $multivalue_seperator,
            'default_value' => $default_value,
            'post-seperator' => $post_seperator
        );
        ksort(self::$search_query_variables);
    }
    
    /**
     * Add rewrite tags to WordPress installation
     */
    public static function add_rewrite_tags() {
        foreach(self::$search_query_variables as $variable) {
            add_rewrite_tag('%'.$variable['key'].'%', '('.$variable['regexp'].')');
        }
    }

    /**
     * Add rewrite rules to WordPress installation
     */
    public static function add_rewrite_rules() {
        if(get_option(self::SEARCH_PAGE)) {
            $searchPageID = intval(get_option(self::SEARCH_PAGE));
            $searchPageName = get_page_uri($searchPageID);
            $regex = $searchPageName . '/';
            foreach(self::$search_query_variables as $variable) {
                // An optional non-capturing group wrapped around the $regexp.
                if($variable['prefix-key'] == true) {
                    $regex .= sprintf('(?:%s(%s)%s?)?', $variable['key'].self::QUERY_PREFIX_CHAR, $variable['regexp'], $variable['post-seperator']);
                } else {
                    $regex .= sprintf('(?:(%s)%s?)?', $variable['regexp'], $variable['post-seperator']);
                }
            }
            $regex .= '$';
            
            $redirect = "index.php?page_id=$searchPageID";
            $v = 1;
            foreach(self::$search_query_variables as $variable) {
                // An optional non-capturing group wrapped around the $regexp.
                $redirect .= sprintf('&%s=$matches[%u]', $variable['key'], $v);
                $v++;
            }
            add_rewrite_rule($regex, $redirect, 'top');
        }
    }
    
    public static function search_query_prettify() {
        foreach(self::$search_query_variables as $variable) {
            if(array_key_exists($variable['key'], $_GET)) {
                $redirection = self::generate_pretty_search_url(self::get_search_vars(false));
                wp_redirect($redirection);
                exit();
            }
        }
    }
    
    public static function generate_pretty_search_url($variables = array()) {
        $variables = array_merge(self::get_search_vars(), $variables);
        // Start with the search page uri.
        $result = get_page_uri(get_option(self::SEARCH_PAGE)) . '/';
        $last_post_seperator = '';
        foreach(self::$search_query_variables as $variable) {
            if(!array_key_exists($variable['key'], $variables)) {
                $variables[$variable['key']] = "";
            }
            $value = $variables[$variable['key']];
            if(empty($value) && $variable['default_value'] != null) {
                $value = $variable['default_value'];
            }
            if($value) {
                if(is_array($value)) {
                    $value = implode($variable['multivalue-seperator'], $value);
                }
                $value = urlencode($value);
                if($variable['prefix-key']) {
                    $result .= $variable['key'] . self::QUERY_PREFIX_CHAR . $value . $variable['post-seperator'];
                } else {
                    $result .= $value . $variable['post-seperator'];
                }
            }
            $last_variable = $variable;
        }
        if(substr($result, -1) === $last_variable['post-seperator']) {
            $result = substr($result, 0, strlen($result)-1)."/";
        }
        // Fixing postfix issues, removing the last post-seperator.
        return site_url($result);
    }

    /**
     * Generate data and include template for search results
     * @param  array $args 
     * @return string The markup generated.
     */
    public function generate_search_results($args = array()) {
        $search_vars = self::get_search_vars();
        $search = '';
        $search = $search_vars[self::QUERY_KEY_FREETEXT];
        $args = array(
          'post_type' => self::CUSTOM_POST_TYPE,
          'post_status' => 'publish',
          'posts_per_page' => get_option('max_search_results', 10),
          'ignore_sticky_posts'=> 1,
          'orderby' => 'date',
          'order' => 'DESC',
          's' => $search,
          'date_query' => array(
            array(
                'after' => strtotime(date('Y-m-d'))
            )
          ),
          'meta_query' => array(
            'key' => 'end_date',
            'value' => date(self::DATE_FORMAT_PHP),
            'compare' => '<='
          )
        );        

        self::$search_results = new WP_Query($args);
    }

    public static function get_search_results() {
        return self::$search_results;
    }

    public static function set_search_results($search_results) {
        self::$search_results = apply_filters(self::FILTER_PREPARE_RESULTS,$search_results);
    }

    public function get_search_page() {
        //Include template for program listing results
        if(get_option(self::SEARCH_PAGE) && is_page(get_option(self::SEARCH_PAGE))) {
            $this->search_query_prettify();
            $this->generate_search_results();

            $page = self::get_search_var(self::QUERY_KEY_PAGE, false, true, 1);
            //Look in theme dir and include if found
            $include = locate_template('templates/' . self::SEARCH_PAGE . '.php', false);
            if($include == "") {
                //Include from plugin template  
                $include = plugin_dir_path(__FILE__).'/templates/' . self::SEARCH_PAGE . '.php';
            }
            require($include);
            exit();
        }
    }

    public function get_login_page() {
        //Include template for program listing results
        if(get_option(self::LOGIN_PAGE) && is_page(get_option(self::LOGIN_PAGE))) {
            //Look in theme dir and include if found
            $include = locate_template('templates/' . self::LOGIN_PAGE . '.php', false);
            if($include == "") {
                //Include from plugin template  
                $include = plugin_dir_path(__FILE__).'/templates/' . self::LOGIN_PAGE . '.php';
            }
            require($include);
            exit();
        }
    }

    public function get_signup_page() {
        //Include template for program listing results
        if(get_option(self::SIGNUP_PAGE) && is_page(get_option(self::SIGNUP_PAGE))) {
            //Look in theme dir and include if found
            $include = locate_template('templates/' . self::SIGNUP_PAGE . '.php', false);
            if($include == "") {
                //Include from plugin template  
                $include = plugin_dir_path(__FILE__).'/templates/' . self::SIGNUP_PAGE . '.php';
            }
            require($include);
            exit();
        }
    }

    public static function printThumbnail($post_id) {
        $attachment_ids = explode( ',', get_post_meta( $post_id, '_easy_image_gallery', true ));

        if ($attachment_ids && (count($attachment_ids) > 0 && strlen($attachment_ids[0]) > 0)) {
            $attachment_id = $attachment_ids[0];
            $image = wp_get_attachment_image( $attachment_id, apply_filters( 'easy_image_gallery_thumbnail_image_size', 'thumbnail' ), '', array( 'alt' => trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ) ) );
            echo $image;
        } else {
            echo '<img src="' . plugins_url('img/no-img.jpg', __FILE__ ) . '" alt="No image" height="100" width="100" />';
        }
    }

    public static function create_search_form($freetext_placeholder = "") {
        $placeholder = $freetext_placeholder;
        if(get_option(self::SEARCH_PAGE)) {
            $page = get_permalink(get_option(self::SEARCH_PAGE));
        } else {
            $page = "";
        }   
        
        $include = locate_template('templates/search.php', false);
        if($include == "") {
            //Include from plugin template      
            $include = plugin_dir_path(__FILE__)."/templates/search.php";
        }
        require($include);
    }

    function register_a_user(){
      if(isset($_GET['do']) && $_GET['do'] == 'register'):
        $errors = array();
        if(empty($_POST['user']) || empty($_POST['email'])) $errors[] = 'provide a user and email';
        if(!empty($_POST['spam'])) $errors[] = 'gtfo spammer';

        $user_login = esc_attr($_POST['user']);
        $user_email = esc_attr($_POST['email']);
        require_once(ABSPATH.WPINC.'/registration.php');

        $sanitized_user_login = sanitize_user($user_login);
        $user_email = apply_filters('user_registration_email', $user_email);

        if(!is_email($user_email)) $errors[] = 'invalid e-mail';
        elseif(email_exists($user_email)) $errors[] = 'this email is already registered, bla bla...';

        if(empty($sanitized_user_login) || !validate_username($user_login)) $errors[] = 'invalid user name';
        elseif(username_exists($sanitized_user_login)) $errors[] = 'user name already exists';

        if(empty($errors)):
          $user_pass = wp_generate_password();
          $user_id = wp_create_user($sanitized_user_login, $user_pass, $user_email);

          if(!$user_id):
            $errors[] = 'registration failed...';
          else:
            update_user_option($user_id, 'default_password_nag', true, true);
            wp_new_user_notification($user_id, $user_pass);
          endif;
        endif;

        if(!empty($errors)) define('REGISTRATION_ERROR', serialize($errors));
        else define('REGISTERED_A_USER', $user_email);
      endif;
    }

    public static function get_dates($post_id, $available_today = false) {
        global $wpdb;
        $extra = '';
        if ($available_today) {
            $extra = ' AND NOW() BETWEEN start AND end';
        }
        $results = $wpdb->get_results($wpdb->prepare( 
            "
            SELECT start, end
            FROM $wpdb->auction_dates
            WHERE post_id = %d
            " . $extra,
            $post_id
        ));
        return $results;
    }

    public static function set_dates($post_id, $start_dates, $end_dates) {
        global $wpdb;
        if (count($start_dates) != count($end_dates) && count($start_dates) > 0) {
            return false;
        }
        $count = count($start_dates);
        $insert = '';
        $remove = array();
        for ($i = 0; $i < $count; $i++) {
            $insert .= '(' . $post_id . ', \'' . $start_dates[$i] . '\', \'' . $end_dates[$i] . '\'),';
            $remove[] = '(start <> \'' . $start_dates[$i] . '\' OR end <> \'' . $end_dates[$i] . '\')';
        }
        $insert = rtrim($insert, ',');

        $results = $wpdb->query(
            "
            INSERT INTO $wpdb->auction_dates
            (post_id, start, end)
            VALUES 
            " . $insert . " ON DUPLICATE KEY UPDATE post_id=VALUES(post_id), start=VALUES(start), end=VALUES(end)"
        );
        if ($results === false) {
            return false;
        }
        if (!empty($remove)) {
            $results = $wpdb->query(
                "
                DELETE FROM $wpdb->auction_dates
                WHERE post_id=$post_id AND 
                " . implode($remove, ' AND ')
            );
        }
        return $results !== false;
    }

    public static function set_address($post_id, $address) {
        // TODO save address for a product
    }

    public static function get_addresses($user_id = false) {
        if ($user_id === false) {
            $user_id = get_current_user_id();
        }
        // TODO: Find all addresses for a user and his products
    }

    public function load_admin_dependencies() {
        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        wp_enqueue_style('auction-admin-styles', plugins_url( 'css/admin_styles.css' , __FILE__ ));
        wp_enqueue_script('auction-admin-functions', plugins_url( 'js/admin_functions.js' , __FILE__ ),array('jquery', 'jquery-ui-datepicker'),'1.0',true);

        $translation_array = array(
            'date_format'  => self::DATE_FORMAT
        );
        wp_localize_script( 'auction-admin-functions', 'auction_admin', $translation_array );
    }
}

new Auction();