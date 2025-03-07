<?php
session_start();
header("Location: " . (isset($_SESSION['user_id']) ? "./templates/home.php" : "./admin/login.php"));
exit;
?>