<?php
/**
 * Main plugin class file.
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Main plugin class.
 */
class DDTT_DEBUG_TOOLS {

    /**
	 * Constructor
	 */
	public function __construct() {
        // Ensure is_plugin_active() exists for multisite
		if ( !function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . DDTT_ADMIN_URL . '/includes/plugin.php' );
		}

        // Add "Settings" link to plugins page
        add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), [ $this, 'settings_link' ] );

        // Load dependencies.
        if ( is_admin() ) {
			$this->load_admin_dependencies();
		}
        $this->load_dependencies();

        // Add wp_mail failure notices to debug.log
        if ( get_option( DDTT_GO_PF.'wp_mail_failure' ) && get_option( DDTT_GO_PF.'wp_mail_failure' ) == 1 ) {
            add_action( 'wp_mail_failed', [ $this, 'mail_failure' ], 10, 1 );
        }

        // Add data to image src
        add_filter( 'kses_allowed_protocols', [ $this, 'kses_allowed_protocols' ] );
        
	} // End __construct()


    /**
     * Add "Settings" link to plugins page
     * 
     * @return string
     */
    public function settings_link() {
        $links[] = '<a href="'.ddtt_plugin_options_path().'">'.__('Settings').'</a>';
        return $links;
    } // End settings_link()

    
    /**
     * Global dependencies
     * Not including scripts
     * 
     * @return void
     */
    public function load_dependencies() {
        // Admin Options page
        require_once DDTT_PLUGIN_ADMIN_PATH . 'global-options.php';

        // Admin bar
        require_once DDTT_PLUGIN_CLASSES_PATH . 'class-admin-bar.php';

        // Resources
        require_once DDTT_PLUGIN_CLASSES_PATH . 'class-resources.php';

        // Online Users
        if ( get_option( DDTT_GO_PF.'online_users' ) && get_option( DDTT_GO_PF.'online_users' ) == 1 ) {
            require_once DDTT_PLUGIN_CLASSES_PATH . 'class-online-users.php';
        }

        // Discord notifications
        require_once DDTT_PLUGIN_CLASSES_PATH . 'class-discord.php';

        // Miscellaneous functions
        require_once DDTT_PLUGIN_INCLUDES_PATH . 'functions.php';

        // Backdoor
        require_once DDTT_PLUGIN_INCLUDES_PATH . 'backdoor.php';
    } // End load_dependencies()


    /**
     * Admin-only dependencies
     *
	 * @return void
     */
    public function load_admin_dependencies() {
        // Classes
        require_once DDTT_PLUGIN_CLASSES_PATH . 'class-logs.php';
        require_once DDTT_PLUGIN_CLASSES_PATH . 'class-download-files.php';
        require_once DDTT_PLUGIN_CLASSES_PATH . 'class-wpconfig.php';
        require_once DDTT_PLUGIN_CLASSES_PATH . 'class-htaccess.php';
        require_once DDTT_PLUGIN_CLASSES_PATH . 'class-quick-links.php';
        require_once DDTT_PLUGIN_CLASSES_PATH . 'class-deactivate.php';
        require_once DDTT_PLUGIN_CLASSES_PATH . 'class-feedback.php';

        // Admin menu, also loads options.php
        require_once DDTT_PLUGIN_ADMIN_PATH . 'menu.php';
        
        // Options page functions such as form table rows
        require_once DDTT_PLUGIN_ADMIN_PATH . 'functions.php';

        // All functions modifying the admin area only
        require_once DDTT_PLUGIN_ADMIN_PATH . 'admin-area.php';

        // Admin additional CSS from php file
        // Must not be initialized too early, or else error upon activation:
        // "The plugin generated X characters of unexpected output during activation"
        add_action( 'admin_init', function() {

            // Check if we have any files in our css folder
            if ( $stylesheets = ddtt_get_styles( false ) ) {

                // Add each stylesheet
                for ( $i = 0; $i < count( $stylesheets ); $i++ ) {

                    // Add the spreadsheets
                    if ( str_ends_with( $stylesheets[$i], '.php' ) ) {
                        require_once DDTT_PLUGIN_ADMIN_PATH.'css/'.$stylesheets[$i];

                    } elseif ( str_ends_with( $stylesheets[$i], '.css' ) ) {
                        wp_register_style( DDTT_GO_PF.'admin'.$i, DDTT_PLUGIN_CSS_PATH.$stylesheets[$i], [], DDTT_VERSION );
                        wp_enqueue_style( DDTT_GO_PF.'admin'.$i );
                    }
                }
            }
        } );

        // Enqueue scripts
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    } // End load_admin_dependencies()


    /**
     * Show wp_mail() errors
     *
     * @param [type] $wp_error
     * @return void
     */
    public function mail_failure( $wp_error ) {
        error_log( 'Mailing Error Found: ');
        error_log( print_r( $wp_error, true) );
    } // End mail_failure()


    /**
     * Add data to image src
     *
     * @param array $protocols
     * @return array
     */
    public function kses_allowed_protocols( $protocols ) {
        $protocols[] = 'data';
        return $protocols;
    } // End kses_allowed_protocols()


    /**
     * Enqueue scripts
     * Reminder to bump version number during testing to avoid caching
     *
     * @param string $screen
     * @return void
     */
    public function enqueue_scripts( $screen ) {
        
        // Get the options page slug
        $options_page = 'toplevel_page_'.DDTT_TEXTDOMAIN;

        // Allow for multisite
        if ( is_network_admin() ) {
            $options_page .= '-network';
        }

        // Are we on the options page?
        if ( $screen != $options_page ) {
            return;
        }

        // Handle
        $handle = DDTT_GO_PF.'script';

        // Feedback form
        if ( ddtt_get( 'tab', '==', 'about' ) ) {
            wp_register_script( $handle, DDTT_PLUGIN_JS_PATH.'feedback.js', [ 'jquery' ], '1.0.2' );
            wp_localize_script( $handle, 'feedbackAjax', [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
        }

        // Run jQuery, et al
        if ( ddtt_get( 'tab', '==', 'about' ) ) {
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( $handle );
        }
    } // End enqueue_scripts()
}