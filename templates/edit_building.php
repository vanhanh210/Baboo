<?php
session_start();
require '../config/database.php';
require '../admin/getallroom.php';
include '../admin/getallbuilding.php';  
require '../config/google_drive.php'; // Include Google Drive service

$driveService = new GoogleDriveService($conn); // Initialize Google Drive service
$building_types = getDistinctBuildingTypes();
$room_types = getDistinctRoomTypes();
if (isset($_GET['building_id'])) {
    $building_id = $_GET['building_id'];
    $rooms = getAllRooms($building_id);
    $sql = "SELECT * FROM buildings WHERE building_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $building_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $building = $result->fetch_assoc();
    $stmt->close();
} else {
    die("building ID not provided.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $district = $_POST['district'];
    $owner_phone = $_POST['owner_phone'];
    $owner_name = $_POST['owner_name'];
    $building_type = $_POST['building_type'];
    $electricity_price = $_POST['electricity_price'];
    $water_price = $_POST['water_price'];
    $service_price = $_POST['service_price'];
    $description = $_POST['description'];
    $rooms = isset($_POST['rooms']) ? json_decode($_POST['rooms'], true) : [];

    if (!empty($_FILES["building_image"]["tmp_name"][0])) {
        // ✅ Handle multiple image uploads
        $uploaded_file_urls = $driveService->uploadFilesAndSave($_FILES["building_image"], $building_id);
        if (!empty($uploaded_file_urls)) {
            $file_urls = array_merge($file_urls, $uploaded_file_urls);
        }
    }

    $file_urls_json = json_encode($file_urls);

    // ✅ Update building details with multiple images stored as JSON
    $sql = "UPDATE buildings SET 
    name = ?, street = ?, city = ?, district = ?,
    owner_phone = ?, owner_name = ?, building_type = ?, electricity_price = ?, 
    water_price = ?, service_price = ?, description = ?, last_modified = NOW(), photo_urls = ?, approved = 0 WHERE building_id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("ssssssssssssi", $name, $street, $city, $district, $owner_phone, $owner_name, 
                      $building_type, $electricity_price, $water_price, $service_price, 
                      $description, $file_urls_json, $building_id);
    
    
    $stmt->execute();
    $stmt->close();

    if (!empty($rooms)) {
        $deleteRoomsSql = "DELETE FROM rooms WHERE building_id = ?";
        $stmtDeleteRooms = $conn->prepare($deleteRoomsSql);
        $stmtDeleteRooms->bind_param("i", $building_id);
        $stmtDeleteRooms->execute();
        $stmtDeleteRooms->close();
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
                $room_photos = $googleDriveService->uploadFilesAndSave($_FILES[$roomImageKey], $building_id, $room_name);
            }

            // ✅ Convert room photos array to JSON
            $room_photos_json = json_encode($room_photos);

            $stmtRoom->bind_param("isdssss", $building_id, $room_name, $rental_price, $area, $room_type, $room_status, $room_photos_json);
            $stmtRoom->execute();
        }
        $stmtRoom->close();
    }

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
    }
    
    $conn->close();
    header("Location: manage_buildings.php");
    exit();
}

