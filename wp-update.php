<?php
/*
Plugin Name: Wordpress Update
Description: Update Wordpress with ACFs and Databases updates versionned
Version: 0.1
Author: Rodskin
Author URI: https://ko-fi.com/rodskin
*/


require ('classes/wp-update-messages.php');

class WP_Update_Plugin
{
    public function __construct()
    {
        global $type_centrale;
        register_activation_hook(__FILE__, array('WP_Update_Plugin', 'install'));
        $this->add_hook();
        // END CONSTRUCT
        register_uninstall_hook(__FILE__, array('WP_Update_Plugin', 'uninstall'));
    }

    public function add_hook()
    {
        add_action('admin_init', array($this, 'import_updates'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_css_js'));
        // Save fields in functionality plugin
        add_filter( 'acf/settings/save_json', array( $this, 'get_local_json_path' ) );
        add_filter( 'acf/settings/load_json', array( $this, 'add_local_json_path' ) );
    }

    public static function install()
    {
        require_once(plugin_dir_path( __FILE__ ) . 'sql/install.php');
    }

    public static function uninstall()
    {
        require_once(plugin_dir_path( __FILE__ ) . 'sql/uninstall.php');
    }

    public function add_admin_menu()
    {
        add_menu_page('WP Update', 'WP Update', 'manage_options', 'wp-update', array($this, 'home_html'));
    }

    public function home_html()
    {
        echo '<h1>' . get_admin_page_title() . '</h1>';
?>
        <form method="post" action="">
            <table id="wp-update_table" cellpading="0" cellspacing="0">
                <thead>
                    <tr>
                        <th class="wp-update_table_filename">Nom du fichier</th>
                        <th class="wp-update_table_status">Status</th>
                        <th class="wp-update_table_date">Date import</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $directory = plugin_dir_path( __FILE__ ) . 'data';
                    $scanned_directory = $this->get_scanned_dir_files($directory);
                    foreach ($scanned_directory as $file) {
                        $file_infos = $this->get_file_infos($file);
                ?>
                <tr>
                    <td><?php echo $file; ?></td>
                    <?php
                    if (!empty($file_infos)) {
                    ?>
                        <td class="update_ok">Importé</td>
                    <?php
                    } else {
                    ?>
                        <td class="update_ko">À importer</td>
                    <?php
                    }
                    ?>
                    <td><?php echo !empty($file_infos)? $file_infos[0]->date_install: '-'; ?></td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php submit_button(); ?>
        </form>
<?php
    }

    public function admin_enqueue_css_js ($hook)
    {
        if ($hook !== 'toplevel_page_wp-update') {
            return;
        }
        wp_enqueue_style( 'wp-update-admin', plugin_dir_url(__FILE__) . 'css/wp-update.css', [], '1.0', 'all');
    }

    public function import_updates()
    {
        if (!empty($_POST) && count($_POST) > 0) {
            $this->import_updates_files();
        }
    }

    public function get_scanned_dir_files ($path)
    {
        $scan_ommit = array('..', '.', '.gitkeep');
        return array_diff(scandir($path), $scan_ommit);
    }

    public function get_file_infos ($filename)
    {
        global $wpdb;
        $sql = 'SELECT *
                FROM `' . $wpdb->prefix . 'wp_update' . '`
                WHERE `name` = "' . $filename . '"
                LIMIT 1';
        $return = $wpdb->get_results($sql);
        return $return;
    }

    public function import_updates_files ()
    {

        $directory = plugin_dir_path( __FILE__ ) . 'data';
        $scanned_directory = $this->get_scanned_dir_files($directory);
        foreach ($scanned_directory as $file) {
            $file_infos = $this->get_file_infos($file);
            if (empty($file_infos)) {
                include_once($directory . '/' . $file);
                $this->save_file_update($file);
            }
        }
    }

    public function save_file_update ($filename)
    {
        global $wpdb;
        $sql = 'INSERT INTO `' . $wpdb->prefix . 'wp_update` (`name`, `date_install`) VALUES ("' . $filename . '", "' . date('Y-m-d') . '")';
        $query_result = $wpdb->query($sql);
        if ($query_result !== false) {
            new WP_Update_Messages('Import de ' . $filename, 'notice-success');
        } else {
            new WP_Update_Messages('Import de ' . $filename, 'error');
        }
    }

    public function get_local_json_path() {
        return plugin_dir_path( __FILE__ ) . 'acf-json';
    }

    public function add_local_json_path( $paths ) {
        $paths[] = plugin_dir_path( __FILE__ ) . 'acf-json';

        return $paths;
    }
}

new WP_Update_Plugin();