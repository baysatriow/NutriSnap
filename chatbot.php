<?php
include 'partials/session.php';
include 'partials/main.php'; 
include 'partials/config.php';
include "partials/functions.crud.php";

$user_id_from_session = $_SESSION["id_user"] ?? null;

// Variabel untuk diteruskan ke JavaScript
$initial_conversation_id_for_js = null;
$current_user_id_for_js = $user_id_from_session;

if (isset($_GET['conversation_id'])) {
    $conversation_id_from_url = trim($_GET['conversation_id']);
    if (!empty($conversation_id_from_url)) {
        $initial_conversation_id_for_js = $conversation_id_from_url;
    }
}

$dataAkun = null;
if ($user_id_from_session) {
    $dataAkun = fetch($koneksi, 'users', ['id_user' => $user_id_from_session]);
}
$user_avatar_path = ($dataAkun && !empty($dataAkun['profile_image_path']) && file_exists($dataAkun['profile_image_path'])) ? htmlspecialchars($dataAkun['profile_image_path']) : 'assets/images/avatar/user.svg';

?>

<head>
    <?php include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php'; ?>
    <style>
        /* Tema Warna Dasar (Light Mode) */
        :root {
            --theme-light-bg-page: #f9fafb;
            --theme-light-bg-chat-area-wrapper: #ffffff; /* Latar belakang area chat scrollable */
            --theme-light-bg-input-area: #f8f9fa;
            --theme-light-bg-input-wrapper: #fff;
            --theme-light-text-primary: #1f2937;
            --theme-light-text-secondary: #6b7280;
            --theme-light-border: #e5e7eb;
            --theme-light-user-bubble-bg: #083A5E; /* Biru tua untuk user */
            --theme-light-user-bubble-text: #ffffff;
            --theme-light-model-bubble-bg: #f0f2f5; /* Abu-abu muda untuk model */
            --theme-light-model-bubble-text: #333333;
            --theme-light-button-newchat-bg: #10b981; /* Hijau dari update sebelumnya */
            --theme-light-button-newchat-text: #ffffff;
            --theme-light-button-newchat-hover-bg: #0d9268;
            --theme-light-send-button-bg: #10b981;
            --theme-light-send-button-text: #ffffff;
            --theme-light-send-button-hover-bg: #059669;
            --theme-light-action-button-icon: #6b7280;
            --theme-light-action-button-hover-bg: #f3f4f6;
            --theme-light-scrollbar-track: #f1f5f9;
            --theme-light-scrollbar-thumb: #cbd5e1;
            --theme-light-image-indicator-bg: #eff6ff;
            --theme-light-image-indicator-text: #3b82f6;
        }

        /* Tema Gelap (Dark Mode) */
        [data-theme="dark"] {
            --theme-dark-bg-page: #111827; /* Gray 900 */
            --theme-dark-bg-chat-area-wrapper: #1f2937; /* Gray 800 - untuk area chat scrollable */
            --theme-dark-bg-input-area: #1a202c; /* Sedikit lebih gelap dari chat area wrapper */
            --theme-dark-bg-input-wrapper: #2d3748; /* Gray 700 - untuk custom-chat-wrapper */
            --theme-dark-text-primary: #f3f4f6;  /* Gray 100 */
            --theme-dark-text-secondary: #9ca3af; /* Gray 400 */
            --theme-dark-border: #4b5563;     /* Gray 600 */
            --theme-dark-user-bubble-bg: #065f46; /* Hijau tua (Emerald 800) */
            --theme-dark-user-bubble-text: #d1fae5; /* Hijau sangat muda (Emerald 100) */
            --theme-dark-model-bubble-bg: #374151; /* Gray 700 - untuk AI bubble */
            --theme-dark-model-bubble-text: #e5e7eb; /* Gray 200 */
            --theme-dark-button-newchat-bg: #10b981; /* Tetap hijau cerah agar menonjol */
            --theme-dark-button-newchat-text: #ffffff;
            --theme-dark-button-newchat-hover-bg: #0d9268;
            --theme-dark-send-button-bg: #10b981;
            --theme-dark-send-button-text: #ffffff;
            --theme-dark-send-button-hover-bg: #059669;
            --theme-dark-action-button-icon: #9ca3af; /* Gray 400 */
            --theme-dark-action-button-hover-bg: #374151; /* Gray 700 */
            --theme-dark-scrollbar-track: #2d3748; /* Gray 700 */
            --theme-dark-scrollbar-thumb: #4b5563; /* Gray 600 */
            --theme-dark-image-indicator-bg: #1e3a8a; /* Biru tua untuk indikator gambar */
            --theme-dark-image-indicator-text: #bfdbfe; /* Biru muda untuk teks indikator */
        }

        body.chatbot {
            background-color: var(--theme-light-bg-page);
            color: var(--theme-light-text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        [data-theme="dark"] body.chatbot {
            background-color: var(--theme-dark-bg-page);
            color: var(--theme-dark-text-primary);
        }

        .typing-indicator { display: inline-block; color: var(--theme-light-text-secondary); font-style: italic; }
        [data-theme="dark"] .typing-indicator { color: var(--theme-dark-text-secondary); }
        .typing-indicator .dots { display: inline-block; position: relative; }
        .typing-indicator .dots::after { content: ''; animation: typing-dots 1.5s infinite; position: absolute; width: 0; }
        @keyframes typing-dots { 0% { content: ''; } 25% { content: '.'; } 50% { content: '..'; } 75% { content: '...'; } 100% { content: ''; } }

        .chat-input-area {
            padding: 10px 15px;
            background-color: var(--theme-light-bg-input-area);
            border-top: 1px solid var(--theme-light-border);
            transition: background-color 0.3s ease, border-top-color 0.3s ease;
        }
        [data-theme="dark"] .chat-input-area {
            background-color: var(--theme-dark-bg-input-area);
            border-top: 1px solid var(--theme-dark-border);
        }

        .new-chat-button-container { display: flex; justify-content: center; margin-bottom: 12px; }
        #newChatButton {
            background-color: var(--theme-light-button-newchat-bg);
            color: var(--theme-light-button-newchat-text);
            border: none; padding: 10px 25px; border-radius: 25px; cursor: pointer;
            font-size: 1rem; font-weight: 500; transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15); display: inline-flex;
            align-items: center; justify-content: center; gap: 8px; line-height: 1.5;
        }
        [data-theme="dark"] #newChatButton {
            background-color: var(--theme-dark-button-newchat-bg);
            color: var(--theme-dark-button-newchat-text);
            box-shadow: 0 2px 5px rgba(0,0,0,0.25);
        }
        #newChatButton i { font-size: 1.2em; }
        #newChatButton:hover {
            background-color: var(--theme-light-button-newchat-hover-bg);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2); transform: translateY(-1px);
        }
        [data-theme="dark"] #newChatButton:hover {
            background-color: var(--theme-dark-button-newchat-hover-bg);
        }
        #newChatButton:active { transform: translateY(0); box-shadow: 0 2px 5px rgba(0,0,0,0.15); }

        .custom-chat-wrapper {
            position: relative !important; background: var(--theme-light-bg-input-wrapper) !important;
            border-radius: 16px !important; padding: 12px 16px !important;
            max-width: 100% !important; width: 100% !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08) !important;
            border: 2px solid var(--theme-light-border) !important;
            transition: all 0.3s ease !important;
        }
        [data-theme="dark"] .custom-chat-wrapper {
            background: var(--theme-dark-bg-input-wrapper) !important;
            border-color: var(--theme-dark-border) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2) !important;
        }
        .custom-chat-wrapper:focus-within { border-color: var(--theme-light-button-newchat-bg) !important; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important; }
        [data-theme="dark"] .custom-chat-wrapper:focus-within { border-color: var(--theme-dark-button-newchat-bg) !important; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important; }

        .image-preview-section { margin-bottom: 12px !important; position: relative !important; min-height: 0 !important; transition: all 0.3s ease !important; }
        .image-preview-section.show { display: block !important; }
        .image-preview-section.hide { display: none !important; }
        .preview-image-container { position: relative !important; display: inline-block !important; line-height: 0 !important; }
        .preview-image { max-width: 150px !important; max-height: 150px !important; object-fit: cover !important; border-radius: 8px !important; display: block !important; border: 2px solid var(--theme-light-border) !important; box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important; }
        [data-theme="dark"] .preview-image { border-color: var(--theme-dark-border) !important; }

        .remove-image-btn { position: absolute !important; top: -8px !important; right: -8px !important; background: #ef4444 !important; color: white !important; border: 2px solid white !important; width: 24px !important; height: 24px !important; border-radius: 50% !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 14px !important; line-height: 1 !important; font-weight: bold !important; box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important; z-index: 10 !important; }
        .remove-image-btn:hover { background: #dc2626 !important; transform: scale(1.1) !important; }

        .input-container { display: flex !important; align-items: flex-end !important; gap: 8px !important; min-height: 44px !important; }
        #textInput {
            flex: 1 !important; border: none !important; outline: none !important;
            font-size: 15px !important; line-height: 1.5 !important; resize: none !important;
            min-height: 44px !important; max-height: 120px !important;
            background: transparent !important; padding: 10px 0 !important;
            overflow-y: auto !important; font-family: inherit !important;
            color: var(--theme-light-text-primary) !important;
        }
        [data-theme="dark"] #textInput { color: var(--theme-dark-text-primary) !important; }
        #textInput::placeholder { color: var(--theme-light-text-secondary) !important; }
        [data-theme="dark"] #textInput::placeholder { color: var(--theme-dark-text-secondary) !important; }

        .chat-action-btn { background: none !important; border: none !important; cursor: pointer !important; padding: 8px !important; border-radius: 8px !important; transition: all 0.2s ease !important; display: flex !important; align-items: center !important; justify-content: center !important; width: 40px !important; height: 40px !important; flex-shrink: 0 !important; }
        .chat-action-btn:hover { background: var(--theme-light-action-button-hover-bg) !important; }
        [data-theme="dark"] .chat-action-btn:hover { background: var(--theme-dark-action-button-hover-bg) !important; }
        .chat-action-btn i { font-size: 18px !important; color: var(--theme-light-action-button-icon) !important; }
        [data-theme="dark"] .chat-action-btn i { color: var(--theme-dark-action-button-icon) !important; }

        .send-btn { background: var(--theme-light-send-button-bg) !important; }
        [data-theme="dark"] .send-btn { background: var(--theme-dark-send-button-bg) !important; }
        .send-btn:hover { background: var(--theme-light-send-button-hover-bg) !important; }
        [data-theme="dark"] .send-btn:hover { background: var(--theme-dark-send-button-hover-bg) !important; }
        .send-btn i { color: var(--theme-light-send-button-text) !important; }
        [data-theme="dark"] .send-btn i { color: var(--theme-dark-send-button-text) !important; }

        .image-upload-btn i { color: #3b82f6 !important; } /* Biru untuk ikon gambar */
        [data-theme="dark"] .image-upload-btn i { color: #60a5fa !important; } /* Biru lebih terang di dark mode */
        .image-upload-btn.has-image i { color: var(--theme-light-button-newchat-bg) !important; }
        [data-theme="dark"] .image-upload-btn.has-image i { color: var(--theme-dark-button-newchat-bg) !important; }

        #imageInput { display: none !important; }
        @media (max-width: 768px) { #textInput { font-size: 14px !important; min-height: 40px !important; } .chat-action-btn { width: 36px !important; height: 36px !important; } .chat-action-btn i { font-size: 16px !important; } .preview-image { max-width: 120px !important; max-height: 120px !important; } }

        .image-indicator {
            font-size: 13px !important; padding: 4px 8px !important;
            border-radius: 6px !important; align-items: center !important;
            gap: 4px !important; margin-bottom: 8px !important; transition: all 0.3s ease !important;
            background: var(--theme-light-image-indicator-bg) !important;
            color: var(--theme-light-image-indicator-text) !important;
        }
        [data-theme="dark"] .image-indicator {
            background: var(--theme-dark-image-indicator-bg) !important;
            color: var(--theme-dark-image-indicator-text) !important;
        }
        .image-indicator.show { display: flex !important; }
        .image-indicator.hide { display: none !important; }
        .image-indicator i { font-size: 14px !important; }

        .custom-chat-wrapper * { box-sizing: border-box !important; }

        #textInput::-webkit-scrollbar { width: 4px !important; }
        #textInput::-webkit-scrollbar-track { background: var(--theme-light-scrollbar-track) !important; border-radius: 2px !important; }
        [data-theme="dark"] #textInput::-webkit-scrollbar-track { background: var(--theme-dark-scrollbar-track) !important; }
        #textInput::-webkit-scrollbar-thumb { background: var(--theme-light-scrollbar-thumb) !important; border-radius: 2px !important; }
        [data-theme="dark"] #textInput::-webkit-scrollbar-thumb { background: var(--theme-dark-scrollbar-thumb) !important; }
        #textInput::-webkit-scrollbar-thumb:hover { background: #94a3b8 !important; }
        [data-theme="dark"] #textInput::-webkit-scrollbar-thumb:hover { background: #6b7280 !important; }


        .single__question__answer { margin-bottom: 20px; }
        .question_user { display: flex; justify-content: flex-end; margin-left: auto; max-width: 75%; }
        .question_user .left_user_info { display: flex; align-items: flex-start; gap: 10px; flex-direction: row-reverse; }
        .question_user .left_user_info img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }

        .answer__area { display: flex; align-items: flex-start; gap: 10px; max-width: 75%; }
        .answer__area .thumbnail img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;}
        .answer_main__wrapper {
            background-color: var(--theme-light-model-bubble-bg);
            color: var(--theme-light-model-bubble-text);
            padding: 10px 15px; border-radius: 18px 18px 18px 0; flex-grow: 1;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        [data-theme="dark"] .answer_main__wrapper {
            background-color: var(--theme-dark-model-bubble-bg);
            color: var(--theme-dark-model-bubble-text);
        }
        .answer_main__wrapper .common__title {
            font-size: 0.9em; font-weight: bold;
            color: var(--theme-light-model-bubble-text);
            margin-bottom: 5px;
            transition: color 0.3s ease;
        }
        [data-theme="dark"] .answer_main__wrapper .common__title {
            color: var(--theme-dark-model-bubble-text);
        }
        .answer_main__wrapper .disc {
            font-size: 1em;
            color: var(--theme-light-model-bubble-text);
            word-wrap: break-word; white-space: pre-wrap;
            transition: color 0.3s ease;
        }
        [data-theme="dark"] .answer_main__wrapper .disc {
            color: var(--theme-dark-model-bubble-text);
        }
        .answer_main__wrapper .disc p { margin-bottom: 0.5em; }
        .answer_main__wrapper .disc ul, .answer_main__wrapper .disc ol { margin-top: 0.5em; margin-bottom: 0.5em; padding-left: 20px;}
        .answer_main__wrapper .disc li { margin-bottom: 0.2em;}

        .chat-area-wrapper {
            display: flex; flex-direction: column;
            height: calc(100vh - 70px - 60px - 40px);
            background-color: var(--theme-light-bg-chat-area-wrapper);
            transition: background-color 0.3s ease;
        }
        [data-theme="dark"] .chat-area-wrapper {
            background-color: var(--theme-dark-bg-chat-area-wrapper);
        }
        .question_answer__wrapper__chatbot {
            flex-grow: 1; overflow-y: auto; padding: 20px;
            /* Scrollbar styling untuk area chat */
        }
        .question_answer__wrapper__chatbot::-webkit-scrollbar { width: 6px; }
        .question_answer__wrapper__chatbot::-webkit-scrollbar-track { background: var(--theme-light-scrollbar-track); }
        [data-theme="dark"] .question_answer__wrapper__chatbot::-webkit-scrollbar-track { background: var(--theme-dark-scrollbar-track); }
        .question_answer__wrapper__chatbot::-webkit-scrollbar-thumb { background: var(--theme-light-scrollbar-thumb); border-radius: 3px;}
        [data-theme="dark"] .question_answer__wrapper__chatbot::-webkit-scrollbar-thumb { background: var(--theme-dark-scrollbar-thumb); }
        .question_answer__wrapper__chatbot::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        [data-theme="dark"] .question_answer__wrapper__chatbot::-webkit-scrollbar-thumb:hover { background: #6b7280; }


        .main-center-content-m-left.search-sticky .chat-input-area {
            position: sticky; bottom: 0; z-index: 10;
        }
        /* Copyright */
        .copyright-area-bottom {
            text-align: center;
            padding: 10px 15px; /* Padding lebih kecil */
            background-color: var(--theme-light-bg-input-area); /* Samakan dengan area input */
            border-top: 1px solid var(--theme-light-border);
            transition: background-color 0.3s ease, border-top-color 0.3s ease;
        }
        [data-theme="dark"] .copyright-area-bottom {
            background-color: var(--theme-dark-bg-input-area);
            border-top-color: var(--theme-dark-border);
        }
        .copyright-area-bottom p { margin-bottom: 0; color: var(--theme-light-text-secondary); font-size: 0.85rem;}
        [data-theme="dark"] .copyright-area-bottom p { color: var(--theme-dark-text-secondary); }
        .copyright-area-bottom a { color: var(--theme-light-button-newchat-bg) !important; text-decoration: none; font-weight: 500;}
        [data-theme="dark"] .copyright-area-bottom a { color: var(--theme-dark-button-newchat-bg) !important;}
        .copyright-area-bottom a:hover { text-decoration: underline; color: var(--theme-light-button-newchat-hover-bg) !important;}
        [data-theme="dark"] .copyright-area-bottom a:hover { color: var(--theme-dark-button-newchat-hover-bg) !important;}

    </style>
</head>

<body class="chatbot"> <?php include 'partials/header.php'; ?>
    <div class="dash-board-main-wrapper">
        <?php include 'partials/sidebar.php'; ?>
        <div class="main-center-content-m-left center-content search-sticky">
            <div class="chat-area-wrapper">
                <div class="question_answer__wrapper__chatbot" id="chatHistory">
                    </div>
                <div class="chat-input-area">
                    <div class="new-chat-button-container">
                        <button type="button" id="newChatButton">
                            <i class="fa-regular fa-plus"></i> Percakapan Baru
                        </button>
                    </div>
                    <div class="custom-chat-wrapper">
                        <div class="image-preview-section hide" id="imagePreviewSection">
                            <div class="preview-image-container" id="previewImageContainer"></div>
                        </div>
                        <div class="image-indicator hide" id="imageIndicator">
                            <i class="fa-regular fa-check-circle"></i>
                            <span>1 gambar dipilih</span>
                        </div>
                        <div class="input-container">
                            <textarea id="textInput" placeholder="Tanyakan tentang gizi makanan..."></textarea>
                            <input type="file" id="imageInput" accept="image/*">
                            <button type="button" class="chat-action-btn image-upload-btn" id="imageUploadBtn">
                                <i class="fa-regular fa-image"></i>
                            </button>
                            <button type="button" class="chat-action-btn send-btn" id="sendButton">
                                <i class="fa-regular fa-arrow-up"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="copyright-area-bottom">
                <p> <a href="#">NutriSnap</a> &copy; <?php echo date("Y"); ?>. All Rights Reserved.</p>
            </div>
        </div>
    </div>

    <script>
        const initialConversationIdFromPHP = <?php echo json_encode($initial_conversation_id_for_js); ?>;
        const currentUserIdFromPHP = <?php echo json_encode($current_user_id_for_js); ?>;
        const userAvatarPathFromPHP = <?php echo json_encode($user_avatar_path); ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/autosize@6.0.1/dist/autosize.min.js"></script>
    <?php include 'partials/script.php'; ?>
    <script src="assets/js/chatbot.js"></script>
</body>
</html>
