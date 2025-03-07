<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract form data
    $name = $_POST['name'];
    $address = $_POST['address'];
    $hometown = $_POST['hometown'];
    $birthdate = $_POST['birthdate'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password']; // Consider hashing this password
    $role = $_POST['role'];

    // Prepare SQL statement to insert data
    $sql = "INSERT INTO users (name, address, hometown, birthdate, phone, email, username, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $name, $address, $hometown, $birthdate, $phone, $email, $username, $password, $role);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "New user created successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();

    // Ensure no output before redirection
    ob_start();
    header("Location: ../templates/manage_users.php");
    ob_end_flush();
    exit();
}
?>