<?php
    require 'utils.php';
    session_start();

    if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash']))
    {
		$link = mysqli_connect($server, $user_name, $pass_word, $data_base);
        $email = getSecureInputData($_GET['email']);
        $hash = getSecureInputData($_GET['hash']);
		
        $errorMessage = "";
        
        if ($link) {
            $sql = " SELECT * FROM users WHERE email='$email' AND hash='$hash' AND active='0' ";
            $result = mysqli_query($link, $sql);
            $rowcount = mysqli_num_rows($result);
            
            if ($rowcount == 0) {
                $errorMessage = "This account has already been activated or the URL is invalid.";
            } else {            
                  if (isset($_POST['btnLogin'])) {
                      $temp_password = isset($_POST['temp_password']) ? getSecureInputData($_POST['temp_password']) : null;
                      $new_password = isset($_POST['new_password']) ? getSecureInputData($_POST['new_password']) : null;
                      $confirm_new_password = isset($_POST['confirm_new_password']) ? getSecureInputData($_POST['confirm_new_password']) : null;
                      
                      if ($new_password == $confirm_new_password) {
                          if ($temp_password != $new_password) {
                                $user = mysqli_fetch_assoc($result);
                                if ( password_verify($temp_password, $user['Password']) ) {
                                    $isSaved = updateUserPassword($user, $new_password, $link, $hash);
                                    if ($isSaved == true) {
                                        if (!isset($_SESSION['isLoggedIn'])) {
                                            $url = "https://www.???.site/ChurchScheduler/login.php";
                                            echo '<META HTTP-EQUIV="refresh" content="0;  URL=' . $url . '">';
                                            echo "<script type='text/javascript'>document.location.href='{$url}';</script>";
                                        }
                                    } else {
                                        $errorMessage = "We're sorry. An error occurred while updating your password.";
                                    }
                                } else {
                                    $errorMessage = "We're sorry. We are unable to verify this account.";
                                }
                          } else {
                                    $errorMessage = "The [temporary password] and [new password] fields can not match.";
                          }
                      } else {
                           $errorMessage = "The [new password] and [confirm new password] fields do not match.";
                      }
                  }
            }
            mysqli_close($link);
       }       
    }     
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Church Visitation Scheduler Login</title>

   <script src="../libs/jquery-3.1.1.min.js"></script>
   <script src="../js/utils.js"></script>
   <script src="../libs/underscore.js"></script>
   <script src="../libs/vue2_dev.js"></script>
   <link rel="stylesheet" href="../libs/bootstrap-4.0.0/css/bootstrap.css">
   <script src="../libs/bootstrap-4.0.0/js/bootstrap.js"></script>
   <link rel="stylesheet" href="../libs/font-awesome-4.7.0/css/font-awesome.css">
   <link rel="stylesheet" href="../css/app.css">
</head>
<body>
    <h1 style="text-align:center" class="text-primary">Church Visitation Scheduler</h1>

    <div id="verify" class="panel panel-primary container" style="border-radius:4px;border:1px solid lightgrey;padding:10px!important;width:40% !important; margin-top: 25px; padding:0px;">
      <div class="panel-heading">
        <h4 class="panel-title">Please complete the following form to continue</h4>
      </div>
      <div class="panel-body container"  style="width:auto!important">
          <form id="verify_form" action="" method="post" autocomplete="off">
         <table style="width:auto!important">
           <tr>
               <td><label><font color="red" weight="bold">*</font>Temporary password:</label></td>
               <td>&nbsp;</td>
               <td><input class="form-control" type="password" name="temp_password" v-model="temp_password"></td>
               <td rowspan="6" valign="top">
                   <div style="padding-left:15px!important">
                        <h6>Passwords must have:</h6>
                        <ul>
                            <li>at least 8 characters</li>
                            <li>at least 1 upper case character</li>
                            <li>at least 1 lower case character</li>
                            <li>at least 1 number</li>
                            <li>at least 1 special character&nbsp;<i style="cursor:pointer!important;color:#0275d8!important" v-bind:title="special_characters" class="fa fa-info-circle" aria-hidden="true"></i></li>
                        </ul>
                   </div>
               </td>
           </tr>
           <tr>
               <td><label><font color="red" weight="bold">*</font>New password:</label></td>
               <td>&nbsp;</td>
               <td><input class="form-control" type="password" @keyup="nospaces" name="new_password" v-model="new_password"></td>
           </tr>
           <tr>
               <td><label><font color="red" weight="bold">*</font>Confirm new password:</label></td>
               <td>&nbsp;</td>
               <td><input class="form-control" type="password" @keyup="nospaces" name="confirm_new_password" v-model="confirm_new_password"></td>
           </tr>
           <tr>
               <td></td>
               <td></td>
               <td style="text-align:right"><input v-bind:disabled="!canLogin" v-bind:class="!canLogin" class="btn btn-primary btn-sm" type="submit" value="Save" name="btnLogin"></td>
           </tr>
           <tr><td colspan="3">&nbsp;</td></tr>
           <tr>
               <td colspan="3">
                   <font color="red" name="errorMsgs">
                       <?php
                        if (isset($_POST['btnLogin'])) { echo $errorMessage; }
                       ?>
                   </font>
               </td>
           </tr>
       </table>
      </form>
      </div>
      <div class="panel-footer" style="<color:red></color:red>">&nbsp;&nbsp;&nbsp;&nbsp;Contact your administrator for login assistance.</div>
    </div>
    <div class="pusher"></div>
    <footer class="footer">
          <div class="container">
            <span class="text-muted">Powered with <i class="fa fa-heart" aria-hidden="true"></i> by <a href="#">???.com</a> | <a href="#">???.com</a></span>
          </div>
    </footer>
</body>
</html>
<script src="../js/verify.js"></script>
<script>
    (function () {
        $("#verify_form").attr({action: document.location.href});
    })();
</script>
