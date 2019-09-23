<?php
// exemple de script d'update DB
global $wpdb;
$sql = 'UPDATE `' . $wpdb->prefix . 'users` 
        SET `user_nicename` = "Rodskin"
        WHERE `ID` = 1';
$query_result = $wpdb->query($sql);

// on sauvegarde la MAJ et on affiche le message OK / NOK
if ($query_result !== false) {
    $this->save_file_update($file, true);
} else {
    $this->save_file_update($file, false);
}