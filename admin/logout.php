<?php
session_start();

// Unset all session values
$_SESSION = array();

// Destroy session variables
session_destroy();

// Redirect to login page or home page
header("Location: login.php");
exit;
?>
