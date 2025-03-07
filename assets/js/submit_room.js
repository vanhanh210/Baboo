document.addEventListener('DOMContentLoaded', function () {
    const roomForm = document.querySelector('#lightboxroom form');
    const buildingForm = document.querySelector("#building-form");
    roomForm.addEventListener('submit', function (event) {
        event.preventDefault(); 
        let roomName = document.getElementById('room_name').value;
        let roomPrice = document.getElementById('room_price').value;
        let roomArea = document.getElementById('room_area').value;
        let roomType = document.getElementById('room_type').value;
        let roomStatus = document.getElementById('room_status').value;

        let tableBody = document.getElementById('tableBody');
        let newRow = document.createElement('tr');

        newRow.innerHTML = `
            <td class="room_name">${roomName}</td>
            <td class="room_price">${roomPrice} triệu/tháng</td>
            <td class="room_area">${roomArea} m²</td>
            <td class="room_type">${roomType}</td>
            <td class="room_status">${roomStatus}</td>
            <td class="crud-btn">
                <button class="delete" onclick="deleteRow(this)">
                    <img src="../assets/icons/bin.svg">
                </button>
            </td>
        `;
        tableBody.appendChild(newRow);
        document.getElementById('dataForm').reset();
        closeLightbox();
    });
    
    buildingForm.addEventListener("submit", function (event) {
        event.preventDefault(); 
        collectRoomData();
        this.submit();  
    });
});


function deleteRow(button) {
    button.closest('tr').remove();
}
function collectRoomData() {
    let rooms = [];
    const buildingForm = document.querySelector("#building-form");
    document.querySelectorAll("#tableBody tr").forEach(row => {
        let name = row.querySelector(".room_name")?.textContent.trim() || "";
        let price = row.querySelector(".room_price")?.textContent.replace(" triệu/tháng", "").trim() || "";
        let area = row.querySelector(".room_area")?.textContent.replace(" m²", "").trim() || "";
        let type = row.querySelector(".room_type")?.textContent.trim() || "";
        let status = row.querySelector(".room_status")?.textContent.trim() || "";

        rooms.push({ name, price, area, type, status });
    });

    let hiddenInput = document.querySelector("input[name='rooms']");
    if (!hiddenInput) {
        hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.name = "rooms";
        buildingForm.appendChild(hiddenInput);
    }
    hiddenInput.value = JSON.stringify(rooms);
}

function closeLightbox() {
    let lightboxes = document.querySelectorAll(".lightbox");
    
    lightboxes.forEach(lightbox => {
        lightbox.style.opacity = "0";
        
        lightbox.querySelectorAll(".lightbox-content").forEach(box => {
            box.style.transform = "scale(0.8)";
        });
        setTimeout(() => {
            lightbox.style.display = "none";
        }, 300);
    });
}