<?php
    session_start();
    $_SESSION['isLoggedIn'] = false;
    echo $_SESSION['isLoggedIn'];
    session_destroy();
?>
