const API_KEY = 'AIzaSyAeJx92eVbRORkPJdn163FAXajxdSPodYc';
const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-04-17:GenerateContent?key=${API_KEY}`;

let imageFile = null;

async function sendMessage() {
  const textInput = document.getElementById('textInput').value.trim();
  const chatHistory = document.getElementById('chatHistory');

  if (!textInput && !imageFile) {
    alert('Masukkan teks atau gambar!');
    return;
  }

  if (textInput) appendMessage('question_user', textInput);
  if (imageFile) appendMessage('question_user', '[Gambar Dikirim]');

  let parts = [];
  if (textInput) parts.push({ text: textInput });

  if (imageFile) {
    const base64Image = await toBase64(imageFile);
    parts.push({
      inlineData: {
        mimeType: imageFile.type,
        data: base64Image.split(',')[1]
      }
    });
  }

  const body = {
    contents: [{ role: 'user', parts }],
    generationConfig: {
      stream: true // Enable streaming
    }
  };

  try {
    const typingIndicator = appendMessage('answer_area', '<div class="typing-indicator">NutriSnap sedang mengetik<span class="dots">.</span></div>');
    
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });

    const result = await response.json();
    let reply = result?.candidates?.[0]?.content?.parts?.[0]?.text || 'Tidak ada respons.';
    
    if (!isValidNutritionResponse(reply)) {
      reply = "Maaf, saya hanya dapat memberikan informasi terkait gizi makanan dan pola makan.";
    }

    // Hapus typing indicator dan ganti dengan typewriter effect
    chatHistory.removeChild(typingIndicator);
    appendMessageWithTypewriter('answer_area', reply);

  } catch (error) {
    console.error('Error:', error);
    appendMessage('answer_area', "Terjadi kesalahan saat menghubungi server.");
  }

  // Bersihkan input setelah kirim
  clearInput();
}

function clearInput() {
  document.getElementById('textInput').value = '';
  document.getElementById('imageInput').value = '';
  imageFile = null;
  
  // Hide preview section
  const imagePreviewSection = document.getElementById('imagePreviewSection');
  const previewImageContainer = document.getElementById('previewImageContainer');
  const imageIndicator = document.getElementById('imageIndicator');
  const imageUploadBtn = document.getElementById('imageUploadBtn');
  
  imagePreviewSection.className = 'image-preview-section hide';
  previewImageContainer.innerHTML = '';
  imageIndicator.className = 'image-indicator hide';
  imageUploadBtn.className = 'chat-action-btn image-upload-btn';
  
  // Reset textarea height
  const textarea = document.getElementById('textInput');
  if (window.autosize) {
    autosize.destroy(textarea);
    autosize(textarea);
  }
}

function appendMessage(sender, message) {
  const chatHistory = document.getElementById('chatHistory');
  const div = document.createElement('div');
  div.className = `single__question__answer`;

  if (sender === 'question_user') {
    div.innerHTML = `
      <div class="question_user">
        <div class="left_user_info">
          <img src="assets/images/avatar/user.svg" alt="avatar">
          <div class="question__user">${message}</div>
        </div>
      </div>
    `;
  } else if (sender === 'answer_area') {
    div.innerHTML = `
      <div class="answer__area">
        <div class="thumbnail">
          <img src="assets/images/avatar/04.png" alt="avatar">
        </div>
        <div class="answer_main__wrapper">
          <h4 class="common__title">NutriSnap</h4>
          <p class="disc">${message}</p>
        </div>
      </div>
    `;
  }

  chatHistory.appendChild(div);
  chatHistory.scrollTop = chatHistory.scrollHeight;
  return div;
}

// Fungsi baru untuk typewriter effect
async function appendMessageWithTypewriter(sender, message, speed = 15) {
  const chatHistory = document.getElementById('chatHistory');
  const div = document.createElement('div');
  div.className = `single__question__answer`;

  // Parse markdown dulu biar GACOR
  const formattedMessage = marked.parse(message);
  
  div.innerHTML = `
    <div class="answer__area">
      <div class="thumbnail">
        <img src="assets/images/avatar/04.png" alt="avatar">
      </div>
      <div class="answer_main__wrapper">
        <h4 class="common__title">NutriSnap</h4>
        <p class="disc"></p>
      </div>
    </div>
  `;

  chatHistory.appendChild(div);
  const textContainer = div.querySelector('.disc');
  
  // Fungsi untuk mengetik karakter demi karakter
  async function typeText(container, html) {
    // Buat div sementara untuk mengproses HTML
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    
    // Fungsi rekursif untuk mengetik node dan anak-anaknya
    async function typeNode(node, targetNode) {
      for (let child of node.childNodes) {
        if (child.nodeType === 3) { // Text node
          const text = child.textContent;
          for (let i = 0; i < text.length; i++) {
            targetNode.appendChild(document.createTextNode(text[i]));
            await new Promise(resolve => setTimeout(resolve, speed));
            chatHistory.scrollTop = chatHistory.scrollHeight;
          }
        } else if (child.nodeType === 1) { // Element node
          const newNode = child.cloneNode(false);
          targetNode.appendChild(newNode);
          await typeNode(child, newNode);
        }
      }
    }
    
    await typeNode(tempDiv, container);
  }
  
  await typeText(textContainer, formattedMessage);
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
  console.log('previewImage called'); // Debug log
  
  const file = document.getElementById('imageInput').files[0];
  console.log('Selected file:', file); // Debug log
  
  if (!file) {
    console.log('No file selected');
    return;
  }
  
  imageFile = file;
  const imagePreviewSection = document.getElementById('imagePreviewSection');
  const previewImageContainer = document.getElementById('previewImageContainer');
  const imageIndicator = document.getElementById('imageIndicator');
  const imageUploadBtn = document.getElementById('imageUploadBtn');
  
  console.log('Elements found:', {
    imagePreviewSection: !!imagePreviewSection,
    previewImageContainer: !!previewImageContainer,
    imageIndicator: !!imageIndicator,
    imageUploadBtn: !!imageUploadBtn
  }); // Debug log
  
  // Show image indicator
  imageIndicator.className = 'image-indicator show';
  imageUploadBtn.className = 'chat-action-btn image-upload-btn has-image';
  
  // Create preview
  const reader = new FileReader();
  reader.onload = function(e) {
    console.log('File loaded, data URL length:', e.target.result.length); // Debug log
    
    previewImageContainer.innerHTML = `
      <img src="${e.target.result}" class="preview-image" />
      <button class="remove-image-btn" onclick="removeImage()">×</button>
    `;
    
    // Show preview section
    imagePreviewSection.className = 'image-preview-section show';
    
    console.log('Preview set'); // Debug log
  };
  
  reader.onerror = function(error) {
    console.error('FileReader error:', error);
  };
  
  reader.readAsDataURL(file);
}

function removeImage() {
  console.log('removeImage called'); // Debug log
  
  const imagePreviewSection = document.getElementById('imagePreviewSection');
  const previewImageContainer = document.getElementById('previewImageContainer');
  const imageInput = document.getElementById('imageInput');
  const imageIndicator = document.getElementById('imageIndicator');
  const imageUploadBtn = document.getElementById('imageUploadBtn');
  
  imageFile = null;
  imageInput.value = '';
  previewImageContainer.innerHTML = '';
  imagePreviewSection.className = 'image-preview-section hide';
  imageIndicator.className = 'image-indicator hide';
  imageUploadBtn.className = 'chat-action-btn image-upload-btn';
  
  console.log('Image removed'); // Debug log
}

function isValidNutritionResponse(response) {
    const validKeywords = [
      'gizi', 'nutrisi', 'makanan sehat', 'pola makan', 'vitamin', 'mineral', 'diet', 'gizi makanan',
      'protein', 'karbohidrat', 'lemak', 'serat', 'kandungan gizi', 'kalori', 'makronutrien', 'mikronutrien',
      'vitamin A', 'vitamin B', 'vitamin C', 'vitamin D', 'vitamin E', 'kalsium', 'zat besi', 'magnesium',
      'potassium', 'sodium', 'fosfor', 'omega 3', 'omega 6', 'antioksidan', 'asam lemak', 'glukosa', 'insulin',
      'indeks glikemik', 'porsi makanan', 'makanan rendah kalori', 'makanan tinggi protein', 'makanan kaya serat',
      'diet sehat', 'makanan bergizi', 'makanan seimbang', 'makanan rendah lemak', 'makanan tinggi vitamin',
      'makanan alami', 'makanan olahan', 'pola makan sehat', 'detoksifikasi', 'superfood', 'prebiotik', 'probiotik',
      'makanan organik', 'suplemen', 'makanan untuk diet', 'makanan penambah energi', 'makanan untuk kesehatan jantung',
      'makanan untuk tulang', 'makanan untuk otak', 'makanan untuk kulit', 'gizi seimbang', 'diet rendah gula', 
      'gizi anak', 'gizi lansia', 'pola makan seimbang', 'makanan tinggi serat', 'gizi ibu hamil', 'makanan vegetarian',
      'makanan vegan', 'makanan rendah karbohidrat', 'makanan gluten free', 'kebutuhan gizi', 'kebutuhan kalori'
      ];
  
    return validKeywords.some(keyword => response.toLowerCase().includes(keyword));
  }

window.onload = () => {
  appendMessage('answer_area', "Selamat datang di NutriSnap! Saya hanya akan membahas tentang gizi makanan, pola makan, dan topik terkait kesehatan makanan lainnya. Silakan ajukan pertanyaan tentang gizi atau pola makan Anda.");
  
  // Test preview function on page load
  console.log('Page loaded, testing elements...');
  console.log('ImageInput exists:', !!document.getElementById('imageInput'));
  console.log('PreviewSection exists:', !!document.getElementById('imagePreviewSection'));
  console.log('PreviewContainer exists:', !!document.getElementById('previewImageContainer'));
};