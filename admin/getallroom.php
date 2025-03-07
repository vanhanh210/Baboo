<?php
require '../config/database.php';
function getAllrooms($building_id) {
    global $conn;
    $sql = "SELECT * FROM rooms WHERE building_id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($building_id) {
        $stmt->bind_param("i", $building_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}
function getAllAvailableRooms($building_id) {
    global $conn;

    $rented_sql = "SELECT COUNT(*) AS rented_count FROM rooms WHERE building_id = ? AND room_status = 'Còn trống'";
    $total_sql = "SELECT COUNT(*) AS number_rooms FROM rooms WHERE building_id = ?";

    $stmt = $conn->prepare($rented_sql);
    if ($building_id) {
        $stmt->bind_param("i", $building_id);
    }
    $stmt->execute();
    $rented_result = $stmt->get_result();
    $rented_data = $rented_result->fetch_assoc();
    $rented_count = $rented_data['rented_count'];

    $stmt = $conn->prepare($total_sql);
    if ($building_id) {
        $stmt->bind_param("i", $building_id);
    }
    $stmt->execute();
    $total_result = $stmt->get_result();
    $total_data = $total_result->fetch_assoc();
    $number_rooms = $total_data['number_rooms'];

    return [
        'rented_count' => $rented_count,
        'number_rooms' => $number_rooms
    ];
}

function getInfoRoom($room_id) {
    global $conn;
    $sql = "SELECT * FROM rooms WHERE room_id = ?"; 
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('i', $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room_info = $result->fetch_assoc();
    $stmt->close();
    return $room_info;
}

function getDistinctRoomTypes() {
    // return ['1 phòng đơn','1 phòng đôi','2 phòng đơn','2 phòng đôi'];
    return ['Phòng đơn','Phòng đôi','Phòng VIP'];
}
?>
