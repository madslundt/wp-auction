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
        
        if(is_admin()) {
            add_action('admin_menu', array(&$this,'create_submenu'));
            add_action('admin_init', array(&$this,'register_settings'));
            add_action('admin_init', array(&$this,'settings_updated'));
        }
        add_action('template_redirect', array(&$this, 'register_a_user'));

        add_action('template_redirect', array(&$this, 'get_search_page'));
        add_action('template_redirect', array(&$this, 'get_login_page'));
        add_action('template_redirect', array(&$this, 'get_signup_page'));

        add_action('plugins_loaded',array(&$this,'load_textdomain'));

        add_filter('widgets_init',array(&$this,'register_widgets'));
    }

    public function load_textdomain() {
        load_plugin_textdomain( self::DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/');
    }

    public function register_widgets() {
        register_widget('SearchWidget');
        register_widget('NewestWidget');
    }

    private function load_dependencies() {
        require('widgets/searchwidget.php');
        require('widgets/newestwidget.php');
        require('custom-post-type.php');
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
        $current_value = get_option($args['name'])?get_option($args['name']):'';
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

    public function get_search_page() {
        //Include template for program listing results
        if(get_option(self::SEARCH_PAGE) && is_page(get_option(self::SEARCH_PAGE))) {
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
}

new Auction();