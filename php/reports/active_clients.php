<?php
        require '../utils.php'; 
		$link = mysqli_connect($server, $user_name, $pass_word, $data_base);
	
        $logged_in_user_id = isset($_POST['logged_in_user_id']) ? getSecureInputData($_POST['logged_in_user_id']) : null;
        $logged_in_user_email = isset($_POST['logged_in_user_email']) ? getSecureInputData($_POST['logged_in_user_email']) : null;

        $emparray = array();

        if ($link) {
          $isLoggedIn = isLoggedIn($logged_in_user_email, $logged_in_user_id, $link);
          if ($isLoggedIn) {
                $sql = "SELECT
                            lastname AS 'Last Name',
                            firstname AS 'First Name',
                            phone AS Phone,
                            email AS Email,
                            address AS Address,
                            city AS City,
                            state AS State,
                            zipcode AS 'Zip Code'
                        FROM clients
                        WHERE inactive = 0
                        ORDER BY lastname, firstname, inactive";

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
