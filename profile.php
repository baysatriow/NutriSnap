<?php
require_once "partials/session.php";

require_once "partials/config.php";
include "partials/functions.crud.php";

// Define variables and initialize with empty values or fetched data
$first_name = $last_name = $useremail = $phone_number = $dob = $gender = "";
$height_cm = $weight_kg = $activity_level = $health_goal = $bio = "";
$profile_image = "assets/images/avatar/01.png"; // Default profile image path

// Define error variables
$first_name_err = $last_name_err = $email_err = $phone_number_err = $dob_err = $gender_err = "";
$height_cm_err = $weight_kg_err = $activity_level_err = $health_goal_err = $bio_err = "";
$profile_image_err = ""; 
$update_success_msg = $update_error_msg = "";
$page_load_error = "";

// Get user ID from session
$user_id = $_SESSION["id_user"];

// --- Fetch current user data ---
$dataAkun = fetch($koneksi, 'users', ['id_user' => $user_id]);

if ($dataAkun) {
    $first_name = $dataAkun['first_name'] ?? '';
    $last_name = $dataAkun['last_name'] ?? '';
    $useremail = $dataAkun['useremail'] ?? '';
    $phone_number = $dataAkun['phone_number'] ?? '';
    $dob = $dataAkun['date_of_birth'] ?? '';
    $gender = $dataAkun['gender'] ?? '';
    $height_cm = $dataAkun['height_cm'] ?? '';
    $weight_kg = $dataAkun['weight_kg'] ?? '';
    $activity_level = $dataAkun['activity_level'] ?? '';
    $health_goal = $dataAkun['health_goal'] ?? '';
    $bio = $dataAkun['bio'] ?? '';
    if (!empty($dataAkun['profile_image_path']) && file_exists($dataAkun['profile_image_path'])) {
        $profile_image = $dataAkun['profile_image_path'];
    } else {
        if (!empty($dataAkun['profile_image_path'])) {
            // error_log("Profile image file not found at path: " . $dataAkun['profile_image_path'] . " for user_id: " . $user_id);
        }
        $profile_image = "assets/images/avatar/01.png";
    }
} else {
    $page_load_error = "Error: Tidak dapat mengambil data pengguna saat ini. Form mungkin tidak menampilkan data yang benar.";
}


