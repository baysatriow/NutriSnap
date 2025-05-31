const API_KEY_REC = 'AIzaSyCytmQ3fsd-OqKbpkpstomQU17r_IMXMJI';
const API_URL_STREAM_REC = `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:streamGenerateContent?key=${API_KEY_REC}&alt=sse`;
const SAVE_RECOMMENDATION_URL = './save_recommendation.php';

let conversationHistoryForAPI_REC = [];

async function sendToServer(url, data, method = 'POST') {
  try {
    const options = { method: method, headers: { 'Content-Type': 'application/json' } };
    if ((method === 'POST' || method === 'PUT' || method === 'PATCH') && data) {
      options.body = JSON.stringify(data);
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

async function getMealRecommendation(forceNew = false) {
    if (!recommendationDisplayElement || !loadingIndicatorElement || !getRecommendationBtnElement) {
        console.error("Elemen UI inti untuk rekomendasi tidak diinisialisasi. Menginisialisasi ulang...");
        recommendationDisplayElement = document.getElementById('recommendationDisplay');
        loadingIndicatorElement = document.getElementById('loadingIndicator');
        getRecommendationBtnElement = document.getElementById('getRecommendationBtn');
        if (!recommendationDisplayElement || !loadingIndicatorElement || !getRecommendationBtnElement) {
            alert("Kesalahan internal: Elemen UI penting tidak ditemukan. Silakan refresh halaman.");
            return;
        }
    }

    if (typeof loadSpecificRecommendationFromPHP !== 'undefined' && loadSpecificRecommendationFromPHP &&
        typeof specificRecommendationTextFromPHP !== 'undefined' && specificRecommendationTextFromPHP && !forceNew) {

        if (loadingIndicatorElement) loadingIndicatorElement.style.display = 'none';
        if (typeof marked !== 'undefined') {
            recommendationDisplayElement.innerHTML = marked.parse(specificRecommendationTextFromPHP);
        } else {
            recommendationDisplayElement.textContent = specificRecommendationTextFromPHP;
        }
        getRecommendationBtnElement.textContent = "Minta Rekomendasi Baru Lainnya";
        getRecommendationBtnElement.disabled = false;
        console.log("Menampilkan riwayat rekomendasi spesifik ID:", (typeof currentRecommendationIdFromPHP !== 'undefined' ? currentRecommendationIdFromPHP : 'N/A'));
        return;
    }

    loadingIndicatorElement.style.display = 'flex';
    loadingIndicatorElement.textContent = 'Memproses permintaan Anda...';
    recommendationDisplayElement.innerHTML = '';
    recommendationDisplayElement.appendChild(loadingIndicatorElement);
    getRecommendationBtnElement.disabled = true;
    getRecommendationBtnElement.textContent = "Memuat...";

    let systemInstruction = "Anda adalah NutriSnap, seorang ahli gizi virtual yang ramah dan informatif. Tugas Anda adalah memberikan rekomendasi pola makan harian yang dipersonalisasi.";
    let userQueryPrompt = "Tolong berikan saya rekomendasi pola makan harian (sarapan, makan siang, makan malam, dan 2-3 camilan) berdasarkan data saya. Sertakan perkiraan porsi dan jenis makanan. Fokus pada makanan yang sehat, seimbang, dan mudah ditemukan di Indonesia. Jika ada informasi yang kurang dari data saya, berikan rekomendasi umum yang tetap bermanfaat berdasarkan tujuan kesehatan saya. Format respons dalam Markdown yang rapi.";
    let userDataText = "\n\nBerikut adalah data pengguna:\n";

    if (typeof userDataFromPHP !== 'undefined' && userDataFromPHP) {
        userDataText += `- Nama: ${userDataFromPHP.firstName || ''} ${userDataFromPHP.lastName || ''}\n`;
        if (userDataFromPHP.age) { userDataText += `- Usia: ${userDataFromPHP.age} tahun\n`; }
        else if (userDataFromPHP.dob) { userDataText += `- Tanggal Lahir: ${userDataFromPHP.dob}\n`; }
        userDataText += `- Jenis Kelamin: ${userDataFromPHP.gender || 'Tidak diketahui'}\n`;
        userDataText += `- Tinggi Badan: ${userDataFromPHP.heightCm || 'Tidak diketahui'} cm\n`;
        userDataText += `- Berat Badan: ${userDataFromPHP.weightKg || 'Tidak diketahui'} kg\n`;
        userDataText += `- Tingkat Aktivitas: ${userDataFromPHP.activityLevel || 'Tidak diketahui'}\n`;
        userDataText += `- Tujuan Kesehatan Utama: ${userDataFromPHP.healthGoal || 'Tidak ada tujuan spesifik, fokus pada kesehatan umum'}\n`;
        userDataText += `- Catatan/Bio: ${userDataFromPHP.bio || 'Tidak ada catatan tambahan'}\n`;
    } else {
        userDataText += "- Data pengguna tidak tersedia. Berikan rekomendasi pola makan umum yang sehat dan seimbang untuk orang dewasa di Indonesia.\n";
    }

    conversationHistoryForAPI_REC = [{ role: 'user', parts: [{ text: systemInstruction + userDataText + userQueryPrompt }] }];
    const bodyForAPI = { contents: conversationHistoryForAPI_REC, generationConfig: { temperature: 0.7, maxOutputTokens: 2048 } };
    let fullResponseText = "";

    try {
        const response = await fetch(API_URL_STREAM_REC, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(bodyForAPI),
        });

        loadingIndicatorElement.style.display = 'none';
        getRecommendationBtnElement.disabled = false;
        getRecommendationBtnElement.textContent = "Dapatkan Rekomendasi Baru";


        if (!response.ok) {
            let errorBodyText = `API Error ${response.status}`;
            try { const errorBody = await response.json(); errorBodyText += `: ${JSON.stringify(errorBody)}`; }
            catch (e) { try { errorBodyText += `: ${await response.text()}`; } catch (readError) {} }
            console.error(errorBodyText);
            recommendationDisplayElement.innerHTML = `<p class="error-message">Terjadi kesalahan saat menghubungi AI (${response.status}). Coba lagi nanti.</p>`;
            return;
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        recommendationDisplayElement.innerHTML = '';

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
                            if (textContent) {
                                fullResponseText += textContent;
                                recommendationDisplayElement.innerHTML = typeof marked !== 'undefined' ? marked.parse(fullResponseText) : fullResponseText;
                            }
                            const finishReason = jsonData?.candidates?.[0]?.finishReason;
                            if (finishReason && finishReason !== "STOP") {
                                console.warn("Stream finished with reason:", finishReason);
                                let reasonText = "";
                                if (finishReason === "SAFETY") { reasonText = "\n\n_(Rekomendasi mungkin dipotong karena alasan keamanan.)_"; }
                                else if (finishReason === "MAX_TOKENS") { reasonText = "\n\n_(Rekomendasi mungkin terpotong karena mencapai batas panjang maksimum.)_"; }
                                else { reasonText = `\n\n_(Stream dihentikan: ${finishReason})_`; }
                                recommendationDisplayElement.innerHTML += typeof marked !== 'undefined' ? marked.parse(reasonText) : reasonText;
                            }
                        } catch (parseError) {
                            console.warn("Gagal parsing JSON dari stream:", jsonString, parseError);
                        }
                    }
                }
            }
        }

        if (fullResponseText) {
            recommendationDisplayElement.innerHTML = typeof marked !== 'undefined' ? marked.parse(fullResponseText) : fullResponseText;
        } else if (!recommendationDisplayElement.innerHTML.includes("error-message")) {
            recommendationDisplayElement.innerHTML = "<p>Tidak ada rekomendasi yang diterima atau konten diblokir.</p>";
            console.warn("Respons model kosong atau diblokir sepenuhnya.");
        }

        if (typeof currentUserIdForJS !== 'undefined' && currentUserIdForJS && fullResponseText &&
            (forceNew || (typeof loadSpecificRecommendationFromPHP !== 'undefined' && !loadSpecificRecommendationFromPHP))) {
            const recommendationDataForDB = { user_id: currentUserIdForJS, recommendation_text: fullResponseText };
            try {
                await sendToServer(SAVE_RECOMMENDATION_URL, recommendationDataForDB);
                console.log("Rekomendasi baru berhasil disimpan ke DB.");
            } catch (error) {
                console.error("Gagal menyimpan rekomendasi baru ke DB:", error);
            }
        }
    } catch (error) {
        console.error('Error fetching or processing stream for recommendation:', error);
        if (loadingIndicatorElement) loadingIndicatorElement.style.display = 'none';
        if (getRecommendationBtnElement) {
            getRecommendationBtnElement.disabled = false;
            getRecommendationBtnElement.textContent = (typeof loadSpecificRecommendationFromPHP !== 'undefined' && loadSpecificRecommendationFromPHP && !forceNew)
                                                    ? "Minta Rekomendasi Baru Lainnya"
                                                    : "Dapatkan Rekomendasi Baru";
        }
        if (recommendationDisplayElement) recommendationDisplayElement.innerHTML = `<p class="error-message">Terjadi kesalahan koneksi atau pemrosesan data saat meminta rekomendasi.</p>`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    recommendationDisplayElement = document.getElementById('recommendationDisplay');
    loadingIndicatorElement = document.getElementById('loadingIndicator');
    getRecommendationBtnElement = document.getElementById('getRecommendationBtn');

    if (getRecommendationBtnElement) {
        getRecommendationBtnElement.addEventListener('click', () => {
            getRecommendationBtnElement.textContent = "Dapatkan Rekomendasi Baru";
            getMealRecommendation(true);
        });
    } else {
        console.error("Tombol 'getRecommendationBtn' tidak ditemukan.");
    }

    if (recommendationDisplayElement && loadingIndicatorElement) {
        getMealRecommendation(false);
    } else {
         console.error("Elemen 'recommendationDisplay' atau 'loadingIndicator' tidak ditemukan saat DOMContentLoaded.");
         if(getRecommendationBtnElement) getRecommendationBtnElement.disabled = true;
    }
});
