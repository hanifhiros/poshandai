import './bootstrap';


document.addEventListener('DOMContentLoaded', function () {
    const savedTheme = localStorage.getItem('theme') || 'light';

    document.documentElement.setAttribute('data-theme', savedTheme);

    const themeObserver = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.attributeName === 'data-theme') {
                localStorage.setItem('theme', document.documentElement.getAttribute('data-theme'));
            }
        });
    });

    themeObserver.observe(document.documentElement, {
        attributes: true
    });
});
