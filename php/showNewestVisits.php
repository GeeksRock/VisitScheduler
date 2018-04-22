<?php
        require 'utils.php';
        $link = mysqli_connect($server, $user_name, $pass_word, $data_base);
		
        $logged_in_user_id = isset($_POST['logged_in_user_id']) ? getSecureInputData($_POST['logged_in_user_id']) : null;
        $logged_in_user_email = isset($_POST['logged_in_user_email']) ? getSecureInputData($_POST['logged_in_user_email']) : null;
        
        $emparray = array();
        $sql = "";
        $search_param = isset($_POST['search_param']) ? getSecureInputData($_POST['search_param']) : null;
        
        if ($link) {
          $isLoggedIn = isLoggedIn($logged_in_user_email, $logged_in_user_id, $link);
          if ($isLoggedIn) {
                if ($search_param == null) {
                    $sql = "
						SELECT
							v.Id,
                            v.Date,
							v.Completed,
                            CONCAT(v.TimeHour, ':', v.TimeMinutes, ' ', v.TimeOfDay) AS Time,
                            CONCAT(c.lastname, ', ', c.firstname) AS Client,
							c.Id AS Client_Id,
                            CONCAT(u.lastname, ', ', u.firstname) AS Visitor,
							u.Id AS Visitor_Id,
                            t.type AS Type,
							t.Id AS Type_Id,
                            STR_TO_DATE(v.Date, '%m/%d/%Y') < CURDATE() AS Overdue
                        FROM visits v, clients c, users u, visit_type t
                        WHERE c.Id = v.Client_Id
							AND u.Id = v.User_Id
							AND v.Type_Id = t.Id
                        ORDER BY v.Id DESC";
                }
                          
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
