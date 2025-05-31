<?php
require_once "partials/session.php";

require_once "partials/config.php";
include "partials/functions.crud.php";

// Get user ID from session
if (!isset($_SESSION["id_user"])) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id_user"];
$dataAkun = fetch($koneksi, 'users', $where = ['id_user' => $user_id]);

if (!$dataAkun) {
    session_destroy();
    header("location: login.php");
    exit;
}

include 'partials/main.php';
?>

<head>
    <?php include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php'; ?>
    <style>
        /* Tema Warna Dasar (Light Mode) */
        :root {
            --theme-green-primary: #10b981; /* Hijau utama (Emerald 500) */
            --theme-green-secondary: #059669; /* Hijau lebih gelap (Emerald 600) */
            --theme-green-light: #d1fae5;   /* Hijau muda untuk latar belakang (Emerald 100) */
            --theme-green-text: #065f46;    /* Hijau tua untuk teks (Emerald 800) */
            --theme-green-hover-bg: #047857; /* Hijau lebih gelap lagi untuk hover solid (Emerald 700) */

            --theme-text-dark: #1f2937;     /* Gray 800 */
            --theme-text-medium: #4b5563;   /* Gray 600 */
            --theme-text-light: #6b7280;    /* Gray 500 */
            --theme-text-banner: #ffffff;   /* Teks putih untuk banner */
            --theme-text-banner-subtle: rgba(255, 255, 255, 0.9);

            --theme-border-color: #d1d5db;  /* Gray 300 */
            --theme-background-page: #f9fafb; /* Gray 50 */
            --theme-background-card: #ffffff;

            /* Variabel Gradient Banner (Light Mode - Kebiruan) */
            /* --theme-banner-gradient-start-light: #4A00E0;
            --theme-banner-gradient-end-light: #8E2DE2; */
            /* /* Alternatif lain yang lebih biru:  */
            --theme-banner-gradient-start-light:rgb(49, 70, 192);
            --theme-banner-gradient-end-light: #587dff; 


            
            --theme-shadow-banner: rgba(16, 185, 129, 0.2);
            --theme-shadow-card: rgba(0, 0, 0, 0.07);
            --theme-shadow-card-hover: rgba(16, 185, 129, 0.15);
        }

        /* Tema Gelap (Dark Mode) */
        [data-theme="dark"] {
            --theme-green-primary: #22c55e; /* Hijau lebih cerah di dark mode (Green 500) */
            --theme-green-secondary: #16a34a; /* Green 600 */
            --theme-green-light: #052e16;   /* Latar belakang hijau sangat gelap (Green 950) */
            --theme-green-text: #6ee7b7;    /* Teks hijau terang (Emerald 300) */
            --theme-green-hover-bg: #15803d; /* Green 700 */

            --theme-text-dark: #f3f4f6;     /* Gray 100 */
            --theme-text-medium: #d1d5db;   /* Gray 300 */
            --theme-text-light: #9ca3af;    /* Gray 400 */
            --theme-text-banner: #ffffff;
            --theme-text-banner-subtle: rgba(255, 255, 255, 0.85);

            --theme-border-color: #4b5563;  /* Gray 600 */
            --theme-background-page: #111827; /* Gray 900 */
            --theme-background-card: #1f2937; /* Gray 800 */

            /* Variabel Gradient Banner (Dark Mode - Kehijauan) */
            --theme-banner-gradient-start-dark: #065f46; /* Hijau Tua (Emerald 800) */
            --theme-banner-gradient-end-dark: #10b981;   /* Hijau Cerah (Emerald 500) */

            --theme-shadow-banner: rgba(34, 197, 94, 0.25);
            --theme-shadow-card: rgba(0, 0, 0, 0.2); /* Shadow lebih gelap */
            --theme-shadow-card-hover: rgba(34, 197, 94, 0.3);
        }

        body.dashboard-home {
            background-color: var(--theme-background-page);
            color: var(--theme-text-medium);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Banner Styling */
        .banner-badge.bg_image {
            /* Menggunakan variabel untuk gradient, dan menghapus url gambar */
            background-image: linear-gradient(to right, var(--theme-banner-gradient-start-light), var(--theme-banner-gradient-end-light));
            background-size: cover;
            background-position: center;
            padding: 60px 40px;
            border-radius: 20px;
            color: var(--theme-text-banner);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px var(--theme-shadow-banner);
            transition: background-image 0.3s ease; /* Transisi untuk background */
        }
        [data-theme="dark"] .banner-badge.bg_image {
            background-image: linear-gradient(to right, var(--theme-banner-gradient-start-dark), var(--theme-banner-gradient-end-dark));
        }

        .banner-badge .inner .title {
            font-size: 2.8rem; /* Font lebih besar */
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--theme-text-banner);
            text-shadow: 1px 1px 4px rgba(0,0,0,0.25);
        }

        .banner-badge .inner .dsic {
            font-size: 1.15rem; /* Sedikit lebih besar */
            margin-bottom: 35px;
            line-height: 1.75;
            max-width: 750px;
            color: var(--theme-text-banner-subtle);
        }

        .banner-badge .inner .rts-btn.btn-blur {
            background-color: rgba(255, 255, 255, 0.15);
            color: var(--theme-text-banner);
            border: 2px solid var(--theme-text-banner);
            padding: 14px 35px; /* Padding lebih besar */
            font-weight: 600;
            font-size: 1.05rem;
            border-radius: 30px;
            transition: all 0.3s ease;
        }

        .banner-badge .inner .rts-btn.btn-blur:hover {
            background-color: var(--theme-text-banner);
            color: var(--theme-green-secondary); /* Menggunakan variabel warna hijau */
            transform: translateY(-3px) scale(1.03); /* Efek hover lebih menonjol */
        }

        .banner-badge .inner .inner-right-iamge img {
            max-width: 300px; /* Sedikit lebih besar */
            position: absolute;
            top: -300px;
            right: 40px; /* Penyesuaian posisi */
            bottom: 0;
            opacity: 0.9;
        }
         @media (max-width: 991px) {
            .banner-badge .inner .inner-right-iamge { display: none; }
            .banner-badge .inner { text-align: center; }
            .banner-badge .inner .dsic { margin-left: auto; margin-right: auto; }
        }
         @media (max-width: 768px) {
            .banner-badge.bg_image { padding: 40px 20px; }
            .banner-badge .inner .title { font-size: 2.2rem; }
            .banner-badge .inner .dsic { font-size: 1rem; }
        }


        /* Welcome Message */
        .search__generator {
             padding: 20px;
        }
        .search__generator .title.color-white-title-home {
            color: var(--theme-text-dark) !important;
            font-size: 2rem; /* Lebih besar */
            font-weight: 700; /* Lebih tebal */
            margin-bottom: 10px;
        }
        .search__generator .description {
            color: var(--theme-text-medium) !important;
            margin-top: 0;
            font-size: 1.15rem; /* Sedikit lebih besar */
            margin-bottom: 30px;
        }

        /* Nutrition Importance Cards */
        .nutrition-importance-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px; /* Gap lebih besar */
            margin-top: 30px;
        }

        .nutrition-importance-cards .card {
            background: var(--theme-background-card);
            padding: 30px; /* Padding lebih besar */
            border-radius: 16px; /* Border radius lebih besar */
            box-shadow: 0 6px 20px var(--theme-shadow-card);
            transition: all 0.3s ease-in-out; /* Transisi lebih halus */
            border: 1px solid var(--theme-border-color);
            display: flex;
            flex-direction: column;
        }

        .nutrition-importance-cards .card:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 15px 35px var(--theme-shadow-card-hover);
            border-color: var(--theme-green-primary);
        }

        .nutrition-importance-cards .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px dashed var(--theme-border-color);
        }

        .nutrition-importance-cards .card-icon {
            font-size: 2.5rem; /* Ikon lebih besar */
            margin-right: 18px; /* Jarak ikon lebih besar */
            color: var(--theme-green-primary);
            line-height: 1;
        }

        .nutrition-importance-cards .card h5 {
            color: var(--theme-green-text);
            margin-bottom: 0;
            font-size: 1.4rem; /* Judul kartu lebih besar */
            font-weight: 700; /* Lebih tebal */
        }

        .nutrition-importance-cards .card p {
            color: var(--theme-text-medium);
            line-height: 1.7; /* Line height lebih nyaman */
            margin-bottom: 0;
            font-size: 1rem; /* Deskripsi kartu lebih besar */
            flex-grow: 1;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .search__generator .title.color-white-title-home { font-size: 1.6rem; }
            .search__generator .description { font-size: 1.05rem; }
            .nutrition-importance-cards { grid-template-columns: 1fr; gap: 20px; }
            .nutrition-importance-cards .card { padding: 25px; }
            .nutrition-importance-cards .card-icon { font-size: 2.2rem; }
            .nutrition-importance-cards .card h5 { font-size: 1.25rem; }
            .nutrition-importance-cards .card p { font-size: 0.9rem; }
        }
    </style>
