<?php
$file = basename(__FILE__);

$result = activate_plugin( 'tourcom-tourfinance/tourcom-tourfinance.php' );

if ( is_wp_error( $result ) ) {
    $this->save_file_update($file, false);
} else {
    $this->save_file_update($file, true);
}