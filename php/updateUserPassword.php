<?php
        require 'utils.php';
		$link = mysqli_connect($server, $user_name, $pass_word, $data_base);
		
        $logged_in_user_id = isset($_POST['logged_in_user_id']) ? getSecureInputData($_POST['logged_in_user_id']) : null;
        $logged_in_user_email = isset($_POST['logged_in_user_email']) ? getSecureInputData($_POST['logged_in_user_email']) : null;

        $first_name = isset($_POST['first_name']) ? getSecureInputData($_POST['first_name']) : null;
        $last_name = isset($_POST['last_name']) ? getSecureInputData($_POST['last_name']) : null;
        $email = isset($_POST['email']) ? getSecureInputData($_POST['email']) : null;
        $password = isset($_POST['password']) ? getSecureInputData(password_hash($_POST['password'], PASSWORD_BCRYPT)) : null;
        $hash = isset($_POST['hash']) ? getSecureInputData($_POST['hash']) : null;
        $new_hash = getSecureInputData( md5( rand(0,1000) ) );

        if ($link) {
           $isLoggedIn = isLoggedIn($logged_in_user_email, $logged_in_user_id, $link);
           if ($isLoggedIn) {
                $sqlUpdate = "UPDATE users
                              SET password = '$password', hash = '$new_hash', active = '0'
                              WHERE email = '$email' AND hash = '$hash' ";

                if ($link->query($sqlUpdate) === TRUE) {
                      //echo "User password reset successfully";
                      echo sendPasswordResetEmail($email, $first_name, $last_name, $new_hash, getSecureInputData($_POST['password']));
                } else {
                      echo "An error occurred while resetting this user's password: " . $sqlUpdate . "<br>" . $link->error;
                }
           } else {
                forbidden();
           }
           mysqli_close($link);
        }
?>
