<?php
/**
 * Plugin Name:       WP Tools
 * Description:       Wordpress developer-focused plugin.
 * Version:           1.0.0
 * License:           GPL-2.0+
 * Author:            Miguel Calderón
 * Text Domain:       wptools
 */

require_once plugin_dir_path( __FILE__ ) . '/includes/register.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/login.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/menu.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/footer.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/user.php';


function enqueueAssets() { 
  wp_register_script( 'jQuery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js', null, null, true );
  wp_enqueue_script('jQuery');
  wp_enqueue_script( 'popper_js', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', 
  					array(), 
  					'1.14.3', 
  					true); 
  wp_register_script( 'custom-js', plugins_url( '/js/custom.js' , __FILE__), array( 'jquery' )); 
  wp_register_style( 'custom-css', plugins_url( '/css/style.css' , __FILE__ ),'','', 'screen' );
  wp_enqueue_style( 'bootstrap_css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css', array());
  wp_enqueue_script( 'bootstrap_js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js',	array('jquery'), '4.1.3', true);  
  wp_enqueue_script( 'custom-js' );
  wp_enqueue_style( 'custom-css' );
}

add_action( 'wp_enqueue_scripts', 'enqueueAssets' );
class WpTool {

    /**
     * Initializes the plugin.
     *
     * To keep the initialization fast, only add filter and action
     * hooks in the constructor.
     */


    public function __construct() {
        $register = new register();
        $register->add_dependencies();
        $login = new login();
        $login->add_dependencies();
        $menu = new menu();
        $menu->add_dependencies();
        $footer = new footer();
        $footer->add_dependencies();
        User::init();
        
        add_filter('query_vars', [$this, 'addQueryVars']);
        add_action( 'template_redirect', [$this, 'actionIntercept'] );
    }

    public static function addQueryVars($qvars)
    {
        $qvars[] = 'tool_form';
        return $qvars;
    }

    public static function plugin_activated() {
        // Information needed for creating the plugin's pages
        $page_definitions = array(
          'register' => array(
            'title' => __( 'Register', 'wptool' ),
            'content' => '[custom-register-form]'
          ),
          'login' => array(
              'title' => __( 'Login', 'wptool' ),
              'content' => '[custom-login-form]'
          ),
          'forgot-password' => array(
            'title' => __( 'Forgot Your Password?', 'wptool' ),
            'content' => '[custom-password-lost-form]'
          )
        );

        foreach ( $page_definitions as $slug => $page ) {
            // Check that the page doesn't exist already
            $query = new WP_Query( 'pagename=' . $slug );
            if ( ! $query->have_posts() ) {
                // Add the page using the data from the array above
                wp_insert_post(
                    array(
                        'post_content'   => $page['content'],
                        'post_name'      => $slug,
                        'post_title'     => $page['title'],
                        'post_status'    => 'publish',
                        'post_type'      => 'page',
                        'ping_status'    => 'closed',
                        'comment_status' => 'closed',
                    )
                );
            }
        }
    }
}

// Initialize the plugin
$WPTool = new WpTool();
// Create the custom pages at plugin activation
register_activation_hook( __FILE__, array( 'WpTool', 'plugin_activated' ) );
require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';