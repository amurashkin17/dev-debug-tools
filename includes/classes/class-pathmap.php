<?php
/**
 * Pathname mapping class
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main plugin class.
 */
class DDTT_PATHMAP {
        
    private $enable;            // this class is enabled
    private $server_prefix;     // pathname prefix to check for and discard
    private $url_prefix;        // pathname prefix to add
    
    /**
     * Constructor
     */
    public function __construct() {
        
        
        $this->enable = get_option( DDTT_GO_PF.'path_map_enable' ) && get_option( DDTT_GO_PF.'path_map_enable' ) == 1;
        if ( $this->enable ) {
                                  
            $this->server_prefix = str_replace( '\\', '/', get_option( DDTT_GO_PF.'path_map_server_prefix' ) );
            $this->url_prefix    = get_option( DDTT_GO_PF.'path_map_url_prefix' );
        }
        
        if ( true === WP_DEBUG ) {
            ddtt_write_log([
                'pathmap_enable'        => $this->enable,
                'pathmap_server_prefix' => $this->server_prefix,
                'pathmap_url_prefix'    => $this->url_prefix
            ]);
        }
        
    } // End __construct()
    
    
    /**
     * If $token is a string that starts with $path_map_server_prefix,
     *   1. constructs href by discarding the prefix and prepending $path_map_url_prefix
     *   2. returns appropriate <a href=.../a> hyperlink.
     * Otherwise, 
     *   returns HTML escaped $token
     *
     * @param mixed $token
     * @return string
     */
    
    public function hyperlink_or_text( $token ) {
        
        if ( $this->enable && is_string($token) ) {
            
            $token = str_replace( '\\', '/', $token );
            if ( str_starts_with( $token, $this->server_prefix ) ) {
       
                $link = $this->url_prefix . substr($token, strlen($this->server_prefix));
                
                if ( true === WP_DEBUG ) {
                    ddtt_write_log([
                        'pathmap_enable'        => $enable,
                        'pathmap_server_prefix' => $server_prefix,
                        'pathmap_url_prefix'    => $url_prefix,
                        'pathmap_link'          => $link
                    ]);
                }
                
                return '<a href="'.$link.'" style="text-decoration: none">'.esc_html($token).'</a>';  
            }
        } 

        return esc_html( $token );       
    }
}