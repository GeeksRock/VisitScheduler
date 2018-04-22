<?php
        require 'utils.php';
        $link = mysqli_connect($server, $user_name, $pass_word, $data_base);
		
        $logged_in_user_id = isset($_POST['logged_in_user_id']) ? getSecureInputData($_POST['logged_in_user_id']) : null;
        $logged_in_user_email = isset($_POST['logged_in_user_email']) ? getSecureInputData($_POST['logged_in_user_email']) : null;

        $emparray = array();

        if ($link) {
          $isLoggedIn = isLoggedIn($logged_in_user_email, $logged_in_user_id, $link);
          if ($isLoggedIn) {
                $sql = "SELECT
							v.Id,
                            v.Date,
							v.Completed,
							v.FollowUpRequired,
                            CONCAT(v.TimeHour, ':', v.TimeMinutes, ' ', v.TimeOfDay) AS Time,
                        	CONCAT(c.lastname, ', ', c.firstname) AS Client,
							c.Id AS Client_Id,
                        	CONCAT(u.lastname, ', ', u.firstname) AS Visitor,
							u.Id AS Visitor_Id,
                        	t.type AS Type,
							t.id AS Type_Id,
                        	((STR_TO_DATE(v.Date, '%m/%d/%Y %h:%i %p') < CURDATE()) = 1) as Overdue
                        FROM visits v, clients c, users u, visit_type t
                        WHERE c.Id = v.Client_Id
                        AND u.Id = v.User_Id
                        AND v.Type_Id = t.Id
						AND v.User_Id = '$logged_in_user_id'
                        AND v.Completed = 0
                        AND (WEEK(STR_TO_DATE(v.Date, '%m/%d/%Y'), 0) = WEEK(NOW(), 0))
                        ORDER BY STR_TO_DATE(CONCAT(v.Date, ' ', v.TimeHour, ':', v.TimeMinutes, ' ', v.TimeOfDay), '%m/%d/%Y %h:%i %p')";
						/*
							AND (YEARWEEK(STR_TO_DATE(v.Date, '%m/%d/%Y'), 3) = YEARWEEK(DATE(), 3))
							https://stackoverflow.com/questions/14191216/mysql-get-current-week-spanning-last-year-and-this-year
						*/
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
