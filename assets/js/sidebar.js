function toggleSidebar() {
    var sidebar = document.querySelector('.sidebar');
    var button = document.getElementById('sidebar-icon');
    sidebar.classList.toggle('active');
    button.setAttribute('aria-expanded', sidebar.classList.contains('active'));
}

function toggleFilter() {
    var filter_form = document.querySelector('.filter-form');
    var button = document.getElementById('filter-icon');
    var clsbutton = document.getElementById('close-btn');

    filter_form.classList.toggle('active');

    button.setAttribute('aria-expanded', filter_form.classList.contains('active'));

    button.classList.toggle('hidden', filter_form.classList.contains('active'));
    clsbutton.classList.toggle('hidden', !filter_form.classList.contains('active'));
}