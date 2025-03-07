<?php
$current_page = $_SERVER['REQUEST_URI']; // Get full URL with query parameters
?>

<div class="sidebar">
    <div class="logo-container">
        <a href="../templates/home.php">
            <img class="logo-img" src="https://lh3.googleusercontent.com/d/1ubPF3d8jKvmjGSkyGloz8FEOI1ah97T3" alt="Company Logo">
        </a>
    </div>
    <ul>
        <li>
            <a href="../templates/accommodation_info.php"
                class="<?= strpos($current_page, 'accommodation_info.php') !== false ? 'active' : '' ?>">
                Thông tin lưu trú
            </a>
        </li>
        <li>
            <a href="../templates/manage_buildings.php"
                class="<?= strpos($current_page, 'manage_buildings.php') !== false ? 'active' : '' ?>">
                Quản lý toà nhà
            </a>
        </li>

        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['manager', 'admin'])): ?>
        <li>
            <a href="../templates/pending_buildings.php"
                class="<?= strpos($current_page, 'pending_buildings.php') !== false ? 'active' : '' ?>">
                Toà nhà chưa duyệt
            </a>
        </li>
        <?php endif; ?>

        <!-- Accordion for Revenue -->
        <li class="accordion-item">
            <a href="javascript:void(0)"
                class="accordion-toggle <?= (strpos($current_page, 'sales.php') !== false || strpos($current_page, 'managers.php') !== false) ? 'active' : '' ?>">
                Doanh thu
            </a>
            <div class="accordion-content"
                style="<?= (strpos($current_page, 'sales.php') !== false || strpos($current_page, 'managers.php') !== false) ? 'display: block;' : '' ?>">
                <a href="../templates/sales.php">
                    Doanh thu Sale
                </a>
                <a href="../templates/managers.php">
                    Quản lý
                </a>
            </div>
        </li>

        <li>
            <a href="../templates/manage_contract.php"
                class="<?= strpos($current_page, 'manage_contract.php?new=1') === false && strpos($current_page, 'manage_contract.php') !== false ? 'active' : '' ?>">
                Hợp đồng
            </a>
        </li>
        <li>
            <a href="../templates/manage_contract.php?new=1"
                class="<?= strpos($current_page, 'manage_contract.php?new=1') !== false ? 'active' : '' ?>">
                Hợp đồng mới
            </a>
        </li>

        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['manager', 'admin'])): ?>
        <li>
            <a href="../templates/manage_users.php"
                class="<?= strpos($current_page, 'manage_users.php') !== false ? 'active' : '' ?>">
                Quản lý người dùng
            </a>
        </li>
        <li>
            <a href="../templates/post.php" class="<?= strpos($current_page, 'post.php') !== false ? 'active' : '' ?>">
                Đăng bài
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<script src="../assets/js/script.js"></script>