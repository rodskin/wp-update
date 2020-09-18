<?php
$file = basename(__FILE__);
global $wpdb;
$sql = 'UPDATE `' . $wpdb->prefix . 'options`
        SET `option_value` = "Vie du réseau"
        WHERE `option_name` = "options_titre_reseau"';
$query_result = $wpdb->query($sql);


if ($query_result !== false) {
    $this->save_file_update($file, true);
} else {
    $this->save_file_update($file, false);
}

