<?php
/*
Plugin Name: WP Update
Plugin URI: https://github.com/rodskin/wp-update
Description: Update Wordpress with ACFs and Databases updates versionned
Version: 0.1
Author: Rodskin
Author URI: https://rodskin.github.io/
*/


require ('classes/wp-update-messages.php');

class WP_Update_Plugin
{
    protected $plugin_path = '';

    public function __construct()
    {
		if (!defined('WP_ENVIRONNMENT')) {
			new WP_Update_Messages(__('La constante WP_ENVIRONNMENT n\'est pas definie (local | preprod | prod)', 'wp-update'), 'error');
		}
        $this->plugin_path = plugin_dir_path( __FILE__ );
        register_activation_hook(__FILE__, array('WP_Update_Plugin', 'install'));
        $this->add_hook();
        // END CONSTRUCT
        register_uninstall_hook(__FILE__, array('WP_Update_Plugin', 'uninstall'));
    }

    public function add_hook()
    {
        // Only allow fields to be edited on development
        if ( (!defined( 'WP_ENVIRONNMENT' ) || !in_array(WP_ENVIRONNMENT, array('local', 'preprod'))) ) {
            //add_filter( 'acf/settings/show_admin', '__return_false' );
        }
		add_action('plugins_loaded', array($this,'plugin_init')); 
        add_action('admin_init', array($this, 'import_updates'));
        if (is_plugin_active('advanced-custom-fields-pro/acf.php') || is_plugin_active('advanced-custom-fields/acf.php')) {
            // Save fields in functionality plugin
            add_filter('acf/settings/save_json', array($this, 'get_local_json_path'));
            add_filter('acf/settings/load_json', array($this, 'add_local_json_path'));
        }
		if (is_multisite()) {
			add_action('network_admin_menu', array($this, 'add_network_admin_menu'));
			add_action('network_admin_edit_wpupdatenetworkaction', array($this, 'network_save_settings'));
		} else {
			add_action('admin_menu', array($this, 'add_admin_menu'));
		}

        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_css_js'));
    }
	
	public function plugin_init ()
	{
		load_plugin_textdomain( 'wp-update', false, $this->plugin_path . '/languages/' );
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
        // show the menu link only for administrator
        $user = wp_get_current_user()->roles[0];
        if($user == 'administrator') {
            add_menu_page('WP Update', 'WP Update', 'manage_options', 'wp-update', array($this, 'home_html'), 'dashicons-update', 666);
        }

        add_filter( 'add_menu_classes', array($this, 'add_updates_bubble'));
    }
	
	public function add_network_admin_menu()
    {
        // show the menu link only for administrator
        $user = wp_get_current_user()->roles[0];
        if($user == 'administrator') {
            add_menu_page('WP Update', 'WP Update', 'manage_options', 'wp-update', array($this, 'home_html'), 'dashicons-update', 666);
			$hook = add_submenu_page('wp-update', 'Settings', 'Settings', 'manage_options', 'wp-update_network_settings', array($this, 'network_settings_html'));
        }

        add_filter( 'add_menu_classes', array($this, 'add_updates_bubble'));
    }
	
	public function settings_html()
    {
        echo '<h1>' . get_admin_page_title() . '</h1>';
?>
        <form method="post" action="options.php">
            <?php settings_fields('wp-update_settings'); ?>
            <label>Choisir un sous-site avec ACF maitre</label>
			<select name="wp-update_acf-master">
				<option value=""<?php if (get_option('wp-update_acf-master') == '') { echo ' selected="selected"'; } ?>><?php echo __('No master site', 'wp-update'); ?></option>
				<?php
					$sites = get_sites();
					foreach ($sites as $site) {
						echo '<option value="' . $site->blog_id . '"';
						if (get_option('wp-update_acf-master') == $site->blog_id) {
							echo ' selected="selected"';
						}
						echo '>' . $site->domain . '</option>';
					}
				?>
			</select>
            <?php submit_button(__('Update settings', 'wp-update')); ?>
        </form>
<?php
    }
	
