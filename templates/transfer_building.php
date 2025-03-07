<?php
session_start();
include '../admin/getalluser.php';
include '../admin/getallbuilding.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $selected_buildings = isset($_POST['selected_buildings']) ? json_decode($_POST['selected_buildings'], true) : [];

    $building_ids = implode(',', array_map('intval', $selected_buildings));

    $sql_notify = "INSERT INTO notifications (user_id, building_id, message, type, created_at) VALUES (?, ?, ?, 'building', NOW())";
    $stmt_notify = $conn->prepare($sql_notify);

    foreach ($selected_buildings as $building_id) {
        $building_info = getInfoBuilding($building_id);
        $message = "Quản lý đã lấy lại quyền quản lý tòa nhà [" . $building_info['name'] . "]";

        $stmt_notify->bind_param("iis", getInfoBuilding($building_id)['user_id'], $building_id, $message);
        $stmt_notify->execute();
    }
    $stmt_notify->close();

    $sql = "UPDATE buildings SET user_id = ? WHERE building_id IN ($building_ids)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Chuyển nhượng thành công!";        
        $sql_notify = "INSERT INTO notifications (user_id, building_id, message, type, created_at) VALUES (?, ?, ?, 'building', NOW())";
        $stmt_notify = $conn->prepare($sql_notify);

        foreach ($selected_buildings as $building_id) {
            $building_info = getInfoBuilding($building_id);
            $message = "Quản lý đã chuyển nhượng tòa nhà [" . $building_info['name'] . "] cho bạn";

            $stmt_notify->bind_param("iis", $user_id, $building_id, $message);
            $stmt_notify->execute();
        }
        $stmt_notify->close();
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra. Vui lòng thử lại.";
    }
    $stmt->close();
    
    header("Location: manage_buildings.php");
    exit();
}


$user_name_list = getAllUsersName();
$users = [];
while ($row = $user_name_list->fetch_assoc()) {
    $users[] = ["user_id" => $row["user_id"], "username" => $row["username"]];
}

$building_name_list = getAllBuildingsName();
$buildings = [];
while ($row = $building_name_list ->fetch_assoc()) {
    $buildings[] = ["building_id" => $row["building_id"], "name" => $row["name"]];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chuyển nhượng toà nhà</title>
    <link href="../assets/css/base.css" rel="stylesheet"> 
</head>
<body>
    <?php include '../includes/header.php'; ?> 

    <div class="head-container">
        <div class="main-content" id="edit-building">
            <h1>Chuyển nhượng Toà Nhà</h1>
            <form id="building-form" action="transfer_building.php" method="post">
                <div class="form-group">
                    <div id="building_section">
                        <label for="searchInput">Toà:</label>
                        <div>
                            <input type="text" id="searchInput" name="searchInput" placeholder="Tìm kiếm tên toà/ ID toà ..." autocomplete="off"/>
                            <div id="searchResults" class="dropdown-search">
                                <?php foreach ($buildings as $building) : ?>    
                                    <div class="dropdown-item" onclick="addBuildingTag('<?= $building['building_id'] ?>', '<?= $building['name'] ?>')">
                                        <?= $building['building_id'] . " - " . $building['name'] ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div id="selectedBuildings" class="selected-buildings"></div>
                    <input type="hidden" id="selected_buildings" name="selected_buildings" />
                </div>
                <div class="form-group">
                    <label for="userSearchInput">Người tiếp quản:</label>
                    <input type="text" id="userSearchInput" name="userSearchInput" placeholder="Tìm kiếm tên/ ID người dùng ..." autocomplete="off"/>

                    <div id="userSearchResults" class="user-dropdown-search">
                        <?php foreach ($users as $user) : ?>
                            <div class="user-dropdown-item" data-user-id="<?= $user['user_id'] ?>" data-username="<?= $user['username'] ?>">
                                <?= $user['user_id'] . " - " . $user['username'] ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="user_id" name="user_id">
                </div>
                <button type="submit">Chuyển nhượng</button>
                <button type="button" class="cancel-btn" onclick="window.history.back();">Hủy</button>
            </form>
        </div>
        <?php include '../includes/sidebar.php'; ?>
    </div>
    <script src="../assets/js/find_buildings.js"></script>
    <script src="../assets/js/find_one_user.js"></script>
</body>
</html>