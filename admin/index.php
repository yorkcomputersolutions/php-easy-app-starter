<?php

/* Load Admin Dashboard Bootstrap */
require_once './admin.php';

global $dbh;

$page = isset( $_GET['page'] ) ? $_GET['page'] : '';

echo BASE_URL . 'admin/css/styles.css';

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta lang="en-us">
        <title>Project Name</title>
        <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL . '/admin/css/styles.css'; ?>" />
    </head>

    <body>
        <div class="dashboard-flex">
            <aside class="sidebar">
                <nav>
                    
                </nav> 
            </aside>

            <div class="dashboard-content-wrap">
                <div class="dashboard-content-padding">

                </div>
            </div>
        </div>
    </body>
</html>
