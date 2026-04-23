const bindPasswordToggle = (button) => {
    if (button.dataset.passwordToggleBound === 'true') return;

    const target = document.getElementById(button.dataset.passwordToggle);
    if (!target) return;

    const iconShow = button.querySelector('[data-icon="show"]');
    const iconHide = button.querySelector('[data-icon="hide"]');

    button.addEventListener('click', () => {
        const willShow = target.type === 'password';
        target.type = willShow ? 'text' : 'password';
        iconShow.classList.toggle('hidden', willShow);
        iconHide.classList.toggle('hidden', !willShow);
    });

    button.dataset.passwordToggleBound = 'true';
};

document.querySelectorAll('[data-password-toggle]').forEach(bindPasswordToggle);