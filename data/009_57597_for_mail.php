<?php
$file = basename(__FILE__);
global $wpdb;
$sql = 'UPDATE `' . $wpdb->prefix . 'options`
        SET `option_value` = "La réservation suivante est en attente :
(#_EVENTNAME - #_EVENTDATES à #_EVENTTIMES)

Par: #_BOOKINGNAME (#_BOOKINGEMAIL)


Cette réservation est maintenant en attente de confirmation."
        WHERE `option_name` = "dbem_bookings_contact_email_pending_body"';
$query_result = $wpdb->query($sql);

$sql = 'UPDATE `' . $wpdb->prefix . 'options`
        SET `option_value` = "Vous avez réservé (#_BOOKINGSPACES) place(s) pour (#_EVENTNAME).
Quand : (#_EVENTDATES à #_EVENTTIMES)
Où : (#_LOCATIONNAME)


Votre inscription est confirmée.

Cordialement,
L\'équipe TourCom"
        WHERE `option_name` = "dbem_bookings_email_confirmed_body"';
$query_result = $wpdb->query($sql);


$sql = 'UPDATE `' . $wpdb->prefix . 'options`
        SET `option_value` = "Vous avez demandé à réserver (#_BOOKINGSPACES) place(s) pour (#_EVENTNAME).
Quand : (#_EVENTDATES à #_EVENTTIMES)
Où : (#_LOCATIONNAME)


Cette réservation est maintenant en attente de confirmation. Une fois approuvée vous recevrez un e-mail automatique de confirmation.

Cordialement,
L\'équipe TourCom"
        WHERE `option_name` = "dbem_bookings_email_pending_body"';
$query_result = $wpdb->query($sql);


if ($query_result !== false) {
    $this->save_file_update($file, true);
} else {
    $this->save_file_update($file, false);
}

