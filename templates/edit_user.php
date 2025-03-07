<?php
session_start();
require '../config/database.php';

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch user details
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
} else {
    die("User ID not provided.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract form data
    $name = $_POST['name'];
    $address = $_POST['address'];
    $hometown = $_POST['hometown'];
    $birthdate = $_POST['birthdate'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Prepare SQL statement to update data
    $sql = "UPDATE users SET name = ?, address = ?, hometown = ?, birthdate = ?, phone = ?, email = ?, username = ?, password = ?, role = ?, last_access = NOW() WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("sssssssssi", $name, $address, $hometown, $birthdate, $phone, $email, $username, $password, $role, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "User updated successfully.";
    } else {
        echo "Error updating user: " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
    $conn->close();

    // Redirect to manage_users.php
    header("Location: manage_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Người Dùng</title>
    <link href="../assets/css/base.css" rel="stylesheet"> 
</head>
<body>
    <?php include '../includes/header.php'; ?> <!-- Bao gồm tiêu đề -->

    <div class="head-container">
        <div class="main-content" id="edit-user">
            <h1>Chỉnh Sửa Người Dùng</h1>
            <form action="edit_user.php?user_id=<?php echo $user_id; ?>" method="post">
                <div class="form-group">
                    <label for="name">Tên:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="address">Địa chỉ:</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="hometown">Quê quán:</label>
                    <input type="text" id="hometown" name="hometown" value="<?php echo htmlspecialchars($user['hometown']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="birthdate">Ngày sinh:</label>
                    <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Điện thoại:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="username">Tên đăng nhập:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu:</label>
                    <input type="text" id="password" name="password" value="<?php echo htmlspecialchars($user['password']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="role">Vai trò:</label>
                    <select id="role" name="role">
                        <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Quản trị viên</option>
                        <option value="manager" <?php echo ($user['role'] == 'manager') ? 'selected' : ''; ?>>Quản lý</option>
                        <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>Người dùng</option>
                    </select>
                </div>
                <button type="submit">Cập Nhật Người Dùng</button>
            </form>
        </div>
        <?php include '../includes/sidebar.php'; ?> <!-- Bao gồm thanh bên -->
    </div>
</body>
</html>