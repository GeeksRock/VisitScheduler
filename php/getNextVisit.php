<?php
        require 'utils.php';
        $link = mysqli_connect($server, $user_name, $pass_word, $data_base);
		
        $logged_in_user_id = isset($_POST['logged_in_user_id']) ? getSecureInputData($_POST['logged_in_user_id']) : null;
        $logged_in_user_email = isset($_POST['logged_in_user_email']) ? getSecureInputData($_POST['logged_in_user_email']) : null;
		$client_id = isset($_POST['client_id']) ? getSecureInputData($_POST['client_id']) : null;

        $emparray = array();

        if ($link) {
          $isLoggedIn = isLoggedIn($logged_in_user_email, $logged_in_user_id, $link);
          if ($isLoggedIn) {
                $sql = "SELECT v.`Id`, v.`Date`, 
							CONCAT(v.TimeHour, ':', v.TimeMinutes, ' ', v.TimeOfDay) AS Time, 
							CONCAT(u.firstname, ' ', u.lastname) AS Visitor,
							t.type AS Type
						FROM visits v, clients c, visit_type t, users u
						WHERE v.`Client_Id` = c.Id
							AND c.Id = '$client_id'
							AND u.Id = v.User_Id
							AND v.Type_Id = t.Id
							AND v.Completed = 0
						ORDER BY STR_TO_DATE(CONCAT(v.Date, ' ', v.TimeHour, ':', v.TimeMinutes, ' ', v.TimeOfDay), '%m/%d/%Y %h:%i %p')
						LIMIT 1;";

                $result = mysqli_query($link, $sql);

                if ($result) {
                    while ($row = mysqli_fetch_object($result)) {
                        $emparray[] = $row;
                    }
                }
                echo json_encode($emparray);
          } else {
             forbidden();
          }
            mysqli_close($link);
        }
?>
