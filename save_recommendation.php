<?php
header('Content-Type: application/json');
require_once "partials/config.php";

// Ambil data JSON dari body request
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Input JSON tidak valid.']);
    exit;
}

$user_id = $input['user_id'] ?? null;
$recommendation_text = $input['recommendation_text'] ?? null;

if (empty($user_id) || empty($recommendation_text)) {
    echo json_encode(['success' => false, 'message' => 'User ID atau teks rekomendasi tidak boleh kosong.']);
    exit;
}

$sql = "INSERT INTO user_recommendations (user_id, recommendation_text, created_at) VALUES (?, ?, NOW())";

if ($stmt = mysqli_prepare($koneksi, $sql)) {
    mysqli_stmt_bind_param($stmt, "is", $user_id, $recommendation_text);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Rekomendasi berhasil disimpan.']);
    } else {
        error_log("MySQLi execute error: " . mysqli_stmt_error($stmt));
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan rekomendasi ke database.']);
    }
    mysqli_stmt_close($stmt);
} else {
    error_log("MySQLi prepare error: " . mysqli_error($koneksi));
    echo json_encode(['success' => false, 'message' => 'Gagal menyiapkan statement database.']);
}

mysqli_close($koneksi);
?>