// --- Processing form data when form is submitted ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $new_profile_image_path = null;
    $upload_ok = 1;
    $image_upload_attempted = (isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["error"] != UPLOAD_ERR_NO_FILE);
    $current_db_image_path = $dataAkun['profile_image_path'] ?? null;

    if ($image_upload_attempted) {
        if ($_FILES["profile_pic"]["error"] == 0) {
            $target_dir = "assets/images/avatar/";
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0755, true)) {
                    $profile_image_err = "Gagal membuat direktori unggah: " . $target_dir . ". Periksa izin server.";
                    $upload_ok = 0;
                }
            } elseif (!is_writable($target_dir)) {
                $profile_image_err = "Direktori unggah: " . $target_dir . " tidak dapat ditulisi. Periksa izin server.";
                $upload_ok = 0;
            }

            if ($upload_ok) {
                $image_file_type = strtolower(pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION));
                $unique_filename = uniqid('avatar_', true) . '.' . $image_file_type;
                $target_file = $target_dir . $unique_filename;

                $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
                if ($check === false) {
                    $profile_image_err = "File yang diunggah bukan gambar yang valid.";
                    $upload_ok = 0;
                }

                if ($upload_ok && $_FILES["profile_pic"]["size"] > 5000000) {
                    $profile_image_err = "Maaf, ukuran file terlalu besar (maks 5MB).";
                    $upload_ok = 0;
                }

                $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
                if ($upload_ok && !in_array($image_file_type, $allowed_types)) {
                    $profile_image_err = "Maaf, hanya format JPG, JPEG, PNG & GIF yang diperbolehkan.";
                    $upload_ok = 0;
                }

                if ($upload_ok) {
                    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                        $new_profile_image_path = $target_file;
                        if ($current_db_image_path && $current_db_image_path != 'assets/images/avatar/01.png' && file_exists($current_db_image_path)) {
                            if ($current_db_image_path != $new_profile_image_path) {
                                unlink($current_db_image_path);
                            }
                        }
                    } else {
                        $profile_image_err = "Maaf, terjadi error saat memindahkan file yang diunggah. Periksa izin server.";
                        $upload_ok = 0;
                    }
                }
            }
        } elseif ($_FILES["profile_pic"]["error"] != UPLOAD_ERR_OK) {
            $php_upload_errors = [
                UPLOAD_ERR_INI_SIZE   => "Ukuran file melebihi batas unggah server (upload_max_filesize).",
                UPLOAD_ERR_FORM_SIZE  => "Ukuran file melebihi batas MAX_FILE_SIZE pada form HTML.",
                UPLOAD_ERR_PARTIAL    => "File hanya terunggah sebagian.",
                UPLOAD_ERR_NO_TMP_DIR => "Folder sementara tidak ditemukan.",
                UPLOAD_ERR_CANT_WRITE => "Gagal menulis file ke disk.",
                UPLOAD_ERR_EXTENSION  => "Ekstensi PHP menghentikan unggahan file.",
            ];
            $profile_image_err = "Error unggah file: " . ($php_upload_errors[$_FILES["profile_pic"]["error"]] ?? "Error tidak diketahui kode " . $_FILES["profile_pic"]["error"]);
            $upload_ok = 0;
        }
    }

    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $phone_number = trim($_POST["phone_number"]);
    if (empty(trim($_POST["date_of_birth"]))) { $dob_err = "Masukkan tanggal lahir Anda."; } else { $dob = trim($_POST["date_of_birth"]); }
    if (empty($_POST["gender"])) { $gender_err = "Pilih jenis kelamin Anda."; } else { $gender = $_POST["gender"]; $allowed_genders = ['male', 'female']; if (!in_array($gender, $allowed_genders)) { $gender_err = "Jenis kelamin tidak valid."; $gender = ""; } }
    if (empty(trim($_POST["height_cm"]))) { $height_cm_err = "Masukkan tinggi badan Anda."; } elseif (!is_numeric($_POST["height_cm"]) || $_POST["height_cm"] <= 0) { $height_cm_err = "Masukkan angka positif yang valid untuk tinggi."; } else { $height_cm = trim($_POST["height_cm"]); }
    if (empty(trim($_POST["weight_kg"]))) { $weight_kg_err = "Masukkan berat badan Anda."; } elseif (!is_numeric($_POST["weight_kg"]) || $_POST["weight_kg"] <= 0) { $weight_kg_err = "Masukkan angka positif yang valid untuk berat."; } else { $weight_kg = trim($_POST["weight_kg"]); }
    if (empty($_POST["activity_level"])) { $activity_level_err = "Pilih tingkat aktivitas Anda."; } else { $activity_level = $_POST["activity_level"]; $allowed_levels = ['sedentary', 'lightly_active', 'moderately_active', 'very_active', 'extra_active']; if (!in_array($activity_level, $allowed_levels)) { $activity_level_err = "Tingkat aktivitas tidak valid."; $activity_level = ""; } }
    if (!empty($_POST["health_goal"])) { $health_goal = $_POST["health_goal"]; $allowed_goals = ['weight_loss', 'weight_gain', 'weight_maintain', 'muscle_gain', 'eat_healthier', 'general_wellness', 'other']; if (!in_array($health_goal, $allowed_goals)) { $health_goal_err = "Tujuan kesehatan tidak valid."; $health_goal = ""; } } else { $health_goal = null; }
    $bio = trim($_POST["bio"]);

    $text_data_valid = (empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($phone_number_err) && empty($dob_err) && empty($gender_err) && empty($height_cm_err) && empty($weight_kg_err) && empty($activity_level_err) && empty($health_goal_err) && empty($bio_err));

    $db_update_successful = false;
    $image_path_saved_to_db = false;

    if ($text_data_valid) {
        $update_data = [
            'first_name' => $first_name, 'last_name' => $last_name, 'phone_number' => $phone_number,
            'date_of_birth' => $dob, 'gender' => $gender, 'height_cm' => $height_cm,
            'weight_kg' => $weight_kg, 'activity_level' => $activity_level,
            'health_goal' => $health_goal, 'bio' => $bio
        ];

        if ($image_upload_attempted && $upload_ok && $new_profile_image_path !== null) {
            $update_data['profile_image_path'] = $new_profile_image_path;
        }

        if (update($koneksi, 'users', $update_data, ['id_user' => $user_id])) {
            $db_update_successful = true;

            if (isset($update_data['profile_image_path'])) {
                $check_db_path = fetch($koneksi, 'users', ['id_user' => $user_id]);
                if ($check_db_path && $check_db_path['profile_image_path'] == $update_data['profile_image_path']) {
                    $image_path_saved_to_db = true;
                }
            } elseif (!$image_upload_attempted) {
                $image_path_saved_to_db = true;
            }

            // Re-fetch data untuk tampilan
            $dataAkun = fetch($koneksi, 'users', ['id_user' => $user_id]);
            if ($dataAkun) {
                $first_name = $dataAkun['first_name'] ?? ''; $last_name = $dataAkun['last_name'] ?? '';
                $useremail = $dataAkun['useremail'] ?? ''; $phone_number = $dataAkun['phone_number'] ?? '';
                $dob = $dataAkun['date_of_birth'] ?? ''; $gender = $dataAkun['gender'] ?? '';
                $height_cm = $dataAkun['height_cm'] ?? ''; $weight_kg = $dataAkun['weight_kg'] ?? '';
                $activity_level = $dataAkun['activity_level'] ?? ''; $health_goal = $dataAkun['health_goal'] ?? '';
                $bio = $dataAkun['bio'] ?? '';
                if (!empty($dataAkun['profile_image_path']) && file_exists($dataAkun['profile_image_path'])) {
                    $profile_image = $dataAkun['profile_image_path'];
                } else { $profile_image = "assets/images/avatar/01.png"; }
            }
        } else {
            $update_error_msg = "Gagal memperbarui profil di database.";
            if ($image_upload_attempted && $upload_ok && $new_profile_image_path !== null) {
                $profile_image_err = ($profile_image_err ? $profile_image_err . " " : "") . "Gambar berhasil diunggah ke server tetapi gagal disimpan ke database karena error update.";
            }
        }
    } else {
        $update_error_msg = "Harap perbaiki error pada form.";
    }

    if ($db_update_successful) {
        if ($image_upload_attempted) {
            if ($upload_ok && $image_path_saved_to_db) {
                $update_success_msg = "Profil dan gambar berhasil diperbarui!";
            } elseif ($upload_ok && !$image_path_saved_to_db) {
                $update_error_msg = "Data teks diperbarui. Gambar diunggah, tetapi path gagal disimpan ke database.";
                if(empty($profile_image_err)) $profile_image_err = "Path gambar baru tidak tersimpan dengan benar di database.";
            } else { // $upload_ok == 0
                $update_error_msg = ($text_data_valid ? "Data teks mungkin telah diperbarui, tetapi " : "") . "Unggah gambar gagal: " . $profile_image_err;
            }
        } else {
            $update_success_msg = "Profil berhasil diperbarui.";
        }
    } else {
        if (!empty($profile_image_err) && strpos($update_error_msg, $profile_image_err) === false) {
            $update_error_msg .= " Info gambar: " . $profile_image_err;
        }
    }
}

