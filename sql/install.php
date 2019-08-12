<?php
global $wpdb;
$requests = array();
$requests[] = 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . 'wp_update` (
            `ID` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(50) NOT NULL,
            `date_install` DATE
        );';

foreach ($requests as $sql) {
    $wpdb->query($sql);
}

