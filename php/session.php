<?php
        require 'utils.php';
        $allow_pass_through = isset($_POST['allow_pass_through']) ? getSecureInputData($_POST['allow_pass_through']) : 0;

        if ($allow_pass_through == 1) {
            session_start();
            echo json_encode($_SESSION);
        } else {
            return "forbidden";
        }
?>
