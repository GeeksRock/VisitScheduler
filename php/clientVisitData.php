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
                $sql = "SELECT * FROM visits WHERE client_id = $client_id";
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
