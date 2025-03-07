    <?php
    session_start();
    require '../config/database.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['payment_id']) || empty($_POST['manager_id']) || empty($_POST['booking_id'])) {
        die("Invalid request.");
    }

    $booking_id = $_POST['booking_id'];
    $payment_id = $_POST['payment_id'];
    $manager_id = $_POST['manager_id'];
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        die("Unauthorized access.");
    }

    $conn->begin_transaction(); 

    try {
        $update_sql = "UPDATE contracts_payment SET status = 'Đã thanh toán', created_date = NOW() WHERE payment_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('i', $payment_id);
        $update_stmt->execute();

        if ($update_stmt->affected_rows <= 0) {
            throw new Exception("No rows updated or payment not found.");
        }
        $update_stmt->close();

        // Danh sách người nhận thông báo
        $recipients = [$user_id, $manager_id];
        $message = "Một thanh toán vừa được xác nhận.";

        // Gửi thông báo
        $notification_sql = "INSERT INTO notifications (user_id, booking_id, message, type, created_at) VALUES (?, ?, ?, 'booking', NOW())";
        $notification_stmt = $conn->prepare($notification_sql);

        foreach ($recipients as $recipient_id) {
            $notification_stmt->bind_param("iis", $recipient_id, $booking_id, $message);
            $notification_stmt->execute();

            if ($notification_stmt->affected_rows <= 0) {
                throw new Exception("Failed to insert notification for user ID $recipient_id.");
            }
        }
        $notification_stmt->close();

        $conn->commit(); 

        header("Location: ../templates/contracts.php?booking_id=" . $booking_id);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . htmlspecialchars($e->getMessage()));
    }

    $conn->close();
    ?>