	public function network_settings_html()
    {
        echo '<h1>' . get_admin_page_title() . '</h1>';
?>
        <form method="post" action="edit.php?action=wpupdatenetworkaction">
            <?php wp_nonce_field( 'wpupdate-validate' ); ?>
            <label>Choisir un sous-site avec ACF maitre network</label>
			<select name="wp-update_acf-master">
				<option value=""<?php if (get_site_option('wp-update_acf-master') == '') { echo ' selected="selected"'; } ?>><?php echo __('No master site', 'wp-update'); ?></option>
				<?php
					$sites = get_sites();
					foreach ($sites as $site) {
						echo '<option value="' . $site->blog_id . '"';
						if (get_site_option('wp-update_acf-master') == $site->blog_id) {
							echo ' selected="selected"';
						}
						echo '>' . $site->domain . '</option>';
					}
				?>
			</select>
            <?php submit_button(__('Update settings', 'wp-update')); ?>
        </form>
<?php
    }
	
	public function network_save_settings ()
	{
		check_admin_referer( 'wpupdate-validate' ); // Nonce security check
		update_site_option( 'wp-update_acf-master', $_POST['wp-update_acf-master'] );
		wp_redirect( add_query_arg( array(
			'page' => 'wp-update_network_settings',
			'updated' => true ), network_admin_url('admin.php?page=wp-update_network_settings')
		));
		exit;
	}


    public function add_updates_bubble( $menu )
    {
        $pending_count = $this->get_updates_bubble_count(); // Use your code to create this number
        $menu[666][0] .= " <span class='update-plugins count-$pending_count'><span class='plugin-count'>" . number_format_i18n($pending_count) . '</span></span>';
        if (is_plugin_active('advanced-custom-fields-pro/acf.php') || is_plugin_active('advanced-custom-fields/acf.php')) {
            $acf_num_to_sync = $this->sync_acf_fields();
            $menu['80.025'][0] .= ' <span class="update-plugins count-' . $acf_num_to_sync . '"><span class="plugin-count">(sync: ' . number_format_i18n($acf_num_to_sync) . ')</span></span>';
        }
        return $menu;
    }

    public function get_updates_bubble_count () {
        $directory = plugin_dir_path( __FILE__ ) . 'data';
        $scanned_directory = $this->get_scanned_dir_files($directory);
        $return_number = 0;
        foreach ($scanned_directory as $file) {
            $file_infos = $this->get_file_infos($file);
            if (empty($file_infos)) {
                $return_number ++;
            }
        }
        return $return_number;
    }

