<?php
    require 'php/utils.php';
    $link = mysqli_connect($server, $user_name, $pass_word, $data_base);
	
    $email = isset($_POST['email']) ? getSecureInputData($_POST['email']) : null;
    $password = isset($_POST['password']) ? getSecureInputData($_POST['password']) : null;

    $errorMessage = "";

    if ($link) {
        $sql = " SELECT * FROM users  WHERE email = '$email' ";
        $result = mysqli_query($link, $sql);
        $rowcount = mysqli_num_rows($result);

        if ($rowcount == 0) {
            $errorMessage = "Invalid login attempt. Please try again.";
        } else {
            $user = mysqli_fetch_assoc($result);

            if ( password_verify($password, $user['Password']) ) {
                  if (isset($_POST['btnLogin'])) {
                      session_start();
                      $_SESSION['email'] = $user['Email'];
					  $_SESSION['phone'] = $user['Phone'];
                      $_SESSION['firstName'] = $user['FirstName'];
                      $_SESSION['lastName'] = $user['LastName'];
                      $_SESSION['isAdmin'] = $user['IsAdmin'];
                      $_SESSION['id'] = $user['Id'];
                      $_SESSION['isLoggedIn'] = true;
                      header("location:mobile.php"); 
                  } else {
                      $errorMessage = "Invalid login attempt. Please try again.";
                  }
            } else {
                $errorMessage = "Invalid login attempt. Please try again.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Visitation Scheduler Login</title>

   <script src="libs/jquery-3.1.1.min.js"></script>
   <script src="libs/"></script>
   <script src="js/utils.js"></script>
   <link rel="stylesheet" href="css/app.css">
   <link rel="stylesheet" href="libs/bootstrap-4.0.0/css/bootstrap.css">
   <script src="libs/bootstrap-4.0.0/js/bootstrap.js"></script>
   <link rel="stylesheet" href="libs/font-awesome-4.7.0/css/font-awesome.css">


</head>
<body>
    <div id="login" class="panel panel-primary container" style="border-radius:4px;border:1px solid lightgrey;padding:10px!important;margin-top: 5px; padding:0px;">
	<br/>
      <div class="panel-body container">
	      <h4 style="text-align:center" class="text-primary">Church Visitation Scheduler - Login</h4>
          <form action="login.php" method="post">
			  <div class="row">
				<div class="col-md-12 col-lg-12 col-sm-12">
				  <label>Email:</label>
				</div>
				<div class="col-md-12 col-lg-12 col-sm-12">
				  <input class="form-control" name="email">
				</div>
			  </div>
			  <div class="row">
				<div class="col-md-12 col-lg-12 col-sm-12">
				  <label>Password:</label>
				</div>
				<div class="col-md-12 col-lg-12 col-sm-12">
				  <input class="form-control" type="password" name="password">
				</div>
			  </div>
			  <div class="row">               
				<div class="col-md-12 col-lg-12 col-sm-12 float-right">
					<p></p>
					<input class="float-right btn btn-primary btn-sm" type="submit" value="Login" name="btnLogin">
				</div>
				<div class="col-md-12 col-lg-12 col-sm-12">
                   <font color="red" name="errorMsgs">
                       <?php
                        if (isset($_POST['btnLogin'])) { echo $errorMessage; }
                       ?>
                   </font>
				</div>
			  </div>

      </form>      
      </div>
      <div class="panel-footer" style="<color:red></color:red>">Contact your administrator for login assistance.</div>
    </div>
        <div class="pusher"><br/><br/><br/><br/></div>
    <footer class="footer">
          <div class="container">
            <span class="text-muted">Powered with <i class="fa fa-heart" aria-hidden="true"></i> by <a href="#">clac.com</a> | <a href="#">codelikeachicken.com</a></span>
          </div>
    </footer>
</body>
</html>
