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

        // pathname map that transforms pathnames to hyperlinks
        $pathmap = new DDTT_PATHMAP();
        
        // Cycle through the options
        foreach( $all_options as $option => $value ) {
                      
            echo '<tr>
                <td><span class="highlight-variable">'.esc_attr( $option ).'</span></td>
                <td>'.$pathmap->hyperlink_or_text( $value ).'</td>
            </tr>';
        }

echo '</table>
</div>';