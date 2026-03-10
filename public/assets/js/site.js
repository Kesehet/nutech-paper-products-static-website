(function () {
    var toggle = document.getElementById("mobile-nav-toggle");
    var nav = document.getElementById("mobile-nav");

    if (!toggle || !nav) {
        return;
    }

    toggle.addEventListener("click", function () {
        nav.classList.toggle("hidden");
    });
})();

