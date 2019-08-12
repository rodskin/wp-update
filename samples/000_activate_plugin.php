<?php
// exemple de script d'activation de module
$plugin_name = 'hello.php'; // hello.php is basic test module. for acf for example it will be "advanced-custom-fields-pro/acf.php"
global $wpdb;
$sql = 'SELECT *
        FROM `' . $wpdb->prefix . 'options` 
        WHERE `option_name` = "active_plugins"
        LIMIT 1';
$result = $wpdb->get_results($sql);

$modules_activated = unserialize($result[0]->option_value);
$modules_activated[] = $plugin_name;
$active_plugins = serialize($modules_activated);

$sql = 'UPDATE `' . $wpdb->prefix . 'options`
        SET `option_value` = "' . str_replace('"', '\"', $active_plugins) . '"
        WHERE `option_id` = ' . $result[0]->option_id;
$wpdb->query($sql);

