<?php
session_start();
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['building_id']) && isset($_POST['action'])) {
    $building_id = $_POST['building_id'];
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $sql = "UPDATE buildings SET approved = 1 WHERE building_id = ?";
    } elseif ($action === 'stop') {
        $sql = "UPDATE buildings SET approved = 0 WHERE building_id = ?";
    } else {
        echo "Invalid action.";
        exit();
    }

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    
    $stmt->bind_param("i", $building_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $message = $action === 'approve' ? "Toà nhà '$name' đã được duyệt." : "Toà nhà '$name' đã bị tạm ngưng, liên hệ admin để được giải quyết.";
        $notification_sql = "INSERT INTO notifications (user_id, building_id, message, type, created_at) VALUES (?, ?, ?, 'status', NOW())";
        $notification_stmt = $conn->prepare($notification_sql);
        
        if ($notification_stmt) {
            $notification_stmt->bind_param("iis", $user_id, $building_id, $message);
            $notification_stmt->execute();
            
            if ($notification_stmt->affected_rows <= 0) {
                echo "Failed to insert notification for admin ID $admin_id: " . $notification_stmt->error;
            }
    
            $notification_stmt->close();
        } else {
            echo "MySQL prepare error for notification: " . $conn->error;
        }
    
        header("Location: ../templates/view_building.php?building_id=" . $building_id);
        exit();
    } else {
        echo "Error updating building status or no changes made: " . htmlspecialchars($stmt->error);
    }
    
    $stmt->close();

} else {
    echo "Invalid request.";
}

$conn->close();
?>