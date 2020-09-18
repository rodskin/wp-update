<?php
$file = basename(__FILE__);
global $wpdb;
$file_import = $this->plugin_path . 'externals/export_fournisseurs_tourcom.csv';
/*$sql = 'UPDATE `' . $wpdb->prefix . 'options`
        SET `option_value` = "TourCom"
        WHERE `option_name` = "dbem_mail_sender_name"';
$query_result = $wpdb->query($sql);*/

if (!file_exists($file_import)) {
    new WP_Update_Messages('Le fichier "' . $file_import . '" n\'existe pas', 'error');
} else {
    $path_parts = pathinfo($file_import);
    if ($path_parts['extension'] != 'csv') {
        new WP_Update_Messages('Le fichier "' . $file_import . '" n\'est pas au format CSV', 'error');
    } else {
        $row = 0;
        if (($handle = fopen($file_import, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                if ($row == 0) {
                    if (count(array_intersect(array('wordpress_id', 'code_fournisseur'), $data)) == 2) {
                        $headers = array_flip($data);
                    } else {
                        new WP_Update_Messages('il manque les champ "wordpress_id" ou "code_fournisseur"', 'error');
                        return;
                    }
                } else {
                    if ($data[$headers['code_fournisseur']] != '') {
                        $sql = 'UPDATE `' . $wpdb->prefix . 'postmeta`
                            SET `meta_value` = "' . $data[$headers['code_fournisseur']] . '"
                            WHERE `meta_key` = "code_fournisseur"
                            AND `post_id` = ' . (int)$data[$headers['wordpress_id']];
                        $query_result = $wpdb->query($sql);

                    }
                }
                $row++;
            }
            fclose($handle);
        }


        if ($query_result !== false) {
            $this->save_file_update($file, true);
        } else {
            $this->save_file_update($file, false);
        }
    }
}

