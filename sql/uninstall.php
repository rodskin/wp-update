<?php
global $wpdb;
$requests = array();
$requests[] = 'DROP TABLE IF EXISTS `' . $wpdb->prefix . 'wp_update`;';
foreach ($requests as $sql) {
    $wpdb->query($sql);
}