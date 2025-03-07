<?php
require '../config/database.php';

function getAllBuildings($name = NULL, $exename = NULL, $price = NULL, $selected_type = NULL, $user_id = NULL, $status_type = NULL, $city = NULL, $district = NULL, $room_type = NULL, $approved = NULL) {
    global $conn;
    
    $sql = "SELECT * FROM buildings b WHERE 1=1";
    $params = [];
    $binding_types = "";

    // Handle price filter dynamically
    if ($price !== NULL && $price != '') {
        if ($price === "1-3") {
            $sql .= " AND CAST(SUBSTRING_INDEX(b.rental_price, ' - ', 1) AS UNSIGNED) <= 3
                      AND CAST(SUBSTRING_INDEX(b.rental_price, ' - ', -1) AS UNSIGNED) >= 1";
        } elseif ($price === "3-5") {
            $sql .= " AND CAST(SUBSTRING_INDEX(b.rental_price, ' - ', 1) AS UNSIGNED) <= 5
                      AND CAST(SUBSTRING_INDEX(b.rental_price, ' - ', -1) AS UNSIGNED) >= 3";
        } elseif ($price === "5-8") {
            $sql .= " AND CAST(SUBSTRING_INDEX(b.rental_price, ' - ', 1) AS UNSIGNED) <= 8
                      AND CAST(SUBSTRING_INDEX(b.rental_price, ' - ', -1) AS UNSIGNED) >= 5";
        } elseif ($price === "8-10") {
            $sql .= " AND CAST(SUBSTRING_INDEX(b.rental_price, ' - ', 1) AS UNSIGNED) <= 10
                      AND CAST(SUBSTRING_INDEX(b.rental_price, ' - ', -1) AS UNSIGNED) >= 8";
        } elseif ($price === "above_10") {
            $sql .= " AND CAST(SUBSTRING_INDEX(b.rental_price, ' - ', -1) AS UNSIGNED) > 10";
        }
    }
    
    if (!empty($selected_type)) {
        $sql .= " AND b.building_type = ?";
        $params[] = $selected_type;
        $binding_types .= "s";
    }

    if ($user_id !== NULL) {
        $sql .= " AND b.user_id = ?";
        $params[] = $user_id;
        $binding_types .= "i";
    }

    if ($status_type !== NULL && $status_type != "Tất cả") {
        if ($status_type === "Hết phòng") {
            $sql .= " AND NOT EXISTS (
                        SELECT 1 
                        FROM rooms r 
                        WHERE r.building_id = b.building_id 
                        AND r.room_status = 'Còn trống'
                     )";
        } elseif ($status_type === "Còn phòng") {
            $sql .= " AND EXISTS (
                        SELECT 1 
                        FROM rooms r 
                        WHERE r.building_id = b.building_id 
                        AND r.room_status = 'Còn trống'
                     )";
        }
    }

    if (!empty($city)) {
        $sql .= " AND b.city = ?";
        $params[] = $city;
        $binding_types .= "s";
    }

    if (!empty($district)) {
        $sql .= " AND b.district = ?";
        $params[] = $district;
        $binding_types .= "s";
    }

    if (!empty($name)) {
        $sql .= " AND b.name LIKE ?";
        $params[] = "%{$name}%";
        $binding_types .= "s";
    }

    if (!empty($exename)) {
        $sql .= " AND b.user_id IN (
                     SELECT u.user_id FROM users u 
                     WHERE u.name LIKE ?
                 )";
        $params[] = "%{$exename}%";
        $binding_types .= "s";
    }

    if (!empty($room_type)) {
        $sql .= " AND EXISTS (
            SELECT 1 FROM rooms r 
            WHERE b.building_id = r.building_id 
            AND r.room_type = ?
        )";
        $params[] = $room_type;
        $binding_types .= "s";
    }

    if (!empty($approved)) {
        $sql .= " AND b.approved = ?";
        $params[] = $approved;
        $binding_types .= "i";
    }
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($binding_types, ...$params);
    }

    $stmt->execute();
    return $stmt->get_result();
}

function getAllBuildingsOfUser($user_id) {
    global $conn;
    
    $sql = "SELECT * FROM buildings WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

function getDistinctBuildingTypes() {
    // global $conn;
    // $sql = "SELECT DISTINCT building_type FROM buildings";
    // $result = $conn->query($sql);
    // $types = [];
    // while ($row = $result->fetch_assoc()) {
    //     $types[] = $row['building_type'];
    // }
    return ['Căn hộ/ Chung cư', 'Nhà ở', 'Văn phòng, Mặt bằng kinh doanh', 'Đất', 'Phòng trọ'];
}

function getInfoBuilding($building_id) {
    global $conn;
    $sql = "SELECT * FROM buildings WHERE building_id = ?"; 
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('i', $building_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $building_info = $result->fetch_assoc();
    $stmt->close();
    return $building_info;
}

function getAllBuildingsName() {
    global $conn;
    $sql = "SELECT building_id, name FROM buildings";
        
    $stmt = $conn->prepare($sql);
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}
?>
