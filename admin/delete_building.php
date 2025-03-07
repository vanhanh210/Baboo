<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['building_id'])) {
    $building_id = $_POST['building_id'];
    
    // Prepare a delete statement
    $sql = "DELETE FROM buildings WHERE building_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $building_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Ensure no output before redirection
        ob_start();
        header("Location: ../templates/manage_buildings.php");
        ob_end_flush();
        exit();
    } else {
        echo "Error deleting building: " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
    $conn->close();
} else {
    echo "building ID not set or invalid request method.";
}
?>