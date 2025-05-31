<?php

include "partials/config.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$response = ['success' => false, 'message' => 'Request tidak valid.', 'chat_history' => []];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET['conversation_id']) || empty(trim($_GET['conversation_id']))) {
        $response['message'] = 'Parameter "conversation_id" diperlukan dan tidak boleh kosong.';
    } else {
        $conversation_id = trim($_GET['conversation_id']);

        try {
            $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $sql = "SELECT sender, message, created_at FROM chat_history WHERE conversation_id = :conversation_id ORDER BY created_at ASC";
            // K<alo mau load img:
            // $sql = "SELECT sender, message, image_data, created_at FROM chat_history WHERE conversation_id = :conversation_id ORDER BY created_at ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':conversation_id', $conversation_id);
            $stmt->execute();

            $chat_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['success'] = true;
            $response['message'] = 'Riwayat percakapan berhasil dimuat.';
            $response['chat_history'] = $chat_history;

        } catch (PDOException $e) {
            error_log("Database Error (load_chat.php): " . $e->getMessage());
            $response['message'] = 'Terjadi kesalahan pada server database saat memuat riwayat.';
            // $response['db_error'] = $e->getMessage(); // Jangan tampilkan ini di produksi
        } finally {
            $pdo = null;
        }
    }
} else {
    $response['message'] = 'Metode request tidak didukung. Gunakan GET.';
}

echo json_encode($response);
?>
