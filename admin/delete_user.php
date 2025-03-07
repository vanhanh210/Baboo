<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    
    // Prepare a delete statement
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        ob_start();
        header("Location: ../templates/manage_users.php");
        ob_end_flush();
        exit();
    } else {
        echo "Error deleting user: " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
    $conn->close();
} else {
    echo "User ID not set or invalid request method.";
}
?>