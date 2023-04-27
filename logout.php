<?php
	require_once "myLib.php";

	if (isset($_COOKIE["token"]) and preg_match("/^[\da-f]{32}$/", $_COOKIE["token"]))
	{
		mysqli_query($link, "DELETE FROM `Queue`.`sessions` WHERE token = '".$_COOKIE["token"]."'");
	}
	setcookie("token","",time()-3600);
	header("Location: authPage.php");
?>