<?php
require './header.php';

$input_db_host = '';
$input_db_name = '';
$input_db_user_name = '';
$input_db_user_pass = '';
$input_db_table_prefix = '';
$db_is_validated = false;
$user_is_validated = false;

function install_tables( $dbh, $data ) {
    $table_names = array(
        $data['table_prefix'] . 'users'
    );

    foreach ( $table_names as $table_name ) {
        $dbh->exec( "DROP TABLE IF EXISTS " . $table_name );
    }

    $users_table_name = $data['table_prefix'] . 'users';
    $dbh->exec( "CREATE TABLE " . $users_table_name . " (
        `id` bigint NOT NULL AUTO_INCREMENT,
        `user_login` varchar(50) NOT NULL,
        `user_pass` varchar(255) NOT NULL,
        `user_email` varchar(100) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;" );
}

if ( isset( $_POST['installer_form'] ) ) {
    if ( isset( $_POST['action'] ) ) {
        switch ( $_POST['action'] ) {
            case 'db_validate': {
                $input_db_host = isset( $_POST['db_host'] ) ? $_POST['db_host'] : '';
                $input_db_name = isset( $_POST['db_name'] ) ? $_POST['db_name'] : '';
                $input_db_user_name = isset( $_POST['db_user_name'] ) ? $_POST['db_user_name'] : '';
                $input_db_user_pass = isset( $_POST['db_user_pass'] ) ? $_POST['db_user_pass'] : '';
                $input_db_table_prefix = isset( $_POST['db_table_prefix'] ) ? $_POST['db_table_prefix'] : '';

                try {
                    $dbh = new PDO( 'mysql:host=' . $input_db_host . ';dbname=' . $input_db_name, $input_db_user_name, $input_db_user_pass );
                    install_tables(
                        $dbh,
                        array(
                            'table_prefix' => $input_db_table_prefix
                        )
                    );
                    $dbh = null;
                    $db_is_validated = true;
                }
                catch ( PDOException $e ) {
                    $db_is_validated = false;
                }

                if ( $db_is_validated ) {
                    $base_url = '';
                    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                        $base_url .= "https";
                    }
                    else {
                        $base_url .= "http";
                        // Here append the common URL characters. 
                        $base_url .= "://"; 
                        // Append the host(domain name, ip) to the URL. 
                        $base_url .= $_SERVER['HTTP_HOST'];
                    }

                    $config_string = "
<?php
define( 'DB_HOST', '" . $input_db_host . "' );
define( 'DB_NAME', '" . $input_db_name . "' );
define( 'DB_USER_NAME', '" . $input_db_user_name . "' );
define( 'DB_USER_PASS', '" . $input_db_user_pass . "' );

define( 'DB_TABLE_PREFIX', '" . $input_db_table_prefix . "' );

define( 'BASE_URL', '" . $base_url . "' );
";

                    $config_file = fopen( 'config.php', 'w' ) or die( 'Cannot open the file.  Please check your write permissions.' );
                    fwrite( $config_file, $config_string );
                    fclose( $config_file );
                }

                break;
            }

            case 'admin_account': {
                $user_login = isset( $_POST['user_login'] ) ? $_POST['user_login'] : '';
                $user_email = isset( $_POST['user_email'] ) ? $_POST['user_email'] : '';
                $user_pass = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : '';

                if ( ! empty( $user_login ) &&
                    ! empty( $user_email ) &&
                    ! empty( $user_pass ) ) {
                    
                    $result_insert_user = pisp_insert_user( array(
                        'user_login' => $user_login,
                        'user_email' => $user_email,
                        'user_pass'  => $user_pass
                    ) );

                    if ( $result_insert_user ) {
                        header( 'location: ?page=finished' );
                    }
                }
                
                break;
            }
        }
    }
}

$pages = array(
    'welcome' => array(
        'id' => 'welcome',
        'title' => 'Welcome to the Installer',
        'description' => 'To begin the install process, please click next.',
        'html' => ''
    ),
    'db_details' => array(
        'id' => 'db_details',
        'title' => 'Database Details',
        'description' => 'Please enter your database credentials.',
        'html' => ''
    ),
    'admin_account' => array(
        'id' => 'admin_account',
        'title' => 'Login Details',
        'description' => 'Please enter details to log in with.',
        'html' => ''
    ),
    'finished' => array(
        'id' => 'finished',
        'title' => 'Finished',
        'description' => 'You\'re all set!',
        'html' => '<p>Click <a href="login.php">here</a> to login.</p>'
    )
);



// Generate markup

$pages['welcome']['html'] = <<<EOD
<a class="btn-primary" href="?page=db_details">Next</a>
EOD;

