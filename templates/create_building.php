<?php
session_start(); 
require '../config/database.php';
include '../admin/getallbuilding.php';  
include '../admin/getallroom.php';  
$building_types = getDistinctBuildingTypes();
$room_types = getDistinctRoomTypes();
$user_id = $_SESSION['user_id'];
$data = [
    'Đà Nẵng' => ['Hải Châu', 'Thanh Khê', 'Sơn Trà', 'Ngũ Hành Sơn', 'Liên Chiểu', 'Cẩm Lệ', 'Hòa Vang'],
    'Hồ Chí Minh' => ['Quận 1', 'Quận 2', 'Quận 3', 'Quận 4', 'Quận 5', 'Quận 6', 'Quận 7', 'Quận 8', 'Quận 9', 'Quận 10', 'Quận 12']
];
?>

<!DOCTYPE html>
<html lang="vi"></html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Toà Nhà Mới</title>
    <link href="../assets/css/base.css" rel="stylesheet"> 
</head>
<body>
    <?php include '../includes/header.php'; ?> <!-- Bao gồm tiêu đề -->

    <div class="head-container">
        <div class="main-content" id="create-building">
            <h1>Thêm Toà Nhà Mới</h1>
            <form id="building-form" action="../admin/process_create_building.php" method="post" enctype="multipart/form-data"> <!-- ✅ Added enctype -->
                <input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id?>"> 
                <div class="form-group">
                    <label for="name">Tên:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <!-- ✅ Added file upload input -->
                <div class="form-group">
                    <label for="building_image">Ảnh tòa nhà:</label>
                    <input type="file" id="building_images" name="building_images[]" accept="image/*" multiple>
                    </div>
                <div class="flex-wrap">
                <div class="form-group">
                    <label for="city">Thành phố:</label>
                    <select id="city" name="city" required>
                        <option value="">Chọn thành phố</option>
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
                    <input type="text" id="street" name="street" placeholder="Tên đường, số nhà" required>
                </div>
                </div>
                <div class="form-group">
                    <label for="owner_name">Tên chủ toà nhà:</label>
                    <input type="text" id="owner_name" name="owner_name">
                </div>
                <div class="form-group">
                    <label for="phone">Điện thoại:</label>
                    <input type="text" id="owner_phone" name="owner_phone">
                </div>
                <div class="form-group">
                    <label for="building_type">Loại hình cho thuê</label>
                    <select id="building_type" name="building_type">
                        <option value="Trống" selected>Trống</option>
                        <?php foreach ($building_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="electricity_price">Tiền điện:</label>
                    <input type="float" id="electricity_price" name="electricity_price" value="" required>
                </div>
                <div class="form-group">
                    <label for="water_price">Tiền nước:</label>
                    <input type="float" id="water_price" name="water_price" value="" required>
                </div>
                <div class="form-group">
                    <label for="service_price">Tiền dịch vụ (đồng):</label>
                    <input type="text" id="service_price" name="service_price" value="" required>
                </div>
                <div class="form-group">
                    <label for="description">Tiện nghi:</label>
                    <input type="text" id="description" name="description" value="" required>
                </div>
                <div class="form-group">
                    <div class="manage-head">
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
                        </tbody>
                    </table>
                </div>
                <button type="submit">Thêm toà nhà</button>
            </form>
            
        </div>
        <?php include '../includes/sidebar.php'; ?> <!-- Bao gồm thanh bên -->
    </div>
    
    <script>
        const districtsData = <?php echo json_encode($data); ?>;
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
                        districtSelect.appendChild(option);
                    });
                }
            });
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
