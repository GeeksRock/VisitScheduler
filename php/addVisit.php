<?php
        require 'utils.php';
        $link = mysqli_connect($server, $user_name, $pass_word, $data_base);
		
        $logged_in_user_id = isset($_POST['logged_in_user_id']) ? getSecureInputData($_POST['logged_in_user_id']) : null;
        $logged_in_user_email = isset($_POST['logged_in_user_email']) ? getSecureInputData($_POST['logged_in_user_email']) : null;

        $date = isset($_POST['date']) ? getSecureInputData($_POST['date']) : null;
        $time_hour = isset($_POST['time_hour']) ? getSecureInputData($_POST['time_hour']) : null;
        $time_minutes = isset($_POST['time_minutes']) ? getSecureInputData($_POST['time_minutes']) : null;
        $time_of_day = isset($_POST['time_of_day']) ? getSecureInputData($_POST['time_of_day']) : null;
        $client_id = isset($_POST['client_id']) ? getSecureInputData($_POST['client_id']) : null;
        $user_id = isset($_POST['user_id']) ? getSecureInputData($_POST['user_id']) : 2;
        $type_id = isset($_POST['type_id']) ? getSecureInputData($_POST['type_id']) : 1;

        if ($link) {
            $isLoggedIn = isLoggedIn($logged_in_user_email, $logged_in_user_id, $link);
            if ($isLoggedIn) {
                 $sqlInsert = "INSERT INTO visits
                                  (date, timehour, timeminutes, timeofday, type_id, client_id, user_id) 
                              VALUES
                                  ('$date', '$time_hour', '$time_minutes', '$time_of_day', '$type_id', '$client_id', '$user_id')"; 

                  if ($link->query($sqlInsert) === TRUE) {
                        echo "Visit added successfully";
                  } else {
                        echo "An error occurred while updating this visit: " . $sqlInsert . "<br>" . $link->error;
                  }
            } else {
                forbidden(); 
            }
        }

        mysqli_close($link);
?>
