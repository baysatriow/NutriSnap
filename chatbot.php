<?php include 'partials/session.php'; ?>
<?php include 'partials/main.php'; ?>

<head>
    <?php include 'partials/title-meta.php'; ?>
    <!-- bootstrap Css -->
    <?php include 'partials/head-css.php'; ?>
<style>
    .typing-indicator {
        display: inline-block;
        color: #666;
        font-style: italic;
    }

    .typing-indicator .dots {
        display: inline-block;
        position: relative;
    }

    .typing-indicator .dots::after {
        content: '';
        animation: typing-dots 1.5s infinite;
        position: absolute;
        width: 0;
    }

    @keyframes typing-dots {
        0% {
            content: '';
        }
        25% {
            content: '.';
        }
        50% {
            content: '..';
        }
        75% {
            content: '...';
        }
        100% {
            content: '';
        }
    }

    /* Custom chat input wrapper */
    .custom-chat-wrapper {
        position: relative !important;
        background: #fff !important;
        border-radius: 16px !important;
        padding: 12px 16px !important;
        margin: 20px auto !important;
        max-width: 100% !important;
        width: 100% !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08) !important;
        border: 2px solid #e5e7eb !important;
        transition: all 0.3s ease !important;
    }

    .custom-chat-wrapper:focus-within {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
    }

    /* Image preview section */
    .image-preview-section {
        margin-bottom: 12px !important;
        position: relative !important;
        min-height: 0 !important;
        transition: all 0.3s ease !important;
    }

    .image-preview-section.show {
        display: block !important;
    }

    .image-preview-section.hide {
        display: none !important;
    }

    .preview-image-container {
        position: relative !important;
        display: inline-block !important;
        line-height: 0 !important;
    }

    .preview-image {
        max-width: 150px !important;
        max-height: 150px !important;
        object-fit: cover !important;
        border-radius: 8px !important;
        display: block !important;
        border: 2px solid #e5e7eb !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    }

    .remove-image-btn {
        position: absolute !important;
        top: -8px !important;
        right: -8px !important;
        background: #ef4444 !important;
        color: white !important;
        border: 2px solid white !important;
        width: 24px !important;
        height: 24px !important;
        border-radius: 50% !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 14px !important;
        line-height: 1 !important;
        font-weight: bold !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
        z-index: 10 !important;
    }

    .remove-image-btn:hover {
        background: #dc2626 !important;
        transform: scale(1.1) !important;
    }

    /* Input container */
    .input-container {
        display: flex !important;
        align-items: flex-end !important;
        gap: 8px !important;
        min-height: 44px !important;
    }

    /* Textarea styling */
    #textInput {
        flex: 1 !important;
        border: none !important;
        outline: none !important;
        font-size: 15px !important;
        line-height: 1.5 !important;
        resize: none !important;
        min-height: 44px !important;
        max-height: 120px !important;
        background: transparent !important;
        padding: 10px 0 !important;
        overflow-y: auto !important;
        font-family: inherit !important;
    }

    #textInput::placeholder {
        color: #9ca3af !important;
    }

    /* Button styling */
    .chat-action-btn {
        background: none !important;
        border: none !important;
        cursor: pointer !important;
        padding: 8px !important;
        border-radius: 8px !important;
        transition: all 0.2s ease !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 40px !important;
        height: 40px !important;
        flex-shrink: 0 !important;
    }

    .chat-action-btn:hover {
        background: #f3f4f6 !important;
    }

    .chat-action-btn i {
        font-size: 18px !important;
        color: #6b7280 !important;
    }

    .send-btn {
        background: #10b981 !important;
        color: white !important;
    }

    .send-btn:hover {
        background: #059669 !important;
    }

    .send-btn i {
        color: white !important;
    }

    .image-upload-btn.has-image i {
        color: #10b981 !important;
    }

    .image-upload-btn i {
        color: #3b82f6 !important;
    }

    /* Hidden file input */
    #imageInput {
        display: none !important;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .custom-chat-wrapper {
            padding: 10px 12px !important;
            margin: 15px auto !important;
        }

        #textInput {
            font-size: 14px !important;
            min-height: 40px !important;
        }

        .chat-action-btn {
            width: 36px !important;
            height: 36px !important;
        }

        .chat-action-btn i {
            font-size: 16px !important;
        }

        .preview-image {
            max-width: 120px !important;
            max-height: 120px !important;
        }
    }

    /* Image indicator */
    .image-indicator {
        color: #3b82f6 !important;
        font-size: 13px !important;
        padding: 4px 8px !important;
        background: #eff6ff !important;
        border-radius: 6px !important;
        align-items: center !important;
        gap: 4px !important;
        margin-bottom: 8px !important;
        transition: all 0.3s ease !important;
    }

    .image-indicator.show {
        display: flex !important;
    }

    .image-indicator.hide {
        display: none !important;
    }

    .image-indicator i {
        font-size: 14px !important;
    }

    /* Override any conflicting styles */
    .custom-chat-wrapper * {
        box-sizing: border-box !important;
    }

    /* Scrollbar styling for textarea */
    #textInput::-webkit-scrollbar {
        width: 4px !important;
    }

    #textInput::-webkit-scrollbar-track {
        background: #f1f5f9 !important;
        border-radius: 2px !important;
    }

    #textInput::-webkit-scrollbar-thumb {
        background: #cbd5e1 !important;
        border-radius: 2px !important;
    }

    #textInput::-webkit-scrollbar-thumb:hover {
        background: #94a3b8 !important;
    }
