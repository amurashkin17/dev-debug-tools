<?php 
// Include the header
include 'header.php';

// Listen for $_REQUEST to add Must-Use-Plugin; we will not be adding it by default
if ( isset( $_REQUEST ) && isset( $_REQUEST[ 'settings-updated' ] ) && $_REQUEST[ 'settings-updated' ] ) {
    $DDTT_LOGS = new DDTT_LOGS();
    $add_mu_plugin = get_option( DDTT_GO_PF.'error_enable' );
    if ( $add_mu_plugin ) {
        if ( $DDTT_LOGS->add_remove_mu_plugin( 'add' ) ) {
            header( 'Refresh:0' );
        }
    } else {
        $DDTT_LOGS->add_remove_mu_plugin( 'remove' );
    }
}

// Check debug logging enabled
if ( WP_DEBUG ) {
    $debugging = 'ENABLED';
} else {
    $debugging = 'DISABLED';
}
?>
<style>
p.submit {
    display: inline-block;
}
#all-unchecked {
    display: none;
    margin-left: 10px;
    padding: 10px 12px;
    background: red;
    font-weight: bold;
    border-radius: 5px;
}
.false {
    color: #FF99CC;
}
</style>

<form method="post" action="options.php">
    <?php settings_fields( DDTT_PF.'group_error' ); ?>
    <?php do_settings_sections( DDTT_PF.'group_error' ); ?>
    <table class="form-table">
        <?php 
        $allowed_html = ddtt_wp_kses_allowed_html();

        echo wp_kses( ddtt_options_tr( 'error_enable', 'Enable Custom Debug Levels', 'checkbox', 'Updating the error reporting level requires adding the settings via a Must-Use-Plugin so it loads before any regular plugin and before the theme is loaded.' ), $allowed_html );

        echo wp_kses( ddtt_options_tr( 'error_uninstall', 'Remove Must-Use-Plugin When Uninstalling Developer Debug Tools', 'checkbox', 'If enabled above, selecting this option will remove the Must-Use-Plugin upon uninstall. Keep this unchecked if you want to leave it.' ), $allowed_html );
        ?>

    </table>

    <br>
    <div class="full_width_container">

        <em>The "Actual" column has been added to verify what is actually set.</em>
        <br><br>

        <table class="admin-large-table">
            <tr>
                <th style="width: 50px;">Value</th>
                <th style="width: 180px;">Constant</th>
                <th style="width: 50px;">Enabled</th>
                <th style="width: 50px;">Actual</th>
                <th style="width: auto;">Description</th>
            </tr>
            <?php
            // Trigger error link
            $trigger = '<a href="https://www.php.net/manual/en/function.trigger-error.php" target="_blank">trigger_error()</a>';
            $set_error_handler = '<a href="https://www.php.net/manual/en/function.set-error-handler.php" target="_blank">set_error_handler()</a>';
            
            // All possible constants
            $data = [
                [ 1, 'E_ERROR', 'Fatal run-time errors. These indicate errors that can not be recovered from, such as a memory allocation problem. Execution of the script is halted.' ],
                [ 2, 'E_WARNING', 'Run-time warnings (non-fatal errors). Execution of the script is not halted.' ],
                [ 4, 'E_PARSE', 'Compile-time parse errors. Parse errors should only be generated by the parser.' ],
                [ 8, 'E_NOTICE', 'Run-time notices. Indicate that the script encountered something that could indicate an error, but could also happen in the normal course of running a script.' ],
                [ 16, 'E_CORE_ERROR', 'Fatal errors that occur during PHP\'s initial startup. This is like an <code class="hl">E_ERROR</code>, except it is generated by the core of PHP.' ],
                [ 32, 'E_CORE_WARNING', 'Warnings (non-fatal errors) that occur during PHP\'s initial startup. This is like an <code class="hl">E_WARNING</code>, except it is generated by the core of PHP.' ],
                [ 64, 'E_COMPILE_ERROR', 'Fatal compile-time errors. This is like an <code class="hl">E_ERROR</code>, except it is generated by the Zend Scripting Engine.' ],
                [ 128, 'E_COMPILE_WARNING', 'Compile-time warnings (non-fatal errors). This is like an <code class="hl">E_WARNING</code>, except it is generated by the Zend Scripting Engine.' ],	 
                [ 256, 'E_USER_ERROR', 'User-generated error message. This is like an <code class="hl">E_ERROR</code>, except it is generated in PHP code by using the PHP function '.$trigger.'.' ],
                [ 512, 'E_USER_WARNING', 'User-generated warning message. This is like an <code class="hl">E_WARNING</code>, except it is generated in PHP code by using the PHP function '.$trigger.'.' ],
                [ 1024, 'E_USER_NOTICE', 'User-generated notice message. This is like an <code class="hl">E_NOTICE</code>, except it is generated in PHP code by using the PHP function '.$trigger.'.' ],
                [ 2048, 'E_STRICT', 'Enable to have PHP suggest changes to your code which will ensure the best interoperability and forward compatibility of your code.' ],
                [ 4096, 'E_RECOVERABLE_ERROR', 'Catchable fatal error. It indicates that a probably dangerous error occurred, but did not leave the Engine in an unstable state. If the error is not caught by a user defined handle (see also '.$set_error_handler.'), the application aborts as it was an <code class="hl">E_ERROR</code>.' ],
                [ 8192, 'E_DEPRECATED', 'Run-time notices. Enable this to receive warnings about code that will not work in future versions.' ],
                [ 16384, 'E_USER_DEPRECATED', 'User-generated warning message. This is like an <code class="hl">E_DEPRECATED</code>, except it is generated in PHP code by using the PHP function '.$trigger.'.' ],
                [ 32767, 'E_ALL', 'All errors, warnings, and notices.' ]
            ];

            // Get our settings
            $enabled_constants = get_option( DDTT_GO_PF.'error_constants' );
            if ( $enabled_constants ) {
                $enabled_constants = filter_var_array( $enabled_constants, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $enabled_constants = array_keys( $enabled_constants );
                $default = false;
            } else {
                $enabled_constants = [];
                $default = true;
            }

            // Error reporting exceptions
            $actual_constants = ddtt_get_error_reporting_constants();

            // Store the total values
            $total_enabled_values = 0;
            $total_actual_values = 0;

            // Allowed html
            $allowed_html = ddtt_wp_kses_allowed_html();

            // Iter the data
            foreach ( $data as $d ) {

                // Variables
                $value = $d[0];
                $variable = $d[1];
                $desc = $d[2];

                // Check actual
                if ( in_array( $variable, $actual_constants ) || ( $variable == 'E_ALL' && $total_actual_values == E_ALL ) ) {
                    $actual = true;
                    $total_actual_values += $value;
                } else {
                    $actual = false;
                }
                
                // Check if we enabled it
                if ( in_array( $variable, $enabled_constants ) || ( $variable == 'E_ALL' && $total_enabled_values == E_ALL ) ) {
                    $enabled = true;
                    $total_enabled_values += $value;
                } elseif ( $default ) {
                    $enabled = $actual;
                } else {
                    $enabled = false;
                }                

                // Actual
                $display_actual = $actual ? '<span class="true">TRUE</span>' : '<span class="false">FALSE</span>';

                // Select field
                $input = '<input type="checkbox" id="error_constants_'.strtolower( $variable ).'" class="error_constants_checkboxes" name="'.DDTT_GO_PF.'error_constants['.$variable.']" value="1"'.checked( 1, $enabled, false ).'/>';

                // Add the row
                ?>
                <tr>
                    <td><?php echo absint( $value ); ?></td>
                    <td><span class="highlight-variable"><?php echo esc_html( $variable ); ?></span></td>
                    <td><?php echo wp_kses( $input, $allowed_html ); ?></td>
                    <td><?php echo wp_kses( $display_actual, $allowed_html ); ?></td>
                    <td><?php echo wp_kses( $desc, $allowed_html ); ?></td>
                </tr>
                <?php
            }
            ?>
        </table>
    </div>

    <?php submit_button(); ?> <div id="all-unchecked">You cannot report on nothing. To disable reporting altogether, simply set <code class="hl">WP_DEBUG</code> to false on your <code class="hl">wp-config.php</code> file.</div>
</form>

<script>
jQuery( $ => {
    // Listen for constant changes
    $( '.error_constants_checkboxes' ).on( 'change', function() {

        // Check if we selecting the E_ALL box
        const isEALL = $( this ).attr( 'id' ) == 'error_constants_e_all';

        // Uncheck
        if ( !$( this ).is( ':checked' ) ) {

            // Ensure that we don't uncheck all
            var allUnchecked = true;
            if ( !isEALL ) {
                $( '.error_constants_checkboxes' ).each( function() {
                    if ( $( this ).is( ':checked' ) ) {
                        allUnchecked = false;
                    }
                } );
            }
            if ( isEALL || allUnchecked ) {
                $( '#submit' ).prop( 'disabled', true );
                $( '#all-unchecked' ).css( 'display', 'inline-block' );
            }

            // Make sure E_ALL is unchecked
            $( '#error_constants_e_all' ).prop( 'checked', false );

            // De-select all
            if ( isEALL ) {
                $( '.error_constants_checkboxes' ).not( this ).prop( 'checked', this.checked );
            }
            
        // Check
        } else {

            // Ensure that we don't uncheck all
            if ( $( '#submit' ).prop( 'disabled' ) ) {
                $( '#submit' ).prop( 'disabled', false );
                $( '#all-unchecked' ).hide();
            }

            // Auto select all
            var allChecked = true;
            $( '.error_constants_checkboxes' ).each( function() {
                if ( $( this ).attr( 'id' ) !== 'error_constants_e_all' && !$( this ).is( ':checked' ) ) {
                    allChecked = false;
                }
            } );
            if ( allChecked ) {
                $( '#error_constants_e_all' ).prop( 'checked', true );
            }
            
            // Select all
            if ( isEALL ) {
                $( '.error_constants_checkboxes' ).not( this ).prop( 'checked', this.checked );
            }
        }
    } );
} )
</script>