$pages['db_details']['html'] = <<<EOD
%validation-description%
<form action="" method="post">
    <input name="installer_form" type="hidden" value="true" />
    <input name="action" type="hidden" value="db_validate" />
    <table class="form-table">
        <tr>
            <td><label for="db_host">DB Host:</label></td>
            <td><input id="db_host" name="db_host" type="text" value="%input-db-host%" /></td>
        </tr>

        <tr>
            <td><label for="db_name">DB Name:</label></td>
            <td><input id="db_name" name="db_name" type="text" value="%input-db-name%" /></td>
        </tr>  

        <tr>
            <td><label for="db_user_name">DB User Name:</label></td>
            <td><input id="db_user_name" name="db_user_name" type="text" value="%input-db-user-name%" /></td>
        </tr>

        <tr>
            <td><label for="db_user_pass">DB Pass:</label></td>
            <td><input id="db_user_pass" name="db_user_pass" type="password" value="%input-db-user-pass%" /></td>
        </tr>

        <tr>
            <td><label for="db_table_prefix">DB Table Prefix:</label></td>
            <td><input id="db_table_prefix" name="db_table_prefix" type="text" value="%input-db-table-prefix%" /></td>
        </tr>
    </table>

    %validate-button%
</form>

%next-button%
EOD;

$pages['admin_account']['html'] = <<<EOD
<form action="" method="post">
    <input name="installer_form" type="hidden" value="true" />
    <input name="action" type="hidden" value="admin_account" />
    <table class="form-table">
        <tr>
            <td><label for="user_login">Username:</label></td>
            <td><input id="user_login" name="user_login" type="text" value="%input-username%" /></td>
        </tr>

        <tr>
            <td><label for="user_email">Email:</label></td>
            <td><input id="user_email" name="user_email" type="text" value="%input-email%" /></td>
        </tr>

        <tr>
            <td><label for="user_pass">Password:</label></td>
            <td><input id="user_pass" name="user_pass" type="text" value="%input-pass%" /></td>
        </tr>
    </table>

    <button class="btn-primary" type="submit">Register</button>
</form>
EOD;

$page = isset( $_GET['page'] ) ? $pages[$_GET['page']] : $pages[array_key_first( $pages )];

switch ( $page['id'] ) {
    case 'db_details': {
        if ( isset( $db_is_validated ) ) {
            if ( $db_is_validated === true ) {
                $page['html'] = str_replace( '%validation-description%', '<p class="validation-description">Successfully connected to the database.  Please click next to continue.</p>', $page['html'] );
                $page['html'] = str_replace( '%validate-button%', '', $page['html'] );
                $page['html'] = str_replace( '%next-button%', '<a class="btn-primary" href="?page=admin_account">Next</a>', $page['html'] );
            }
            else if ( $db_is_validated === false ) {
                $page['html'] = str_replace( '%validation-description%', '<p class="validation-description">Failed to connect to the database.  Please try again.</p>', $pages['db_details']['html'] );
                $page['html'] = str_replace( '%validate-button%', '<button class="btn-primary" type="submit">Validate</button>', $page['html'] );
                $page['html'] = str_replace( '%next-button%', '', $page['html'] );
            }

            $page['html'] = str_replace(
                array(
                    '%input-db-host%',
                    '%input-db-name%',
                    '%input-db-user-name%',
                    '%input-db-user-pass%',
                    '%input-db-table-prefix%'
                ),
                array(
                    $input_db_host,
                    $input_db_name,
                    $input_db_user_name,
                    $input_db_user_pass,
                    $input_db_table_prefix
                ),
                $page['html']
            );
        }
        else {
            $page['html'] = str_replace( '%validation-description%', '', $page['html'] );
            $page['html'] = str_replace( '%validate-button%', '<button class="btn-primary" type="submit">Validate</button>', $page['html'] );
            $page['html'] = str_replace( '%next-button%', '', $page['html'] );

            $page['html'] = str_replace(
                array(
                    '%input-db-host%',
                    '%input-db-name%',
                    '%input-db-user-name%',
                    '%input-db-user-pass%',
                    '%input-db-table-prefix%'
                ),
                array(
                    '',
                    '',
                    '',
                    '',
                    'pisp_'
                ),
                $page['html']
            );
        }

        break;
    }

    case 'admin_account': {
        $page['html'] = str_replace(
            array(
                '%input-username%',
                '%input-email%',
                '%input-pass%',
            ),
            array(
                '',
                '',
                ''
            ),
            $page['html']
        );
        break;
    }
}


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta lang="en-us">
        <title><?php echo $page['title']; ?></title>

        <link rel="stylesheet" type="text/css" href="<?php echo INC_CSS_URL . 'styles.css'; ?>" />
    </head>
    
    <body>
        <div class="parent">
            <div class="wrap">
                <div class="wrap-padding">
                    <?php
                    if ( isset( $page['title'] ) ) {
                        ?>
                        <h2><?php echo $page['title']; ?></h2>
                        <?php
                    }

                    if ( isset( $page['description'] ) ) {
                        ?>
                        <p><?php echo $page['description']; ?></p>
                        <?php
                    }

                    if ( isset( $page['html'] ) ) {
                        ?>
                        <?php echo $page['html']; ?>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>