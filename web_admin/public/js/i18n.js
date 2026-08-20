(function () {
    const translations = {
        en: {
            home: "Home",
            services: "Services",
            howItWorks: "How It Works",
            trackOrder: "Track Order",
            privacyPolicy: "Privacy Policy",
            termsOfUse: "Terms of Use",
            contactUs: "Contact Us",
            copyright: "© 2026 Cool Clean Sdn Bhd. All rights reserved.",
            adminLogin: "Admin Login",
            heroTitle: "Laundry pickup and delivery made simple.",
            heroText: "CoolClean helps busy customers book laundry service, pay online, track progress, and receive clean clothes back at their doorstep.",
            bookMobile: "Book From Mobile",
            bookMobileText: "Customer selects service, pickup address, and preferred time.",
            driverPickup: "Driver Pickup",
            driverPickupText: "Driver accepts the paid booking and collects the clothes.",
            statusTracking: "Status Tracking",
            statusTrackingText: "Customer follows washing, drying, returning, and completed status.",
            adminMonitoring: "Admin Monitoring",
            adminMonitoringText: "Admin monitors bookings, drivers, customers, services, and reports.",
            loginTitle: "Secure admin access",
            loginText: "Only authorized administrators can access booking, customer, driver, payment, and report information.",
            securityNote: "Security note: this login page uses Spring Security, encrypted passwords, protected sessions, and CSRF protection.",
            email: "Email",
            password: "Password",
            login: "Login",
            backHome: "Back to Home",
            passwordPolicy: "Password must contain uppercase letters, lowercase letters, and numbers.",
            passwordOk: "Password format looks valid.",
            passwordBad: "Use uppercase, lowercase, and numbers.",
            invalidLogin: "Invalid email or password.",
            logout: "You have logged out.",
            dashboard: "Dashboard",
            bookings: "Bookings",
            customers: "Customers",
            drivers: "Drivers",
            liveOperations: "Live Operations",
            laundryServices: "Services",
            laundryLocations: "Laundry Locations",
            payments: "Payments",
            driverPayouts: "Driver Payouts",
            reports: "Reports",
            logoutButton: "Logout"
        },
        ms: {
            home: "Utama",
            services: "Servis",
            howItWorks: "Cara Guna",
            trackOrder: "Semak Pesanan",
            privacyPolicy: "Polisi Privasi",
            termsOfUse: "Terma Penggunaan",
            contactUs: "Hubungi Kami",
            copyright: "© 2026 Cool Clean Sdn Bhd. Hak cipta terpelihara.",
            adminLogin: "Log Masuk Admin",
            heroTitle: "Servis ambil dan hantar dobi dengan mudah.",
            heroText: "CoolClean membantu pelanggan menempah servis dobi, membuat bayaran, menyemak status, dan menerima pakaian bersih di depan pintu.",
            bookMobile: "Tempah Melalui Telefon",
            bookMobileText: "Pelanggan memilih servis, alamat pickup, dan masa pilihan.",
            driverPickup: "Pickup Oleh Pemandu",
            driverPickupText: "Pemandu menerima tempahan yang telah dibayar dan mengambil pakaian.",
            statusTracking: "Semakan Status",
            statusTrackingText: "Pelanggan menyemak status basuh, kering, penghantaran balik, dan siap.",
            adminMonitoring: "Pemantauan Admin",
            adminMonitoringText: "Admin memantau tempahan, pemandu, pelanggan, servis, dan laporan.",
            loginTitle: "Akses admin yang selamat",
            loginText: "Hanya admin yang dibenarkan boleh mengakses maklumat tempahan, pelanggan, pemandu, bayaran, dan laporan.",
            securityNote: "Nota keselamatan: halaman log masuk ini menggunakan Spring Security, kata laluan terenkripsi, sesi terlindung, dan perlindungan CSRF.",
            email: "Emel",
            password: "Kata Laluan",
            login: "Log Masuk",
            backHome: "Kembali ke Laman Utama",
            passwordPolicy: "Kata laluan mesti mengandungi huruf besar, huruf kecil, dan nombor.",
            passwordOk: "Format kata laluan sah.",
            passwordBad: "Gunakan huruf besar, huruf kecil, dan nombor.",
            invalidLogin: "Emel atau kata laluan tidak sah.",
            logout: "Anda telah log keluar.",
            dashboard: "Papan Pemuka",
            bookings: "Tempahan",
            customers: "Pelanggan",
            drivers: "Pemandu",
            liveOperations: "Operasi Langsung",
            laundryServices: "Servis",
            laundryLocations: "Lokasi Dobi",
            payments: "Bayaran",
            driverPayouts: "Bayaran Pemandu",
            reports: "Laporan",
            logoutButton: "Log Keluar"
        }
    };

    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]+$/;

    function setLanguage(lang) {
        const selected = translations[lang] ? lang : "en";
        localStorage.setItem("coolcleanLanguage", selected);
        document.documentElement.lang = selected === "ms" ? "ms" : "en";

        document.querySelectorAll("[data-i18n]").forEach((element) => {
            const key = element.getAttribute("data-i18n");
            if (translations[selected][key]) {
                element.textContent = translations[selected][key];
            }
        });

        document.querySelectorAll(".language-select").forEach((select) => {
            select.value = selected;
        });

        updatePasswordMessage();
    }

    function updatePasswordMessage() {
        const passwordInput = document.querySelector("[data-password-policy]");
        const message = document.querySelector("[data-password-message]");
        if (!passwordInput || !message) {
            return;
        }

        const lang = localStorage.getItem("coolcleanLanguage") || "en";
        const isValid = passwordRegex.test(passwordInput.value);
        message.textContent = isValid ? translations[lang].passwordOk : translations[lang].passwordBad;
        message.classList.toggle("valid", isValid);
    }

    document.addEventListener("DOMContentLoaded", () => {
        const savedLanguage = localStorage.getItem("coolcleanLanguage") || "en";
        setLanguage(savedLanguage);

        document.querySelectorAll(".language-select").forEach((select) => {
            select.addEventListener("change", (event) => setLanguage(event.target.value));
        });

        const passwordInput = document.querySelector("[data-password-policy]");
        if (passwordInput) {
            passwordInput.addEventListener("input", updatePasswordMessage);
        }
    });
})();
