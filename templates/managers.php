<?php
session_start();
include '../admin/getallbooking.php';
include '../admin/getallbuilding.php';
include '../admin/getallroom.php';
include '../admin/getalluser.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$bookings = getAllBookingsOfManagers($month, $year);
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
    <title>Doanh thu quản lý</title>
    <link rel="stylesheet" href="../assets/css/revenue.css">
    <link href="../assets/css/filter.css" rel="stylesheet"> 
    <link href="../assets/css/base.css" rel="stylesheet"> 
    <style>
        
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?> 
    <div class="head-container">
        <div class="main-content" id="edit-booking">
            <div class="manage-head" id="managers">
                <h1>Doanh thu quản lý</h1>
            </div>
            <div class="icon-container">
                <a id="filter-icon" aria-haspopup="true" aria-expanded="false" onclick="toggleFilter()"><img src="../assets/icons/filter.svg"></a>
            </div>
            <form id="filter-section" class="filter-form" method="get" action="managers.php">
                <?php include '../includes/filter_bookings.php' ?>
            </form>
            <hr>
            <h2>Doanh thu tháng <?php echo htmlspecialchars($month)?><?php echo htmlspecialchars($year ? " năm ".$year : "")?></h2>
            <div class="grid-template">
                <div class="grid-item">
                    <h3>Tổng giá trị hợp đồng</h3>
                    <h4 id="revenue"></h4>
                </div>
                <div class="grid-item">
                    <h3>Doanh thu</h3>
                    <h4 id="commission" data-tooltip="Tổng giá trị hợp đồng / 12"></h4>
                </div>
                <div class="grid-item">
                    <h3>Số lượng hợp đồng</h3>
                    <h4 id="number_contracts"></h4>
                </div>
                <div class="grid-item">
                    <h3>Thu nhập hoạt động managers</h3>
                    <h4 id="income_managers" data-tooltip="20% Doanh thu"></h4>
                </div>
            </div>    
            <input id="total_collect" type="hidden" value="0"></input>
            <div style="overflow-x: auto; width: 100%; height: 100%">
                <table>
                    <tr>
                        <th>Mã hợp đồng</th>
                        <th>Tên toà nhà</th>
                        <th>Phòng</th>
                        <th id="sort-manager">Người quản lý <span id="sort-icon1">▲</span></th>
                        <th id="sort-sale">Người sales <span id="sort-icon2">▲</span></th>
                        <th>Giá bán</th>
                        <th>Thời hạn hợp đồng</th>
                        <th>Tổng giá trị hợp đồng</th>
                        <th>Doanh thu</th>
                    </tr>
                    <?php if ($bookings->num_rows > 0): ?>
                        <?php while ($booking = $bookings->fetch_assoc()): ?>
                            <tr id="rows">
                                <?php $contracts = getAllContracts($booking['booking_id']);
                                    $paidContracts = array_filter($contracts->fetch_all(MYSQLI_ASSOC), function ($contract) use ($month) {
                                        return $contract['status'] === 'Đã thanh toán' && date('m', strtotime($contract['created_date'])) == $month;
                                    });

                                    $Total = array_sum(array_column($paidContracts, 'value'));
                                ?>
                                <input id="total_collect" type="hidden" value="<?php echo $Total?>"></input>
                                <td><?php echo htmlspecialchars($booking['booking_id'])?></td>
                                <td><?php echo htmlspecialchars(getInfoBuilding($booking['building_id'])['name']); ?> triệu</td>
                                <td><?php echo htmlspecialchars(getInfoRoom($booking['room_id'])['room_name']); ?></td>
                                <td><?php echo htmlspecialchars(getUsernameById(getInfoBuilding($booking['building_id'])['user_id'])); ?></td>
                                <td><?php echo htmlspecialchars(getUsernameById($booking['user_id'])); ?></td>
                                <td><?php echo htmlspecialchars(formatNumber(getInfoRoom($booking['room_id'])['rental_price']))?> triệu</td>
                                <td><?php echo htmlspecialchars($booking['payment_term'])?></td>
                                <td><?php echo htmlspecialchars(formatNumber($Total))?> triệu</td>
                                <td><?php echo htmlspecialchars(formatNumber($Total/12))?> triệu</td>
                                
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
<script src="../assets/js/sort.js"></script>
<script>
document.getElementById("filter-section").addEventListener("submit", function(event) {
    const inputs = this.querySelectorAll("input, select");
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.removeAttribute("name"); 
        }
    });
});
</script>