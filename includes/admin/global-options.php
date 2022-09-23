<?php
/**
 * Global options class file.
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Initiate the class
 */
new DDTT_GLOBAL_OPTIONS;


/**
 * Main plugin class.
 */
class DDTT_GLOBAL_OPTIONS {

    /**
	 * Constructor
	 */
	public function __construct() {
        // Call register settings function
        add_action( 'admin_init', [ $this, 'register_settings' ] );
	} // End __construct()


    /**
     * Register settings
     * Do not need to include the prefix
     *
     * @return void
     */
    public function register_settings() {
        // General Settings
        $this->register_group_settings( 'settings', array(
            'dev_email',
            'dev_timezone',
            'color_comments',
            'color_fx_vars',
            'color_syntax',
            'color_text_quotes',
            'test_number',
            'centering_tool_cols',
            'stop_heartbeat',
            'enable_curl_timeout',
            'change_curl_timeout',
            'ql_user_id',
            'ql_post_id',
            'wp_mail_failure',
            'admin_bar_resources',
            'admin_bar_gf',
            'admin_bar_shortcodes',
            'admin_bar_centering_tool',
            'admin_bar_post_info',
        ) );

        // Log Settings
        $this->register_group_settings( 'log', array(
            'omit_lines'
        ) );

        // Regex Settings
        $this->register_group_settings( 'regex', array(
            'regex_string',
            'regex_pattern'
        ) );
    } // End register_settings()


    /**
     * Register group settings
     * 
     * @return void
     */
    public function register_group_settings( $group_name = 'options', $options = [] ) {   
        if ( !empty( $options ) ) {
            foreach ( $options as $option ) {
                register_setting( DDTT_PF.'group_'.$group_name, DDTT_GO_PF.$option );
            }
        }
    } // End register_group_settings
}