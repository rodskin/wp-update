<?php
$file = basename(__FILE__);

global $wpdb;

$array_fields_to_update = array('dbem_agency', 'dbem_supervisor', 'dbem_phone', 'dbem_supervisor_email');

$sql = 'SELECT `option_value`
        FROM `' . $wpdb->prefix . 'options`
        WHERE `option_name` = "em_user_fields"
        LIMIT 1';
$result = $wpdb->get_results($sql);

$fields = unserialize($result[0]->option_value);

foreach ($fields as $field_slug => $field_values) {
    if (in_array($field_slug, $array_fields_to_update)) {
        $fields[$field_slug]['required'] = '0';
        $fields[$field_slug]['options_text_regex'] = '';
    }
}
$fields = serialize($fields);

$sql = 'UPDATE `' . $wpdb->prefix . 'options`
        SET `option_value` = "' . str_replace('"', '\"', $fields) . '"
        WHERE `option_name` = "em_user_fields"';
$query_result = $wpdb->query($sql);

if ( is_wp_error( $query_result ) ) {
    $this->save_file_update($file, false);
} else {
    $this->save_file_update($file, true);
}