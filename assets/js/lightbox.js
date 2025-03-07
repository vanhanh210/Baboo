function openLightbox(button) {
    const name = button.getAttribute('data-name') || "";
    const price = button.getAttribute('data-price') || "";
    const area = button.getAttribute('data-area') || "";
    const type = button.getAttribute('data-type') || "";
    const status = button.getAttribute('data-status') || "";

    let lightbox = document.querySelector("#lightboxroom","#lightboxviewroom");
    if (!lightbox) {
        console.error("⚠️ Lightbox element not found!");
        return;
    }

    lightbox.style.display = 'flex';

    setTimeout(() => {
        lightbox.style.opacity = "1";
        lightbox.querySelectorAll(".lightbox-content").forEach(box => {
            box.style.transform = "scale(1)";
        });
    }, 10);
    document.getElementById('room_id').value = button.getAttribute('data-id');
    document.getElementById('room_name').value = name;
    document.getElementById('room_price').value = price;
    document.getElementById('room_area').value = area;
    document.getElementById('room_status').value = status;
    document.getElementById('room_type').value = type;
    
}

function markAsRead(notificationId, message, createdAt) {
    fetch('../admin/mark_as_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + notificationId
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            let lightbox = document.getElementById('lightboxview');
            if (!lightbox) {
                console.error("⚠️ Notification lightbox not found!");
                return;
            }

            lightbox.style.display = 'flex';

            document.getElementById('notif-message').innerHTML = message;
            document.getElementById('notif-time').innerText = createdAt;
            document.getElementById('notif-' + notificationId)?.classList.remove('unread');

            setTimeout(() => {
                lightbox.style.opacity = "1";
                lightbox.querySelectorAll(".lightbox-content").forEach(box => {
                    box.style.transform = "scale(1)";
                });
            }, 10);

            let notifbox = document.getElementById("notification-box");
            if (notifbox) notifbox.style.display = "none";
        }
    });
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