</style>
    
</head>

<body class="chatbot">

<?php include 'partials/header.php'; ?>

    <div class="dash-board-main-wrapper">
        <?php include 'partials/sidebar.php'; ?>
        <div class="main-center-content-m-left center-content search-sticky">
            <!-- Container for Question and Answer -->
            <div class="question_answer__wrapper__chatbot" id="chatHistory">
                <!-- Buat Jawaban -->
            </div>
            
            <!-- Custom Chat Wrapper -->
            <div class="custom-chat-wrapper">
                <!-- Image Preview Section -->
                <div class="image-preview-section hide" id="imagePreviewSection">
                    <div class="preview-image-container" id="previewImageContainer">
                        <!-- Preview image akan muncul di sini -->
                    </div>
                </div>
                
                <!-- Image Indicator -->
                <div class="image-indicator hide" id="imageIndicator">
                    <i class="fa-regular fa-check-circle"></i>
                    <span>1 gambar dipilih</span>
                </div>
                
                <!-- Input Container -->
                <div class="input-container">
                    <textarea id="textInput" placeholder="Tanyakan tentang gizi makanan..."></textarea>
                    
                    <input type="file" id="imageInput" accept="image/*" onchange="previewImage()">
                    
                    <button type="button" class="chat-action-btn image-upload-btn" id="imageUploadBtn" onclick="document.getElementById('imageInput').click();">
                        <i class="fa-regular fa-image"></i>
                    </button>
                    
                    <button type="button" class="chat-action-btn send-btn" onclick="sendMessage()">
                        <i class="fa-regular fa-arrow-up"></i>
                    </button>
                </div>
            </div>
            
            <div class="copyright-area-bottom">
                <p> <a href="#">NutriSnap</a> 2025. All Rights Reserved.</p>
            </div>
        </div>
    </div>

    <!-- jquery Js -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <?php include 'partials/script.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/autosize@4.0.2/dist/autosize.min.js"></script>
    <script>
        // Auto-resize textarea
        autosize(document.querySelector('#textInput'));
        
        // Prevent form submission on Enter (untuk textarea multiline)
        document.getElementById('textInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Click outside to focus textarea
        document.querySelector('.custom-chat-wrapper').addEventListener('click', function(e) {
            if (e.target === this) {
                document.getElementById('textInput').focus();
            }
        });
    </script>
</body>

</html>