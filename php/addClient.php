<?php
        require 'utils.php';        
        $link = mysqli_connect($server, $user_name, $pass_word, $data_base);
		
        $logged_in_user_id = isset($_POST['logged_in_user_id']) ? getSecureInputData($_POST['logged_in_user_id']) : null;
        $logged_in_user_email = isset($_POST['logged_in_user_email']) ? getSecureInputData($_POST['logged_in_user_email']) : null;
        
        $first_name = isset($_POST['first_name']) ? getSecureInputData($_POST['first_name']) : null; 
        $last_name = isset($_POST['last_name']) ? getSecureInputData($_POST['last_name']) : null;
        $email = isset($_POST['email']) ? getSecureInputData($_POST['email']) : null;
        $phone = isset($_POST['phone']) ? getSecureInputData($_POST['phone']) : null;
        $address = isset($_POST['address']) ? getSecureInputData($_POST['address']) : null;
        $city = isset($_POST['city']) ? getSecureInputData($_POST['city']) : null;
        $state = isset($_POST['state']) ? getSecureInputData($_POST['state']) : null;
        $zip_code = isset($_POST['zip_code']) ? getSecureInputData($_POST['zip_code']) : null;
        $id = isset($_POST['id']) ? getSecureInputData($_POST['id']) : null;

        if ($link) {
            $isLoggedIn = isLoggedIn($logged_in_user_email, $logged_in_user_id, $link);
            if ($isLoggedIn) {
                $sqlInsert = "INSERT INTO clients
                                 (firstname, lastname, email, phone, address, city, state, zipcode)
                             VALUES
                                 ('$first_name', '$last_name', '$email', '$phone', '$address', '$city', '$state', '$zip_code')";
     
                 if ($link->query($sqlInsert) === TRUE) {
                       echo "Client added successfully";
                 } else {
                       echo "An error occurred while adding this client: " . $sqlInsert . "<br>" . $link->error;
                 }
            } else {
                forbidden();
            }
            mysqli_close($link);
        }
?>
