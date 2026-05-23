(function () {
    try {
        if (window.localStorage && window.localStorage.getItem("systemMonitoringTheme") === "dark") {
            document.documentElement.classList.add("dark-theme");
        }
    } catch (error) {
    }
}());
