<?php
require_once("../functions/session.php");
require_once("../config/config.default.php");
require_once("MainSalesAssociateAndQuote.php");
require_once("../functions/functions.php");
require_once("../functions/password_functions.php");
require_once("SalesAssociateStore.php");

$loadAssociateData = new SalesAssociateStore();
$username = "";
global $errors;

if (isset($_POST["submit"])) {
    if (isset($_POST["userid"]) && $_POST["userid"] !== "") {
    } else {
        $errors["userid"] = "userid" . " can't be blank.";
    }

    if (isset($_POST["password"]) && $_POST["password"] !== "") {
    } else {
        $errors["password"] = "password" . " can't be blank.";
    }
    
    if (empty($errors)) {
        $username = $_POST["userid"];
        $password = $_POST["password"];
        
        $associate = $loadAssociateData->getAssociateByUsername($username);                  
    
        $existing_hash = $associate->password;

        $valid_psw = password_check($password, $existing_hash);
        
        if ($valid_psw) {
            $_SESSION["userid"] = $username;
            redirect_to("../views/CreateQuotesGUI.php");
            exit();
        } else {
            $_SESSION["message"] = "Userid/password not found.";
        }
    } else {
    }
}
?>

<html lang="en">
	<head>
		<title>Quote System </title>
	</head>
	<body>
    <div id="header">
      <h1>Quote System</h1>
    </div>
    <div id="page">
        <?php echo message(); ?>
        <?php echo form_errors($errors); ?>
        
        <h2> Login</h2>
        <form action="login.php" method="post">
            <p>Userid:
                <input type="text" name="userid" value="<?php echo $username; ?>" />
            </p>
            <p>Password:
                <input type="password" name="password" value="" />
            </p>
            <input type="submit" name="submit" value="submit" />
         </form>
         </div>

    </body>
</html>