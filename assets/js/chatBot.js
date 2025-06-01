const API_KEY = 'AIzaSyCytmQ3fsd-OqKbpkpstomQU17r_IMXMJI';
const API_URL_STREAM = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-04-17:streamGenerateContent?key=${API_KEY}&alt=sse`;

const SAVE_CHAT_URL = './save_chat.php';
const LOAD_CHAT_URL = './load_chat.php';

let imageFile = null;
let conversationHistoryForAPI = [];
let currentConversationId = null;

async function sendToServer(url, data, method = 'POST') {
  try {
    const options = {
      method: method,
      headers: { 'Content-Type': 'application/json' }
    };
    if (method === 'POST' || method === 'PUT' || method === 'PATCH') {
      if (data) { options.body = JSON.stringify(data); }
    }
    const response = await fetch(url, options);
    if (!response.ok) {
      let errorData;
      try { errorData = await response.json(); }
      catch (e) { errorData = { message: `Server error: ${response.status}. Respons bukan JSON.` }; }
      console.error('Server request failed:', response.status, errorData.message || `Server returned status ${response.status}`);
      throw new Error(errorData.message || `Server error: ${response.status}`);
    }
    const responseData = await response.json();
    console.log("Respons JSON diterima dari server PHP:", responseData);
    return responseData;
  } catch (error) {
    console.error(`Error in sendToServer (${url}):`, error.message);
    throw error;
  }
}

function generateConversationId() {
    return `conv_${Date.now()}_${Math.random().toString(36).substring(2, 9)}`;
}

async function sendMessage() {
    const textInputElement = document.getElementById('textInput');
    const textInput = textInputElement.value.trim();
    const chatHistoryUI = document.getElementById('chatHistory');

    if (typeof currentUserIdFromPHP === 'undefined' || !currentUserIdFromPHP) {
        alert('Sesi pengguna tidak valid atau ID pengguna tidak ditemukan. Silakan login kembali.');
        appendMessageToUI('model', 'Sesi pengguna tidak valid. Tidak dapat mengirim pesan.');
        return;
    }

    if (!textInput && !imageFile) {
        alert('Masukkan teks atau gambar!');
        return;
    }

    let userMessageForDisplay = textInput;
    if (imageFile && textInput) {
        userMessageForDisplay = `${textInput} [Gambar Dikirim]`;
    } else if (imageFile && !textInput) {
        userMessageForDisplay = '[Gambar Dikirim]';
    }
    appendMessageToUI('user', userMessageForDisplay);

    let currentUserParts = [];
    if (textInput) currentUserParts.push({ text: textInput });

    let base64Image = null;
    if (imageFile) {
        try {
            base64Image = await toBase64(imageFile);
            currentUserParts.push({
                inlineData: {
                    mimeType: imageFile.type,
                    data: base64Image.split(',')[1]
                }
            });
        } catch (error) {
            console.error("Error converting image to Base64:", error);
            appendMessageToUI('model', "Gagal memproses gambar.");
            clearInput();
            return;
        }
    }

    const userMessageDataForDB = {
        user_id: currentUserIdFromPHP,
        conversation_id: currentConversationId,
        sender: 'user',
        message: textInput || (imageFile ? "[Gambar Dikirim]" : "[Pesan Kosong]"),
        image_data: imageFile ? base64Image : null
    };

    try {
        await sendToServer(SAVE_CHAT_URL, userMessageDataForDB);
        console.log("Pesan pengguna disimpan ke DB.");
    } catch (error) {
        console.error("Gagal menyimpan pesan pengguna ke DB:", error);
    }

    conversationHistoryForAPI.push({ role: 'user', parts: currentUserParts });

    const bodyForAPI = {
        contents: conversationHistoryForAPI,
        generationConfig: { temperature: 0.7, maxOutputTokens: 2048 },
    };

    const initialBubble = createResponseBubbleStructure();
    if (chatHistoryUI) chatHistoryUI.appendChild(initialBubble.bubbleDiv);
    const responseTextElement = initialBubble.textElement;
    if (chatHistoryUI) chatHistoryUI.scrollTop = chatHistoryUI.scrollHeight;

    clearInput();
    let fullResponseText = "";

    try {
        const response = await fetch(API_URL_STREAM, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(bodyForAPI),
        });

        if (!response.ok) {
            let errorBodyText = `API Error ${response.status}`;
            try { const errorBody = await response.json(); errorBodyText += `: ${JSON.stringify(errorBody)}`; }
            catch (e) { try { errorBodyText += `: ${await response.text()}`; } catch (readError) {} }
            console.error(errorBodyText);
            if (responseTextElement) responseTextElement.textContent = `Terjadi kesalahan saat menghubungi server (${response.status}).`;
            conversationHistoryForAPI.pop();
            return;
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();

        while (true) {
            const { value, done } = await reader.read();
            if (done) break;
            const chunkText = decoder.decode(value, { stream: true });
            const lines = chunkText.split('\n');
            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    const jsonString = line.substring(5).trim();
                    if (jsonString) {
                        try {
                            const jsonData = JSON.parse(jsonString);
                            const textContent = jsonData?.candidates?.[0]?.content?.parts?.[0]?.text || '';
                            if (textContent && responseTextElement) {
                                responseTextElement.textContent += textContent;
                                fullResponseText += textContent;
                                if (chatHistoryUI) chatHistoryUI.scrollTop = chatHistoryUI.scrollHeight;
                            }
                            const finishReason = jsonData?.candidates?.[0]?.finishReason;
                            if (finishReason && finishReason !== "STOP" && responseTextElement) {
                                console.warn("Stream finished with reason:", finishReason);
                                let reasonText = "";
                                if (finishReason === "SAFETY") { reasonText = "\n\n(Respons diblokir karena alasan keamanan)"; }
                                else if (finishReason === "MAX_TOKENS") { reasonText = "\n\n(Respons mungkin terpotong karena batas maksimum)";}
                                else { reasonText = `\n\n(Stream dihentikan: ${finishReason})` }
                                if (typeof marked !== 'undefined') {
                                     responseTextElement.innerHTML = marked.parse(responseTextElement.textContent + reasonText);
                                } else {
                                     responseTextElement.textContent += reasonText;
                                }
                            }
                        } catch (parseError) {
                            console.warn("Gagal parsing JSON dari stream:", jsonString, parseError);
                        }
                    }
                }
            }
        }

        let displayText = fullResponseText;
        if (!isValidNutritionResponse(fullResponseText) && fullResponseText.trim() !== "") {
            displayText = "Maaf, saya hanya dapat memberikan informasi terkait gizi makanan dan pola makan.";
        }

        let aiMessageForDB;
        if (displayText) {
            conversationHistoryForAPI.push({ role: 'model', parts: [{ text: fullResponseText }] });
            aiMessageForDB = {
                user_id: currentUserIdFromPHP,
                conversation_id: currentConversationId,
                sender: 'model',
                message: displayText,
                image_data: null
            };
        } else {
            const placeholderText = "(Respons tidak valid atau diblokir)";
            conversationHistoryForAPI.push({ role: 'model', parts: [{ text: placeholderText }] });
            aiMessageForDB = {
                user_id: currentUserIdFromPHP,
                conversation_id: currentConversationId,
                sender: 'model',
                message: placeholderText,
                image_data: null
            };
            console.warn("Respons model kosong atau diblokir sepenuhnya.");
        }

        try {
            await sendToServer(SAVE_CHAT_URL, aiMessageForDB);
            console.log("Respons AI disimpan ke DB.");
        } catch (error) {
            console.error("Gagal menyimpan respons AI ke DB:", error);
        }

        if (responseTextElement) {
            if (typeof marked !== 'undefined') {
                responseTextElement.innerHTML = marked.parse(displayText || "(Tidak ada respons teks)");
            } else {
                responseTextElement.textContent = displayText || "(Tidak ada respons teks)";
            }
        }
        if (chatHistoryUI) chatHistoryUI.scrollTop = chatHistoryUI.scrollHeight;

        const MAX_HISTORY_TURNS = 10;
        if (conversationHistoryForAPI.length > MAX_HISTORY_TURNS * 2) {
            conversationHistoryForAPI.splice(0, conversationHistoryForAPI.length - (MAX_HISTORY_TURNS * 2));
            console.log("Riwayat percakapan API dipotong.");
        }

    } catch (error) {
        console.error('Error fetching or processing stream:', error);
        if(conversationHistoryForAPI.length > 0 && conversationHistoryForAPI[conversationHistoryForAPI.length -1].role === 'user'){
            conversationHistoryForAPI.pop();
        }
        if (responseTextElement) {
            responseTextElement.textContent = "Terjadi kesalahan koneksi atau pemrosesan data.";
        } else {
            appendMessageToUI('model', "Terjadi kesalahan koneksi atau pemrosesan data.");
        }
        if(chatHistoryUI) chatHistoryUI.scrollTop = chatHistoryUI.scrollHeight;
    }
}

function createResponseBubbleStructure() {
    const div = document.createElement('div');
    div.className = `single__question__answer`;
    div.innerHTML = `
      <div class="answer__area">
        <div class="thumbnail">
          <img src="assets/images/avatar/04.png" alt="avatar" onerror="this.onerror=null; this.src='https://placehold.co/40x40/E0E0E0/B0B0B0?text=AI'">
        </div>
        <div class="answer_main__wrapper">
          <h4 class="common__title">NutriSnap</h4>
          <div class="disc"></div>
        </div>
      </div>
    `;
    const textElement = div.querySelector('.answer_main__wrapper .disc');
    return { bubbleDiv: div, textElement: textElement };
}

function appendMessageToUI(role, message, isMarkdown = false) {
    const chatHistoryUI = document.getElementById('chatHistory');
    if (!chatHistoryUI) {
        console.error("Elemen chatHistory tidak ditemukan di DOM.");
        return null;
    }
    const div = document.createElement('div');
    div.className = `single__question__answer`;
    const userAvatar = (typeof userAvatarPathFromPHP !== 'undefined' && userAvatarPathFromPHP)
                       ? userAvatarPathFromPHP
                       : 'assets/images/avatar/user.svg';

    if (role === 'user') {
        div.innerHTML = `
          <div class="question_user">
            <div class="left_user_info">
              <div class="question__user_text"></div>
              <img src="${userAvatar}" alt="avatar" onerror="this.onerror=null; this.src='https://placehold.co/40x40/E0E0E0/B0B0B0?text=U'">
            </div>
          </div>
        `;
        div.querySelector('.question__user_text').textContent = message;
    } else if (role === 'model') {
        div.innerHTML = `
          <div class="answer__area">
            <div class="thumbnail">
              <img src="assets/images/avatar/04.png" alt="avatar" onerror="this.onerror=null; this.src='https://placehold.co/40x40/E0E0E0/B0B0B0?text=AI'">
            </div>
            <div class="answer_main__wrapper">
              <h4 class="common__title">NutriSnap</h4>
              <div class="disc"></div>
            </div>
          </div>
        `;
        const discElement = div.querySelector('.disc');
        if (isMarkdown && typeof marked !== 'undefined') {
            discElement.innerHTML = marked.parse(message);
        } else {
            discElement.textContent = message;
        }
    }

    chatHistoryUI.appendChild(div);
    chatHistoryUI.scrollTop = chatHistoryUI.scrollHeight;
    return div;
}

function clearInput() {
    const textInputElement = document.getElementById('textInput');
    if(textInputElement) textInputElement.value = '';
    const imageInputElement = document.getElementById('imageInput');
    if(imageInputElement) imageInputElement.value = '';
    imageFile = null;
    const imagePreviewSection = document.getElementById('imagePreviewSection');
    const previewImageContainer = document.getElementById('previewImageContainer');
    const imageIndicator = document.getElementById('imageIndicator');
    const imageUploadBtn = document.getElementById('imageUploadBtn');
    if (imagePreviewSection) imagePreviewSection.className = 'image-preview-section hide';
    if (previewImageContainer) previewImageContainer.innerHTML = '';
    if (imageIndicator) imageIndicator.className = 'image-indicator hide';
    if (imageUploadBtn) imageUploadBtn.className = 'chat-action-btn image-upload-btn';
    if (typeof autosize !== 'undefined' && textInputElement) {
        autosize.update(textInputElement);
    }
}

function toBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

function previewImage() {
    const fileInput = document.getElementById('imageInput');
    if (!fileInput) return;
    const file = fileInput.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
        alert('Silakan pilih file gambar.');
        fileInput.value = ''; return;
    }
    imageFile = file;
    const imagePreviewSection = document.getElementById('imagePreviewSection');
    const previewImageContainer = document.getElementById('previewImageContainer');
    const imageIndicator = document.getElementById('imageIndicator');
    const imageUploadBtn = document.getElementById('imageUploadBtn');
    if (imageIndicator) imageIndicator.className = 'image-indicator show';
    if (imageUploadBtn) imageUploadBtn.className = 'chat-action-btn image-upload-btn has-image';
    const reader = new FileReader();
    reader.onload = function(e) {
        if (previewImageContainer) {
            previewImageContainer.innerHTML = `
              <img src="${e.target.result}" class="preview-image" alt="Preview Gambar"/>
              <button class="remove-image-btn" onclick="removeImage()" aria-label="Hapus Gambar">×</button>
            `;
        }
        if (imagePreviewSection) imagePreviewSection.className = 'image-preview-section show';
    };
    reader.onerror = function(error) { console.error('FileReader error:', error); alert('Gagal membaca file preview.'); removeImage(); };
    reader.readAsDataURL(file);
}

function removeImage() {
    const imagePreviewSection = document.getElementById('imagePreviewSection');
    const previewImageContainer = document.getElementById('previewImageContainer');
    const imageInput = document.getElementById('imageInput');
    const imageIndicator = document.getElementById('imageIndicator');
    const imageUploadBtn = document.getElementById('imageUploadBtn');
    imageFile = null;
    if (imageInput) imageInput.value = '';
    if (previewImageContainer) previewImageContainer.innerHTML = '';
    if (imagePreviewSection) imagePreviewSection.className = 'image-preview-section hide';
    if (imageIndicator) imageIndicator.className = 'image-indicator hide';
    if (imageUploadBtn) imageUploadBtn.className = 'chat-action-btn image-upload-btn';
}

function isValidNutritionResponse(response) {
    if (!response || !response.trim()) {
        console.log("Respons kosong atau hanya spasi.");
        return false;
    }

    const lowerCaseResponse = response.toLowerCase();

    const invalidKeywords = [
        'paku'
    ];

    for (const keyword of invalidKeywords) {
        if (lowerCaseResponse.includes(keyword.toLowerCase())) {
            console.log(`Ditemukan kata kunci tidak valid: "${keyword}"`);
            return false;
        }
    }

    const validKeywords = [
        'gizi', 'nutrisi', 'makanan sehat', 'pola makan', 'vitamin', 'mineral', 'diet', 'kalori',
        'protein', 'karbohidrat', 'lemak', 'serat', 'kandungan gizi', 'makronutrien', 'mikronutrien',
        'resep sehat', 'indeks glikemik', 'porsi makan', 'makanan bergizi', 'makanan seimbang',
        'hidrasi', 'suplemen', 'label nutrisi', 'superfood', 'antioksidan', 'metabolisme',
        'energi', 'pencernaan', 'vegetarian', 'vegan', 'rendah gula', 'rendah garam',
        'tinggi protein', 'tinggi serat', 'masak sehat', 'bahan makanan', 'buah', 'sayur',
        'kebutuhan kalori', 'kebutuhan gizi', 'sarapan', 'makan siang', 'makan malam', 'camilan',
        'air putih', 'elektrolit', 'asam amino', 'omega 3', 'probiotik', 'prebiotik',
        'indeks massa tubuh', 'bmi', 'defisit kalori', 'surplus kalori', 'penambah berat badan',
        'penurun berat badan', 'ahli gizi', 'nutrisionis', 'konsultasi gizi', 'jurnal makanan'
    ];

    const foundValidKeyword = validKeywords.some(keyword => lowerCaseResponse.includes(keyword.toLowerCase()));

    if (!foundValidKeyword) {
        console.log("Tidak ditemukan kata kunci nutrisi yang valid.");
    }

    return foundValidKeyword;
}

async function loadChatHistory() {
    const chatHistoryUI = document.getElementById('chatHistory');
    if (!chatHistoryUI) {
        console.error("Elemen #chatHistory tidak ditemukan untuk memuat riwayat.");
        return;
    }
    if (!currentConversationId) {
        console.log("Tidak ada ID percakapan untuk dimuat. Memulai sesi baru.");
        chatHistoryUI.innerHTML = '';
        appendMessageToUI('model', "Selamat datang di NutriSnap! Tanya seputar gizi atau unggah gambar makanan.", true);
        return;
    }
    try {
        const historyData = await sendToServer(`${LOAD_CHAT_URL}?conversation_id=${currentConversationId}`, null, 'GET');
        chatHistoryUI.innerHTML = '';
        conversationHistoryForAPI = [];

        if (historyData.success && Array.isArray(historyData.chat_history)) {
            if (historyData.chat_history.length > 0) {
                historyData.chat_history.forEach(item => {
                    appendMessageToUI(item.sender, item.message, item.sender === 'model');
                    let parts = [{ text: item.message }];

                    if (item.sender === 'user' && item.image_data) {
                        try {
                            const base64Parts = item.image_data.split(',');
                            if (base64Parts.length === 2) {
                                const mimeTypeMatch = base64Parts[0].match(/:(.*?);/);
                                if (mimeTypeMatch && mimeTypeMatch[1]) {
                                    parts.push({ inlineData: { mimeType: mimeTypeMatch[1], data: base64Parts[1] } });
                                    if (item.message === "[Gambar Dikirim]") {
                                        const textPartIndex = parts.findIndex(p => p.text === "[Gambar Dikirim]");
                                        if (textPartIndex > -1) parts.splice(textPartIndex, 1);
                                    }
                                } else {
                                    console.warn("Tidak dapat mengekstrak mimeType dari image_data historis:", item.image_data.substring(0, 50));
                                }
                            } else {
                                console.warn("Format image_data historis tidak sesuai (tidak ada koma pemisah):", item.image_data.substring(0,50));
                            }
                        } catch(e) {
                            console.error("Error memproses image_data dari histori:", e);
                        }
                    }
                    conversationHistoryForAPI.push({ role: item.sender, parts: parts.filter(p => p.text !== "[Gambar Dikirim]" || !parts.some(pt => pt.inlineData)) }); // Filter out placeholder if image exists
                });
                console.log("Riwayat percakapan dimuat dari server.");
            } else {
                console.log("Tidak ada riwayat percakapan di server untuk ID ini.");
                appendMessageToUI('model', "Selamat datang kembali! Belum ada riwayat untuk percakapan ini. Lanjutkan atau mulai topik baru.", true);
            }
        } else {
            console.error("Gagal memuat riwayat dari server atau format data salah:", historyData.message || "Format data tidak sesuai");
            appendMessageToUI('model', "Gagal memuat riwayat percakapan. Memulai sesi baru.", true);
        }
    } catch (error) {
        console.error("Error loading chat history:", error);
        chatHistoryUI.innerHTML = '';
        appendMessageToUI('model', "Terjadi kesalahan saat memuat riwayat. Memulai sesi baru.", true);
    }
    if(chatHistoryUI) chatHistoryUI.scrollTop = chatHistoryUI.scrollHeight;
}


window.onload = async () => {
    if (typeof initialConversationIdFromPHP !== 'undefined' && initialConversationIdFromPHP) {
        currentConversationId = initialConversationIdFromPHP;
        localStorage.setItem('nutriSnapConversationId', currentConversationId);
        console.log("NutriSnap: Memuat sesi dari URL, ID:", currentConversationId);
        await loadChatHistory();
    } else {
        currentConversationId = localStorage.getItem('nutriSnapConversationId');
        if (!currentConversationId ) {
            currentConversationId = generateConversationId();
            localStorage.setItem('nutriSnapConversationId', currentConversationId);
            console.log("NutriSnap: Sesi baru, ID:", currentConversationId);
            appendMessageToUI('model', "Selamat datang di NutriSnap! Tanya seputar gizi atau unggah gambar makanan.", true);
        } else {
            console.log("NutriSnap: Melanjutkan sesi dari localStorage, ID:", currentConversationId);
            await loadChatHistory();
        }
    }

    const sendButton = document.getElementById('sendButton');
    if (sendButton) sendButton.addEventListener('click', sendMessage);

    const textInput = document.getElementById('textInput');
    if (textInput) {
        textInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        });
        if (typeof autosize !== 'undefined') autosize(textInput); else console.warn("Autosize library not loaded.");
    }

    const imageInput = document.getElementById('imageInput');
    if (imageInput) imageInput.addEventListener('change', previewImage);

    const imageUploadBtn = document.getElementById('imageUploadBtn');
    if(imageUploadBtn && imageInput) {
        imageUploadBtn.addEventListener('click', () => {
            imageInput.click();
        });
    }

    const newChatButton = document.getElementById('newChatButton');
    if (newChatButton) {
        newChatButton.addEventListener('click', () => {
            if (confirm("Apakah Anda yakin ingin memulai percakapan baru? Riwayat saat ini akan disimpan jika ada pesan.")) {
                currentConversationId = generateConversationId();
                localStorage.setItem('nutriSnapConversationId', currentConversationId);

                const chatHistoryUI = document.getElementById('chatHistory');
                if(chatHistoryUI) chatHistoryUI.innerHTML = '';
                conversationHistoryForAPI = [];
                clearInput();
                appendMessageToUI('model', "Percakapan baru dimulai! Silakan ajukan pertanyaan Anda.", true);
                console.log("Percakapan baru dimulai dengan ID:", currentConversationId);
            }
        });
    } else {
        // console.warn("Tombol 'newChatButton' tidak ditemukan.");
    }
};
