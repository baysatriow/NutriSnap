<div class="left-side-bar">
    <div class="overlay-mobile-area"></div>
    <div class="inner">
        <div class="single-menu-wrapper">
            <a href="index.php" class="single-menu openuptip" flow="right" tooltip="Search">
                <div class="icon">
                    <img src="assets/images/icons/01.png" alt="icons">
                </div>
                <p>Home</p>
            </a>
            <a href="chatbot.php" class="single-menu openuptip" flow="right" tooltip="Search">
                <div class="icon">
                    <img src="assets/images/icons/04.png" alt="icons">
                </div>
                <p>Analisis Makanan</p>
            </a>
            <a href="riwayat.php" class="single-menu">
                <div class="icon">
                    <img src="assets/images/icons/07.png" alt="icons">
                </div>
                <p>Riwayat Analisis</p>
            </a>
            <a href="rekomendasi.php" class="single-menu">
                <div class="icon">
                    <img src="assets/images/icons/07.png" alt="icons">
                </div>
                <p>Rekomendasi Pola Makan</p>
            </a>
            <a href="profile.php" class="single-menu">
                <div class="icon">
                    <img src="assets/images/icons/08.png" alt="icons">
                </div>
                <p>Personalisasi</p>
            </a>
        </div>
        <div class="single-menu-wrapper">
            <a href="logout.php" class="single-menu">
                <div class="icon">
                    <img src="assets/images/icons/09.png" alt="icons">
                </div>
                <p>Logout</p>
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Get current URL path and location
        var path = window.location.pathname;
        var page = path.split("/").pop();

        // Add active class to appropriate menu item
        var menuLinks = document.querySelectorAll('.single-menu');

        menuLinks.forEach(function(menuLink) {
            var link = menuLink.getAttribute('href');

            // Check if the current page URL matches the menu link
            if (page === link || (page === '' && link === '/')) {
                menuLink.classList.add('active');
            }
        });
    });
</script>