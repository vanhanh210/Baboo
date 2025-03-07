<?php
require '../config/database.php';

function get_notifications($user_id) {
    global $conn;
    $sql = "SELECT * FROM notifications WHERE user_id = ? OR type = 'admin' ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result;
}
function get_admin_notifications() {
    global $conn;
    $sql = "SELECT * FROM notifications WHERE type = 'admin' ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result;
}

function getInfoNotification($id) {
    global $conn;
    $sql = "SELECT * FROM notifications WHERE id = ?"; 
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $noti_info = $result->fetch_assoc();
    $stmt->close();
    return $noti_info;
}
?>