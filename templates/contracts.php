<?php
session_start();
require '../admin/getallbooking.php';
include '../admin/getallbuilding.php';

$booking_id = $_GET['booking_id'];
$contracts = getAllContracts($booking_id);

$sql = "SELECT * FROM bookings WHERE booking_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();
function formatNumber($number) {
    $rounded = round($number, 3); 
    return rtrim(rtrim($rounded, '0'), '.'); 
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Hợp Đồng</title>
    <link rel="stylesheet" href="../assets/css/column.css">
    <link href="../assets/css/base.css" rel="stylesheet"> 
</head>
<body>
    <?php include '../includes/header.php'; ?> 
    <div class="head-container">
        <div class="main-content" id="edit-booking">
            <h1>Thông Tin Hợp Đồng</h1>
            <div class="column-container">
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Tên khách hàng:</div>
                        <div class="info-value"><?php echo htmlspecialchars($booking['guest_name']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Số điện thoại:</div>
                        <div class="info-value"><?php echo htmlspecialchars($booking['phone']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">CCCD:</div>
                        <div class="info-value"><?php echo htmlspecialchars($booking['identification_card']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Ngày ký:</div>
                        <div class="info-value"><?php echo htmlspecialchars($booking['signed_date']) ?></div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-row">
                        <div class="info-label">Thời hạn hợp đồng:</div>
                        <div class="info-value"><?php echo htmlspecialchars($booking['payment_term']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Ngày bắt đầu:</div>
                        <div class="info-value"><?php echo htmlspecialchars($booking['lease_start_date']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Ngày kết thúc:</div>
                        <div class="info-value"><?php echo htmlspecialchars($booking['lease_end_date']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Ngày cọc:</div>
                        <div class="info-value"><?php echo htmlspecialchars($booking['deposit_term']) ?></div>
                    </div>
                </div>
            </div>
            <div style="overflow-x: auto; width: 100%;">
                <table>
                    <tr>
                        <th>Kỳ hạn</th>
                        <th>Giá trị hợp đồng</th>
                        <th>Tình trạng thanh toán</th>
                        <th>Doanh thu</th>
                        <?php if ($_SESSION['user_id'] != getInfoBuilding($booking['building_id'])['user_id'] && !in_array($_SESSION['role'], ['manager','admin'])): ?>
                            <th>Xác nhận đã thanh toán</th>
                        <?php endif; ?>
                        </tr>
                    <?php if ($contracts->num_rows > 0): ?>
                        <?php while ($contract = $contracts->fetch_assoc()): ?>
                            <tr>
                                <?php 
                                $payment_mapping = [
                                    '3 tháng 1' => 3,
                                    '4 tháng 1' => 4,
                                    '6 tháng 1' => 6,
                                    '1 năm' => 12
                                ];
                                
                                ?>
                                <td>Tháng <?php echo htmlspecialchars($contract['name']) == 1 ? " đầu tiên" : htmlspecialchars($contract['name']); ?></td>
                                <td><?php echo htmlspecialchars(formatNumber($contract['value'])); ?> triệu</td>
                                <td><?php echo htmlspecialchars($contract['status']); ?></td>
                                <td><?php echo htmlspecialchars(formatNumber($contract['value'] / 12)); ?> triệu</td>
                                <?php if ($_SESSION['user_id'] != getInfoBuilding($booking['building_id'])['user_id'] && !in_array($_SESSION['role'], ['manager','admin'])): ?>
                                    <td>
                                        <form action="../admin/confirm_payment.php" method="post" onsubmit="return confirm('Bạn có chắc chắn rằng bạn đã nhận thanh toán này? (Không thể khôi phục)');" style="display:inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                                            <input type="hidden" name="payment_id" value="<?php echo $contract['payment_id']; ?>">
                                            <input type="hidden" name="manager_id" value="<?php echo getInfoBuilding($booking['building_id'])['user_id']?>">
                                            <button type="submit" class="create" style="width: fit-content; <?php echo ($contract['status'] === 'Chưa thanh toán') ? '' : 'background: gray'; ?>" 
                                                <?php echo ($contract['status'] === 'Chưa thanh toán') ? '' : 'disabled'; ?>>
                                                <?php echo ($contract['status'] === 'Chưa thanh toán') ? 'Xác nhận' : 'Đã xác nhận'; ?>
                                            </button>   
                                        </form>
                                    </td>
                                <?php endif; ?>    
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10">Không tìm thấy toà nhà</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        <?php include '../includes/sidebar.php'; ?>
    </div>
</body>
</html>