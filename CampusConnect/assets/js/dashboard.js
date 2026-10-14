// assets/js/dashboard.js
document.addEventListener('DOMContentLoaded', function () {
    // Animate progress bar fills on load
    document.querySelectorAll('.progress-bar-fill').forEach(function (bar) {
        var target = bar.getAttribute('data-value') || bar.style.width;
        bar.style.width = '0%';
        requestAnimationFrame(function () {
            bar.style.transition = 'width .6s ease';
            bar.style.width = target;
        });
    });

    // Simple notification polling (every 60s) if unread badge exists
    var badge = document.querySelector('.badge-dot');
    if (badge && window.BASE_URL !== undefined) {
        setInterval(function () {
            fetch(window.BASE_URL + '/api/notifications.php?action=count')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data && typeof data.unread !== 'undefined') {
                        badge.textContent = data.unread;
                        badge.style.display = data.unread > 0 ? 'inline-block' : 'none';
                    }
                })
                .catch(function () { /* silent fail */ });
        }, 60000);
    }
});
