<?php
require '../config/database.php';
function getAllBookingsOfUser($user_id, $month = NULL, $new = NULL) { 
    global $conn;
    
    $sql = "SELECT building_id FROM buildings WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $building_ids = [];
    while ($row = $result->fetch_assoc()) {
        $building_ids[] = $row['building_id'];
    }

    $conditions = [];
    $params = [];
    $param_types = '';

    if (!empty($building_ids)) {
        $placeholders = implode(',', array_fill(0, count($building_ids), '?'));
        $conditions[] = "(user_id = ? OR building_id IN ($placeholders))";
        $params[] = $user_id;
        $param_types .= 'i' . str_repeat('i', count($building_ids));
        $params = array_merge($params, $building_ids);
    } else {
        $conditions[] = "user_id = ?";
        $params[] = $user_id;
        $param_types .= 'i';
    }    

    if ($month !== NULL) {
        $conditions[] = "(
            (YEAR(lease_start_date) = YEAR(lease_end_date) AND ? BETWEEN MONTH(lease_start_date) AND MONTH(lease_end_date)) 
            OR 
            (YEAR(lease_start_date) < YEAR(lease_end_date) AND 
                (? BETWEEN MONTH(lease_start_date) AND 12 OR ? BETWEEN 1 AND MONTH(lease_end_date))
            )
        )";
        $params[] = $month;
        $params[] = $month;
        $params[] = $month;
        $param_types .= 'iii';
    }
    
    if ($new !== NULL) {
        $conditions[] = "MONTH(NOW()) = MONTH(created_date) AND YEAR(NOW()) = YEAR(created_date)";
    }

    $sql = "SELECT * FROM bookings WHERE " . implode(" AND ", $conditions);
    $stmt = $conn->prepare($sql);

    if ($param_types) {
        $stmt->bind_param($param_types, ...$params);
    }

    $stmt->execute();
    
    return $stmt->get_result();
}

function getAllBookingsOfSales($month, $year) {
    global $conn;

    $role = $_SESSION['role'];
    $userId = $_SESSION['user_id'];

    $sql = "
        SELECT DISTINCT b.* 
        FROM bookings b
        JOIN contracts_payment c ON b.booking_id = c.booking_id
        WHERE MONTH(c.created_date) = ? AND YEAR(c.created_date) = ?
        AND c.status = 'Đã thanh toán'
    ";
    
    if ($role !== 'admin' && $role !== 'manager') {
        $sql .= " AND b.user_id = ?";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    if ($role !== 'admin' && $role !== 'manager') {
        $stmt->bind_param("iii", $month, $year, $userId);
    } else {
        $stmt->bind_param("ii", $month, $year);
    }

    $stmt->execute();
    return $stmt->get_result(); 
}

function getAllBookingsOfManagers($month, $year) {
    global $conn;

    $role = $_SESSION['role'];
    $userId = $_SESSION['user_id'];

    $sql = "SELECT DISTINCT b.*
        FROM bookings b
        JOIN contracts_payment c ON b.booking_id = c.booking_id
        JOIN buildings bl ON b.building_id = bl.building_id
        WHERE MONTH(c.created_date) = ? AND YEAR(c.created_date) = ?
        AND c.status = 'Đã thanh toán'";

    if ($role !== 'admin' && $role !== 'manager') {
        $sql .= " AND bl.user_id = ?";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    if ($role !== 'admin' && $role !== 'manager') {
        $stmt->bind_param("iii", $month, $year, $userId);
    } else {
        $stmt->bind_param("ii", $month, $year);
    }

    $stmt->execute();
    return $stmt->get_result(); 
}

function getAllContracts($booking_id) {
    global $conn;
    $sql = "SELECT * FROM contracts_payment WHERE booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $bookings_result = $stmt->get_result();
    return $bookings_result;
}

function getAllBookings() {
    global $conn;
    $sql = "SELECT * FROM bookings";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $bookings_result = $stmt->get_result();
    return $bookings_result;
}
?>