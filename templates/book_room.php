<?php
session_start();
require '../config/database.php';

if (isset($_GET['room_id'])) {
    $room_id = $_GET['room_id'];
    $building_id = $_GET['building_id'];
    $sql = "SELECT * FROM rooms WHERE room_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
    $stmt->close();
} else {
    die("room ID not provided.");
}
?>

<!DOCTYPE html>
<html lang="vi"></html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt phòng</title>
    <link href="../assets/css/base.css" rel="stylesheet"> 
</head>
<body>
    <?php include '../includes/header.php'; ?> 
    <div class="head-container">
        <div class="main-content" id="create-booking">
            <div id="book" class="manage-head">
                <h1>Đặt phòng</h1>
            </div> <br>
            <form action="../admin/process_create_booking.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="room_id" id="room_id" value="<?php echo $room_id?>">
                <input type="hidden" name="building_id" id="building_id" value="<?php echo $building_id?>">
                <div class="form-group">
                    <label for="guest_name">Tên khách hàng:</label>
                    <input type="text" id="guest_name" name="guest_name" required>
                </div>
                <div class="form-group">
                    <label for="phone">Số điện thoại:</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="identification_card">Căn cước công dân:</label>
                    <input type="text" id="identification_card" name="identification_card">
                </div>
                <div class="form-group">
                    <label for="deposit_term">Ngày cọc:</label>
                    <input type="date" id="deposit_term" name="deposit_term">
                </div>
                <div class="form-group">
                    <label for="signed_date">Ngày ký</label>
                    <input type="date" id="signed_date" name="signed_date">
                </div>
                <div class="form-group">
                    <label for="payment_term">Thời hạn hợp đồng</label>
                    <select id="payment_term" name="payment_term">
                        <option value="Trống" selected>Trống</option>
                        <option value="1 tháng">1 tháng</option>
                        <option value="2 tháng">2 tháng</option>
                        <option value="3 tháng">3 tháng</option>
                        <option value="4 tháng">4 tháng</option>
                        <option value="5 tháng">5 tháng</option>
                        <option value="6 tháng">6 tháng</option>
                        <option value="7 tháng">7 tháng</option>
                        <option value="8 tháng">8 tháng</option>
                        <option value="9 tháng">9 tháng</option>
                        <option value="10 tháng">10 tháng</option>
                        <option value="11 tháng">11 tháng</option>
                        <option value="1 năm">1 năm</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="lease_start_date">Ngày bắt đầu:</label>
                    <input type="date" id="lease_start_date" name="lease_start_date" required>
                </div>
                <div class="form-group">
                    <label for="lease_end_date">Ngày kết thúc:</label>
                    <input type="date" id="lease_end_date" name="lease_end_date" required>
                </div>
                <div class="form-group">
                    <label for="photo_urls">Tải ảnh hợp đồng:</label>
                    <input type="file" id="photo_urls" name="photo_urls" accept="image/*" required>
                </div>
                <button type="submit">Xác nhận đặt phòng</button>
                <button class="cancel-btn" onclick="window.history.back();">Hủy</button>
            </form>
        </div>
        <?php include '../includes/sidebar.php'; ?> 
    </div>
</body>
</html>
