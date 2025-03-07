document.addEventListener('DOMContentLoaded', function() {
    const bell = document.getElementById('notificationBell');
    const notificationBox = document.getElementById('notification-box');

    bell.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationBox.style.display = notificationBox.style.display === 'none' ? 'block' : 'none';
    });

    document.addEventListener('click', function(e) {
        if (!notificationBox.contains(e.target) && e.target !== bell) {
            notificationBox.style.display = 'none';
        }
    });

    document.getElementById('markAllReadButton').addEventListener('click', function() {
    fetch('../admin/mark_all_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notificationItems = document.querySelectorAll('.notification-item');
            notificationItems.forEach(item => {
                item.classList.remove('unread'); 
            });
        }
    })
    .catch((error) => {
        console.error('Error:', error);
    });
    });
});
