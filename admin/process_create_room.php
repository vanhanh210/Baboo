<?php
session_start();
require '../config/database.php';
require '../config/google_drive.php'; // ✅ Include Google Drive service

// Function to write logs (ensure directory exists)
function logMessage($message) {
    $logDir = '../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true); // ✅ Create logs directory if missing
    }

    $logFile = $logDir . '/error_log.txt';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    logMessage("🚀 Starting room creation...");

    // Capture form data
    $building_id = $_POST['building_id'];
    $room_name = $_POST['room_name'];
    $rental_price = $_POST['rental_price'];
    $area = $_POST['area'];
    $room_status = $_POST['room_status'];
    $room_type = $_POST['room_type'];

    logMessage("✔ Received form data: Room - $room_name, Building ID - $building_id");

    // ✅ Insert room data (WITHOUT the photo first)
    $sql = "INSERT INTO rooms (building_id, room_name, area, rental_price, room_status, room_type) VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        logMessage("❌ MySQL prepare error: " . $conn->error);
        die("MySQL prepare error: " . $conn->error);
    }

    $stmt->bind_param("isssss", $building_id, $room_name, $area, $rental_price, $room_status, $room_type);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $room_id = $stmt->insert_id;
        logMessage("✅ New room created with ID: $room_id");

        // ✅ Handle multiple file uploads **AFTER** creating the room
        $googleDriveService = new GoogleDriveService($conn);
        $photo_urls = [];

        if (isset($_FILES["photo_urls"])) {
            logMessage("✔ Uploading multiple room images to Google Drive...");

            // ✅ Upload multiple files and store URLs
            $photo_urls = $googleDriveService->uploadFilesAndSave($_FILES["photo_urls"], $building_id, $room_name);

            if (!empty($photo_urls)) {
                logMessage("✅ Google Drive upload successful for multiple images.");

                // ✅ Convert array to JSON string for storage
                $photo_urls_json = json_encode($photo_urls);

                // ✅ Update room with photo URLs
                $update_sql = "UPDATE rooms SET photo_urls = ? WHERE room_id = ?";
                $update_stmt = $conn->prepare($update_sql);

                if ($update_stmt) {
                    $update_stmt->bind_param("si", $photo_urls_json, $room_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                    logMessage("✅ Database updated with multiple room image URLs.");
                } else {
                    logMessage("❌ Database update error: " . $conn->error);
                }
            } else {
                logMessage("❌ No images uploaded or upload failed.");
            }
        }

        logMessage("🔄 Updating building rental price...");

        $price_sql = "SELECT MIN(rental_price) AS min_price, MAX(rental_price) AS max_price FROM rooms WHERE building_id = ?";
        $price_stmt = $conn->prepare($price_sql);
        $price_stmt->bind_param("i", $building_id);
        $price_stmt->execute();
        $price_stmt->bind_result($min_price, $max_price);
        $price_stmt->fetch();
        $price_stmt->close();

        if ($min_price !== null && $max_price !== null) {
            $updated_price = number_format($min_price, 0, '.', ',') . " - " . number_format($max_price, 0, '.', ',');

            $update_building_sql = "UPDATE buildings SET rental_price = ? WHERE building_id = ?";
            $update_building_stmt = $conn->prepare($update_building_sql);
            $update_building_stmt->bind_param("si", $updated_price, $building_id);
            $update_building_stmt->execute();
            $update_building_stmt->close();

            logMessage("✅ Cập nhật giá tòa nhà: $updated_price");
        } else {
            logMessage("⚠️ Không có giá phòng hợp lệ để cập nhật.");
        }

        logMessage("✅ New room creation process completed successfully.");
    } else {
        logMessage("❌ Failed to insert room.");
        die("❌ Failed to insert room.");
    }

    $stmt->close();
    $conn->close();

    logMessage("✅ Redirecting to edit rooms page.");
    header("Location: ../templates/edit_rooms.php?building_id=" . $building_id);
    exit();
}
?>
