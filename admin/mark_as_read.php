<?php
session_start();
require '../config/database.php';
include 'get_notifications.php';
include 'getallbuilding.php';

header('Content-Type: application/json'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && isset($_SESSION['user_id'])) {
        $notificationId = $_POST['id'];
        $userId = $_SESSION['user_id'];

        $notification = getInfoNotification($notificationId);

        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $notificationId, $userId);
        $stmt->execute();
        $stmt->close();

        if ($notification['type'] === 'booking') {
            $sql = "SELECT booking_id FROM notifications WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $notificationId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $bookingId = $row['booking_id'];
                header('Location: ../templates/contracts.php?booking_id=' . $bookingId);
            } else {
                header('Location: ../templates/notifications.php?error=no_booking_found');
            }
            exit;
        } else if (in_array($notification['type'], ['admin','private'])) {
            echo json_encode(["success" => true]);
            exit;
        } else {
            $sql = "SELECT building_id FROM notifications WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $notificationId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $buildingId = $row['building_id'];
                $building = getInfoBuilding($buildingId);
                if ($building['user_id'] === $_SESSION['user_id']) {
                    header('Location: ../templates/edit_rooms.php?building_id=' . $buildingId);
                } else {
                    header('Location: ../templates/view_building.php?building_id=' . $buildingId);
                }
            } else {
                header('Location: ../templates/notifications.php?error=no_building_found');
            }
            exit;
        }
    }
}
echo json_encode(["success" => false]);
$conn->close();
?>
