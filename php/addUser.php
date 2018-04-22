<?php
        require 'utils.php';
        $link = mysqli_connect($server, $user_name, $pass_word, $data_base);
		
        $logged_in_user_id = isset($_POST['logged_in_user_id']) ? getSecureInputData($_POST['logged_in_user_id']) : null;
        $logged_in_user_email = isset($_POST['logged_in_user_email']) ? getSecureInputData($_POST['logged_in_user_email']) : null;
        
        $first_name = isset($_POST['first_name']) ? getSecureInputData($_POST['first_name']) : null;
        $last_name = isset($_POST['last_name']) ? getSecureInputData($_POST['last_name']) : null;
        $email = isset($_POST['email']) ? getSecureInputData($_POST['email']) : null;
        $phone = isset($_POST['phone']) ? getSecureInputData($_POST['phone']) : null;
        $is_admin = isset($_POST['is_admin']) ? getBooleanValueAsInteger(getSecureInputData($_POST['is_admin'])) : 0;
        $password = isset($_POST['password']) ? getSecureInputData(password_hash($_POST['password'], PASSWORD_BCRYPT)) : null;
        $hash = getSecureInputData( md5( rand(0,1000) ) );              

        if ($link) {
           $isLoggedIn = isLaoggedIn($logged_in_user_email, $logged_in_user_id, $link);
		   $emailAddressAvailable = isUniqueEmailAddress($email, $link);
		   //echo "emailAddressAvailable: " . $emailAddressAvailable;
           if ($isLoggedIn && $emailAddressAvailable == 1) {
               $sqlInsert = "INSERT INTO users
                                        (firstname, lastname, email, phone, isadmin, password, hash)
                                    VALUES
                                        ('$first_name', '$last_name', '$email', '$phone', '$is_admin', '$password', '$hash')";

                if ($link->query($sqlInsert) === TRUE) {
                      //echo "User added successfully";
                      echo sendAccountCreationEmail($email, $first_name, $last_name, $hash, getSecureInputData($_POST['password']));
                } else {
                      echo "An error occurred while creating this user: " . $sqlInsert . "<br>" . $link->error;
                }
           } else {
			   if ($emailAddressAvailable === false) {
				   echo "This email address is already in use." . $link->error;
			   } else {
					forbidden();
			   }
           }
           mysqli_close($link);
        }
?>
