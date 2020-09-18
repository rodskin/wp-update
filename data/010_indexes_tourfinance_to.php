<?php
$file = basename(__FILE__);
global $wpdb;
$sql = 'CREATE INDEX ' . $wpdb->prefix . 'tourcom_tourfinance_to_CODTO_IDX USING BTREE ON ' . $wpdb->prefix . 'tourcom_tourfinance_to (CODTO);';
$query_result = $wpdb->query($sql);

$sql = 'CREATE INDEX ' . $wpdb->prefix . 'tourcom_tourfinance_to_JSAISIE_IDX USING BTREE ON ' . $wpdb->prefix . 'tourcom_tourfinance_to (JSAISIE);';
$query_result = $wpdb->query($sql);

$sql = 'CREATE INDEX ' . $wpdb->prefix . 'tourcom_tourfinance_to_CODAG_IDX USING BTREE ON ' . $wpdb->prefix . 'tourcom_tourfinance_to (CODAG);';
$query_result = $wpdb->query($sql);

$sql = 'CREATE INDEX ' . $wpdb->prefix . 'tourcom_tourfinance_to_REGUL_IDX USING BTREE ON ' . $wpdb->prefix . 'tourcom_tourfinance_to (REGUL);';
$query_result = $wpdb->query($sql);


if ($query_result !== false) {
    $this->save_file_update($file, true);
} else {
    $this->save_file_update($file, false);
}