include 'partials/main.php';
?>

<head>
    <?php include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php'; ?>
    <style>
        /* General error/success messages */
        .error-message { color: #dc3545; font-size: 0.875em; margin-top: 0.25rem; }
        .success-message { color: #198754; font-weight: bold; margin-bottom: 1rem; }
        .warning-message { color: #ffc107; font-size: 0.875em; margin-top: 0.25rem; }


        /* Banner and Profile Image Area */
        .thumbnaiul {
            position: relative;
            margin-bottom: 80px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .thumbnaiul .banner-image {
            display: block;
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            margin: -60px auto 15px auto;
            position: relative;
            border: 4px solid #fff;
            background-color: #e9ecef;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .profile-image img#profilePicPreview {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        /* Tombol Unggah Foto Profil  */
        .thumbnaiul label#visibleProfilePicLabel {
            position: absolute;
            bottom: 15px;
            right: 15px;
            z-index: 2;
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
         .thumbnaiul label#visibleProfilePicLabel svg {
            width: 18px;
            height: 18px;
        }

        /* Styling Form Select */
        select.form-control {
            height: 46px;
            padding: .375rem .75rem;
            font-size: 16px;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            -webkit-appearance: none; -moz-appearance: none; appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%236c757d%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat; background-position: right .75rem center; background-size: 8px 10px;
        }
        select.form-control:focus { border-color: #86b7fe; outline: 0; box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25); }

        /* Tombol Submit "Update Profile" */
        .user-profile-content-details form .rts-btn.btn-primary { margin-top: 1.5rem; margin-bottom: 1rem; width: auto; padding: 0.75rem 1.5rem; }

        /* Penyesuaian Responsif */
        @media (max-width: 576px) {
            .profile-image { width: 100px; height: 100px; margin-top: -50px; border-width: 3px; }
            .thumbnaiul { margin-bottom: 65px; }
            .thumbnaiul .banner-image { height: 150px; }
            .thumbnaiul label#visibleProfilePicLabel { padding: 0.4rem 0.8rem; font-size: 0.8rem; bottom: 8px; right: 8px; }
            .thumbnaiul label#visibleProfilePicLabel svg { width: 16px; height: 16px; }
            .half-input-wrapper { flex-direction: column; }
            .half-input-wrapper .single { width: 100%; margin-bottom: 1rem; }
            .half-input-wrapper .single:last-child { margin-bottom: 0; }
            .user-profile-content-details form .rts-btn.btn-primary { padding: 0.6rem 1.2rem; }
        }
    </style>
</head>

<body class="user-profile">

<?php include 'partials/header.php'; ?>

    <div class="dash-board-main-wrapper">
        <?php include 'partials/sidebar.php'; ?>

        <div class="user-profile-area">
            <div class="container-custom">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="user-profile-inner">
                            <h4 class="title">Personalisasi</h4>

                            <?php if(!empty($page_load_error)): ?>
                                <div class="alert alert-danger error-message"><?php echo htmlspecialchars($page_load_error); ?></div>
                            <?php endif; ?>
                            <?php if(!empty($update_success_msg)): ?>
                                <div class="alert alert-success success-message"><?php echo htmlspecialchars($update_success_msg); ?></div>
                            <?php endif; ?>
                            <?php if(!empty($update_error_msg)): ?>
                                <div class="alert alert-danger error-message"><?php echo htmlspecialchars($update_error_msg); ?></div>
                            <?php elseif(!empty($profile_image_err)): ?>
                                <div class="alert alert-warning warning-message"><?php echo htmlspecialchars($profile_image_err); ?></div>
                            <?php endif; ?>


                            <div class="user-profile-main-wrapper">
                                <div class="thumbnaiul">
                                     <img src="assets/images/banner/01.jpg" alt="Cover Photo" class="banner-image">
                                     <div class="profile-image">
                                        <img src="<?php echo htmlspecialchars($profile_image); ?>?t=<?php echo time(); ?>" alt="Foto Profil" id="profilePicPreview" onerror="this.onerror=null; this.src='assets/images/avatar/01.png';">
                                     </div>
                                     <label for="visibleProfilePicUpload" id="visibleProfilePicLabel" class="rts-btn btn-primary">
                                         <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_1314_3881)"><path d="M14.9995 15.8333C15.1643 15.8332 15.3254 15.7844 15.4624 15.6928C15.5994 15.6012 15.7062 15.4711 15.7693 15.3188C15.8323 15.1666 15.8488 14.9991 15.8167 14.8374C15.7845 14.6758 15.7052 14.5273 15.5887 14.4108L10.4454 9.26828C9.66399 8.48715 8.60438 8.04834 7.49953 8.04834C6.39468 8.04834 5.33506 8.48715 4.55369 9.26828L2.74369 11.0775C2.59189 11.2346 2.5079 11.4451 2.5098 11.6636C2.5117 11.8821 2.59934 12.0911 2.75384 12.2456C2.90835 12.4001 3.11736 12.4878 3.33586 12.4897C3.55436 12.4916 3.76486 12.4076 3.92203 12.2558L5.73203 10.4466C6.20804 9.99173 6.84111 9.73788 7.49953 9.73788C8.15794 9.73788 8.79101 9.99173 9.26703 10.4466L14.4104 15.5891C14.5666 15.7454 14.7785 15.8332 14.9995 15.8333Z" fill="white"/><path d="M13.3337 8.74984C13.9105 8.74984 14.4744 8.57878 14.9541 8.25829C15.4337 7.9378 15.8076 7.48228 16.0283 6.94933C16.2491 6.41638 16.3068 5.82994 16.1943 5.26416C16.0817 4.69838 15.804 4.17868 15.3961 3.77078C14.9882 3.36287 14.4685 3.08509 13.9027 2.97255C13.3369 2.86001 12.7505 2.91777 12.2175 3.13852C11.6845 3.35928 11.229 3.73312 10.9085 4.21276C10.5881 4.6924 10.417 5.25631 10.417 5.83317C10.417 6.60672 10.7243 7.34859 11.2713 7.89557C11.8182 8.44255 12.5601 8.74984 13.3337 8.74984ZM13.3337 4.58317C13.5809 4.58317 13.8226 4.65648 14.0281 4.79384C14.2337 4.93119 14.3939 5.12641 14.4885 5.35482C14.5831 5.58323 14.6079 5.83456 14.5596 6.07703C14.5114 6.31951 14.3924 6.54224 14.2175 6.71706C14.0427 6.89187 13.82 7.01092 13.5775 7.05915C13.335 7.10739 13.0837 7.08263 12.8553 6.98802C12.6269 6.89341 12.4317 6.7332 12.2943 6.52763C12.157 6.32207 12.0837 6.0804 12.0837 5.83317C12.0837 5.50165 12.2154 5.18371 12.4498 4.94929C12.6842 4.71487 13.0021 4.58317 13.3337 4.58317Z" fill="white"/><path d="M19.1663 13.3335C18.9453 13.3335 18.7334 13.4213 18.5771 13.5776C18.4208 13.7339 18.333 13.9458 18.333 14.1668V15.8335C18.333 16.4965 18.0696 17.1324 17.6008 17.6013C17.1319 18.0701 16.496 18.3335 15.833 18.3335H14.1663C13.9453 18.3335 13.7334 18.4213 13.5771 18.5776C13.4208 18.7338 13.333 18.9458 13.333 19.1668C13.333 19.3878 13.4208 19.5998 13.5771 19.7561C13.7334 19.9124 13.9453 20.0001 14.1663 20.0001H15.833C16.9377 19.9988 17.9967 19.5594 18.7778 18.7783C19.5589 17.9972 19.9983 16.9382 19.9997 15.8335V14.1668C19.9997 13.9458 19.9119 13.7339 19.7556 13.5776C19.5993 13.4213 19.3873 13.3335 19.1663 13.3335Z" fill="white"/><path d="M0.833333 6.66667C1.05435 6.66667 1.26631 6.57887 1.42259 6.42259C1.57887 6.26631 1.66667 6.05435 1.66667 5.83333V4.16667C1.66667 3.50363 1.93006 2.86774 2.3989 2.3989C2.86774 1.93006 3.50363 1.66667 4.16667 1.66667H5.83333C6.05435 1.66667 6.26631 1.57887 6.42259 1.42259C6.57887 1.26631 6.66667 1.05435 6.66667 0.833333C6.66667 0.61232 6.57887 0.400358 6.42259 0.244078C6.26631 0.0877974 6.05435 0 5.83333 0L4.16667 0C3.062 0.00132321 2.00296 0.440735 1.22185 1.22185C0.440735 2.00296 0.00132321 3.062 0 4.16667L0 5.83333C0 6.05435 0.0877974 6.26631 0.244078 6.42259C0.400358 6.57887 0.61232 6.66667 0.833333 6.66667Z" fill="white"/><path d="M5.83333 18.3335H4.16667C3.50363 18.3335 2.86774 18.0701 2.3989 17.6013C1.93006 17.1324 1.66667 16.4965 1.66667 15.8335V14.1668C1.66667 13.9458 1.57887 13.7339 1.42259 13.5776C1.26631 13.4213 1.05435 13.3335 0.833333 13.3335C0.61232 13.3335 0.400358 13.4213 0.244078 13.5776C0.0877974 13.7339 0 13.9458 0 14.1668L0 15.8335C0.00132321 16.9382 0.440735 17.9972 1.22185 18.7783C2.00296 19.5594 3.062 19.9988 4.16667 20.0001H5.83333C6.05435 20.0001 6.26631 19.9124 6.42259 19.7561C6.57887 19.5998 6.66667 19.3878 6.66667 19.1668C6.66667 18.9458 6.57887 18.7338 6.42259 18.5776C6.26631 18.4213 6.05435 18.3335 5.83333 18.3335Z" fill="white"/><path d="M15.833 0H14.1663C13.9453 0 13.7334 0.0877974 13.5771 0.244078C13.4208 0.400358 13.333 0.61232 13.333 0.833333C13.333 1.05435 13.4208 1.26631 13.5771 1.42259C13.7334 1.57887 13.9453 1.66667 14.1663 1.66667H15.833C16.496 1.66667 17.1319 1.93006 17.6008 2.3989C18.0696 2.86774 18.333 3.50363 18.333 4.16667V5.83333C18.333 6.05435 18.4208 6.26631 18.5771 6.42259C18.7334 6.57887 18.9453 6.66667 19.1663 6.66667C19.3873 6.66667 19.5993 6.57887 19.7556 6.42259C19.9119 6.26631 19.9997 6.05435 19.9997 5.83333V4.16667C19.9983 3.062 19.5589 2.00296 18.7778 1.22185C17.9967 0.440735 16.9377 0.00132321 15.833 0V0Z" fill="white"/></g><defs><clipPath id="clip0_1314_3881"><rect width="20" height="20" fill="white"/></clipPath></defs>
                                         </svg>
                                        Ganti Foto Profil
                                    </label>
                                     <input type="file" id="visibleProfilePicUpload" style="display: none;" accept="image/png, image/jpeg, image/jpg, image/gif">
                                </div>

                                <div class="user-profile-content-details">
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                                        <input type="file" id="hiddenProfilePicInput" name="profile_pic" style="display: none;" accept="image/png, image/jpeg, image/jpg, image/gif">

                                        <div class="half-input-wrapper">
                                            <div class="single">
                                                <label for="f-name">Nama Depan</label>
                                                <input id="f-name" name="first_name" type="text" placeholder="Masukkan nama depan" value="<?php echo htmlspecialchars($first_name); ?>">
                                                <?php if(!empty($first_name_err)): ?><div class="error-message"><?php echo $first_name_err; ?></div><?php endif; ?>
                                            </div>
                                            <div class="single">
                                                <label for="l-name">Nama Belakang</label>
                                                <input id="l-name" name="last_name" type="text" placeholder="Masukkan nama belakang" value="<?php echo htmlspecialchars($last_name); ?>">
                                                <?php if(!empty($last_name_err)): ?><div class="error-message"><?php echo $last_name_err; ?></div><?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="half-input-wrapper">
                                            <div class="single">
                                                <label for="email">Email</label>
                                                <input id="email" name="email_display" type="email" placeholder="contoh@email.com" value="<?php echo htmlspecialchars($useremail); ?>" readonly style="background-color: #e9ecef; cursor: not-allowed;"> <small class="form-text text-muted">Email tidak dapat diubah.</small> <?php if(!empty($email_err)): ?><div class="error-message"><?php echo $email_err; ?></div><?php endif; ?>
                                            </div>
                                            <div class="single">
                                                <label for="phonenumber">Nomor Telepon</label>
                                                <input id="phonenumber" name="phone_number" type="tel" placeholder="+62 xxx xxx xxx" value="<?php echo htmlspecialchars($phone_number); ?>">
                                                <?php if(!empty($phone_number_err)): ?><div class="error-message"><?php echo $phone_number_err; ?></div><?php endif; ?>
                                            </div>
                                        </div>

                                        <h5 class="mt-4 mb-3">Data Kesehatan & Gizi</h5>
                                        <div class="half-input-wrapper">
                                            <div class="single">
                                                <label for="dob">Tanggal Lahir</label>
                                                <input id="dob" name="date_of_birth" type="date" value="<?php echo htmlspecialchars($dob); ?>" >
                                                <?php if(!empty($dob_err)): ?><div class="error-message"><?php echo $dob_err; ?></div><?php endif; ?>
                                            </div>
                                            <div class="single">
                                                <label for="gender">Jenis Kelamin</label>
                                                <select id="gender" name="gender" class="form-control" >
                                                    <option value="" <?php echo empty($gender) ? 'selected' : ''; ?> disabled>Pilih Jenis Kelamin...</option>
                                                    <option value="male" <?php echo ($gender === 'male') ? 'selected' : ''; ?>>Pria</option>
                                                    <option value="female" <?php echo ($gender === 'female') ? 'selected' : ''; ?>>Wanita</option>
                                                </select>
                                                <?php if(!empty($gender_err)): ?><div class="error-message"><?php echo $gender_err; ?></div><?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="half-input-wrapper">
                                            <div class="single">
                                                <label for="height">Tinggi Badan (cm)</label>
                                                <input id="height" name="height_cm" type="number" step="0.1" min="1" placeholder="Contoh: 170.5" value="<?php echo htmlspecialchars($height_cm); ?>" > <?php if(!empty($height_cm_err)): ?><div class="error-message"><?php echo $height_cm_err; ?></div><?php endif; ?>
                                            </div>
                                            <div class="single">
                                                <label for="weight">Berat Badan (kg)</label>
                                                <input id="weight" name="weight_kg" type="number" step="0.1" min="1" placeholder="Contoh: 65.2" value="<?php echo htmlspecialchars($weight_kg); ?>" > <?php if(!empty($weight_kg_err)): ?><div class="error-message"><?php echo $weight_kg_err; ?></div><?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="single">
                                           <label for="activity-level">Tingkat Aktivitas Harian</label>
                                           <select id="activity-level" name="activity_level" class="form-control" >
                                                <option value="" <?php echo empty($activity_level) ? 'selected' : ''; ?> disabled>Pilih Tingkat Aktivitas...</option>
                                                <option value="sedentary" <?php echo ($activity_level === 'sedentary') ? 'selected' : ''; ?>>Sedentari (Tidak/jarang olahraga)</option>
                                                <option value="lightly_active" <?php echo ($activity_level === 'lightly_active') ? 'selected' : ''; ?>>Aktivitas Ringan (1-3 hr/mgg)</option>
                                                <option value="moderately_active" <?php echo ($activity_level === 'moderately_active') ? 'selected' : ''; ?>>Aktivitas Sedang (3-5 hr/mgg)</option>
                                                <option value="very_active" <?php echo ($activity_level === 'very_active') ? 'selected' : ''; ?>>Sangat Aktif (6-7 hr/mgg)</option>
                                                <option value="extra_active" <?php echo ($activity_level === 'extra_active') ? 'selected' : ''; ?>>Ekstra Aktif (Sangat berat/fisik)</option>
                                           </select>
                                           <?php if(!empty($activity_level_err)): ?><div class="error-message"><?php echo $activity_level_err; ?></div><?php endif; ?>
                                        </div>
                                         <div class="single mt-3">
                                           <label for="health-goal">Tujuan Kesehatan Utama</label>
                                           <select id="health-goal" name="health_goal" class="form-control">
                                                <option value="" <?php echo empty($health_goal) ? 'selected' : ''; ?> disabled>Pilih Tujuan Anda...</option>
                                                <option value="weight_loss" <?php echo ($health_goal === 'weight_loss') ? 'selected' : ''; ?>>Menurunkan Berat Badan</option>
                                                <option value="weight_gain" <?php echo ($health_goal === 'weight_gain') ? 'selected' : ''; ?>>Menambah Berat Badan</option>
                                                <option value="weight_maintain" <?php echo ($health_goal === 'weight_maintain') ? 'selected' : ''; ?>>Menjaga Berat Badan</option>
                                                <option value="muscle_gain" <?php echo ($health_goal === 'muscle_gain') ? 'selected' : ''; ?>>Meningkatkan Massa Otot</option>
                                                <option value="eat_healthier" <?php echo ($health_goal === 'eat_healthier') ? 'selected' : ''; ?>>Makan Lebih Sehat</option>
                                                <option value="general_wellness" <?php echo ($health_goal === 'general_wellness') ? 'selected' : ''; ?>>Kesehatan Umum</option>
                                                <option value="other" <?php echo ($health_goal === 'other') ? 'selected' : ''; ?>>Lainnya (Jelaskan di Bio)</option>
                                           </select>
                                            <?php if(!empty($health_goal_err)): ?><div class="error-message"><?php echo $health_goal_err; ?></div><?php endif; ?>
                                        </div>
                                        <div class="single mt-4">
                                            <label for="bio">Bio / Catatan Tambahan</label>
                                            <textarea name="bio" id="bio" cols="30" rows="5" placeholder="Ceritakan sedikit tentang diri Anda, preferensi makanan, alergi, atau kondisi kesehatan relevan lainnya..."><?php echo htmlspecialchars($bio); ?></textarea>
                                             <?php if(!empty($bio_err)): ?><div class="error-message"><?php echo $bio_err; ?></div><?php endif; ?>
                                        </div>

                                        <button type="submit" class="rts-btn btn-primary">Update Profile</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="copyright-area-bottom">
                <p> <a href="#">NutriSnap</a> <?php echo date("Y"); ?>. All Rights Reserved.</p>
            </div>
        </div> </div> <?php include 'partials/script.php'; ?>
    <script>
        // Input file yang terlihat oleh pengguna
        const visibleProfilePicUpload = document.getElementById('visibleProfilePicUpload');
        // Input file tersembunyi yang ada di dalam form
        const hiddenProfilePicInput = document.getElementById('hiddenProfilePicInput');
        // Elemen img untuk preview
        const profilePicPreview = document.getElementById('profilePicPreview');
        const defaultProfilePic = 'assets/images/avatar/01.png';

        if (visibleProfilePicUpload && hiddenProfilePicInput && profilePicPreview) {
            visibleProfilePicUpload.onchange = evt => {
                const [file] = visibleProfilePicUpload.files;
                if (file) {
                    const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
                    if (allowedTypes.includes(file.type)) {
                        // Tampilkan preview menggunakan file dari input yang terlihat
                        profilePicPreview.src = URL.createObjectURL(file);
                        profilePicPreview.onload = () => {
                            URL.revokeObjectURL(profilePicPreview.src);
                        }

                        hiddenProfilePicInput.files = visibleProfilePicUpload.files;
                    } else {
                        alert('Tipe file tidak valid. Harap pilih file gambar (PNG, JPG, JPEG, GIF).');
                        visibleProfilePicUpload.value = '';
                        hiddenProfilePicInput.value = '';
                        // profilePicPreview.src = "<?php echo htmlspecialchars($profile_image); ?>?t=" + new Date().getTime(); // Reset ke gambar profil saat ini
                    }
                } else {
                    hiddenProfilePicInput.value = '';
                }
            };

            profilePicPreview.onerror = () => {
                profilePicPreview.src = defaultProfilePic;
            };
        }
    </script>
</body>
</html>
