<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['room_id'])) {
    $room_id = $_POST['room_id'];
    $building_id = $_POST['building_id'];
    
    $sql = "DELETE si FROM sale_income si JOIN bookings b ON si.booking_id = b.booking_id WHERE b.room_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo "Related sale_income deleted successfully.<br>";
    } else {
        echo "No related sale_income found.<br>";
    }
    $stmt->close();

    $sql = "DELETE FROM bookings WHERE room_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo "Related bookings deleted successfully.<br>";
    } else {
        echo "No related bookings found.<br>";
    }
    $stmt->close();

    $sql = "DELETE FROM rooms WHERE room_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $room_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: ../templates/edit_rooms.php?building_id=" . $building_id);
        exit();
    } else {
        echo "Error deleting room: " . htmlspecialchars($stmt->error);
    }
    $stmt->close();

    $conn->close();
} else {
    echo "Room ID not set or invalid request method.";
}
?>