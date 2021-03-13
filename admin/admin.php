<?php

if ( ! defined( 'ADMIN' ) ) {
    define( 'ADMIN', true );
}

require_once dirname( __DIR__ ) . '/load.php';

if ( ! isset( $_SESSION['logged_in'] ) ) {
    header( 'location: ../login.php' );
}
