<?php
require_once "partials/session.php";

require_once "partials/config.php";
include "partials/functions.crud.php";

$page_load_error = "";
$chat_history_grouped = [];
$recommendation_history_grouped = [];

if (isset($_SESSION["id_user"])) {
    $user_id = $_SESSION["id_user"];
    $dataAkun = fetch($koneksi, 'users', $where = ['id_user' => $user_id]);

    // Fetch Chat History
    $sql_chat = "SELECT conversation_id, message, created_at
                 FROM chat_history
                 WHERE user_id = ?
                 ORDER BY created_at DESC";
    if ($stmt_chat = mysqli_prepare($koneksi, $sql_chat)) {
        mysqli_stmt_bind_param($stmt_chat, "i", $user_id);
        mysqli_stmt_execute($stmt_chat);
        $result_chat = mysqli_stmt_get_result($stmt_chat);
        $temp_chat_history = [];
        while ($row = mysqli_fetch_assoc($result_chat)) {
            $temp_chat_history[] = $row;
        }
        mysqli_stmt_close($stmt_chat);

        $unique_conversations = [];
        foreach ($temp_chat_history as $chat) {
            if (!isset($unique_conversations[$chat['conversation_id']])) {
                $unique_conversations[$chat['conversation_id']] = $chat;
            }
        }

        foreach ($unique_conversations as $chat) {
            $date = date('Y-m-d', strtotime($chat['created_at']));
            $chat_history_grouped[$date][] = $chat;
        }
        krsort($chat_history_grouped);
    } else {
        $page_load_error .= " Error mengambil riwayat chat: " . mysqli_error($koneksi);
    }

    // Fetch Recommendation History
    $sql_rec = "SELECT id, recommendation_text, created_at
                FROM user_recommendations
                WHERE user_id = ?
                ORDER BY created_at DESC";
    if ($stmt_rec = mysqli_prepare($koneksi, $sql_rec)) {
        mysqli_stmt_bind_param($stmt_rec, "i", $user_id);
        mysqli_stmt_execute($stmt_rec);
        $result_rec = mysqli_stmt_get_result($stmt_rec);
        while ($row = mysqli_fetch_assoc($result_rec)) {
            $date = date('Y-m-d', strtotime($row['created_at']));
            $recommendation_history_grouped[$date][] = $row;
        }
        mysqli_stmt_close($stmt_rec);
        krsort($recommendation_history_grouped);
    } else {
        $page_load_error .= " Error mengambil riwayat rekomendasi: " . mysqli_error($koneksi);
    }

} else {
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
            --theme-green-primary: #10b981;
            --theme-green-secondary: #059669;
            --theme-green-dark-text: #065f46;
            --theme-text-dark: #2c3e50;
            --theme-text-medium: #55575c;
            --theme-text-light: #6c757d;
            --theme-border-light: #e0e4e8;
            --theme-border-medium: #ced4da;
            --theme-background-page: #f9fafb;
            --theme-background-content: #ffffff;
            --theme-background-item: #ffffff;
            --theme-background-item-hover: #f1f5f9;
            --theme-shadow-soft: rgba(0,0,0,0.05);
        }

        /* Tema Gelap (Dark Mode) */
        [data-theme="dark"] {
            --theme-green-primary: #22c55e; /* Green 500 */
            --theme-green-secondary: #16a34a; /* Green 600 */
            --theme-green-dark-text: #6ee7b7; /* Emerald 300 untuk teks judul di dark mode */
            --theme-text-dark: #f3f4f6;     /* Gray 100 */
            --theme-text-medium: #d1d5db;   /* Gray 300 */
            --theme-text-light: #9ca3af;    /* Gray 400 */
            --theme-border-light: #4b5563;  /* Gray 600 */
            --theme-border-medium: #374151; /* Gray 700 */
            --theme-background-page: #111827; /* Gray 900 */
            --theme-background-content: #1f2937; /* Gray 800 */
            --theme-background-item: #2d3748; /* Gray 700 lebih gelap sedikit */
            --theme-background-item-hover: #374151; /* Gray 600/700 lebih gelap */
            --theme-shadow-soft: rgba(0,0,0,0.2);
        }

        body.page-riwayat {
            background-color: var(--theme-background-page);
            color: var(--theme-text-medium);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .history-section {
            margin-bottom: 30px;
        }
        .history-date-group h5 {
            font-size: 1.2rem;
            color: var(--theme-green-dark-text);
            padding-bottom: 10px;
            margin-bottom: 15px;
            border-bottom: 1px solid var(--theme-border-light);
            font-weight: 600;
            transition: color 0.3s ease, border-bottom-color 0.3s ease;
        }
        .history-item {
            display: block;
            padding: 12px 15px;
            margin-bottom: 10px;
            background-color: var(--theme-background-item);
            border: 1px solid var(--theme-border-light);
            border-radius: 6px;
            text-decoration: none;
            color: var(--theme-text-dark);
            transition: all 0.3s ease;
        }
        .history-item:hover {
            background-color: var(--theme-background-item-hover);
            border-left: 3px solid var(--theme-green-primary);
            transform: translateX(5px);
            box-shadow: 0 2px 5px var(--theme-shadow-soft);
        }
        .history-item .time {
            font-size: 0.85em;
            color: var(--theme-text-light);
            display: block;
            margin-bottom: 5px;
            transition: color 0.3s ease;
        }
        .history-item .preview {
            font-size: 0.95em;
            color: var(--theme-text-medium);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            transition: color 0.3s ease;
        }

        /* Navigasi Tab */
        .nav-pills .nav-link {
            color: var(--theme-green-secondary);
            margin: 0 5px;
            border: 1px solid transparent;
            border-radius: 0.375rem;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
        .nav-pills .nav-link.active,
        .nav-pills .nav-link:hover {
            background-color: var(--theme-green-primary) !important;
            color: white !important;
            border-color: var(--theme-green-primary) !important;
        }
        .nav-pills .nav-link:not(.active):hover {
             background-color: var(--theme-green-light) !important;
             color: var(--theme-green-dark-text) !important;
             border-color: var(--theme-green-light) !important;
        }
        [data-theme="dark"] .nav-pills .nav-link:not(.active):hover {
             background-color: rgba(34, 197, 94, 0.2) !important;
             color: var(--theme-green-primary) !important;
             border-color: rgba(34, 197, 94, 0.2) !important;
        }


        .nav-search-between .left-area .title {
            margin-bottom: 15px;
            color: var(--theme-text-dark);
            transition: color 0.3s ease;
        }
        .tab-content {
            background-color: var(--theme-background-content);
            padding: 25px;
            border-radius: 8px;
            margin-top: 25px !important;
            border: 1px solid var(--theme-border-medium);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .no-history {
            text-align: center;
            padding: 25px;
            color: var(--theme-text-light);
            font-style: italic;
            font-size: 1.05rem;
            transition: color 0.3s ease;
        }
        .nav-pills .nav-link i {
            margin-right: 8px;
        }

    </style>
</head>

<body class="community-feed page-riwayat"> <?php include 'partials/header.php'; ?>

    <div class="dash-board-main-wrapper">
        <?php include 'partials/sidebar.php'; ?>
        <div class="main-center-content-m-left">
            <div class="search__generator">

                <div class="nav-search-between">
                    <div class="left-area">
                        <h4 class="title">Riwayat Analisis Saya</h4>
                    </div>
                    <ul class="nav nav-pills mb-3" id="history-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="chat-history-tab" data-bs-toggle="pill" data-bs-target="#chat-history-content" type="button" role="tab" aria-controls="chat-history-content" aria-selected="true">
                                <i class="fa-regular fa-comments"></i>Riwayat Chat
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="recommendation-history-tab" data-bs-toggle="pill" data-bs-target="#recommendation-history-content" type="button" role="tab" aria-controls="recommendation-history-content" aria-selected="false">
                                <i class="fa-regular fa-file-lines"></i>Riwayat Rekomendasi
                            </button>
                        </li>
                    </ul>
                </div>

                <?php if(!empty($page_load_error)): ?>
                    <div class="alert alert-danger mt-3"><?php echo htmlspecialchars($page_load_error); ?></div>
                <?php endif; ?>

                <div class="tab-content" id="history-tabContent">
                    <div class="tab-pane fade show active" id="chat-history-content" role="tabpanel" aria-labelledby="chat-history-tab">
                        <?php if (!empty($chat_history_grouped)): ?>
                            <?php foreach ($chat_history_grouped as $date => $chats): ?>
                                <div class="history-section">
                                    <div class="history-date-group">
                                      <h5><?php echo date('d F Y', strtotime($date)); ?></h5>
                                    </div>
                                    <?php foreach ($chats as $chat): ?>
                                        <a href="chatbot.php?conversation_id=<?php echo htmlspecialchars($chat['conversation_id']); ?>" class="history-item">
                                            <span class="time"><?php echo date('H:i', strtotime($chat['created_at'])); ?></span>
                                            <span class="preview">
                                                Chat: <?php echo htmlspecialchars(mb_strimwidth($chat['message'], 0, 70, "...")); ?>
                                            </span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-history">Tidak ada riwayat chat ditemukan.</p>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="recommendation-history-content" role="tabpanel" aria-labelledby="recommendation-history-tab">
                        <?php if (!empty($recommendation_history_grouped)): ?>
                            <?php foreach ($recommendation_history_grouped as $date => $recommendations): ?>
                                <div class="history-section">
                                     <div class="history-date-group">
                                        <h5><?php echo date('d F Y', strtotime($date)); ?></h5>
                                     </div>
                                    <?php foreach ($recommendations as $rec): ?>
                                        <a href="rekomendasi.php?recommendation_id=<?php echo htmlspecialchars($rec['id']); ?>" class="history-item">
                                            <span class="time"><?php echo date('H:i', strtotime($rec['created_at'])); ?></span>
                                            <span class="preview">
                                                Rekomendasi: <?php echo htmlspecialchars(mb_strimwidth(strip_tags($rec['recommendation_text']), 0, 70, "...")); ?>
                                            </span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-history">Tidak ada riwayat rekomendasi ditemukan.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div> <div class="copyright-area-bottom">
                <p> <a href="#">NutriSnap</a> &copy; <?php echo date("Y"); ?>. All Rights Reserved.</p>
            </div>
        </div> </div> <?php include 'partials/script.php'; ?>
    </body>
</html>
