document.addEventListener("DOMContentLoaded", function () {
    let sortOrder1 = 1;
    let sortOrder2 = 1;
    const sortIcon1 = document.getElementById("sort-icon1");
    const sortIcon2 = document.getElementById("sort-icon2");

    document.getElementById("sort-manager").addEventListener("click", function () {
        let table = document.querySelector("table tbody");
        let rows = Array.from(table.querySelectorAll("tr"));

        rows.forEach((row, index) => row.dataset.index = index);

        let headerRow = table.querySelector("tr:first-child");
        let dataRows = rows.slice(1);

        dataRows.sort((rowA, rowB) => {
            let cellA = rowA.children[4].textContent.trim().toLowerCase(); 
            let cellB = rowB.children[4].textContent.trim().toLowerCase();
            if (cellA === cellB) {
                return rowA.dataset.index - rowB.dataset.index;
            }
            return cellA.localeCompare(cellB) * sortOrder1;
        });

        sortOrder1 *= -1;

        sortIcon1.textContent = sortOrder1 === 1 ? "▲" : "▼";

        table.innerHTML = "";
        table.appendChild(headerRow);
        dataRows.forEach(row => table.appendChild(row));
    });
    document.getElementById("sort-sale").addEventListener("click", function () {
        let table = document.querySelector("table tbody");
        let rows = Array.from(table.querySelectorAll("tr"));

        rows.forEach((row, index) => row.dataset.index = index);

        let headerRow = table.querySelector("tr:first-child");
        let dataRows = rows.slice(1);

        dataRows.sort((rowA, rowB) => {
            let cellA = rowA.children[5].textContent.trim().toLowerCase(); 
            let cellB = rowB.children[5].textContent.trim().toLowerCase();
            if (cellA === cellB) {
                return rowA.dataset.index - rowB.dataset.index;
            }
            return cellA.localeCompare(cellB) * sortOrder2;
        });

        sortOrder2 *= -1;

        sortIcon2.textContent = sortOrder2 === 1 ? "▲" : "▼";

        table.innerHTML = "";
        table.appendChild(headerRow);
        dataRows.forEach(row => table.appendChild(row));
    });

    function formatNumber(value) {
        let rounded = value.toFixed(3); 
        return rounded.replace(/\.?0+$/, ""); 
    }
    
    function calculateStats() {
        let totalRevenue = 0;
        let visibleRows = document.querySelectorAll("table tbody tr:not(.hidden):not(:first-child)"); // Chỉ lấy dòng không bị ẩn, bỏ qua dòng tiêu đề

        visibleRows.forEach(row => {
            let totalCollect = parseFloat(row.querySelector("input[id='total_collect']").value) || 0;
            totalRevenue += totalCollect;
        });

        document.getElementById("revenue").textContent = formatNumber(totalRevenue) + " triệu";
        document.getElementById("commission").textContent = formatNumber(totalRevenue / 12) + " triệu";
        document.getElementById("number_contracts").textContent = visibleRows.length;
        if (document.getElementById("income_sales")) {
            document.getElementById("income_sales").textContent = formatNumber(totalRevenue /12 * 0.8) + " triệu";
        } else {
            document.getElementById("income_managers").textContent = formatNumber(totalRevenue / 12 * 0.2) + " triệu";
        }
    }

    calculateStats(); 

    document.getElementById("search_box").addEventListener("input", function () {
        let searchValue = removeVietnameseTones(this.value.trim().toLowerCase());
        let rows = document.querySelectorAll("table tbody tr:not(:first-child)");

        rows.forEach(row => {
            let managerName = removeVietnameseTones(row.children[4].textContent.trim().toLowerCase());
            if (managerName.includes(searchValue)) {
                row.classList.remove("hidden");
            } else {
                row.classList.add("hidden");
            }
        });

        calculateStats(); 
    });
    document.getElementById("search_box_sale").addEventListener("input", function () {
        let searchValue = removeVietnameseTones(this.value.trim().toLowerCase());
        let rows = document.querySelectorAll("table tbody tr:not(:first-child)");

        rows.forEach(row => {
            let managerName = removeVietnameseTones(row.children[5].textContent.trim().toLowerCase());
            if (managerName.includes(searchValue)) {
                row.classList.remove("hidden");
            } else {
                row.classList.add("hidden");
            }
        });

        calculateStats(); 
    });

    function removeVietnameseTones(str) {
        return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/đ/g, "d").replace(/Đ/g, "D");
    }
});

