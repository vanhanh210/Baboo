<?php
session_start();
require '../config/database.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($conn !== null && !$conn->connect_error) {
        $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $user['password'] === $password) {  // Check for plain text password match
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];  // Make sure this line correctly assigns the role from the database
            // Update the last_access column
            $stmt = $conn->prepare("UPDATE users SET last_access = NOW() WHERE user_id = ?");
            $stmt->bind_param("i", $user['user_id']);
            $stmt->execute();
            $stmt->close();
            
            header("Location: ../templates/home.php");
            exit();
        } else {
            $error = 'Đăng nhập thất bại, mật khẩu sai';
        }
        $stmt->close();
    } else {
        $error = 'Database connection is not available.';
    }
    $conn->close();
}

// Only show the login form if POST hasn't been submitted or it failed
if ($_SERVER["REQUEST_METHOD"] != "POST" || !empty($error)) :
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/base.css">
</head>
<body class ="login">
    <div class="contact"><img src="../assets/icons/phone.svg">(+84) xxx xxx</div>
    <div class="login-container">
        <div class="login-logo-container">
        <img class="logo-img" src="https://lh3.googleusercontent.com/d/1ubPF3d8jKvmjGSkyGloz8FEOI1ah97T3" alt="Company Logo">
        </div>
        <h2>Baboo House</h2>
        <h3><b>Đăng nhập</b></h3>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="./login.php" method="POST">
            <div class="form-group">
                <label for="username">Tên đăng nhập:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Mật Khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>
<?php endif; ?>
