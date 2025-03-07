<?php 
session_start(); 
require '../config/database.php';
include '../admin/getalluser.php';

$user_name_list = getAllUsersName();

$users = [];
while ($row = $user_name_list->fetch_assoc()) {
    $users[] = ["user_id" => $row["user_id"], "username" => $row["username"]];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng bài</title>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <link href="../assets/css/base.css" rel="stylesheet"> 
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="head-container">
        <div class="main-content" id="create-building">
            <div class="manage-head" id="post">
                <h1>Thêm thông báo mới</h1>      
            </div>
            <form id="email-form" action="../admin/process_create_post.php" method="post">
                <input type="hidden" id="selected_users" name="selected_users">
                <div class="form-group">
                    <label for="title">Tiêu đề: </label>
                    <input type="text" maxlength="30" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label>Đối tượng:</label>
                    <div id="public_section">
                        <input type="checkbox" id="public" name="public" checked>
                        <label for="public">Công khai</label>
                    </div>  
                    
                    <div id="private_section">
                        <input type="checkbox" id="private" name="private">
                        <label for="private_user">Đến:</label>
                        <div class="search-container">
                            <input type="text" id="searchInput" placeholder="Tìm kiếm người dùng..." disabled />
                            <div id="searchResults" class="dropdown-search"></div>
                        </div>
                    </div>
                    <div class="selected-users" id="selectedUsers"></div>
                </div>
                <div class="form-group">
                    <label for="editor">Nội dung:</label>
                    <div id="editor"></div>
                    <input type="hidden" id="content" name="content">
                </div>  
                <button type="submit" onclick='document.getElementById("content").value = quill.root.innerHTML;'>Đăng</button>
            </form>

        </div>
        <?php include '../includes/sidebar.php'; ?> 
    </div>

    <script>
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Nhập nội dung thông báo...',
            modules: {
                toolbar: [
                    [{ header: [1, 2, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['clean']
                ]
            }
        });
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const publicCheckbox = document.getElementById("public");
        const privateCheckbox = document.getElementById("private");
        const privateUserInput = document.getElementById("private_user");
        const searchInput = document.getElementById("searchInput");
        const searchResults = document.getElementById("searchResults");
        const selectedUsersDiv = document.getElementById("selectedUsers");
        const form = document.getElementById("email-form");
        let allUsers = <?php echo json_encode($users); ?>;
        let selectedUsers = new Set();

        function on_public() {
            privateCheckbox.checked = false;
            searchInput.disabled = true;
            searchResults.innerHTML = "";
            selectedUsersDiv.innerHTML = "";
            searchInput.value = "";
            selectedUsers.clear();
        }

        publicCheckbox.addEventListener("change", function () {
            if (this.checked) {
                on_public();
            } else {
                privateCheckbox.checked = true;
                publicCheckbox.checked = false;
                searchInput.disabled = false;
            }
        });

        privateCheckbox.addEventListener("change", function () {
            if (this.checked) {
                privateCheckbox.checked = true;
                publicCheckbox.checked = false;
                searchInput.disabled = false;
            } else {
                publicCheckbox.checked = true;
                on_public();
            }
        });

            function convertVietnamese(str) {
                return str
                    .normalize("NFD") 
                    .replace(/[\u0300-\u036f]/g, "") 
                    .toLowerCase();
            }
        searchInput.addEventListener("input", function () {
            let searchText = convertVietnamese(searchInput.value.trim());
            searchResults.innerHTML = "";
            searchResults.style.display = "none";

            if (searchText.length < 1) return;
            let filteredUsers = allUsers.filter(user => 
                convertVietnamese(user.username.toLowerCase()).includes(searchText) || 
                user.user_id.toString().includes(searchText)
            );

            if (filteredUsers.length > 0) {
                searchResults.style.display = "block";
                filteredUsers.forEach(user => {
                    let div = document.createElement("div");
                    div.classList.add("dropdown-item");
                    div.textContent = `${user.user_id} - ${user.username}`;
                    div.addEventListener("click", function () {
                        addUserTag(user.user_id, user.username);
                        searchInput.value = "";
                        searchResults.style.display = "none";
                    });
                    searchResults.appendChild(div);
                });
            }
        });

        function addUserTag(userId, username) {
            if (selectedUsers.has(userId)) return;
            let tag = document.createElement("span");
            tag.classList.add("user-tag");
            tag.textContent = `${userId} - ${username}`;
            tag.dataset.userId = userId; 

            let removeBtn = document.createElement("span");
            removeBtn.textContent = " ✖";
            removeBtn.classList.add("remove-tag");
            removeBtn.addEventListener("click", function () {
                tag.remove();
                selectedUsers.delete(userId);
            });

            tag.appendChild(removeBtn);
            selectedUsersDiv.appendChild(tag);
            selectedUsers.add(userId);
        }

        document.addEventListener("click", function (event) {
            if (!searchResults.contains(event.target) && !searchInput.contains(event.target)) {
                searchResults.style.display = "none";
            }
        });
        form.addEventListener("submit", function (event) {
            if (privateCheckbox.checked && selectedUsers.size === 0) {
                event.preventDefault(); 
                alert("Vui lòng chọn ít nhất một người dùng nếu bạn muốn gửi riêng tư.");
                return;
            }
            document.getElementById("selected_users").value = JSON.stringify(Array.from(selectedUsers));
        });
    });
    </script>
</body>
</html>
