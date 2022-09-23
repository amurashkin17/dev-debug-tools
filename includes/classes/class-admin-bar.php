<?php
/**
 * Admin bar class
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Initiate the class
 */
new DDTT_ADMIN_BAR;


/**
 * Main plugin class.
 */
class DDTT_ADMIN_BAR {

    /**
	 * Constructor
	 */
	public function __construct() {

        // Customize the admin bar menu
        add_action( 'admin_bar_menu', [ $this, 'admin_bar' ], 99999 );
	} // End __construct()


    /**
     * Customize Admin Bar Items
     *
     * @param object $wp_admin_bar
     * @return void
     */
    public function admin_bar( $wp_admin_bar ) {
        // Get the current URL
        $current_url = ddtt_get_current_url();


        /**
         * Remove Items
         */

        // Items on both front-end and admin area
        $wp_admin_bar->remove_node( 'wp-logo' ); // The WordPress Logo
        $wp_admin_bar->remove_node( 'top-secondary' ); // Howdy, Display Name


        /**
         * Add the user's name and ID in a better way with page loaded info
         */
        $user_id = get_current_user_id();
        $current_user = wp_get_current_user();
        $profile_url = get_edit_profile_url( $user_id );
        
        // Add the user info
        if ( 0 != $user_id ) {
            $account_text = sprintf( __( '<span class="full-width-only">Logged in as: </span>%1$s (<span class="full-width-only">User </span>ID %2$s)', 'dev-debug-tools' ), $current_user->display_name, $user_id );       
            $wp_admin_bar->add_node( [
                'id' => DDTT_GO_PF.'my-account',
                'title' => $account_text,
                'href' => $profile_url,
                'meta' => [
                    'class' => DDTT_GO_PF.'my-account-name',
                ],
            ] );

            // Dev only
            if ( ddtt_is_dev() ) {

                // Get the dev's timezone
                if ( get_option( 'ddtt_dev_timezone' ) && get_option( 'ddtt_dev_timezone' ) != ''){
                    $tz = get_option( 'ddtt_dev_timezone' );
                } else {
                    $tz = wp_timezone_string();
                }
                $currentTime = ddtt_convert_timezone( null, 'g:i A', $tz );
                $currentDate = ddtt_convert_timezone( null, 'n/j/y', $tz );

                // Add the page loaded information
                $loaded_text = sprintf( __( 'Page loaded at %1$s on %2$s', 'dev-debug-tools' ), $currentTime, $currentDate );       
                $wp_admin_bar->add_node( [
                    'id' => DDTT_GO_PF.'page-loaded',
                    'parent' => DDTT_GO_PF.'my-account',
                    'title' => $loaded_text,
                    'meta' => [
                        'class' => DDTT_GO_PF.'page-loaded-time',
                    ],
                ] );
            }

            // Add a logout link   
            $logout_link = '/wp-login.php?action=logout';
            $wp_admin_bar->add_node( [
                'id' => DDTT_GO_PF.'admin-logout',
                'parent' => DDTT_GO_PF.'my-account',
                'title' => 'Log Out',
                'href' => $logout_link,
                'meta' => [
                    'class' => DDTT_GO_PF.'logout-link',
                ],
            ] );
        }


        /**
         * Add the post ID and status
         */
        if ( !get_option( DDTT_GO_PF.'admin_bar_post_info' ) || get_option( DDTT_GO_PF.'admin_bar_post_info' ) == 0 ) {
            if ( get_the_ID() ) {
                $post_id = get_the_ID();
                
            } else {
    
                // Cornerstone content editor
                $theme = wp_get_theme(); // gets the current theme
                if ( 'X – Child Theme' == $theme->name || 'X' == $theme->parent_theme ) {
                    $cornerstone = get_site_url().'/cornerstone/content/';
                    if ( strpos( $current_url, $cornerstone ) !== false ) {
                        $parsed_url = parse_url( $current_url );
                        $path = $parsed_url[ 'path' ];
                        $explode = explode( '/', $path );
                        $post_id = $explode[3];
                    } else {
                        $post_id = false;
                    }
                }
            }
    
            // If post id exists, continue
            if ( $post_id ){
                
                // Add Page/Post ID
                $post_type = get_post_type( $post_id );
                $post_type_obj = get_post_type_object( $post_type );
                if ($post_type_obj) {
                    $pt_name = esc_html( $post_type_obj->labels->singular_name ).' ID';
                } else {
                    $pt_name = '';
                }
    
                // Add Page/Post Status 
                if ( get_post_status( $post_id ) == 'publish' ) {
                    $post_status = 'Published';
                } elseif ( get_post_status( $post_id ) == 'draft' ) {
                    $post_status = 'Draft';
                } elseif ( get_post_status( $post_id ) == 'private' ) {
                    $post_status = 'Private';
                } elseif ( get_post_status( $post_id ) == 'archive' ) {
                    $post_status = 'Archived';
                } else {
                    $post_status = 'Unknown';
                }
                
                // Add to bar
                $wp_admin_bar->add_node( [
                    'id' => DDTT_GO_PF.'admin-post-id',
                    'title' => sprintf( __( '%1$s %2$s (%3$s)', 'dev-debug-tools' ), $pt_name, $post_id, $post_status ),
                ] );
            }
        }

        
        /**
         * Add resource links
         */
        if ( !get_option( DDTT_GO_PF.'admin_bar_resources' ) || get_option( DDTT_GO_PF.'admin_bar_resources' ) == 0 ) {
            if ( ddtt_is_dev() ) {
                $DDTT_RESOURCES = new DDTT_RESOURCES();
                $links = $DDTT_RESOURCES->get_resources();
                if ( !empty( $links ) ) {
                    $resources_icon = '&#128214;';       
                    $wp_admin_bar->add_node( [
                        'id' => DDTT_GO_PF.'resources',
                        'title' => $resources_icon,
                        'meta' => [
                            'class' => DDTT_GO_PF.'resources',
                        ],
                    ] );

                    // Add each link
                    foreach ( $links as $key => $link ) {
                        $wp_admin_bar->add_node( [
                            'id' => DDTT_GO_PF.'resource-'.$key,
                            'parent' => DDTT_GO_PF.'resources',
                            'title' => $link[ 'title' ],
                            'href' => $link[ 'url' ],
                            'meta' => [
                                'class' => DDTT_GO_PF.'resource',
                                'target' => '_blank'
                            ],
                        ] );
                    }
                }
            }
        }
        

        /**
         * Add additional links to {Site Name} dropdown on front end
         */
        if ( !is_admin() ) {

            // Dev Debug Tools
            if ( ddtt_is_dev() ) {
                $site_name_links = [ [ 'Dev Debug Tools', ddtt_plugin_options_path() ] ];
            }

            // Users, posts, and pages
            $custom_links = [
                [ 'Users', '/'.DDTT_ADMIN_URL.'/users.php' ],
                [ 'Posts', '/'.DDTT_ADMIN_URL.'/edit.php' ],
                [ 'Pages', '/'.DDTT_ADMIN_URL.'/edit.php?post_type=page' ],
            ];

            // Apply filter before GF and plugins
            $custom_links = apply_filters( 'ddtt_admin_bar_dropdown_links', $custom_links );

            // Merge the arrays
            if ( ddtt_is_dev() ) {
                $site_name_links = array_merge( $site_name_links, $custom_links );
            } else {
                $site_name_links = $custom_links;
            }

            // Gravity Forms
            if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ){
                $site_name_links[] = [ 'Forms', '/'.DDTT_ADMIN_URL.'/admin.php?page=gf_edit_forms' ];
            }

            // Plugins
            $site_name_links[] = [ 'Plugins', '/'.DDTT_ADMIN_URL.'/plugins.php' ];

            // Add them to the admin bar
            foreach( $site_name_links as $snl) {
                $wp_admin_bar->add_node( [
                    'id' => DDTT_GO_PF.strtolower( str_replace( ' ', '_', $snl[0] ) ),
                    'parent' => 'site-name',
                    'title' => $snl[0],
                    'href' => $snl[1],
                    'meta' => [
                        'class' => DDTT_GO_PF.'site-name-links',
                    ],
                ] );
            }
        }


