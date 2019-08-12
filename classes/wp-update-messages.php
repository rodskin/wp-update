<?php

class WP_Update_Messages {
    private $_message;
    private $_status;

    function __construct( $message, $status) {
        $this->_message = $message;
        $this->_status = $status;

        add_action( 'admin_notices', array( $this, 'render' ) );
    }

    function render() {
        printf( '<div class="notice %s"><p>%s</p></div>', $this->_status, $this->_message );
    }
}