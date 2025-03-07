<?php
session_start(); 

include '../includes/header.php';
$notifications->data_seek(0);
$notifications_admin->data_seek(0);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Thông Báo</title>
    <link href="../assets/css/base.css" rel="stylesheet"> 
</head>
<body>
    <div class="head-container">
        <div class="main-content">
            <div class="manage-head" id="noti">
                <h1>
                Thông Báo Của Tôi    
                </h1>
            </div>
            <div class="tab-container">
                <div class="tab active" onclick="switchTab('system')">Thông báo hệ thống</div>
                <div class="tab" onclick="switchTab('admin')">Thông báo nội bộ</div>
                <div class="tab" onclick="switchTab('private')">Thông báo từ quản lý</div>
            </div>
            <div id="notification-box-view" class="notification-box-view">
                <div class="notification-content-view">
                    <div id="admin-notifications" class="box"> 
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
                            <div class="notification-item">
                                <div class="notification-message">Không có thông báo</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div id="system-notifications" class="box active"> 
                        <?php if ($notifications->num_rows > 0): ?>
                            <?php while ($notification = $notifications->fetch_assoc()): ?>
                                <?php if (!in_array($notification['type'], ['admin','private'])): ?>
                                    <form action="../admin/mark_as_read.php" method="POST" class="notification-item <?php echo $notification['is_read'] ? '' : 'unread' ?>">
                                        <input type="hidden" name="id" value="<?php echo $notification['id']; ?>">
                                        <button type="submit" class="notification-message" style="color: #111; background: none; border: none; text-align: left; width: 100%; padding: 0; cursor: pointer;">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                        <div class="notification-time" style="font-size: 13px; margin-top: 5px;"><?php echo formatTime($notification['created_at'])?></div>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="notification-item">
                                <div class="notification-message">Không có thông báo</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div id="manager-notifications" class="box"> 
                        <?php 
                            $notifications->data_seek(0);
                            if ($notifications->num_rows > 0): 
                            ?>
                            <?php while ($notification = $notifications->fetch_assoc()): ?>
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
                                <?php endif; ?>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="notification-item">
                                <div class="notification-message">Không có thông báo</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>        
        </div>
        <?php include '../includes/sidebar.php'; ?>
    </div>
</body>
</html>