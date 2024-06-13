<?php include 'header.php'; ?>

<?php
// Get the php.inis
$all_options = ini_get_all( null, false );

// Return the table
echo '<div class="full_width_container">
    <table class="admin-large-table">
        <tr>
            <th>Registered Configuration Option</th>
            <th>Value</th>
        </tr>';

        $path_map_enable = get_option( DDTT_GO_PF.'path_map_enable' ) && get_option( DDTT_GO_PF.'path_map_enable' ) == 1;
        if ( $path_map_enable ) {
            $path_map_server_prefix = get_option( DDTT_GO_PF.'path_map_server_prefix' );
            $path_map_url_prefix = get_option( DDTT_GO_PF.'path_map_url_prefix' );
        }
        
        trigger_error("$path_map_enable ".$path_map_enable,
            "$path_map_server_prefix ".$path_map_server_prefix,
            "$path_map_url_prefix ".$path_map_url_prefix,E_USER_NOTICE ); 
        
        // Cycle through the options
        foreach( $all_options as $option => $value ) {
            
            if ( $path_map_enable ) {
                if ( str_starts_with($value,$path_map_server_prefix) ) {
                    $value = '<a href="'.$path_map_url_prefix.substr($value, strlen($path_map_server_prefix)).
                             '">'.esc_html( $value ).'</a>';
                }
            } else {
                $value = esc_html( $value );
            }
            
            echo '<tr>
                <td><span class="highlight-variable">'.esc_attr( $option ).'</span></td>
                <td>'.$value.'</td>
            </tr>';
        }

echo '</table>
</div>';