<?php require_once("functions/session.php"); ?>
<?php require_once("functions/functions.php"); ?>

<?php
	$_SESSION["userid"] = null;
	redirect_to("login.php");             // May need to tweek this depending on wher ewe are redirecting from
?>