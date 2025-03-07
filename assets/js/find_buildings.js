document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const searchResults = document.getElementById("searchResults");
    const selectedBuildingsDiv = document.getElementById("selectedBuildings");
    const form = document.getElementById("building-form");
    function getAllBuildingsFromDOM() {
        let items = document.querySelectorAll(".dropdown-item");
        let buildingsArray = [];

        items.forEach(item => {
            let text = item.innerHTML.trim();
            let parts = text.split(" - ");
            if (parts.length === 2) {
                buildingsArray.push({
                    building_id: parts[0].trim(),
                    name: parts[1].trim()
                });
            }
        });

        return buildingsArray;
    }
    let allBuildings = getAllBuildingsFromDOM();
    let selectedBuildings = new Set();

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

        let filteredBuildings = allBuildings.filter(building =>
            convertVietnamese(building.name.toLowerCase()).includes(searchText) ||
            building.building_id.toString().includes(searchText)
        );

        if (filteredBuildings.length > 0) {
            searchResults.style.display = "block";
            filteredBuildings.forEach(building => {
                let div = document.createElement("div");
                div.classList.add("dropdown-item");
                div.textContent = `${building.building_id} - ${building.name}`;
                div.addEventListener("click", function () {
                    addBuildingTag(building.building_id, building.name);
                    searchInput.value = "";
                    searchResults.style.display = "none";
                });
                searchResults.appendChild(div);
            });
        }
    });

    function addBuildingTag(buildingId, name) {
        if (selectedBuildings.has(buildingId)) return;

        let tag = document.createElement("span");
        tag.classList.add("user-tag");
        tag.textContent = `${buildingId} - ${name}`;
        tag.dataset.buildingId = buildingId;

        let removeBtn = document.createElement("span");
        removeBtn.textContent = " ✖";
        removeBtn.classList.add("remove-tag");
        removeBtn.addEventListener("click", function () {
            tag.remove();
            selectedBuildings.delete(buildingId);
        });

        tag.appendChild(removeBtn);
        selectedBuildingsDiv.appendChild(tag);
        selectedBuildings.add(buildingId);
    }

    document.addEventListener("click", function (event) {
        if (!searchResults.contains(event.target) && !searchInput.contains(event.target)) {
            searchResults.style.display = "none";
        }
    });

    form.addEventListener("submit", function (event) {
        if (selectedBuildings.size === 0) {
            event.preventDefault();
            alert("Vui lòng chọn ít nhất một toà nhà nếu bạn muốn chuyển nhượng.");
            return;
        }
        document.getElementById("selected_buildings").value = JSON.stringify(Array.from(selectedBuildings));
    });
});