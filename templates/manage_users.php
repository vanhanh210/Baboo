<?php
session_start(); // Start the session
include '../admin/getalluser.php';  // Include the user retrieval script

$search = isset($_GET['search']) ? $_GET['search'] : '';
$users = getAllUsers($search);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
    <link href="../assets/css/base.css" rel="stylesheet"> 
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="head-container">
        <div class="main-content">
        <div class="manage-head" id="user-manager">
                <h1>Quản Lý Người Dùng</h1>
                <button id="flow-btn" class="create" onclick="location.href='create_user.php'">Tạo người dùng mới</button>
            </div>
            <div style="overflow-x: auto; width: 100%;">
            <form class="flex-wrap-fit" class="mange-search-form" method="get" action="manage_users.php">
                <input type="text" name="search" placeholder="tìm kiếm băng tên hoặc tên đăng nhập" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Địa Chỉ</th>
                    <th>Quê Quán</th>
                    <th>Ngày Sinh</th>
                    <th>Số Điện Thoại</th>
                    <th>Email</th>
                    <th>Tên Đăng Nhập</th>
                    <th>Vai Trò</th>
                    <th>Lần cuối truy cập</th>
                    <th>Thao Tác</th>   <!-- Add a new column for the action -->
                </tr>
                <?php if ($users->num_rows > 0): ?>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['address']); ?></td>
                            <td><?php echo htmlspecialchars($user['hometown']); ?></td>
                            <td><?php echo htmlspecialchars($user['birthdate']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo htmlspecialchars($user['last_access']); ?></td> <!-- Display the last access time -->
                            <td class="crud-btn">
                                <form action="../admin/delete_user.php" method="post" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <button class="delete" type="submit"><img src="../assets/icons/bin.svg"></button>
                                </form>
                                <form action="../templates/edit_user.php" method="get" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <button class="edit" type="submit"><img src="../assets/icons/edit.svg"></button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">Không tìm thấy người dùng</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
        </div>
        <?php include '../includes/sidebar.php'; ?>
    </div>
</body>
</html>