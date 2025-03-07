<?php 
require '../config/database.php';
?>
<div class="two-column">
    <div class="flex-wrap">
        <span id="close-btn" onclick="toggleFilter()">&times;</span>
        <div class="form-group">
            <label for="month">Lọc tháng</label>
            <input type="number" min=1 max=12 id="month" name="month" placeholder="Tất cả">
        </div>
        <div class="form-group">
            <label for="month">Lọc năm</label>
            <input type="number" min=0 id="year" name="year" placeholder="Tất cả">
        </div>
        <div class="form-group">
            <label for="submit">&#160;</label>
            <button type="submit" style="width: 100px">Lọc</button>
        </div>
    </div>
    <div class="flex-wrap">
        <input type="text" class="search_box" id="search_box" name="exename" class="searchbox" placeholder="tìm kiếm bằng tên quản lý">
        <input type="text" class="search_box" id="search_box_sale" name="salename" class="searchbox" placeholder="tìm kiếm bằng tên sale">
    </div>
</div>
<script>
    document.querySelectorAll("#search_box, #search_box_sale").forEach(input => {
        input.addEventListener("keypress", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
            }
        });
    });
</script>