</head>

<body class="dashboard-home"> <?php include 'partials/header.php'; ?>

    <div class="dash-board-main-wrapper">
        <?php include 'partials/sidebar.php'; ?>
        <div class="main-center-content-m-left">
            <div class="banner-badge bg_image">
                <div class="inner">
                    <h3 class="title">Analisis Nutrisi Cerdas untuk Hidup Sehat</h3>
                    <p class="dsic">
                        NutriSnap adalah platform AI yang membantu Anda memahami nilai gizi makanan dengan mudah. Cukup scan menu atau foto makanan, dapatkan informasi nutrisi lengkap dan rekomendasi personal untuk pola makan sehat yang sesuai kebutuhan tubuh Anda.
                    </p>
                    <a href="chatbot.php" class="rts-btn btn-blur">Mulai Analisis Nutrisi</a>
                    <div class="inner-right-iamge">
                        <img src="assets/images/banner/01.png" alt="banner">
                    </div>
                </div>
            </div>

            <div class="search__generator mt--50">
                <h4 class="title color-white-title-home">👋 Selamat datang, <?php echo htmlspecialchars($dataAkun['first_name'] ?: 'Pengguna'); ?>!</h4>
                <p class="description">Mengapa Pemenuhan Gizi Itu Penting?</p>
                <div class="nutrition-importance-cards">
                    <div class="card">
                        <div class="card-header">
                            <span class="card-icon">💡</span> <h5>Energi & Konsentrasi</h5>
                        </div>
                        <p>Nutrisi yang tepat meningkatkan energi harian dan daya fokus kerja Anda.</p>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <span class="card-icon">🛡️</span> <h5>Kesehatan Optimal</h5>
                        </div>
                        <p>Asupan gizi seimbang menjaga kekebalan tubuh dan mencegah penyakit kronis.</p>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <span class="card-icon">🚀</span> <h5>Produktivitas & Kualitas Hidup</h5>
                        </div>
                        <p>Pola makan sehat meningkatkan stamina dan kualitas hidup secara keseluruhan.</p>
                    </div>
                </div>
            </div>

            <div class="copyright-area-bottom">
                <p> <a href="#">NutriSnap</a> &copy; <?php echo date("Y"); ?>. All Rights Reserved.</p>
            </div>
        </div>
    </div>

    <?php include 'partials/script.php'; ?>
</body>
</html>
