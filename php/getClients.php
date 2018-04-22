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
                    $sql = "SELECT * 
							FROM clients
							ORDER BY firstname ASC";       
                } else {
                   $sql = "SELECT * FROM clients 
                               WHERE firstname LIKE '%$search_param%' 
                               OR lastname LIKE '%$search_param%'
							   ORDER BY firstname ASC";
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
