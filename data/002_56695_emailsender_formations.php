<?php
$file = basename(__FILE__);
global $wpdb;
$sql = 'UPDATE `' . $wpdb->prefix . 'options`
        SET `option_value` = "tourcom@tourcom.fr"
        WHERE `option_name` = "dbem_mail_sender_address"';
$query_result = $wpdb->query($sql);


if ($query_result !== false) {
    $this->save_file_update($file, true);
} else {
    $this->save_file_update($file, false);
}

