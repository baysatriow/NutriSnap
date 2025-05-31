<?php
include "partials/config.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$response = ['success' => false, 'message' => 'Request tidak valid.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);

    if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = 'Data JSON tidak valid. Error: ' . json_last_error_msg();
        echo json_encode($response);
        exit;
    }

    if (!isset($input['conversation_id']) || !isset($input['sender']) || !isset($input['message']) || !isset($input['user_id'])) {
        $response['message'] = 'Data tidak lengkap. "conversation_id", "user_id", "sender", dan "message" diperlukan.';
    } else {
        $conversation_id = trim($input['conversation_id']);
        $user_id = filter_var($input['user_id'], FILTER_VALIDATE_INT);
        $sender = trim($input['sender']);
        $message_content = trim($input['message']);
        $image_data = $input['image_data'] ?? null;

        if (empty($conversation_id) || $user_id === false || $user_id <= 0 || empty($sender) || (empty($message_content) && $image_data === null) ) {
             $response['message'] = 'ID percakapan, User ID valid, pengirim, dan pesan/gambar tidak boleh kosong.';
        } else {
            try {
                if (!$koneksi) {
                    throw new Exception("Koneksi database gagal: " . mysqli_connect_error());
                }

                $sql = "INSERT INTO chat_history (user_id, conversation_id, sender, message, image_data, created_at) VALUES (?, ?, ?, ?, ?, NOW())";

                if ($stmt = mysqli_prepare($koneksi, $sql)) {
                    mysqli_stmt_bind_param($stmt, "issss", $user_id, $conversation_id, $sender, $message_content, $image_data);

                    if (mysqli_stmt_execute($stmt)) {
                        $response['success'] = true;
                        $response['message'] = 'Pesan berhasil disimpan.';
                        $response['inserted_id'] = mysqli_insert_id($koneksi);
                    } else {
                        $response['message'] = 'Gagal menyimpan pesan ke database: ' . mysqli_stmt_error($stmt);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $response['message'] = 'Gagal menyiapkan statement database: ' . mysqli_error($koneksi);
                }
            } catch (Exception $e) {
                error_log("Database Error (save_chat.php): " . $e->getMessage());
                $response['message'] = 'Terjadi kesalahan pada server database. Silakan coba lagi nanti.';
                // $response['db_error'] = $e->getMessage();
            }
            // mysqli_close($koneksi); 
        }
    }
} else {
    $response['message'] = 'Metode request tidak didukung. Gunakan POST.';
}

echo json_encode($response);
?>
