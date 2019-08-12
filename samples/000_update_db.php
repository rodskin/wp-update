<?php
// exemple de script d'update DB
global $wpdb;
$sql = 'UPDATE `' . $wpdb->prefix . 'users` 
        SET `user_nicename` = "Francis"
        WHERE `ID` = 1';
$wpdb->query($sql);