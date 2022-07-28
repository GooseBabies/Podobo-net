<?php
	session_start();

    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
        header("location: index.php");
        exit;
    }

    $username = $password = "";
    $username_err = $password_err = $login_err = "";

	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	
    $db->busyTimeout(100);

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        
        // Check if password is empty
        if(empty(trim($_POST["password"]))){
            $password_err = "Please enter your password.";
        } else{
            $password = trim($_POST["password"]);
        }
        
        // Validate credentials
        if(empty($username_err) && empty($password_err)){
            // Prepare a select statement
            $sql = $db->prepare("select password from settings where id = :id");
            $sql->bindValue(':id', 1, SQLITE3_TEXT);
            $pass_hash = $sql->execute()->fetchArray()[0] ?? -1;
            
            if(password_verify($password, $pass_hash)){
                // Password is correct, so start a new session
                session_start();
                
                // Store data in session variables
                $_SESSION["loggedin"] = true;
                
                setcookie("pass", $pass_hash, time() + (30 * 24 * 60 * 60));
                
                // Redirect user to welcome page
                header("location: index.php");
            } else{
                // Password is not valid, display a generic error message
                $login_err = "Invalid username or password.";
            }
        }
        // Close connection
        $db = null;
    }

    ?>
	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
	    <title>Podobo Login</title>
	    <link rel="stylesheet" type="text/css" href="../style/PodoboStyle.css" />		
		<link rel="stylesheet" href="../style/w3.css" />
		<link rel="icon" type="image/x-icon" href="../imgs/favicon.ico">
		<base target="_parent" />
        <style>
            body{ font: 14px sans-serif; }
            h2{ color: white; }
        </style>
	<body>
        <div class="w3-center">
            <h2>Login</h2>

            <?php 
            if(!empty($login_err)){
                echo '<div class="alert alert-danger">' . $login_err . '</div>';
            } 
            //echo password_hash("#3MH7BtoJ3s&Rj8$", PASSWORD_DEFAULT);      
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">  
                <div class="w3-center">
                    <label>Password</label>
                    <input type="password" name="password">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="w3-center">
                    <input type="submit" value="Login">
                </div>
            </form>
        </div>
    </body>
</html>