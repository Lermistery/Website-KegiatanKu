const themeSwitch = document.getElementById('theme-switch');
if (themeSwitch) {
    themeSwitch.addEventListener('change', function () {
        document.body.classList.toggle('dark-mode');
    });
}

const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebar = document.querySelector('.sidebar');
if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
        sidebar.classList.toggle('show');
    });
}

if (window.AOS) {
    AOS.init({
        duration: 800,
        once: true
    });
}

// Optional: Highlight active menu (for demo, assuming Dashboard is active)
document.addEventListener('DOMContentLoaded', function () {

    // Animate stats counters
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const target = parseInt(stat.getAttribute('data-target'));
        let count = 0;
        const increment = target / 100;
        const timer = setInterval(() => {
            count += increment;
            if (count >= target) {
                stat.textContent = target;
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(count);
            }
        }, 30);
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const activityCards = document.querySelectorAll('#kegiatan-section ~ .row .activity-card');

    function performSearch() {
        if (!searchInput) return;
        const query = searchInput.value.toLowerCase().trim();

        activityCards.forEach(card => {
            const titleElement = card.querySelector('h5');
            if (titleElement) {
                const title = titleElement.innerText.toLowerCase();
                const cardContainer = card.closest('.col-lg-4');
                if (title.includes(query)) {
                    if (cardContainer) cardContainer.style.display = 'block';
                } else {
                    if (cardContainer) cardContainer.style.display = 'none';
                }
            }
        });

        if (query !== '') {
            const kegiatanSection = document.getElementById('kegiatan-section');
            if (kegiatanSection) {
                kegiatanSection.scrollIntoView({ behavior: 'smooth' });
            }
        }
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }
    if (searchInput) {
        searchInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }

    // Sidebar smooth scroll
    const sidebarLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (href && href.startsWith('#') && href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                    // Optional: Close sidebar on mobile after clicking
                    const sidebar = document.querySelector('.sidebar');
                    if (window.innerWidth < 768 && sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                    }
                }
            }
        });
    });
});