//dropdownmenu//

function toggleDropdown() {
    var dropdown = document.getElementById("userDropdownMenu");
    var button = document.querySelector('.user-dropdown button');
    var isOpen = dropdown.style.display === 'block';
    dropdown.style.display = isOpen ? 'none' : 'block';
    button.setAttribute('aria-expanded', !isOpen);
}


//sidebar//

var acc = document.getElementsByClassName("accordion-toggle");
for (var i = 0; i < acc.length; i++) {
    acc[i].addEventListener("click", function () {
        var panel = this.nextElementSibling;

        // Close all panels before opening the clicked one (optional)
        document.querySelectorAll(".accordion-content").forEach(function (content) {
            if (content !== panel) {
                content.style.maxHeight = null; // Collapse other panels
            }
        });

        // Toggle current panel
        if (panel.style.maxHeight) {
            panel.style.maxHeight = null; // Close
        } else {
            panel.style.maxHeight = panel.scrollHeight + "px"; // Open
        }
    });
}


//switchTab
function switchTab(type) {
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.box').forEach(box => box.classList.remove('active'));

    if (type === 'admin') {
        document.querySelector('.tab-container .tab:nth-child(2)').classList.add('active');
        document.getElementById('admin-notifications').classList.add('active');
    } else if (type === 'private') {
        document.querySelector('.tab-container .tab:nth-child(3)').classList.add('active');
        document.getElementById('manager-notifications').classList.add('active');
    } else {
        document.querySelector('.tab-container .tab:nth-child(1)').classList.add('active');
        document.getElementById('system-notifications').classList.add('active');
    }
}

