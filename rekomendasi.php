<?php
require_once "partials/session.php";

require_once "partials/config.php";
include "partials/functions.crud.php";

$page_load_error = "";
$userDataForJS = null;
$user_name_display = "Pengguna";
$specific_recommendation_text_for_js = null; 
$load_specific_recommendation = false;      
$current_recommendation_id_for_js = null; 

if (isset($_SESSION["id_user"])) {
    $user_id = $_SESSION["id_user"];
    $dataAkun = fetch($koneksi, 'users', ['id_user' => $user_id]);
    // Ambil nama pengguna untuk judul 
    $userAccountData = fetch($koneksi, 'users', ['id_user' => $user_id]);
    if ($userAccountData) {
        $user_name_display = htmlspecialchars(trim($userAccountData['first_name'] . ' ' . $userAccountData['last_name']));
        if (empty($user_name_display)) {
            $user_name_display = htmlspecialchars($userAccountData['username']) ?: "Pengguna";
        }
    } else {
        $page_load_error = "Error: Tidak dapat mengambil data akun pengguna utama.";
    }

    if (isset($_GET['recommendation_id'])) {
        $recommendation_id = filter_var($_GET['recommendation_id'], FILTER_VALIDATE_INT);

        if ($recommendation_id && $recommendation_id > 0) {
            $specific_rec_data = fetch($koneksi, 'user_recommendations', ['id' => $recommendation_id, 'user_id' => $user_id]);
            if ($specific_rec_data) {
                $specific_recommendation_text_for_js = $specific_rec_data['recommendation_text'];
                $current_recommendation_id_for_js = $recommendation_id;
                $load_specific_recommendation = true;
            } else {
                $page_load_error = ($page_load_error ? $page_load_error . " " : "") . "Rekomendasi tidak ditemukan atau Anda tidak memiliki akses ke riwayat ini.";
            }
        } else {
            $page_load_error = ($page_load_error ? $page_load_error . " " : "") . "ID Rekomendasi yang diberikan tidak valid.";
        }
    }

    if (!$load_specific_recommendation && $userAccountData) {
        $age = null;
        if (!empty($userAccountData['date_of_birth'])) {
            try {
                $birthDate = new DateTime($userAccountData['date_of_birth']);
                $today = new DateTime();
                $age = $today->diff($birthDate)->y;
            } catch (Exception $e) { $age = null; }
        }
        $userDataForJS = [
            'firstName' => $userAccountData['first_name'] ?? '', 'lastName' => $userAccountData['last_name'] ?? '',
            'dob' => $userAccountData['date_of_birth'] ?? '', 'age' => $age,
            'gender' => $userAccountData['gender'] ?? '', 'heightCm' => $userAccountData['height_cm'] ?? '',
            'weightKg' => $userAccountData['weight_kg'] ?? '', 'activityLevel' => $userAccountData['activity_level'] ?? '',
            'healthGoal' => $userAccountData['health_goal'] ?? '', 'bio' => $userAccountData['bio'] ?? ''
        ];
    } elseif (!$load_specific_recommendation && !$userAccountData && empty($page_load_error)) {
        $page_load_error = "Tidak dapat mengambil data profil untuk membuat rekomendasi baru.";
    }

} else {
    if (isset($_GET['recommendation_id'])) {
         $page_load_error = "Anda harus login untuk melihat riwayat rekomendasi.";
    } else {
        $page_load_error = "Anda tidak login. Rekomendasi yang diberikan akan bersifat umum.";
    }
}

include 'partials/main.php';
?>

