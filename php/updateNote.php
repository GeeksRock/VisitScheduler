<?php
        require 'utils.php';
		$link = mysqli_connect($server, $user_name, $pass_word, $data_base);

        $logged_in_user_id = isset($_POST['logged_in_user_id']) ? getSecureInputData($_POST['logged_in_user_id']) : null;
        $logged_in_user_email = isset($_POST['logged_in_user_email']) ? getSecureInputData($_POST['logged_in_user_email']) : null;

        $note_details = isset($_POST['note_details']) ? getSecureInputData($_POST['note_details']) : null;
        $note_id = isset($_POST['note_id']) ? getSecureInputData($_POST['note_id']) : null;

        if ($link) {
           $isLoggedIn = isLoggedIn($logged_in_user_email, $logged_in_user_id, $link);
           if ($isLoggedIn) {
               $sqlUpdate = "UPDATE notes 
							SET details = '$note_details'
							WHERE id = '$note_id'";
    
                if ($link->query($sqlUpdate) === TRUE) {
                      echo "Note added successfully";
                } else {
                      echo "An error occurred while updating this note: " . $sqlUpdate . "<br>" . $link->error;
                }
           } else {
                forbidden();
           }
        }
        mysqli_close($link);
?>
