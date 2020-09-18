<?php
// exemple de script d'activation de module
$plugins = 'hello.php'; // hello.php is basic test module. for wp-update for example it will be "wp-update/wp-update.php"

/**
 * Deactivate a single plugin or multiple plugins.
 *
 * The deactivation hook is disabled by the plugin upgrader by using the $silent
 * parameter.
 *
 * @since 2.5.0
 *
 * @param string|array $plugins Single plugin or list of plugins to deactivate.
 * @param bool $silent Prevent calling deactivation hooks. Default is false.
 * @param mixed $network_wide Whether to deactivate the plugin for all sites in the network.
 * 	A value of null (the default) will deactivate plugins for both the site and the network.
 */
$result = deactivate_plugins($plugins);

if (is_wp_error($result)) {
    $this->save_file_update($file, false);
} else {
    $this->save_file_update($file, true);
}