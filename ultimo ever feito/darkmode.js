(function () {
    var STORAGE_KEY = "darkMode";
    var DARK_CLASS = "dark";
    var DARK_MODE_CLASS = "dark-mode";

    function darkModeIsOn() {
        try {
            return localStorage.getItem(STORAGE_KEY) === "on";
        } catch (erro) {
            return false;
        }
    }

    function saveDarkMode(active) {
        try {
            localStorage.setItem(STORAGE_KEY, active ? "on" : "off");
        } catch (erro) {}
    }

    function updateButtons(active) {
        var buttons = document.querySelectorAll(".toggle-btn, [data-dark-toggle]");

        buttons.forEach(function (button) {
            if (button.hasAttribute("data-keep-text")) {
                return;
            }

            button.textContent = active ? "\u2600\uFE0F" : "\uD83C\uDF19";
            button.setAttribute("aria-pressed", active ? "true" : "false");
        });
    }

    function applyDarkMode(active) {
        document.documentElement.classList.toggle(DARK_CLASS, active);
        document.documentElement.classList.toggle(DARK_MODE_CLASS, active);

        if (document.body) {
            document.body.classList.toggle(DARK_CLASS, active);
            document.body.classList.toggle(DARK_MODE_CLASS, active);
            updateButtons(active);
        }
    }

    function toggleDarkMode() {
        var active = !(document.body || document.documentElement).classList.contains(DARK_CLASS);
        saveDarkMode(active);
        applyDarkMode(active);
    }

    applyDarkMode(darkModeIsOn());

    document.addEventListener("DOMContentLoaded", function () {
        applyDarkMode(darkModeIsOn());
    });

    window.toggleDarkMode = toggleDarkMode;
    window.modoEscuro = toggleDarkMode;
    window.aplicarModoEscuro = applyDarkMode;
})();
