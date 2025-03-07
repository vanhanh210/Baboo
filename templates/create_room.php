<?php
include '../admin/getallroom.php';
session_start();   

if (isset($_GET['building_id'])) {
    $building_id = $_GET['building_id'];
}
$room_types = getDistinctRoomTypes();
?>

<!DOCTYPE html>
<html lang="vi"></html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Phòng Mới</title>
    <link href="../assets/css/base.css" rel="stylesheet"> 
</head>
<body>
    <?php include '../includes/header.php'; ?> <!-- Bao gồm tiêu đề -->

    <div class="head-container">
        <div class="main-content" id="create-room">
            <h1>Thêm Phòng Mới</h1>
            <form action="../admin/process_create_room.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="building_id" value="<?php echo $building_id?>">
                <div class="form-group">
                    <label for="room_name">Tên phòng:</label>
                    <input type="text" id="room_name" name="room_name" placeholder="Tên phòng">
                </div>
                <div class="form-group">
                    <label for="room_price">Giá (triệu/tháng):</label>
                    <input type="text" id="room_price" name="rental_price" placeholder="Giá">
                </div>
                <div class="form-group">
                    <label for="room_area">Diện tích:</label>
                    <input type="text" id="room_area" name="area" placeholder="Diện tích">
                </div>
                <div class="form-group">
                    <label for="room_type">Loại phòng</label>
                    <select id="room_type" name="room_type">
                        <option value="" selected>Trống</option>
                        <?php foreach ($room_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="room_status">Tình trạng:</label>
                    <select id="room_status" name="room_status">
                        <option value="Còn trống" selected>Còn trống</option>
                        <option value="Đã thuê">Đã thuê</option>
                    </select>
                </div>    
                <div class="form-group">
                    <label for="photo_urls">Tải ảnh phòng:</label>
                    <input type="file" id="photo_urls" name="photo_urls[]" accept="image/*" multiple>
                    </div>
                <button type="submit">Thêm Phòng</button>
            </form>
        </div>
        <?php include '../includes/sidebar.php'; ?> <!-- Bao gồm thanh bên -->
    </div>
</body>
</html>