<head>
    <?php include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php'; ?>
    <style>
        /* Tema Warna Dasar (Light Mode) */
        :root {
            --theme-green-primary: #10b981;
            --theme-green-secondary: #059669;
            --theme-green-light-bg: #f0fdf4;
            --theme-green-text: #065f46;
            --theme-text-dark: #1f2937;
            --theme-text-medium: #4b5563;
            --theme-text-light: #6b7280;
            --theme-border-light: #e0e4e8;
            --theme-border-medium: #d1d5db;
            --theme-background-page: #f9fafb;
            --theme-background-content: #ffffff;
            --theme-button-primary-bg: #083A5E;
            --theme-button-primary-text: #ffffff;
            --theme-button-primary-hover-bg: #062c47;
            --theme-shadow-soft: rgba(0,0,0,0.05);
            --theme-shadow-card: rgba(0,0,0,0.08);
        }

        /* Tema Gelap (Dark Mode) */
        [data-theme="dark"] {
            --theme-green-primary: #22c55e; /* Green 500, dark mode */
            --theme-green-secondary: #16a34a; /* Green 600 */
            --theme-green-light-bg: #064e3b; /* Latar belakang hijau gelap untuk aksen (Emerald 800/900) */
            --theme-green-text: #6ee7b7;    /* Emerald 300 untuk teks hijau */
            --theme-text-dark: #f3f4f6;     /* Gray 100 */
            --theme-text-medium: #d1d5db;   /* Gray 300 */
            --theme-text-light: #9ca3af;    /* Gray 400 */
            --theme-border-light: #4b5563;  /* Gray 600 */
            --theme-border-medium: #374151; /* Gray 700 */
            --theme-background-page: #111827; /* Gray 900 */
            --theme-background-content: #1f2937; /* Gray 800 */
            --theme-button-primary-bg: var(--theme-green-primary); /* Tombol utama hijau di dark mode */
            --theme-button-primary-text: #ffffff;
            --theme-button-primary-hover-bg: var(--theme-green-secondary);
            --theme-shadow-soft: rgba(0,0,0,0.2);
            --theme-shadow-card: rgba(0,0,0,0.25);
        }

        body.page-rekomendasi {
            background-color: var(--theme-background-page);
            color: var(--theme-text-medium);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .recommendation-container-wrapper {
             margin-top: 20px;
        }
        .recommendation-container {
            padding: 25px;
            background-color: var(--theme-background-content);
            border-radius: 8px;
            box-shadow: 0 4px 12px var(--theme-shadow-card);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .recommendation-title {
            font-size: 2rem;
            color: var(--theme-text-dark);
            margin-bottom: 25px;
            text-align: center;
            border-bottom: 2px solid var(--theme-green-primary);
            padding-bottom: 15px;
            font-weight: 600;
            transition: color 0.3s ease, border-bottom-color 0.3s ease;
        }
        .recommendation-content {
            background-color: var(--theme-background-content);
            padding: 20px;
            border-radius: 6px;
            min-height: 250px;
            line-height: 1.7;
            color: var(--theme-text-medium);
            border: 1px solid var(--theme-border-light);
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
        .recommendation-content h1,
        .recommendation-content h2,
        .recommendation-content h3,
        .recommendation-content h4 {
            margin-top: 1.3em;
            margin-bottom: 0.7em;
            color: var(--theme-green-text);
            font-weight: 600;
            transition: color 0.3s ease;
        }
         .recommendation-content h1 { font-size: 1.7em; }
         .recommendation-content h2 { font-size: 1.5em; }
         .recommendation-content h3 { font-size: 1.3em; }
         .recommendation-content h4 { font-size: 1.15em; }

        .recommendation-content ul, .recommendation-content ol {
            padding-left: 30px;
            margin-bottom: 1.2em;
        }
        .recommendation-content li {
            margin-bottom: 0.7em;
        }
        .recommendation-content strong {
            color: var(--theme-text-dark);
            font-weight: 600;
            transition: color 0.3s ease;
        }
        [data-theme="dark"] .recommendation-content strong {
            color: var(--theme-green-primary);
        }
        .recommendation-content p {
            margin-bottom: 1.2em;
        }
        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 180px;
            font-size: 1.25em;
            color: var(--theme-text-light);
            transition: color 0.3s ease;
        }
        .loading-spinner::before {
            content: ''; width: 35px; height: 35px;
            border: 5px solid var(--theme-border-light);
            border-top: 5px solid var(--theme-green-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 15px;
            transition: border-color 0.3s ease, border-top-color 0.3s ease;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .trigger-recommendation-btn {
            display: block; margin: 30px auto 20px auto; padding: 12px 30px;
            font-size: 1.1rem;
            background-color: var(--theme-button-primary-bg);
            color: var(--theme-button-primary-text);
            border: none; border-radius: 6px; cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 2px 4px var(--theme-shadow-soft);
        }
        .trigger-recommendation-btn:hover {
            background-color: var(--theme-button-primary-hover-bg);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px var(--theme-shadow-medium);
        }
        .trigger-recommendation-btn:disabled {
            background-color: #cccccc; cursor: not-allowed; transform: translateY(0); box-shadow: none;
        }
        [data-theme="dark"] .trigger-recommendation-btn:disabled {
            background-color: #555; color: #999;
        }

        .error-message-container {
            color: #D8000C; background-color: #FFD2D2; border: 1px solid #D8000C;
            padding: 12px 18px; border-radius: 5px; margin-bottom: 25px;
            text-align: center; font-size: 0.95rem;
        }
        [data-theme="dark"] .error-message-container {
            color: #f8d7da; background-color: #58151c; border-color: #a1232f;
        }

        .rts-blog-details-area-top {
            padding-top: 20px;
            padding-bottom: 40px;
        }
    </style>
</head>

<body class="page-rekomendasi" <?php echo isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark' ? 'data-theme="dark"' : ''; ?>>

<?php include 'partials/header.php'; ?>

    <div class="dash-board-main-wrapper">
        <?php include 'partials/sidebar.php'; ?>

        <div class="main-center-content-m-left">
            <div class="search__generator"> <div class="rts-blog-details-area-top"> <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="recommendation-container-wrapper">
                                    <div class="recommendation-container">
                                        <h2 class="recommendation-title">
                                            <?php
                                            if ($load_specific_recommendation) {
                                                echo "Detail Riwayat Rekomendasi";
                                                if ($user_name_display !== "Pengguna" && !empty($user_name_display)) {
                                                    echo " untuk " . $user_name_display;
                                                }
                                            } else {
                                                echo "Rekomendasi Pola Makan untuk " . $user_name_display;
                                            }
                                            ?>
                                        </h2>

                                        <?php if(!empty($page_load_error)): ?>
                                            <div class="error-message-container"><?php echo htmlspecialchars($page_load_error); ?></div>
                                        <?php endif; ?>

                                        <div id="recommendationDisplay" class="recommendation-content">
                                            <div class="loading-spinner" id="loadingIndicator">Memuat...</div>
                                        </div>
                                        <button id="getRecommendationBtn" class="trigger-recommendation-btn">
                                            <?php echo $load_specific_recommendation ? "Minta Rekomendasi Baru Lainnya" : "Dapatkan Rekomendasi Baru"; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="copyright-area-bottom">
                <p> <a href="#">NutriSnap</a> &copy; <?php echo date("Y"); ?>. All Rights Reserved.</p>
            </div>
        </div> </div> <script>
        const userDataFromPHP = <?php echo json_encode($userDataForJS, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const currentUserIdForJS = <?php echo isset($user_id) ? json_encode($user_id) : 'null'; ?>;
        const loadSpecificRecommendationFromPHP = <?php echo json_encode($load_specific_recommendation); ?>;
        const specificRecommendationTextFromPHP = <?php echo json_encode($specific_recommendation_text_for_js, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const currentRecommendationIdFromPHP = <?php echo json_encode($current_recommendation_id_for_js); ?>;

        let recommendationDisplayElement = null;
        let loadingIndicatorElement = null;
        let getRecommendationBtnElement = null;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="assets/js/rekomendasi.js"></script>
    <?php include 'partials/script.php'; ?>
</body>
</html>