    public function home_html()
    {
?>
		<h1><?php echo get_admin_page_title(); ?></h1>
        <form method="post" id="wp-update" action="">
            <input type="hidden" name="wp-update_hidden" value="0" />
            <table id="wp-update_table" cellpading="0" cellspacing="0">
                <thead>
                    <tr>
                        <th class="wp-update_table_filename">Nom du fichier</th>
                        <th class="wp-update_table_status">Status</th>
                        <th class="wp-update_table_date">Date import</th>
                        <th class="wp-update_table_reload">Relancer</th>
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
                        if ($file_infos[0]->status == '1') {
                    ?>
                    <td class="update_ok">Success</td>
                    <?php
                        } else {
                    ?>
                    <td class="update_ko">Error</td>
                    <?php
                        }
                    } else {
                    ?>
                        <td class="update_todo">À importer</td>
                    <?php
                    }
                    ?>
                    <td><?php echo !empty($file_infos)? $file_infos[0]->date_install : '-'; ?></td>
                    <td><?php echo ($file_infos[0]->status == '0' ? '<a href="?page=' . $_GET['page'] . '&reload_update=' . $file_infos[0]->ID . '">relancer</a>' : ''); ?></td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php submit_button(__('Update', 'wp-update')); ?>
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
        if (isset($_GET['reload_update']) && $_GET['reload_update'] != '') {
            $this->reupload_file($_GET['reload_update']);
        }
        if (!empty($_POST) && count($_POST) > 0 && isset($_POST['wp-update_hidden'])) {
            $this->import_updates_files();
        }
    }

    public function sync_acf_fields ()
    {
        // vars
        $groups = acf_get_field_groups();
        $sync   = array();
        // bail early if no field groups
        if( empty( $groups ) ) {
            return 0;
        }

        // find JSON field groups which have not yet been imported
        foreach( $groups as $group ) {

            // vars
            $local      = acf_maybe_get( $group, 'local', false );
            $modified   = acf_maybe_get( $group, 'modified', 0 );
            $private    = acf_maybe_get( $group, 'private', false );
            // ignore DB / PHP / private field groups
            if( $local !== 'json' || $private ) {

                // do nothing

            } elseif( ! $group[ 'ID' ] ) {

                $sync[ $group[ 'key' ] ] = $group;

            } elseif( $modified && $modified > get_post_modified_time( 'U', true, $group[ 'ID' ], true ) ) {

                $sync[ $group[ 'key' ] ]  = $group;
            }
        }
        return count($sync);
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
            }
        }
    }

    public function reupload_file($id)
    {
        global $wpdb;
        $sql = 'SELECT *
                FROM `' . $wpdb->prefix . 'wp_update`
                WHERE `ID` = ' . $id . '
                LIMIT 1';
        $file_infos = $wpdb->get_results($sql);
        $directory = plugin_dir_path( __FILE__ ) . 'data';
        if (!empty($file_infos)) {
            $sql = 'DELETE FROM `' . $wpdb->prefix . 'wp_update` WHERE `ID` = ' . $id;
            $wpdb->query($sql);
            include_once($directory . '/' . $file_infos[0]->name);
        } else {
            new WP_Update_Messages('Fichier inexistant', 'error');
        }
    }

    public function save_file_update ($filename, $status)
    {
        global $wpdb;
        $sql = 'INSERT INTO `' . $wpdb->prefix . 'wp_update` (`name`, `status`, `date_install`) VALUES ("' . $filename . '", "' . ($status !== false ? '1' : '0') . '", "' . date('Y-m-d') . '")';
        $query_result = $wpdb->query($sql);
        if (isset($_GET['reload_update']) && $_GET['reload_update'] != '') {
            wp_redirect('admin.php?page=wp-update');
            exit;
        }
        if ($query_result !== false) {
            if ($status === true) {
                new WP_Update_Messages('Import de ' . $filename, 'notice-success');
            } else {
                new WP_Update_Messages('Erreur dans le fichier d\'import de ' . $filename, 'error');
            }
        } else {
            new WP_Update_Messages('Erreur lors de l\'ajout en base de données de ' . $filename, 'error');
        }

    }

    public function get_local_json_path() {
		$path = plugin_dir_path( __FILE__ ) . 'acf-json';
		if (is_multisite()) {
			// we add blog id
			$path .= '/' . get_current_blog_id();
			if (!is_dir($path)) {
				mkdir($path, '0775');
			}
		}
        return $path;
    }

    public function add_local_json_path( $paths ) {
        $paths = array();
		$acf_path = plugin_dir_path( __FILE__ ) . 'acf-json';
		if (is_multisite()) {
			// we add blog id
			$master_site = get_site_option('wp-update_acf-master');
			if ($master_site !== '' && $master_site != get_current_blog_id()) {
				$paths[] = $acf_path . '/' . $master_site;
			}
            $add_path = $acf_path . '/' . get_current_blog_id();
			if (!is_dir($add_path)) {
				mkdir($add_path, '0775');
			}
			$paths = $add_path;
		} else {
            $paths[] = $acf_path;
        }
        return $paths;
    }
}

new WP_Update_Plugin();

/**
 * TODO
 * option pour auto update des fields ?
 * ajout de la bulle des ACF en attente de synchro
 * https://stackoverflow.com/questions/41171129/including-acf-advanced-custom-fields-in-a-custom-theme-or-plugin-exporting-fi
 */
