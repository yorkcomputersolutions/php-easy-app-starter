<?php

function pisp_does_user_exist_by( $by, $value ) {
    global $dbh;

    $exist_by = '';
    $exist_by_type = null;
    switch ( $by ) {
        case 'id': {
            $exist_by = 'id';
            $exist_by_type = PDO::PARAM_INT;
            break;
        }

        case 'user_login': {
            $exist_by = 'user_login'; 
            $exist_by_type = PDO::PARAM_STR;
            break;
        }

        case 'user_email': {
            $exist_by = 'user_email';
            $exist_by_type = PDO::PARAM_STR;
            break;
        }
    }

    if ( ! empty( $exist_by ) && isset( $exist_by_type ) ) {
        $users_table_name = DB_TABLE_PREFIX . 'users';
        $sql = "SELECT COUNT(*) FROM $users_table_name WHERE $exist_by=:exist_value";
        $stmt = $dbh->prepare( $sql );
        $stmt->bindParam( ':exist_value', $value, $exist_by_type );
        $result_stmt = $stmt->execute();

        if ( $stmt->fetchColumn() > 0 ) {
            return true;
        }
    }

    return false;
}

function pisp_insert_user( $userdata ) {
    global $dbh;

    $result_execute = false;

    if ( ! pisp_does_user_exist_by( 'user_login', $userdata['user_login'] ) &&
        ! pisp_does_user_exist_by( 'user_email', $userdata['user_email'] ) ) {
        
        $users_table_name = DB_TABLE_PREFIX . 'users';
        $stmt = $dbh->prepare( "INSERT INTO $users_table_name (user_login, user_pass, user_email) VALUES (:user_login, :user_pass, :user_email)" );
        $result_execute = $stmt->execute( array(
            'user_login' => $userdata['user_login'],
            'user_pass' => password_hash( $userdata['user_pass'], PASSWORD_DEFAULT ),
            'user_email' => $userdata['user_email']
        ) );
    }

    return $result_execute;
}

