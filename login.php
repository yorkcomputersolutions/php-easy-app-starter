<?php
require_once './header.php';

if ( isset( $_GET['action'] ) ) {
    if ( $_GET['action'] === 'logout' ) {
        session_start();
        session_destroy();
        header( 'location: login.php' );
    }
}

global $dbh;

$login_error = '';

if ( isset( $_POST['action'] ) ) {
    if ( $_POST['action'] === 'login' ) {
        $user_login = isset( $_POST['user_login'] ) ? $_POST['user_login'] : '';
        $user_pass = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : '';


        $users_table_name = DB_TABLE_PREFIX . 'users';

        $stmt = $dbh->prepare( "SELECT * FROM $users_table_name WHERE user_login=:user_login"  );
        $stmt->bindParam( ':user_login', $user_login, PDO::PARAM_STR );
        $stmt->execute();

        $result = $stmt->fetch();

        if ( $result !== false ) {
            $id_stored = $result['id'];
            $user_pass_stored = $result['user_pass'];

            // Account exists, verify the password
            if ( password_verify( $_POST['user_pass'], $user_pass_stored ) ) {
                session_regenerate_id();
                $_SESSION['logged_in'] = true;
                $_SESSION['user_login'] = $user_login_stored;
                $_SESSION['id'] = $id_stored;

                header( 'location: admin' );
                die();
            }
            else {
                $login_error = 'Incorrect username and/or password! not verified';
            }
        }
        else {
            $login_error = 'Incorrect username and/or password!';
        }

    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta lang="en-us">
        <title>Log In</title>
        <link rel="stylesheet" type="text/css" href="<?php echo INC_CSS_URL . 'styles.css'; ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo INC_CSS_URL . 'login.css'; ?>" />
    </head>

    <body>
        <div class="parent">
            <div class="wrap wrap-login">
                <div class="wrap-padding">
                    <h1>Login</h1>
                    <?php
                    if ( ! empty( $login_error ) ) {
                        ?>
                        <p><?php echo $login_error; ?></p>
                        <?php
                    }
                    ?>
                    <form id="loginform" name="loginform" action="login.php" method="post">
                        <input name="action" type="hidden" value="login" />
                        <table class="form-table form-table-login">
                            <tr>
                                <td><label for="user_login">Username</label></td>
                                <td><input id="user_login" name="user_login" type="text" required /> 
                            </tr>

                            <tr>
                                <td><label for="user_pass">Password</label></td>
                                <td><input id="user_pass" name="user_pass" type="password" required />
                            </tr>
                        </table>

                        <button class="btn-primary" type="submit">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>