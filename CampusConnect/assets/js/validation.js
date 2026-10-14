// assets/js/validation.js
(function () {
    function showError(input, message) {
        clearError(input);
        var err = document.createElement('div');
        err.className = 'field-error';
        err.style.color = '#e0413a';
        err.style.fontSize = '.78rem';
        err.style.marginTop = '.25rem';
        err.textContent = message;
        input.insertAdjacentElement('afterend', err);
        input.style.borderColor = '#e0413a';
    }

    function clearError(input) {
        input.style.borderColor = '';
        var next = input.nextElementSibling;
        if (next && next.classList.contains('field-error')) {
            next.remove();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                var valid = true;

                form.querySelectorAll('[required]').forEach(function (input) {
                    if (!input.value.trim()) {
                        valid = false;
                        showError(input, 'This field is required.');
                    } else {
                        clearError(input);
                    }
                });

                var pwd = form.querySelector('input[name="password"]');
                var confirm = form.querySelector('input[name="confirm_password"]');
                if (pwd && confirm && confirm.value && pwd.value !== confirm.value) {
                    valid = false;
                    showError(confirm, 'Passwords do not match.');
                }

                form.querySelectorAll('input[type="email"]').forEach(function (input) {
                    if (input.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value)) {
                        valid = false;
                        showError(input, 'Enter a valid email address.');
                    }
                });

                if (!valid) {
                    e.preventDefault();
                }
            });
        });
    });
})();
