<?php
        require 'utils.php';
        $link = mysqli_connect($server, $user_name, $pass_word, $data_base);
		
        $logged_in_user_id = isset($_POST['logged_in_user_id']) ? getSecureInputData($_POST['logged_in_user_id']) : null;
        $logged_in_user_email = isset($_POST['logged_in_user_email']) ? getSecureInputData($_POST['logged_in_user_email']) : null;
  
        $note_id = isset($_POST['note_id']) ? getSecureInputData($_POST['note_id']) : null;

        if ($link) {
           $isLoggedIn = isLoggedIn($logged_in_user_email, $logged_in_user_id, $link);
           if ($isLoggedIn) {
               $sqlDelete = "DELETE FROM notes 
							WHERE id = '$note_id'";
    
                if ($link->query($sqlDelete) === TRUE) {
                      echo "Note deleted successfully";
                } else {
                      echo "An error occurred while deleting this note: " . $sqlDelete . "<br>" . $link->error;
                }
           } else {
                forbidden();
           }
        }
        mysqli_close($link);
?>
