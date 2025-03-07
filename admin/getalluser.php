<?php
require '../config/database.php';

function getAllUsers($search = '') {
    global $conn;
    $sql = "SELECT user_id, name, address, hometown, birthdate, phone, email, username, role, last_access FROM users";
    if ($search) {
        $search = '%' . $conn->real_escape_string($search) . '%';
        $sql .= " WHERE name LIKE ? OR username LIKE ?";
    }
    $stmt = $conn->prepare($sql);
    if ($search) {
        $stmt->bind_param("ss", $search, $search);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}
function getUsernameById($user_id) {
    global $conn; // Ensure you have access to the database connection
    $stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['name'];
    }
    
    return "N/A"; 
}
function getAllUsersName() {
    global $conn;
    $sql = "SELECT user_id, username FROM users";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}
?>

