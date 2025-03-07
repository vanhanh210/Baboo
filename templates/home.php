<?php

session_start();
include '../admin/getallbuilding.php';  
$your_buildings = getAllBuildings(NULL, NULL, NULL, NULL, $_SESSION['user_id'], NULL, NULL, NULL, NULL, NULL);
if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['manager', 'admin'])) {
    $your_buildings = getAllBuildings(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

include '../includes/header.php';
$notifications->data_seek(0);
$notifications_admin->data_seek(0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BabooHouse</title>
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="../assets/css/base.css" rel="stylesheet"> 
</head>
<body>
    <div class="head-container">
        <div class="main-content">
            <div class="manage-head" id="home">
                <h1>Trang chủ</h1>      
            </div>
            <div class="grid-template">
                <div class="grid-item">
                    <h2>Thông báo hệ thống</h2>
                    <div class="content-grid">
                        <div class="content-wrap">
                        <?php if ($notifications->num_rows > 0): ?>
                            <?php while ($notification = $notifications->fetch_assoc()): ?>
                                <?php if (!in_array($notification['type'], ['admin', 'private'])): ?>
                                    <?php if ($notification['type'] === 'private'): ?>
                                        <div id="notif-<?php echo $notification['id']; ?>" 
                                            class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>" 
                                            onclick="markAsRead(<?php echo $notification['id']; ?>, `<?php echo htmlspecialchars_decode($notification['message']); ?>`, '<?php echo $notification['created_at']; ?>')">
                                            
                                            <div class="notification-title">
                                                <?php echo htmlspecialchars_decode($notification['title']); ?>
                                            </div>
                                            
                                            <div class="notification-message">
                                                <?php 
                                                    $plainTextMessage = strip_tags(html_entity_decode($notification['message'], ENT_QUOTES, 'UTF-8'));
                                                    echo mb_strimwidth($plainTextMessage, 0, 60, '...');
                                                ?>
                                            </div>
                                            
                                            <div class="notification-time" style="font-size: 13px; margin-top: 5px;">
                                                <?php echo formatTime($notification['created_at']); ?>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <form action="../admin/mark_as_read.php" method="POST" class="notification-item <?php echo $notification['is_read'] ? '' : 'unread' ?>">
                                            <input type="hidden" name="id" value="<?php echo $notification['id']; ?>">
                                            <button type="submit" class="notification-message" style="color: #111; background: none; border: none; text-align: left; width: 100%; padding: 0; cursor: pointer;">
                                                <?php echo htmlspecialchars_decode($notification['message']); ?>
                                                <div class="notification-time" style="font-size: 13px; margin-top: 5px;"><?php echo formatTime($notification['created_at'])?></div>
                                                </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="notification-message">Không có thông báo</div>
                        <?php endif; ?>
                        </div>
                        <br><br>
                        <button class="create" onclick="location.href='view_notification.php'">Xem thêm</button>
                    </div>
                </div>
                <div class="grid-item">
                    <h2>Xếp hạng</h2>
                    <div class="content-grid">
                        <div class="content-wrap">

                        </div>
                    </div>  
                </div>
                <div class="grid-item">
                    <h2>Các toà đang quản lý</h2>
                    <div class="content-grid">
                        <div class="content-wrap">
                            <table>
                                <tr>
                                    <th>Tên</th>
                                    <th>Giá</th>
                                    <th>Khu Vực</th>
                                    <th>Tình trạng</th>
                                </tr>
                                <?php if ($your_buildings->num_rows > 0): ?>
                                    <?php while ($building = $your_buildings->fetch_assoc()): ?>
                                        <tr onclick="location.href='edit_rooms.php?building_id=<?php echo htmlspecialchars($building['building_id']); ?>'" style="cursor: pointer;">
                                            <td><?php echo htmlspecialchars($building['name']); ?></td>
                                            <td><?php echo htmlspecialchars($building['rental_price']); ?> triệu/ tháng</td>
                                            <td><?php echo htmlspecialchars($building['district']) . ', ' . htmlspecialchars($building['city']); ?></td>
                                            <td><?php echo htmlspecialchars($building['approved'] ? 'Đã duyệt' : 'Chưa duyệt') ?></td> 
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10">Chưa có toà nhà nào</td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <br><br>
                        <button class="create" onclick="location.href='manage_buildings.php'">Xem thêm</button>
                    </div>  
                </div>

                <div class="grid-item">
                    <h2>Thông báo nội bộ</h2>
                    <div class="content-grid">
                        <div class="content-wrap">
                            <?php if ($notifications_admin->num_rows > 0): ?>
                                <?php while ($notification = $notifications_admin->fetch_assoc()): ?>
                                    <div id="notif-<?php echo $notification['id']; ?>" 
                                        class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>" 
                                        onclick="markAsRead(<?php echo $notification['id']; ?>, `<?php echo htmlspecialchars_decode($notification['message']); ?>`, '<?php echo $notification['created_at']; ?>')">
                                        
                                        <div class="notification-title">
                                            <?php echo htmlspecialchars_decode($notification['title']); ?>
                                        </div>
                                        
                                        <div class="notification-message">
                                            <?php 
                                                $plainTextMessage = strip_tags(html_entity_decode($notification['message'], ENT_QUOTES, 'UTF-8'));
                                                echo mb_strimwidth($plainTextMessage, 0, 60, '...');
                                            ?>
                                        </div>
                                        
                                        <div class="notification-time" style="font-size: 13px; margin-top: 5px;">
                                            <?php echo formatTime($notification['created_at']); ?>
                                        </div>
                                    </div>     
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="notification-message">Không có thông báo</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include '../includes/sidebar.php'; ?>
    </div>
</body>
</html>

<?php 
$notifications->data_seek(0);
$notifications_admin->data_seek(0);
?>
