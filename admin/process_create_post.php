<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $selected_users = $_POST['selected_users'];
    
    if (!empty($selected_users) && $selected_users !== "[]") {
        $selected_users_array = json_decode($selected_users, true); 

        if (is_array($selected_users_array) && count($selected_users_array) > 0) {
            $sql = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'private')";
            $stmt = $conn->prepare($sql);

            foreach ($selected_users_array as $receiver_id) {
                $stmt->bind_param("iss", $receiver_id, $title, $content);
                $stmt->execute();
            }
        }
    } else {
        $sql = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'admin')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $title, $content);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();

    header("Location: ../templates/home.php");
    exit();
}
?>
