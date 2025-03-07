<?php
session_start();
include '../admin/getallbooking.php';
include '../admin/getallbuilding.php';
include '../admin/getallroom.php';

$month = isset($_GET['month']) ? $_GET['month'] : NULL;
$new = isset($_GET['new']) ? $_GET['new'] : NULL;

if (in_array($_SESSION['role'],['admin','manager'])) {
    if ($new) {
        $bookings = getAllBookings(date('m'), 1);
    } else {
        $bookings = getAllBookings($month);
    }
} else {
    if ($new) {
        $bookings = getAllBookingsOfUser($_SESSION['user_id'], NULL, 1);
    } else {
        $bookings = getAllBookingsOfUser($_SESSION['user_id'], $month);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Hợp Đồng</title>
    <link href="../assets/css/filter.css" rel="stylesheet"> 
    <link href="../assets/css/base.css" rel="stylesheet"> 
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="head-container">
        <div class="main-content">
            <div class="manage-head" id="contract">
                <h1>Quản Lý Hợp Đồng</h1>
            </div>
            <?php if (!$new): ?>
                <div class="icon-container">
                    <a id="filter-icon" aria-haspopup="true" aria-expanded="false" onclick="toggleFilter()"><img src="../assets/icons/filter.svg"></a>
                </div>
                <form id="filter-section" class="filter-form" method="get" action="manage_contract.php">
                    <?php include '../includes/filter_bookings.php' ?>
                </form>
            <?php endif; ?>
            <div style="overflow-x: auto; width: 100%;">
            <table>
                <tr>
                    <th>Số hợp đồng</th>
                    <th>Tên khách hàng</th>
                    <th>Toà nhà</th>
                    <th>Tên phòng</th>
                    <th>File hợp đồng</th>
                </tr>
                <?php if ($bookings->num_rows > 0): ?>
                    <?php while ($booking = $bookings->fetch_assoc()): ?>
                        <tr onclick="location.href='contracts.php?booking_id=<?php echo $booking['booking_id']; ?>'" style="cursor: pointer;">
                            <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                            <td><?php echo htmlspecialchars(getInfoBuilding($booking['building_id'])['name']); ?></td>
                            <td><?php echo htmlspecialchars(getInfoRoom($booking['room_id'])['room_name']); ?></td>
                            <td class="crud-btn">
                            <?php if ($booking['photo_urls']): ?>
                                <a href="../includes/view_contract.php?action=download&file=<?php echo urlencode($booking['photo_urls']); ?>" title="xem">
                                    <button title="tải xuống" class="create"><img src="../assets/icons/download.svg"></button>
                                </a>
                                <a href="../includes/view_contract.php?action=view&file=<?php echo urlencode($booking['photo_urls']); ?>" title="xem">
                                    <button title="xem" class="create"><img src="../assets/icons/view.svg"></button>
                                </a>
                            <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">Không tìm thấy hợp đồng</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
        </div>
        <?php include '../includes/sidebar.php'; ?>
    </div>
    <script src="../assets/js/filter.js"></script>
</body>
</html>
<script>
    document.getElementById("filter-section").addEventListener("submit", function(event) {
            const inputs = this.querySelectorAll("input, select");
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.removeAttribute("name"); 
                }
            });
        });
    document.querySelector("#search_box").style.display = 'none';
    document.querySelector("#search_box_sale").style.display = 'none';
</script>