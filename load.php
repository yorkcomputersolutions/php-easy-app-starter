<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'INC' ) ) {
    define( 'INC', 'includes/' );
}

if ( ! defined( 'INC_URL' ) ) {
    define( 'INC_URL', 'includes/' );
}

if ( ! defined( 'INC_CSS_URL' ) ) {
    define( 'INC_CSS_URL', INC_URL . 'css/' );
}




$config_path = '';

if ( file_exists( ABSPATH . 'config.php' ) ) {
    require_once ABSPATH . 'config.php';

    $GLOBALS['dbh'] = null;

    try {
        $GLOBALS['dbh'] = new PDO( 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER_NAME, DB_USER_PASS );
    }
    catch ( PDOException $e ) {
        $GLOBALS['dbh'] = false;
    }
}

session_start();