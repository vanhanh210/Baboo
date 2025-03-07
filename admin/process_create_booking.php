<?php
session_start();
require '../config/database.php';
include '../admin/getallbuilding.php';
include '../admin/getallroom.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    
    $building_id = $_POST["building_id"];
    $room_id = $_POST["room_id"];
    $guest_name = $_POST["guest_name"] ?? '';
    $phone = $_POST["phone"] ?? '';
    $identification_card = $_POST["identification_card"] ?? '';
    $deposit_term = $_POST["deposit_term"] ?? '';
    $signed_date = $_POST["signed_date"] ?? ''; 
    $payment_term = $_POST["payment_term"] ?? '';
    $lease_start_date = $_POST["lease_start_date"] ?? '';   
    $lease_end_date = $_POST["lease_end_date"] ?? '';   
    $photo_urls = '';


    if (isset($_FILES["photo_urls"]) && $_FILES["photo_urls"]["error"] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/contracts/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["photo_urls"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["photo_urls"]["tmp_name"], $target_file)) {
                $photo_urls = $target_file; 
            } else {
                echo "Error uploading photo.";
                exit();
            }
        } else {
            echo "Invalid file type.";
            exit();
        }
    }

    $sql = "INSERT INTO bookings (building_id, user_id, room_id, guest_name, phone, identification_card, deposit_term, signed_date, payment_term, lease_start_date, lease_end_date, photo_urls) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iiisssssssss", $building_id, $user_id, $room_id, $guest_name, $phone, $identification_card, $deposit_term, $signed_date, $payment_term, $lease_start_date, $lease_end_date, $photo_urls);

    if ($stmt->execute()) { 
        echo "New booking created successfully.";
        
        $booking_id = $conn->insert_id;

        $update_sql = "UPDATE rooms SET room_status = 'Đã thuê' WHERE room_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if (!$update_stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $update_stmt->bind_param("i", $room_id);
        
        if ($update_stmt->execute()) {
            echo "Room status updated successfully.";
        } else {
            echo "Error updating room status: " . $update_stmt->error;
        }

        $update_stmt->close();

        $building = getInfoBuilding($building_id);
        $room = getInfoRoom($room_id);
        $message = "Đặt phòng: " . htmlspecialchars($building['name']) . " [" . htmlspecialchars($room['room_name']) . "] thành công";

        $notification_sql = "INSERT INTO notifications (user_id, booking_id, message, type, created_at) VALUES (?, ?, ?, 'booking', NOW())";
        $notification_stmt = $conn->prepare($notification_sql);

        if (!$notification_stmt) {
            echo "Prepare failed for notification: " . $conn->error;
        } else {
            $notification_stmt->bind_param("iis", $user_id, $booking_id, $message);
            
            if (!$notification_stmt->execute()) {
                echo "Error creating notification: " . $notification_stmt->error;
            }

            $notification_stmt->close();
        }

        $message = "Nhận yêu cầu đặt phòng: " . htmlspecialchars($building['name']) . " [" . htmlspecialchars($room['room_name']) . "] thành công";
        $notification_sql = "INSERT INTO notifications (user_id, booking_id, message, type, created_at) VALUES (?, ?, ?, 'booking', NOW())";
        $notification_stmt = $conn->prepare($notification_sql);

        if (!$notification_stmt) {
            echo "Prepare failed for notification: " . $conn->error;
        } else {
            $notification_stmt->bind_param("iis", $building['user_id'], $booking_id, $message);
            
            if (!$notification_stmt->execute()) {
                echo "Error creating notification: " . $notification_stmt->error;
            }

            $notification_stmt->close();
        }

        $admin_sql = "SELECT user_id FROM users WHERE role = 'admin'";
        $admin_result = $conn->query($admin_sql);

        if ($admin_result) {
            $message = "Vừa có một hợp đồng mới từ toà nhà ".$building['name'];
            while ($admin = $admin_result->fetch_assoc()) {
                $admin_id = $admin['user_id'];
                $notification_sql = "INSERT INTO notifications (user_id, booking_id, message, type, created_at) VALUES (?, ?, ?, 'booking', NOW())";
                $notification_stmt = $conn->prepare($notification_sql);
                
                if ($notification_stmt) {
                    $notification_stmt->bind_param("iis", $admin_id, $booking_id, $message);
                    $notification_stmt->execute();  
                    
                    if ($notification_stmt->affected_rows <= 0) {
                        echo "Failed to insert notification for admin ID $admin_id: " . $notification_stmt->error;
                    }

                    $notification_stmt->close();
                } else {
                    echo "MySQL prepare error for notification: " . $conn->error;
                }
            }
        } else {
            echo "Error fetching admins: " . $conn->error;
        }

        // Thêm vào payment
        $payment_mapping = [
            '1 tháng' => 1,
            '2 tháng' => 2,
            '3 tháng' => 3,
            '4 tháng' => 4,
            '5 tháng' => 5,
            '6 tháng' => 6,
            '7 tháng' => 7,
            '8 tháng' => 8,
            '9 tháng' => 9,
            '10 tháng' => 10,
            '11 tháng' => 11,
            '1 năm' => 12
        ];

        $k = $payment_mapping[$payment_term] ?? 1;

        $rental_price = 0;
        $price_sql = "SELECT rental_price FROM rooms WHERE room_id = ?";
        $price_stmt = $conn->prepare($price_sql);
        if ($price_stmt) {
            $price_stmt->bind_param("i", $room_id);
            $price_stmt->execute();
            $price_stmt->bind_result($rental_price);
            $price_stmt->fetch();
            $price_stmt->close();
        }

        for ($i = 1; $i <= $k; $i++) {
            $payment_name = $i;
            $payment_sql = "INSERT INTO contracts_payment (booking_id, name, value) VALUES (?, ?, ?)";
            $payment_stmt = $conn->prepare($payment_sql);
            if ($payment_stmt) {
                $payment_stmt->bind_param("isd", $booking_id, $payment_name, $rental_price);
                $payment_stmt->execute();
                $payment_stmt->close();
            } else {
                echo "Error inserting payment record: " . $conn->error;
            }
        }
        
    } else {
        echo "Error creating booking: " . $stmt->error;
    }

    $stmt->close(); 
    $conn->close();

    ob_start();
    header("Location: ../templates/view_building.php?building_id=" . $building_id);
    ob_end_flush();
    exit();    
}
?>