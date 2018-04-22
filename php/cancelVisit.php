<?php
        require 'utils.php';
        $link = mysqli_connect($server, $user_name, $pass_word, $data_base);

        $logged_in_user_id = isset($_POST['logged_in_user_id']) ? getSecureInputData($_POST['logged_in_user_id']) : null;
        $logged_in_user_email = isset($_POST['logged_in_user_email']) ? getSecureInputData($_POST['logged_in_user_email']) : null;
  
        $visit_id = isset($_POST['visit_id']) ? getSecureInputData($_POST['visit_id']) : null;

        if ($link) {
           $isLoggedIn = isLoggedIn($logged_in_user_email, $logged_in_user_id, $link);
           if ($isLoggedIn) {
               $sqlDelete = "DELETE FROM visits 
							WHERE id = '$visit_id'";
    
                if ($link->query($sqlDelete) === TRUE) {
                      echo "Visit canceled successfully";
                } else {
                      echo "An error occurred while canceling this visit: " . $sqlDelete . "<br>" . $link->error;
                }
           } else {
                forbidden();
           }
        }
        mysqli_close($link);
?>
