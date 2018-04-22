<?php
        $user_name = "???";
        $pass_word = "???";
        $data_base = "???";
        $server = "???";

        function isValidPassword($password) {
            /* giving credit... http://www.thegeekstuff.com/2008/06/the-ultimate-guide-for-creating-strong-passwords */
            $ucAlphaCount = 0; $lcAlphaCount = 0; $numberCount = 0; $specialCount = 0;
            $parsedPwd = str_split($password); $specialChars = array("!", "@", "#", "$", "%", "&", "_");

            //0. 8 characters in length
            if (strlen($password) < 8) { return false; }
            //1. at least one lower case alphabet
            //2. at least one upper case alphabet
            //3. at least one number
            //4. at least one special character
            foreach ($parsedPwd as &$character) {
                if ($character == strtoupper($character)) { $ucAlphaCount++; }
                if ($character == strtolower($character)) { $lcAlphaCount++; }
                if (is_numeric($character) == true) { $numberCount++; }
                if (array_search($character, $specialChars) == $character) { $specialCount++; }
            }
            return ($ucAlphaCount > 0 && $lcAlphaCount > 0 && $numberCount > 0 && $specialCount > 0);
        }
		
		function isUniqueEmailAddress($email, $link) {
              if ($email && $link) {
                  $sql = " SELECT * FROM users WHERE email='$email' ";
                  $result = mysqli_query($link, $sql);
                  $rowcount = mysqli_num_rows($result);
                  return $rowcount == 0;
              }
			  return false;
		}

        function getBooleanValueAsInteger($data) {
            if ($data == "true") { return 1; }
            if ($data == "false") { return 0; }
        }

        function getSecureInputData($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
			$data = addslashes($data);
            return $data;
        }

        function sendAccountCreationEmail($email, $first_name, $last_name, $hash, $password) {
          //https://css-tricks.com/sending-nice-html-email-with-php/
          $to = $first_name. " " .$email;
          $new_line = "\n";

          $headers = "From: <admin@???.site>" . "\r\n" . "BCC: <admin@???.site>, <???@gmail.com>, <???@gmail.com>";
          $login_user_name = "User name: " .$email .$new_line;
          $login_pass_word = "Temporary password: " .$password .$new_line .$new_line;
          $subject = 'Member Care Ministries | Scheduler Account Created';

          $message_body_greeting = "";
          $message_body = "";

          if ($first_name) {
              $message_body_greeting = "Hello, " .$first_name .":" .$new_line. "A Church Visitation Scheduler account has been set up for you. " .$new_line .$new_line;
          } else {
              $message_body_greeting = "Hello, " .$email .$new_line. "A Church Visitation Scheduler account has been set up for you. " .$new_line .$new_line;
          }

          $message_body_info = "Please click the link below, or copy and paste it into your browser, to activate your account:" .$new_line;
          $message_body_link = "https://www.???.site/ChurchScheduler/php/verify.php?email=" .$email. "&hash=" .$hash .$new_line .$new_line;
          $message_body_no_reply = "Please do not reply to this e-mail. For all inquiries, please contact your administrator." .$new_line .$new_line;
          $message_body_footer = "-----------------------------------------------------------------" .$new_line ."Have a blessed day." .$new_line. "Church Visitation Scheduler Coordinators";

          $message_body = $message_body_greeting .$login_user_name .$login_pass_word .$message_body_info .$message_body_link .$message_body_no_reply .$message_body_footer;

          mail( $to, $subject, $message_body, $headers);

          return "User added successfully";
        }

        function sendPasswordResetEmail($email, $first_name, $last_name, $hash, $password) {
          //https://css-tricks.com/sending-nice-html-email-with-php/
          $to = $first_name. " " .$email;
          $new_line = "\n";

          $headers = "From: <admin@???.site>" . "\r\n" . "BCC: <admin@???.site>, <???@gmail.com>, <???@gmail.com>";
          $login_user_name = "User name: " .$email .$new_line;
          $login_pass_word = "Temporary password: " .$password .$new_line .$new_line;
          $subject = 'Church Visitation | Scheduler Account Password Reset';

          $message_body_greeting = "";
          $message_body = "";

          if ($first_name) {
              $message_body_greeting = "Hello, " .$first_name .":" .$new_line. "The password for your Church Visitation Scheduler account has been reset for you. " .$new_line .$new_line;
          } else {
              $message_body_greeting = "Hello, " .$email .$new_line. "The password for your Church Visitation Scheduler account has been reset for you. " .$new_line .$new_line;
          }

          $message_body_info = "Please click the link below, or copy and paste it into your browser, to re-activate your account:" .$new_line;
          $message_body_link = "https://www.???.site/ChurchScheduler/php/verify.php?email=" .$email. "&hash=" .$hash .$new_line .$new_line;
          $message_body_no_reply = "Please do not reply to this e-mail. For all inquiries, please contact your administrator." .$new_line .$new_line;
          $message_body_footer = "-----------------------------------------------------------------" .$new_line ."Have a blessed day." .$new_line. "Church Visitation Scheduler Coordinators";

          $message_body = $message_body_greeting .$login_user_name .$login_pass_word .$message_body_info .$message_body_link .$message_body_no_reply .$message_body_footer;

          mail( $to, $subject, $message_body, $headers);

          return "User password reset successfully";
        }

        function updateUserPassword($user, $new_password, $link, $hash) {
            if ($user && $new_password && $link && $hash) {
                    $email = $user["Email"];
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $sqlUpdate = " UPDATE users SET active = '1', password = '$hashed_password' WHERE email = '$email' AND hash = '$hash' ";
                      
                      if ($link->query($sqlUpdate) === TRUE) {
                          return true;
                      } else {
                          return false;
                      }                      
            } else {
                return false;
            }
        }

        function isLoggedIn($user_email, $user_id, $link) {
          /*
              purpose: security/sanity check...if someone gains access to the root server directory, do not dump data if pages are selected
              use: must be called by each php page before any query is executed
          */
              if ($user_email && $user_id && $link) {
                  $sql = " SELECT * FROM users WHERE email='$user_email' AND id='$user_id' AND active = '1' ";
                  $result = mysqli_query($link, $sql);
                  $rowcount = mysqli_num_rows($result);
                  return $rowcount == 1;
              }
              return false;
        }
        
        function forbidden() {
            $url = "https://www.???.site/ChurchScheduler/error.php";
            echo '<META HTTP-EQUIV="refresh" content="0;  URL=' . $url . '">';
            echo "<script type='text/javascript'>document.location.href='{$url}';</script>";
        }
?>
