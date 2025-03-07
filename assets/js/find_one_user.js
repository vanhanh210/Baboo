document.addEventListener("DOMContentLoaded", function () {
    const userSearchInput = document.getElementById("userSearchInput");
    const userSearchResults = document.getElementById("userSearchResults");
    let items = document.querySelectorAll(".user-dropdown-item");
    let userselected = document.getElementById("user_id");
    const form = document.getElementById("building-form");
    let isUserSelected = false;

    function convertVietnamese(str) {
        return str
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase();
    }

    userSearchInput.addEventListener("input", function () {
        let searchText = convertVietnamese(userSearchInput.value.trim());
        userSearchResults.innerHTML = "";
        userSearchResults.style.display = "none";
        isUserSelected = false;

        if (searchText.length < 1) return;

        let filteredUsers = [];

        items.forEach(item => {
            let userId = item.dataset.userId;
            let username = item.dataset.username;
            let userText = `${userId} - ${username}`;

            if (
                convertVietnamese(username.toLowerCase()).includes(searchText) ||
                userId.includes(searchText)
            ) {
                let div = document.createElement("div");
                div.classList.add("user-dropdown-item");
                div.textContent = userText;
                div.dataset.userId = userId;
                div.dataset.username = username;
                div.addEventListener("click", function () {
                    selectUser(userId, username);
                });
                filteredUsers.push(div);
            }
        });
        if (filteredUsers.length > 0) {
            userSearchResults.style.display = "block";
            filteredUsers.forEach(item => userSearchResults.appendChild(item));
        }
    });

    // Khi chọn một user từ dropdown
    function selectUser(userId, username) {
        userSearchInput.value = `${userId} - ${username}`;
        userSearchResults.style.display = "none";
        isUserSelected = true;
        userselected.value = userId;

    }

    document.addEventListener("click", function (event) {
        if (!userSearchResults.contains(event.target) && !userSearchInput.contains(event.target)) {
            if (!isUserSelected) {
                userSearchInput.value = "";
            }
            userSearchResults.style.display = "none";
        }
    });
    form.addEventListener("submit", function (event) {
        if (!isUserSelected) {
            event.preventDefault();
            userSearchInput.value = "";
            userSearchResults.style.display = "none";
            alert("Tên quản lý không tồn tại, vui lòng chọn lại.");
            return;
        }
    });
});
