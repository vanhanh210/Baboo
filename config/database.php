<?php
$host = 'localhost';
$username = 'mkgvqkcjhosting_digitalxyz';
$password = 'Keelin@28';
$dbname = 'mkgvqkcjhosting_digitalxyz';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Connection is now open for use in other scripts
// Do not close it here
?>