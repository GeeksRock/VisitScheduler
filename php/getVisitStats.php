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
                $sql = "SELECT t.Type AS 'type',
                            (SELECT COUNT(*) FROM visits WHERE type_id = t.Id AND completed = 0 AND client_id = '$client_id') AS 'pending',
                            (SELECT COUNT(*) FROM visits WHERE type_id = t.Id AND completed = 1 AND client_id = '$client_id') AS 'completed',
                            (SELECT COUNT(*) FROM visits WHERE type_id = t.Id AND completed = 0 AND client_id = '$client_id' AND STR_TO_DATE(Date, '%m/%d/%Y') < CURDATE()) AS 'overdue'
						FROM visit_type t";

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
