<?php
        require 'utils.php';
        $link = mysqli_connect($server, $user_name, $pass_word, $data_base);
		
        $logged_in_user_id = isset($_POST['logged_in_user_id']) ? getSecureInputData($_POST['logged_in_user_id']) : null;
        $logged_in_user_email = isset($_POST['logged_in_user_email']) ? getSecureInputData($_POST['logged_in_user_email']) : null;

        $note_details = isset($_POST['note_details']) ? getSecureInputData($_POST['note_details']) : null;
        $note_date = isset($_POST['note_date']) ? getSecureInputData($_POST['note_date']) : null;
        $client_id = isset($_POST['client_id']) ? getSecureInputData($_POST['client_id']) : null;
        $visit_id = isset($_POST['visit_id']) ? getSecureInputData($_POST['visit_id']) : null;
        $visit_type = isset($_POST['visit_type']) ? getSecureInputData($_POST['visit_type']) : null;
        $user_id =  isset($_POST['user_id']) ? getSecureInputData($_POST['user_id']) : null;

        if ($link) {
           $isLoggedIn = isLoggedIn($logged_in_user_email, $logged_in_user_id, $link);
           if ($isLoggedIn) {
               $sqlInsert = "INSERT INTO notes
                                (details, client_id, visit_id, type, user_id, date)
                            VALUES
                                ('$note_details', '$client_id', '$visit_id', '$visit_type', '$user_id', '$note_date')";
    
                if ($link->query($sqlInsert) === TRUE) {
                      echo "Note added successfully";
                } else {
                      echo "An error occurred while adding this note: " . $sqlInsert . "<br>" . $link->error;
                }
           } else {
                forbidden();
           }
        }
        mysqli_close($link);
?>