$data = [
    'Đà Nẵng' => ['Hải Châu', 'Thanh Khê', 'Sơn Trà', 'Ngũ Hành Sơn', 'Liên Chiểu', 'Cẩm Lệ', 'Hòa Vang'],
    'Hồ Chí Minh' => ['Quận 1', 'Quận 2', 'Quận 3', 'Quận 4', 'Quận 5', 'Quận 6', 'Quận 7', 'Quận 8', 'Quận 9', 'Quận 10', 'Quận 12'],
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Toà Nhà</title>
    <link href="../assets/css/base.css" rel="stylesheet"> 
</head>
<body>
    <?php include '../includes/header.php'; ?> 

    <div class="head-container">
        <div class="main-content" id="edit-building">
            <h1>Chỉnh Sửa Toà Nhà</h1>
            <form id="building-form" action="edit_building.php?building_id=<?php echo $building_id; ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Tên toà nhà:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($building['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="building_image">Hình ảnh tòa nhà:</label>
                    <input type="file" id="building_image" name="building_image[]" accept="image/*" multiple>
                    <?php 
                    if (!empty($building['photo_urls'])): 
                        // Convert Google Drive URL to direct displayable image
                        $direct_image_url = $driveService->getDirectGoogleDriveImage($building['photo_urls']);
                    ?>
                        <p>Hình ảnh hiện tại:</p>
                        <img src="<?php echo $direct_image_url; ?>" alt="Building Image" style="width: 300px; height: 300px; object-fit: cover; display: block; margin: auto;">
                    <?php endif; ?>
                </div>
                <div class="flex-wrap">
                    <div class="form-group">
                        <label for="city">Thành phố:</label>
                        <select id="city" name="city" required>
                            <option value="Other">Chọn thành phố</option>
                            <?php foreach ($data as $city => $districts): ?>
                                <option value="<?php echo htmlspecialchars($city); ?>"><?php echo htmlspecialchars($city); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="district">Quận:</label>
                        <select id="district" name="district" required>
                            <option value="">Chọn quận</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="street">Địa chỉ:</label>
                        <input type="text" id="street" name="street" placeholder="Tên đường, số nhà" value="<?php echo htmlspecialchars($building['street']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="owner_phone">Số điện thoại chủ nhà:</label>
                    <input type="text" id="owner_phone" name="owner_phone" value="<?php echo htmlspecialchars($building['owner_phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="owner_name">Tên chủ nhà:</label>
                    <input type="text" id="owner_name" name="owner_name" value="<?php echo htmlspecialchars($building['owner_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="building_type">Loại hình cho thuê</label>
                    <select id="building_type" name="building_type">
                        <option value="Trống" selected>Trống</option>
                        <?php foreach ($building_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($building['building_type'] == $type) ? 'selected' : ''; ?>><?php echo htmlspecialchars($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="electricity_price">Tiền điện (đồng):</label>
                    <input type="float" id="electricity_price" name="electricity_price" value="<?php echo htmlspecialchars($building['electricity_price']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="water_price">Tiền nước (đồng):</label>
                    <input type="float" id="water_price" name="water_price" value="<?php echo htmlspecialchars($building['water_price'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="service_price">Tiền dịch vụ (đồng):</label>
                    <input type="text" id="service_price" name="service_price" value="<?php echo htmlspecialchars($building['service_price'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Tiện nghi:</label>
                    <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($building['description']); ?>">
                </div>
                <div class="form-group">
                    <div class="flex-wrap-fit">
                        <h1>Thêm phòng</h1> 
                        <button type="button" class="create" onclick="openLightbox(this);">Thêm phòng mới</button>
                    </div>    
                    <table>
                        <thead>
                            <tr>
                                <th>Tên</th>
                                <th>Giá</th>
                                <th>Diện tích</th>
                                <th>Tình trạng</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td class="room_name"><?php echo htmlspecialchars($room['room_name']); ?></td>
                                <td class="room_price"><?php echo htmlspecialchars($room['rental_price']); ?> triệu/tháng</td>
                                <td class="room_area"><?php echo htmlspecialchars($room['area']); ?> m&#178;</td>
                                <td class="room_type"><?php echo htmlspecialchars($room['room_type']); ?></td>
                                <td class="room_status"><?php echo htmlspecialchars($room['room_status']); ?></td>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] != 'admin'): ?>
                                <td class="crud-btn">
                                    <button class="delete" onclick="deleteRow(this)">
                                        <img src="../assets/icons/bin.svg">
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit">Lưu</button>
                <button type="button" class="cancel-btn" onclick="window.history.back();">Hủy</button>
            </form>
        </div>
        <?php include '../includes/sidebar.php'; ?>
    </div>
<script>
    const districtsData = <?php echo json_encode($data); ?>;
    const selectedDistrict = '<?php echo htmlspecialchars($building['district']); ?>';
    const selectedCity = '<?php echo htmlspecialchars($building['city']); ?>'; 

    document.addEventListener('DOMContentLoaded', function() {
        const citySelect = document.getElementById('city');
        const districtSelect = document.getElementById('district');

        citySelect.addEventListener('change', function() {
            districtSelect.innerHTML = '<option value="">Chọn quận</option>';
            const selectedCity = this.value;

            if (selectedCity && districtsData[selectedCity]) {
                districtsData[selectedCity].forEach(function(district) {
                    const option = document.createElement('option');
                    option.value = district;
                    option.textContent = district;
                    if (district === selectedDistrict) {
                        option.selected = true; 
                    }
                    districtSelect.appendChild(option);
                });
            }
        });

        if (selectedCity) {
            citySelect.value = selectedCity;
            citySelect.dispatchEvent(new Event('change'));
        }
    });
</script>
<div class="lightbox" id="lightboxroom" style="display:none;">
    <div class="lightbox-content">
    <span class="close" onclick="closeLightbox()">&times;</span>
    <h1>Thêm Phòng Mới</h1>
        <form id="dataForm" method="post" enctype="multipart/form-data" >
            <div class="form-group">
                <label for="room_name">Tên phòng:</label>
                <input type="text" id="room_name" name="room_name" placeholder="Tên phòng" required>
            </div>
            <div class="form-group">
                <label for="room_price">Giá (triệu/thángg):</label>
                <input type="text" id="room_price" name="rental_price" placeholder="Giá" required>
            </div>
            <div class="form-group">
                <label for="room_area">Diện tích:</label>
                <input type="text" id="room_area" name="area" placeholder="Diện tích" required>
            </div>
            <div class="form-group">
                <label for="room_type">Loại phòng</label>
                <select id="room_type" name="room_type" required>
                    <option value="" selected>Trống</option>
                    <?php foreach ($room_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="room_status">Tình trạng:</label>
                <select id="room_status" name="room_status" required>
                    <option value="Còn trống" selected>Còn trống</option>
                    <option value="Đã thuê">Đã thuê</option>
                </select>
            </div>    
            <div class="form-group">
                <label for="photo_urls">Tải ảnh phòng:</label>
                <input type="file" id="building_images" name="building_images[]" accept="image/*" multiple>
                </div>
            <button type="submit">Thêm Phòng</button>
        </form>
    </div>
</div>
<script src="../assets/js/submit_room.js"></script>
</body>
</html>