document.addEventListener('DOMContentLoaded', function () {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const loginForm = document.querySelector('.auth-form__body');

    document.querySelector('[data-password-toggle]')?.addEventListener('click', function () {
        if (!passwordInput) {
            return;
        }

        const show = passwordInput.type === 'password';
        passwordInput.type = show ? 'text' : 'password';
        this.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });

    document.querySelectorAll('[data-demo-login]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (button.disabled || !loginForm) {
                return;
            }

            const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const tokenInput = loginForm.querySelector('input[name="_token"]');

            if (tokenInput && metaToken) {
                tokenInput.value = metaToken;
            }

            if (emailInput) {
                emailInput.value = button.dataset.email || '';
            }

            if (passwordInput) {
                passwordInput.value = button.dataset.password || '';
            }

            button.disabled = true;
            loginForm.requestSubmit();
        });
    });

    document.querySelectorAll('[data-locale-menu]').forEach(function (root) {
        const trigger = root.querySelector('[data-locale-trigger]');
        const panel = root.querySelector('[data-locale-panel]');

        if (!trigger || !panel) {
            return;
        }

        const close = function () {
            panel.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
        };

        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const open = panel.hidden;
            panel.hidden = !open;
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) {
                close();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                close();
            }
        });
    });

    if (window.innerWidth < 768 && emailInput) {
        emailInput.removeAttribute('autofocus');
    }
});
