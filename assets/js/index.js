(function () {
    var root = document.documentElement;
    var themeToggle = document.getElementById("theme-toggle");
    var recordForm = document.getElementById("record-form");
    var modal = document.getElementById("saved-modal");
    var okButton = document.getElementById("saved-modal-ok");

    if (recordForm) {
        var uppercaseFields = recordForm.querySelectorAll('input[type="text"], textarea');

        for (var index = 0; index < uppercaseFields.length; index += 1) {
            uppercaseFields[index].addEventListener("input", function () {
                this.value = this.value.toUpperCase();
            });

            if (uppercaseFields[index].value) {
                uppercaseFields[index].value = uppercaseFields[index].value.toUpperCase();
            }
        }
    }

    if (themeToggle) {
        var updateThemeToggle = function () {
            var isDark = root.classList.contains("dark-theme");
            themeToggle.textContent = isDark ? "Light Mode" : "Dark Mode";
            themeToggle.setAttribute("aria-pressed", isDark ? "true" : "false");
        };

        themeToggle.addEventListener("click", function () {
            root.classList.toggle("dark-theme");

            try {
                window.localStorage.setItem("systemMonitoringTheme", root.classList.contains("dark-theme") ? "dark" : "light");
            } catch (error) {
            }

            updateThemeToggle();
        });

        updateThemeToggle();
    }

    if (!modal || !okButton) {
        return;
    }

    document.body.classList.add("modal-open");

    var closeModal = function () {
        modal.style.display = "none";
        document.body.classList.remove("modal-open");

        if (window.history && typeof window.history.replaceState === "function" && typeof URL === "function") {
            var url = new URL(window.location.href);
            url.searchParams.delete("saved");
            var query = url.searchParams.toString();
            var nextUrl = url.pathname + (query ? "?" + query : "") + url.hash;
            window.history.replaceState({}, document.title, nextUrl);
        }
    };

    okButton.addEventListener("click", function (event) {
        event.preventDefault();
        closeModal();
    });

    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && modal.style.display !== "none") {
            closeModal();
        }
    });

    okButton.focus();
}());
