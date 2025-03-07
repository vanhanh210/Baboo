<?php
session_start();
require '../config/database.php';
require '../admin/getallroom.php';
include '../admin/getalluser.php';

$building_id = $_GET['building_id'];

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

$rooms = getAllRooms($building_id);

/**
 * Converts Google Drive links to direct image links
 */
function getDirectGoogleDriveImage($photo_url) {
    if (preg_match('/id=([a-zA-Z0-9_-]+)/', $photo_url, $matches) || 
        preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $photo_url, $matches)) {
        $file_id = $matches[1];
        return "https://lh3.googleusercontent.com/d/$file_id";
    }
    return $photo_url; // Return original URL if it's not a Google Drive link
}

// ✅ Convert stored building photo URL
$building_photo_url = isset($building['photo_urls']) ? getDirectGoogleDriveImage($building['photo_urls']) : 'default_image.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Toà Nhà</title>
    <link rel="stylesheet" href="../assets/css/rooms.css">
    <link href="../assets/css/base.css" rel="stylesheet"> 
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="head-container">
        <div class="main-content">
            <div class="building-info-container">
            <img src="<?php echo htmlspecialchars($building_photo_url); ?>" alt="Building Image" class="building-image">
            <div>
                    <p>Tên toà nhà: <?php echo htmlspecialchars($building['name']); ?></p>
                    <p>Địa chỉ: <?php echo htmlspecialchars($building['street']); ?>, <?php echo htmlspecialchars($building['district']); ?>, <?php echo htmlspecialchars($building['city']); ?></p>
                    <p>Số điện thoại chủ nhà: <?php echo htmlspecialchars($building['owner_phone']); ?></p>
                    <p>Tên chủ nhà: <?php echo htmlspecialchars($building['owner_name']); ?></p>
                    <p>Tên quản lý: <?php echo htmlspecialchars(getUsernameById($building['user_id'])); ?></p>
                    <p>Loại hình: <?php echo htmlspecialchars($building['building_type']); ?></p>
                    <p>Tiện nghi: <?php echo htmlspecialchars($building['description']); ?></p>
                    <p>Tiền điện: <?php echo htmlspecialchars($building['electricity_price']); ?></p>
                    <p>Tiền nước: <?php echo htmlspecialchars($building['water_price']); ?></p>
                    <p>Tiền dịch vụ: <?php echo htmlspecialchars($building['service_price']); ?></p>
                </div>  
            </div>    
            <div class="flex-wrap-fit">
                <h3>Phòng</h3>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'],['admin','manager'])): ?>
                    <?php if ($building['approved']): ?> 
                        <form action="../admin/approve_room.php" method="POST">
                            <input type="hidden" name="action" value="stop">
                            <input type="hidden" name="user_id" value="<?php echo $building['user_id']; ?>">
                            <input type="hidden" name="name" value="<?php echo $building['name']; ?>">
                            <input type="hidden" name="building_id" value="<?php echo $building_id; ?>">
                            <button type="submit" class="create">Ngưng toà nhà</button>
                        </form>
                    <?php else: ?>
                        <form action="../admin/approve_room.php" method="POST">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="user_id" value="<?php echo $building['user_id']; ?>">
                            <input type="hidden" name="name" value="<?php echo $building['name']; ?>">
                            <input type="hidden" name="building_id" value="<?php echo $building_id; ?>">
                            <button type="submit" class="create">Duyệt toà nhà</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>    
            <div style="overflow-x: auto; width: 100%;">
                <table>
                    <thead>
                        <tr>
                            <th>Tên</th>
                            <th>Giá</th>
                            <th>Diện tích</th>
                            <th>Loại phòng</th>
                            <th>Tình trạng</th>
                            <?php if (isset($_SESSION['role']) && !in_array($_SESSION['role'], ['manager','admin'])): ?>
                                <th>Hành động</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($rooms->num_rows > 0): ?>
                        <?php foreach ($rooms as $room): ?>
                            <tr onclick="openLightbox(this)">
                                <td class="blue-txt"><?php echo htmlspecialchars($room['room_name']); ?></td>
                                <td><?php echo htmlspecialchars($room['rental_price']); ?> triệu/tháng</td>
                                <td><?php echo htmlspecialchars($room['area']); ?> m&#178;</td>
                                <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                                <td class="<?php echo $room['room_status'] === 'Còn trống' ? 'available' : 'occupied'; ?>">
                                    <?php echo htmlspecialchars($room['room_status']); ?>
                                </td>
                                <?php if (isset($_SESSION['role']) && !in_array($_SESSION['role'], ['manager','admin'])): ?>
                                <td>
                                    <?php if ($room['room_status'] == 'Còn trống'): ?>
                                        <button class="create" onclick="location.href='book_room.php?building_id=<?php echo $room['building_id']; ?>&room_id=<?php echo $room['room_id']; ?>'">Đặt phòng</button>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Không tìm thấy phòng</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php include '../includes/sidebar.php'; ?>
    </div>
    <div class="lightbox" id="lightboxviewroom" style="display:none">
        <div class="lightbox-content">
        <span class="close" onclick="closeLightbox()">&times;</span>
            <h3>Thông tin phòng</h3>
            <div class="building-info-container">
            <?php
        // ✅ Convert stored room photo URL
        $room_photo_url = isset($room['photo_urls']) ? getDirectGoogleDriveImage($room['photo_urls']) : 'default_room.jpg';
        ?>
        <img id="lightbox-image" src="<?php echo htmlspecialchars($room_photo_url); ?>" alt="Room Image">                <div>
                <p><b>Tên phòng</b>: <?php echo htmlspecialchars($room['room_name']); ?></p>
                <p><b>Giá</b>: <?php echo htmlspecialchars($room['rental_price']); ?> triệu/tháng</p>
                <p><b>Diện tích</b>: <?php echo htmlspecialchars($room['area']); ?></p>
                <p><b>Loại phòng</b>: <?php echo htmlspecialchars($room['room_type']); ?></p>
                <p><b>Tình trạng</b>: <?php echo htmlspecialchars($room['room_status']); ?></p>
            </div>  
        </div>    
        </div>
    </div>
</body>
</html>