        /**
         * Add centering tool
         */
        $ct_is_enabled = false;
        if ( !get_option( DDTT_GO_PF.'admin_bar_centering_tool' ) || get_option( DDTT_GO_PF.'admin_bar_centering_tool' ) == 0 ) {
            if ( !is_admin() ) {

                // Get column count
                if ( get_option( DDTT_GO_PF.'centering_tool') && get_option( DDTT_GO_PF.'centering_tool') != '' ) {
                    $ct_count = get_option( DDTT_GO_PF.'centering_tool');
                } else {
                    $ct_count = 16;
                }

                // Update
                if ( ddtt_get( 'ct' ) && ddtt_get( 'ct' ) == 'true' ) {
                    update_user_meta( $user_id, DDTT_GO_PF.'centering_tool', $ct_count );
                    ddtt_remove_qs_without_refresh( 'ct', false );
                    
                } elseif ( ddtt_get( 'ct' ) && ddtt_get( 'ct' ) == 'false' ) {
                    update_user_meta( $user_id, DDTT_GO_PF.'centering_tool', false );
                    ddtt_remove_qs_without_refresh( 'ct', false );
                }

                // Display
                $url_to_parse = parse_url( $_SERVER['REQUEST_URI'] );
                $qsi = isset( $url_to_parse['query'] ) ? '&' : '?';
                if ( get_user_meta( $user_id, DDTT_GO_PF.'centering_tool', true ) && get_user_meta( $user_id, DDTT_GO_PF.'centering_tool', true ) != '' ) {
                    $ct_is_enabled = true;

                    // Container
                    $centering_tool = '<div id="ct-top" class="centering-tool" expanded="false">';
                    for ( $i = 0; $i < $ct_count; $i++ ) {
                        if ( $ct_count % 2 == 0 && $i == round( ( $ct_count / 2 ) - 1, 0 ) || 
                            $ct_count % 2 == 1 && $i == round( ( $ct_count / 2 ) - 1, 0 ) || 
                            $ct_count % 2 == 1 && $i == round( ( $ct_count / 2 ) - 2, 0 ) ) {

                            $center = ' center';
                        } else {
                            $center = '';
                        }
                        $centering_tool .= '<div class="ct-q'.$center.'"></div>';
                    }
                    $centering_tool .= '</div>';

                    // Add to page 
                    echo wp_kses_post( $centering_tool );

                    // Text and link
                    $ct_text = 'On';
                    $ct_link = $current_url.$qsi.'ct=false';
                    
                } else {
                    $ct_is_enabled = false;
                    $ct_text = 'Off';
                    $ct_link = $current_url.$qsi.'ct=true';
                }
                $wp_admin_bar->add_node( [
                    'id' => DDTT_GO_PF.'ct-admin-bar',
                    'title' => '&#x271B; '.$ct_text,
                    'href' => $ct_link,
                    'meta' => [
                        'class' => DDTT_GO_PF.'ct-admin-bar-link',
                    ],
                ] );
            }
        }

        
        /**
         * Dev only
         */
        if ( ddtt_is_dev() && !is_admin() ){

            /**
             * Add shortcodes count
             */
            if ( !get_option( DDTT_GO_PF.'admin_bar_shortcodes' ) || get_option( DDTT_GO_PF.'admin_bar_shortcodes' ) == 0 ) {
                $shortcodes = ddtt_get_shortcodes_on_page();
                $sc_bar = sprintf( __( '<span class="full-width-only">[%1$s]</span>', 'dev-debug-tools' ), count( $shortcodes ) ); 
                $wp_admin_bar->add_node( [
                    'id' => 'shortcodes-found',
                    'title' => $sc_bar,
                    'meta' => [
                        'class' => DDTT_GO_PF.'shortcodes-found',
                    ],
                ] );

                // Add the list of shortcodes
                if ( !empty( $shortcodes ) ) {
                    $sc_desc_text = __( 'Shortcodes Found:', 'dev-debug-tools' );   
                    $wp_admin_bar->add_node( [
                        'id' => DDTT_GO_PF.'shortcode-desc',
                        'parent' => 'shortcodes-found',
                        'title' => $sc_desc_text,
                        'meta' => [
                            'class' => DDTT_GO_PF.'shortcode-found',
                        ],
                    ] );

                    $sc_num = 0;
                    foreach ( $shortcodes as $shortcode ) {
                        $loaded_text = sprintf( __( '[%1$s]', 'dev-debug-tools' ), $shortcode );       
                        $wp_admin_bar->add_node( [
                            'id' => DDTT_GO_PF.'shortcode-'.$sc_num,
                            'parent' => 'shortcodes-found',
                            'title' => $loaded_text,
                            'meta' => [
                                'class' => DDTT_GO_PF.'shortcode-found',
                            ],
                        ] );
                        $sc_num++;
                    }
                }
            }


            /**
             * Get the Gravity Forms IDs from the page
             */
            if ( !get_option( DDTT_GO_PF.'admin_bar_gf' ) || get_option( DDTT_GO_PF.'admin_bar_gf' ) == 0 ) {
                if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
                    $form_ids = ddtt_get_form_ids_on_page();
                    $gf_icon = '<div class="wp-menu-image svg" style="width: 19px; height: 23px; display: inline-block; margin: 0 2px 0 -6px; vertical-align: middle; background-image: url(&quot;data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48c3ZnIHdpZHRoPSIyMSIgaGVpZ2h0PSIyMSIgdmlld0JveD0iMCAwIDIxIDIxIiBmaWxsPSIjYTdhYWFkIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxtYXNrIGlkPSJtYXNrMCIgbWFzay10eXBlPSJhbHBoYSIgbWFza1VuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeD0iMiIgeT0iMSIgd2lkdGg9IjE3IiBoZWlnaHQ9IjIwIj48cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTExLjU5MDYgMi4wMzcwM0wxNy4xNzkzIDUuNDQ4MjRDMTcuODk0IDUuODg0IDE4LjQ3NjcgNi45NTI5OCAxOC40NzY3IDcuODI0NTFWMTQuNjUwM0MxOC40NzY3IDE1LjUxODUgMTcuODk0IDE2LjU4NzQgMTcuMTc5MyAxNy4wMjMyTDExLjU5MDYgMjAuNDMxQzEwLjg3OTIgMjAuODY2OCA5LjcxMDU1IDIwLjg2NjggOC45OTkwOSAyMC40MzFMMy40MTA0MSAxNy4wMTk4QzIuNjk1NzMgMTYuNTg0IDIuMTEzMDQgMTUuNTE4NSAyLjExMzA0IDE0LjY0NjlWNy44MjExQzIuMTEzMDQgNi45NTI5OCAyLjY5ODk1IDUuODg0IDMuNDEwNDEgNS40NDgyNEw4Ljk5OTA5IDIuMDM3MDNDOS43MTA1NSAxLjYwMTI2IDEwLjg3OTIgMS42MDEyNiAxMS41OTA2IDIuMDM3MDNaTTE1Ljc0OTQgOS4zNzUwM0g4LjgxMDQ5QzguMzgyOTkgOS4zNzUwMyA4LjA2MjM3IDkuNTAxNjQgNy44MDkwNCA5Ljc3MDY4QzcuMjU0ODggMTAuMzYwMiA2Ljk2MTk2IDExLjUwMzYgNi45MTg0MiAxMi4xNDA2SDEzLjc1MDVWMTAuNDI3NUgxNS43MDE5VjE0LjA5MTJINC44NDAzMUM0Ljg0MDMxIDE0LjA5MTIgNC44Nzk4OSAxMC4wMzk3IDYuMzkxOTcgOC40MzMzOUM3LjAxNzM4IDcuNzY0NzUgNy44NDA3IDcuNDI0NDkgOC44MzAyOCA3LjQyNDQ5SDE1Ljc0OTRWOS4zNzUwM1oiIGZpbGw9IiNhN2FhYWQiLz48L21hc2s+PGcgbWFzaz0idXJsKCNtYXNrMCkiPjxyZWN0IHg9IjAuMjk0OTIyIiB5PSIwLjc1NzgxMiIgd2lkdGg9IjIwIiBoZWlnaHQ9IjIwIiBmaWxsPSIjYTdhYWFkIi8+PC9nPjwvc3ZnPg==&quot;) !important;" aria-hidden="true"><br></div>';
                    if ( empty( $form_ids ) ) {
                        $gf_var = $gf_icon.' No Forms';

                    } elseif ( count( $form_ids ) > 1 ) {
                        $form_links = [];
                        foreach( $form_ids as $form_id ) {
                            $form_links[] = '<a href="/'.DDTT_ADMIN_URL.'/admin.php?page=gf_edit_forms&view=settings&subview=settings&id='.$form_id.'" target="_blank" style="display: inline-block; color: white;">'.$form_id.'</a>';
                        }
                        $form_id_display = '['.implode(',',$form_links).']';
                        $gf_var = $gf_icon.' Form IDs: '.$form_id_display;
                        
                    } else {
                        $form_id_display = '<a href="/'.DDTT_ADMIN_URL.'/admin.php?page=gf_edit_forms&view=settings&subview=settings&id='.$form_ids[0].'" target="_blank" style="display: inline-block; color: white;">'.$form_ids[0].'</a>';
                        $gf_var = $gf_icon.' Form ID: '.$form_id_display;
                    }

                    $gf_bar = '<span class="full-width-only">'.$gf_var.'</span>';
                    $wp_admin_bar->add_node( [
                        'id' => 'gf-found',
                        // 'parent' => 'top-secondary',
                        'title' => $gf_bar,
                        'meta' => [
                            'class' => DDTT_GO_PF.'gf-found',
                        ],
                    ] );
                }
            }
        }

        
        /**
         * ADD CSS
         */
        echo '<style>
        li#wp-admin-bar-gf-found,
        li#wp-admin-bar-shortcodes-found,
        li#wp-admin-bar-dev-update-count,
        li#wp-admin-bar-'.esc_attr( DDTT_GO_PF ).'ct-admin-bar,
        li#wp-admin-bar-'.esc_attr( DDTT_GO_PF ).'admin-post-id,
        li#wp-admin-bar-'.esc_attr( DDTT_GO_PF ).'admin-post-status,
        li#wp-admin-bar-'.esc_attr( DDTT_GO_PF ).'my-account,
        li#wp-admin-bar-'.esc_attr( DDTT_GO_PF ).'resources {
            float: right !important;
        }
        @media (max-width: 1200px) { 
            li#wp-admin-bar-'.esc_attr( DDTT_GO_PF ).'my-account .full-width-only {
                display: none !important;
            }
        }
        ul li .ab-item, #wpadminbar .quicklinks .menupop ul li a strong, #wpadminbar .quicklinks .menupop.hover ul li .ab-item, #wpadminbar .shortlink-input, #wpadminbar.nojs .quicklinks .menupop:hover ul li .ab-item {
            line-height: 2.5 !important;
        }

        div#ct-top {
            height: 25px;
            transition: height 300ms;
            width: 100%;
            background: rgba(0, 0, 0, 0.15);
            display: flex;
            position: fixed;
            top: 30px;
            left: 0;
            right: 0%;
            z-index: 9999;
            cursor: pointer;
        }
        .ct-q {
            width: 25%;
            border-right: 1px solid #000;
        }
        .ct-q.center {
            border-right: 2px solid blue;
        }
        .ct-q:last-child {
            width: 25%;
            border-right: none;
        }
        @media only screen and (max-width: 641px) {
            div#ct-top {
                top: 0 !important;
            }
        }
        </style>';


        /**
         * Add JS
         */
        if ( $ct_is_enabled ) {
            echo '<script>
            document.getElementById( "ct-top" ).addEventListener( "click", function() {
                var ct = document.getElementById( "ct-top" );
                var exp = ct.getAttribute( "expanded" );
                if ( exp == "false" ) {
                    ct.style.height = "100%";
                    ct.setAttribute( "expanded", "true" );
                } else {
                    ct.style.height = "25px";
                    ct.setAttribute( "expanded", "false" );
                }
            });
            </script>';
        }
    } // End admin_bar()
}