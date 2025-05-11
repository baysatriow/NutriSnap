<?php include 'partials/session.php'; ?>
<?php include 'partials/main.php'; ?>
<?php include 'partials/config.php'; ?>
<?php include "partials/functions.crud.php"; ?>
<?php $dataAkun = fetch($koneksi, 'users',$where=null)?>

<head>
    <?php include 'partials/title-meta.php'; ?>
    <!-- bootstrap Css -->
    <?php include 'partials/head-css.php'; ?>
</head>
<style>
    .nutrition-importance-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }
    
    .nutrition-importance-cards .card {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
    }
    
    .nutrition-importance-cards .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(34, 197, 94, 0.1);
        border-color: #22c55e;
    }
    
    .nutrition-importance-cards .card-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .nutrition-importance-cards .card-icon {
        font-size: 2rem;
        margin-right: 12px;
    }
    
    .nutrition-importance-cards .card h5 {
        color: #16a34a; /* Green color theme */
        margin-bottom: 0;
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    .nutrition-importance-cards .card p {
        color: #374151;
        line-height: 1.6;
        margin-bottom: 0;
    }
    
    .description {
        color: rgba(255, 255, 255, 0.9);
        margin-top: 10px;
        font-size: 16px;
    }
    
    .welcome-subtitle {
        color: #16a34a !important;
        font-weight: 500;
    }
    
    .copyright-area-bottom a {
        color: #16a34a !important;
        text-decoration: none;
    }
    
    .copyright-area-bottom a:hover {
        text-decoration: underline;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .nutrition-importance-cards {
            grid-template-columns: 1fr;
        }
    }
</style>
<body>

<?php include 'partials/header.php'; ?>


    <div class="dash-board-main-wrapper">
        <?php include 'partials/sidebar.php'; ?>
        <div class="main-center-content-m-left main-center-content-m-left">
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
                <h4 class="title color-white-title-home">👋 Selamat datang, <?=$dataAkun['username'] ?></h4>
                <p class="description">Mengapa Pemenuhan Gizi Itu Penting?</p>
                <div class="nutrition-importance-cards">
                    <div class="card">
                        <div class="card-header">
                            <span class="card-icon">🧠</span>
                            <h5>Energi & Konsentrasi</h5>
                        </div>
                        <p>Nutrisi yang tepat meningkatkan energi harian dan daya fokus kerja Anda.</p>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <span class="card-icon">💪</span>
                            <h5>Kesehatan Optimal</h5>
                        </div>
                        <p>Asupan gizi seimbang menjaga kekebalan tubuh dan mencegah penyakit kronis.</p>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <span class="card-icon">📈</span>
                            <h5>Produktivitas</h5>
                        </div>
                        <p>Pola makan sehat meningkatkan stamina dan kualitas hidup secara keseluruhan.</p>
                    </div>
                </div>
            </div>
            
            <div class="copyright-area-bottom">
                <p> <a href="#">NutriSnap</a> 2025. All Rights Reserved.</p>
            </div>
        </div>
    </div>

    <!-- jquery Js -->
    <?php include 'partials/script.php'; ?>
    
</body>

</html>