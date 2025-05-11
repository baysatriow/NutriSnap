<link rel="stylesheet" href="assets/css/plugins/fontawesome-6.css">
<link rel="stylesheet" href="assets/css/plugins/swiper.css">
<link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<script>
        // Check if user has already set a theme preference
        var storedTheme = localStorage.getItem('intellactai');

        // If no preference is found, default to dark mode
        if (!storedTheme) {
            storedTheme = "light";
            localStorage.setItem('intellactai', storedTheme);
        }

        // Set the theme based on the stored value
        document.documentElement.setAttribute('data-theme', storedTheme);
    </script>