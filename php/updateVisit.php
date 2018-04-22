<?php
        require 'utils.php';
		$link = mysqli_connect($server, $user_name, $pass_word, $data_base);

        $logged_in_user_id = isset($_POST['logged_in_user_id']) ? getSecureInputData($_POST['logged_in_user_id']) : null;
        $logged_in_user_email = isset($_POST['logged_in_user_email']) ? getSecureInputData($_POST['logged_in_user_email']) : null;
        
        $date = isset($_POST['date']) ? getSecureInputData($_POST['date']) : null;
        $time_hour = isset($_POST['time_hour']) ? getSecureInputData($_POST['time_hour']) : null;
        $time_minutes = isset($_POST['time_minutes']) ? getSecureInputData($_POST['time_minutes']) : null;
        $time_of_day = isset($_POST['time_of_day']) ? getSecureInputData($_POST['time_of_day']) : null;
        $completed = isset($_POST['completed']) ? getBooleanValueAsInteger(getSecureInputData($_POST['completed'])) : 0;
        $follow_up_required = isset($_POST['follow_up_required']) ? getBooleanValueAsInteger(getSecureInputData($_POST['follow_up_required'])) : 0;
        $client_id = isset($_POST['client_id']) ? getSecureInputData($_POST['client_id']) : null;
        $user_id = isset($_POST['user_id']) ? getSecureInputData($_POST['user_id']) : 0;
        $type_id = isset($_POST['type_id']) ? getSecureInputData($_POST['type_id']) : null;
        $id = isset($_POST['visit_id']) ? getSecureInputData($_POST['visit_id'] ): null;

        if ($link) {
          $isLoggedIn = isLoggedIn($logged_in_user_email, $logged_in_user_id, $link);
          if ($isLoggedIn) {
              $sqlUpdate = "UPDATE visits
                  SET date = '$date',
                          timehour = '$time_hour',
                          timeminutes = '$time_minutes',
                          timeofday = '$time_of_day',
                          completed = '$completed',
                          followuprequired = '$follow_up_required',
                          client_id = '$client_id',
                          user_id = '$user_id',
                          type_id = '$type_id'
                      WHERE id = '$id'";

                if ($link->query($sqlUpdate) === TRUE) {
                    echo "Visit updated successfully";
                } else {
                    echo "An error occurred while updating this visit: " . $sqlUpdate . "<br>" . $link->error;
                }
            } else {
                forbidden();
            }
            mysqli_close($link);
        }
?>
