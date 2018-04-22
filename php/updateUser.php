<?php
        require 'utils.php';
        $link = mysqli_connect($server, $user_name, $pass_word, $data_base);
		
        $logged_in_user_id = isset($_POST['logged_in_user_id']) ? getSecureInputData($_POST['logged_in_user_id']) : null;
        $logged_in_user_email = isset($_POST['logged_in_user_email']) ? getSecureInputData($_POST['logged_in_user_email']) : null;
        
        $first_name = isset($_POST['first_name']) ? getSecureInputData($_POST['first_name']) : null;
        $last_name = isset($_POST['last_name']) ? getSecureInputData($_POST['last_name']) : null;
        $email = isset($_POST['email']) ? getSecureInputData($_POST['email']) : null;
        $phone = isset($_POST['phone']) ? getSecureInputData($_POST['phone']) : null;
        $is_admin = isset($_POST['is_admin']) ? getBooleanValueAsInteger(getSecureInputData($_POST['is_admin'])) : 0;
        $is_active = isset($_POST['is_active']) ? getBooleanValueAsInteger(getSecureInputData($_POST['is_active'])) : 0;
        $hash = isset($_POST['hash']) ? getSecureInputData($_POST['hash']) : null;

        if ($link) {
          $isLoggedIn = isLoggedIn($logged_in_user_email, $logged_in_user_id, $link);
          if ($isLoggedIn) {
                $sqlUpdate = "UPDATE users
                    SET firstname = '$first_name',
                            lastname = '$last_name',
                            isadmin = '$is_admin',
                            active = '$is_active',
                            phone = '$phone'
                        WHERE email = '$email' AND hash = '$hash' ";

                if ($link->query($sqlUpdate) === TRUE) {
                        echo "User updated successfully";
                } else {
                        echo "An error occurred while updating this user: " . $sqlUpdate . "<br>" . $link->error;
                }
            } else {
                forbidden();
            }
            mysqli_close($link);
        }
?>
