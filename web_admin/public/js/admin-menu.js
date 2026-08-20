document.addEventListener("DOMContentLoaded", () => {
    const bindMenu = (toggleSelector, navSelector) => {
        const toggle = document.querySelector(toggleSelector);
        const nav = document.querySelector(navSelector);

        if (!toggle || !nav) {
            return;
        }

        toggle.addEventListener("click", () => {
            const isOpen = nav.classList.toggle("open");
            toggle.classList.toggle("open", isOpen);
            toggle.setAttribute("aria-expanded", String(isOpen));
        });
    };

    bindMenu("[data-menu-toggle]", "[data-admin-nav]");
    bindMenu("[data-public-menu-toggle]", "[data-public-nav]");
});
