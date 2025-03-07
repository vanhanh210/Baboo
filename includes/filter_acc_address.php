<?php 
require '../config/database.php';

$data = [
    'Đà Nẵng' => ['Hải Châu', 'Thanh Khê', 'Sơn Trà', 'Ngũ Hành Sơn', 'Liên Chiểu', 'Cẩm Lệ', 'Hòa Vang'],
    'Hồ Chí Minh' => ['Quận 1', 'Quận 2', 'Quận 3', 'Quận 4', 'Quận 5', 'Quận 6', 'Quận 7'],
];
$building_types = getDistinctBuildingTypes();
$room_types = getDistinctRoomTypes();
?>
<form id="filter-section" class="filter-form" method="get" action="accommodation_info.php">
    <span id="close-btn" onclick="toggleFilter()">&times;</span>
    <div class="flex-wrap">
        <div class="form-group">
            <label for="name">Tìm tên toà nhà:</label>
            <input type="text" name="name" class="searchbox" placeholder="tìm kiếm bằng tên toà nhà" value="<?php echo htmlspecialchars($name); ?>">
        </div>
        <div class="form-group">
            <label for="price">Giá:</label>
            <select id="price" name="price">
                <option value="" selected>Tất cả</option>
                <option value="1-3">1 - 3 triệu</option>
                <option value="3-5">3 - 5 triệu</option>
                <option value="5-8">5 - 8 triệu</option>
                <option value="8-10">8 - 10 triệu</option>
                <option value="above_10">Trên 10 triệu</option>
            </select>
        </div>
        <div class="form-group">
            <label for="room_type">Loại phòng:</label>
            <select id="room_type" name="room_type">
                <option value="" selected>Tất cả</option>
                <?php foreach ($room_types as $type): ?>
                    <option value="<?php echo htmlspecialchars($type); ?>" ><?php echo htmlspecialchars($type); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="city">Thành phố:</label>
            <select id="city" name="city">
                <option value="">Chọn thành phố</option>
                <?php foreach ($data as $city => $districts): ?>
                    <option value="<?php echo htmlspecialchars($city); ?>"><?php echo htmlspecialchars($city); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="district">Quận:</label>
            <select id="district" name="district">
                <option value="">Chọn quận</option>
            </select>
        </div>
        <div class="form-group">
            <label for="building_type">Loại hình cho thuê</label>
            <select id="building_type" name="building_type">
                <option value="" selected>Trống</option>
                <?php foreach ($building_types as $type): ?>
                    <option value="<?php echo htmlspecialchars($type); ?>" ><?php echo htmlspecialchars($type); ?></option>
                <?php endforeach; ?>
            </select>
        </div>    
        <div class="form-group">
            <label for="submit">&#160;</label>
            <button type="submit" style="width: 100px">Lọc</button>
        </div>
    </div>
</form>
<script>
    const districtsData = <?php echo json_encode($data); ?>;
    const selectedDistrict = '';
    const selectedCity = ''; 

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

        if (selectedCity) {
            citySelect.value = selectedCity;
            citySelect.dispatchEvent(new Event('change'));
        }
    });
    document.getElementById("filter-section").addEventListener("submit", function(event) {
    const inputs = this.querySelectorAll("input, select");
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.removeAttribute("name"); 
        }
    });
    });
</script>