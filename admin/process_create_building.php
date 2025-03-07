<?php
session_start();
require '../config/database.php';
require '../config/google_drive.php'; // ✅ Include Google Drive service

// ✅ Function to log messages
function logMessage($message) {
    $logDir = '../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/error_log.txt';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// ✅ Ensure the request is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    logMessage("🚀 Starting building creation process...");

    // Capture form data
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $district = $_POST['district'];
    $owner_name = $_POST['owner_name'];
    $owner_phone = $_POST['owner_phone'];
    $building_type = $_POST['building_type'];
    $electricity_price = $_POST['electricity_price'];
    $water_price = $_POST['water_price'];
    $service_price = $_POST['service_price'];
    $description = $_POST['description'];
    $rooms = isset($_POST['rooms']) ? json_decode($_POST['rooms'], true) : [];

    logMessage("✔ Received form data: Building Name - $name, User ID - $user_id, Total Rooms - " . count($rooms));

    // ✅ Start database transaction
    $conn->begin_transaction();

    try {
        // ✅ Insert building data (WITHOUT photos first)
        $sql = "INSERT INTO buildings (user_id, name, street, city, district, owner_name, owner_phone, 
                building_type, electricity_price, water_price, service_price, description, last_modified)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("MySQL prepare error: " . $conn->error);
        $stmt->bind_param("isssssssssss", $user_id, $name, $street, $city, $district, $owner_name, 
                          $owner_phone, $building_type, $electricity_price, $water_price, $service_price, $description);
        $stmt->execute();
        if ($stmt->affected_rows <= 0) throw new Exception("❌ Failed to insert building.");
        
        $building_id = $stmt->insert_id;
        logMessage("✅ New building created with ID: $building_id");
        $stmt->close();

        // ✅ Initialize Google Drive Service
        $googleDriveService = new GoogleDriveService($conn);

        // ✅ Handle multiple building images upload
        $building_photos = [];
        if (isset($_FILES["building_images"])) {
            logMessage("✔ Uploading multiple building images...");
            $building_photos = $googleDriveService->uploadFilesAndSave($_FILES["building_images"], $building_id);
        }

        // ✅ Store building photo URLs in JSON format
        if (!empty($building_photos)) {
            $photo_urls_json = json_encode($building_photos);
            $update_sql = "UPDATE buildings SET photo_urls = ? WHERE building_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $photo_urls_json, $building_id);
            $update_stmt->execute();
            $update_stmt->close();
            logMessage("✅ Stored multiple building images.");
        }

        // ✅ Insert rooms into database
        if (!empty($rooms)) {
            logMessage("🏠 Adding rooms to building ID: $building_id...");
            $stmtRoom = $conn->prepare("INSERT INTO rooms (building_id, room_name, rental_price, area, 
                                       room_type, room_status, photo_urls) VALUES (?, ?, ?, ?, ?, ?, ?)");

            foreach ($rooms as $room) {
                $room_name = $room['name'];
                $rental_price = $room['price'];
                $area = $room['area'];
                $room_type = $room['type'];
                $room_status = $room['status'];
                $room_photos = [];

                // ✅ Upload multiple room images if available
                $roomImageKey = 'room_images_' . preg_replace('/\s+/', '_', strtolower($room_name));
                if (isset($_FILES[$roomImageKey])) {
                    logMessage("✔ Uploading multiple room images for: $room_name...");
                    $room_photos = $googleDriveService->uploadFilesAndSave($_FILES[$roomImageKey], $building_id, $room_name);
                }

                // ✅ Convert room photos array to JSON
                $room_photos_json = json_encode($room_photos);

                $stmtRoom->bind_param("isdssss", $building_id, $room_name, $rental_price, $area, $room_type, $room_status, $room_photos_json);
                $stmtRoom->execute();
            }
            $stmtRoom->close();
        }
        // ✅ Update rental price range for building
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
            logMessage("✅ Updated building rental price: $updated_price");
        } else {
            logMessage("⚠️ No valid room prices to update.");
        }

        // ✅ Notify Admins about the new building
        logMessage("🔔 Notifying admins about new building request...");
        $admin_sql = "SELECT user_id FROM users WHERE role IN ('manager','admin')";
        $admin_result = $conn->query($admin_sql);

        if ($admin_result && $admin_result->num_rows > 0) {
            $message = "Yêu cầu toà nhà '$name' đang chờ duyệt.";
            $notification_sql = "INSERT INTO notifications (user_id, building_id, message, type, created_at) VALUES (?, ?, ?, 'building', NOW())";
            $notification_stmt = $conn->prepare($notification_sql);

            while ($admin = $admin_result->fetch_assoc()) {
                $admin_id = $admin['user_id'];
                $notification_stmt->bind_param("iis", $admin_id, $building_id, $message);
                $notification_stmt->execute();
            }
            $notification_stmt->close();
            logMessage("✅ Admin notifications sent.");
        } else {
            logMessage("⚠️ No admins found for notification.");
        }

        // ✅ Commit transaction if everything is successful
        $conn->commit();
        logMessage("✅ Building creation completed successfully!");

        // ✅ Redirect to management page
        header("Location: ../templates/manage_buildings.php");
        exit();
    } catch (Exception $e) {
        // ❌ Rollback transaction in case of an error
        $conn->rollback();
        logMessage("❌ ERROR: " . $e->getMessage());
        die("❌ ERROR: " . $e->getMessage());
    } finally {
        $conn->close();
    }
}
?>
