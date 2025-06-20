<style>    /* CSS untuk mengatur ukuran logo */

.logo {
    display: block;
    max-width: 200px; /* Atur lebar maksimum sesuai kebutuhan */
    height: auto;
}

.logo img {
    width: 100%;
    height: auto;
    max-height: 60px; /* Atur tinggi maksimum sesuai kebutuhan */
    object-fit: contain; /* Pastikan gambar tidak terpotong dan tetap proporsional */
}
/* CSS untuk avatar di header (sebelum dropdown) */
.single_action__haeader .avatar {
    width: 36px; /* Sesuaikan ukuran sesuai ikon header lainnya */
    height: 36px; /* Sesuaikan ukuran sesuai ikon header lainnya */
    border-radius: 50%; /* Membuatnya bulat */
    overflow: hidden; /* Menyembunyikan bagian gambar yang keluar */
    display: flex; /* Membantu centering gambar */
    align-items: center;
    justify-content: center;
    background-color: #e9ecef; /* Warna latar belakang fallback */
    cursor: pointer; /* Menunjukkan bisa diklik */
}

.single_action__haeader .avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Memastikan gambar menutupi area */
}
/* CSS untuk mengatur avatar di dalam dropdown header */
.user_information_main_wrapper .user_header .main-avatar {
    width: 50px; /* Atur lebar avatar */
    height: 50px; /* Atur tinggi avatar agar sama dengan lebar (untuk lingkaran) */
    border-radius: 50%; /* Membuat bentuk lingkaran */
    overflow: hidden; /* Menyembunyikan bagian gambar yang keluar dari lingkaran */
    margin-right: 15px; /* Memberi jarak ke kanan (ke nama pengguna) */
    flex-shrink: 0; /* Mencegah avatar menyusut jika teks panjang */
    background-color: #e9ecef; /* Warna latar belakang fallback jika gambar error */
}

.user_information_main_wrapper .user_header .main-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Memastikan gambar menutupi area tanpa distorsi */
    display: block;
}

/* Optional: Menyesuaikan layout user_header agar avatar dan teks sejajar */
.user_information_main_wrapper .user_header {
    display: flex; /* Menggunakan flexbox untuk alignment */
    align-items: center; /* Menyelaraskan item secara vertikal di tengah */
    padding: 15px; /* Menambah padding di sekitar header dropdown */
    border-bottom: 1px solid #eee; /* Garis pemisah */
}

.user_information_main_wrapper .user_naim-information {
    /* Tidak perlu style khusus jika flexbox sudah cukup */
    line-height: 1.3; /* Sedikit penyesuaian line-height jika perlu */
}

.user_information_main_wrapper .user_naim-information .title {
    margin-bottom: 2px; /* Mengurangi jarak bawah judul */
    font-size: 1rem; /* Sesuaikan ukuran font jika perlu */
}

.user_information_main_wrapper .user_naim-information .desig {
    font-size: 0.85rem; /* Ukuran font untuk username/designation */
    color: #6c757d; /* Warna teks yang lebih lembut */
}

</style>
<div class="header-area-one">
    <div class="container-30">
        <div class="col-lg-12">
            <div class="header-inner-one">
                <div class="left-logo-area">
                    <a href="index.php" class="logo">
                        <img src="assets/images/logo/Logo.png" alt="logo-image">
                    </a>
                    <div class="left-side-open-clouse" id="collups-left">
                        <img src="assets/images/icons/01.svg" alt="icons">
                    </div>
                </div>
                <div class="header-right">
                    <div class="action-interactive-area__header">
                        <div class="single_action__haeader language  user_avatar__information openuptip" flow="down" tooltip="Language">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.25 3.125V4.375H9.25C8.83816 6.14196 7.99661 7.77997 6.8 9.14375C7.70414 10.0661 8.79525 10.7843 10 11.25L9.55625 12.4C8.20367 11.8567 6.97802 11.0396 5.95625 10C4.9156 11.0255 3.68974 11.8442 2.34375 12.4125L1.875 11.25C3.07285 10.7429 4.16632 10.0182 5.1 9.1125C4.2552 8.08229 3.61842 6.89788 3.225 5.625H4.5375C4.85587 6.57383 5.3405 7.45844 5.96875 8.2375C6.93251 7.12787 7.6162 5.80335 7.9625 4.375H1.25V3.125H5.625V1.25H6.875V3.125H11.25ZM18.75 18.125H17.4062L16.4062 15.625H12.125L11.125 18.125H9.78125L13.5312 8.75H15L18.75 18.125ZM14.2625 10.275L12.625 14.375H15.9062L14.2625 10.275Z" fill="black" />
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.25 3.125V4.375H9.25C8.83816 6.14196 7.99661 7.77997 6.8 9.14375C7.70414 10.0661 8.79525 10.7843 10 11.25L9.55625 12.4C8.20367 11.8567 6.97802 11.0396 5.95625 10C4.9156 11.0255 3.68974 11.8442 2.34375 12.4125L1.875 11.25C3.07285 10.7429 4.16632 10.0182 5.1 9.1125C4.2552 8.08229 3.61842 6.89788 3.225 5.625H4.5375C4.85587 6.57383 5.3405 7.45844 5.96875 8.2375C6.93251 7.12787 7.6162 5.80335 7.9625 4.375H1.25V3.125H5.625V1.25H6.875V3.125H11.25ZM18.75 18.125H17.4062L16.4062 15.625H12.125L11.125 18.125H9.78125L13.5312 8.75H15L18.75 18.125ZM14.2625 10.275L12.625 14.375H15.9062L14.2625 10.275Z" fill="#083A5E" />
                            </svg>
                            <div class="user_information_main_wrapper slide-down__click language-area">
                                <ul class="select-language-area">
                                    <li><a href="#">English</a></li>
                                    <li><a href="#">Indonesia</a></li>
                                </ul>
                            </div>

                        </div>
                        <div class="single_action__haeader rts-dark-light openuptip" flow="down" tooltip="Dark / Light" id="rts-data-toggle">
                            <div class="in-light">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.625 1.25H9.375V4.375H10.625V1.25ZM15.7452 3.37099L13.5541 5.56213L14.4379 6.44593L16.629 4.25478L15.7452 3.37099ZM15.625 9.375H18.75V10.625H15.625V9.375ZM14.4379 13.5541L13.5541 14.4379L15.7452 16.629L16.629 15.7452L14.4379 13.5541ZM9.375 15.625H10.625V18.75H9.375V15.625ZM5.56212 13.5541L3.37097 15.7452L4.25477 16.629L6.44591 14.4379L5.56212 13.5541ZM1.25 9.375H4.375V10.625H1.25V9.375ZM4.25479 3.37097L3.37099 4.25476L5.56214 6.44591L6.44593 5.56211L4.25479 3.37097ZM11.3889 7.92133C10.9778 7.64662 10.4945 7.5 10 7.5C9.33719 7.50074 8.70174 7.76438 8.23306 8.23306C7.76438 8.70174 7.50075 9.33719 7.5 10C7.5 10.4945 7.64662 10.9778 7.92133 11.3889C8.19603 11.8 8.58648 12.1205 9.04329 12.3097C9.50011 12.4989 10.0028 12.5484 10.4877 12.452C10.9727 12.3555 11.4181 12.1174 11.7678 11.7678C12.1174 11.4181 12.3555 10.9727 12.452 10.4877C12.5484 10.0028 12.4989 9.50011 12.3097 9.04329C12.1205 8.58648 11.8 8.19603 11.3889 7.92133ZM7.91661 6.88199C8.5333 6.46993 9.25832 6.25 10 6.25C10.9946 6.25 11.9484 6.64509 12.6517 7.34835C13.3549 8.05161 13.75 9.00544 13.75 10C13.75 10.7417 13.5301 11.4667 13.118 12.0834C12.706 12.7001 12.1203 13.1807 11.4351 13.4645C10.7498 13.7484 9.99584 13.8226 9.26841 13.6779C8.54098 13.5333 7.8728 13.1761 7.34835 12.6517C6.8239 12.1272 6.46675 11.459 6.32206 10.7316C6.17736 10.0042 6.25163 9.25016 6.53545 8.56494C6.81928 7.87971 7.29993 7.29404 7.91661 6.88199Z" fill="#08395D" />
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.625 1.25H9.375V4.375H10.625V1.25ZM15.7452 3.37099L13.5541 5.56213L14.4379 6.44593L16.629 4.25478L15.7452 3.37099ZM15.625 9.375H18.75V10.625H15.625V9.375ZM14.4379 13.5541L13.5541 14.4379L15.7452 16.629L16.629 15.7452L14.4379 13.5541ZM9.375 15.625H10.625V18.75H9.375V15.625ZM5.56212 13.5541L3.37097 15.7452L4.25477 16.629L6.44591 14.4379L5.56212 13.5541ZM1.25 9.375H4.375V10.625H1.25V9.375ZM4.25479 3.37097L3.37099 4.25476L5.56214 6.44591L6.44593 5.56211L4.25479 3.37097ZM11.3889 7.92133C10.9778 7.64662 10.4945 7.5 10 7.5C9.33719 7.50074 8.70174 7.76438 8.23306 8.23306C7.76438 8.70174 7.50075 9.33719 7.5 10C7.5 10.4945 7.64662 10.9778 7.92133 11.3889C8.19603 11.8 8.58648 12.1205 9.04329 12.3097C9.50011 12.4989 10.0028 12.5484 10.4877 12.452C10.9727 12.3555 11.4181 12.1174 11.7678 11.7678C12.1174 11.4181 12.3555 10.9727 12.452 10.4877C12.5484 10.0028 12.4989 9.50011 12.3097 9.04329C12.1205 8.58648 11.8 8.19603 11.3889 7.92133ZM7.91661 6.88199C8.5333 6.46993 9.25832 6.25 10 6.25C10.9946 6.25 11.9484 6.64509 12.6517 7.34835C13.3549 8.05161 13.75 9.00544 13.75 10C13.75 10.7417 13.5301 11.4667 13.118 12.0834C12.706 12.7001 12.1203 13.1807 11.4351 13.4645C10.7498 13.7484 9.99584 13.8226 9.26841 13.6779C8.54098 13.5333 7.8728 13.1761 7.34835 12.6517C6.8239 12.1272 6.46675 11.459 6.32206 10.7316C6.17736 10.0042 6.25163 9.25016 6.53545 8.56494C6.81928 7.87971 7.29993 7.29404 7.91661 6.88199Z" fill="#08395D" />
                                </svg>
                            </div>
                            <div class="in-dark">
                                <svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2.43606 9.58151C3.65752 9.87564 4.92547 9.92252 6.16531 9.71938C7.40516 9.51625 8.59186 9.0672 9.65559 8.39867C10.7193 7.73013 11.6386 6.85561 12.3594 5.82654C13.0802 4.79747 13.5878 3.63465 13.8526 2.40648C14.5174 3.05723 15.0448 3.83492 15.4033 4.69337C15.7619 5.55183 15.9443 6.47357 15.9398 7.40388C15.9393 7.49044 15.9419 7.57777 15.9382 7.665C15.8708 9.2842 15.2384 10.8287 14.1508 12.0301C13.0632 13.2316 11.5892 14.0141 9.98463 14.2419C8.38012 14.4696 6.74651 14.1282 5.36754 13.2768C3.98858 12.4255 2.95137 11.118 2.43606 9.58151V9.58151ZM0.933336 8.6487C0.933165 8.68529 0.9362 8.72182 0.942407 8.75788C1.28351 10.7502 2.34974 12.5458 3.93575 13.7989C5.52175 15.052 7.51534 15.6739 9.53252 15.5449C11.5497 15.4158 13.4478 14.545 14.8612 13.1C16.2746 11.655 17.1033 9.73813 17.1878 7.71859C17.1921 7.61606 17.189 7.51347 17.1897 7.41179C17.1985 6.10006 16.8914 4.80548 16.2943 3.6375C15.6972 2.46953 14.8276 1.4625 13.7591 0.701557C13.667 0.639835 13.5603 0.603476 13.4496 0.59614C13.339 0.588804 13.2284 0.610752 13.129 0.659772C13.0295 0.708793 12.9447 0.783154 12.8832 0.875366C12.8216 0.967579 12.7855 1.07438 12.7783 1.18503C12.661 2.43295 12.2583 3.63719 11.6014 4.70467C10.9444 5.77214 10.0508 6.67425 8.98959 7.34127C7.92838 8.00829 6.728 8.42235 5.48124 8.55146C4.23448 8.68056 2.97473 8.52125 1.79936 8.08583C1.70533 8.04882 1.60382 8.03482 1.50329 8.04497C1.40276 8.05513 1.30611 8.08916 1.22138 8.14423C1.13666 8.19929 1.06632 8.2738 1.01622 8.36155C0.966112 8.4493 0.937697 8.54775 0.933336 8.6487V8.6487Z" fill="white" />
                                    <path d="M2.43606 9.58151C3.65752 9.87564 4.92547 9.92252 6.16531 9.71938C7.40516 9.51625 8.59186 9.0672 9.65559 8.39867C10.7193 7.73013 11.6386 6.85561 12.3594 5.82654C13.0802 4.79747 13.5878 3.63465 13.8526 2.40648C14.5174 3.05723 15.0448 3.83492 15.4033 4.69337C15.7619 5.55183 15.9443 6.47357 15.9398 7.40388C15.9393 7.49044 15.9419 7.57777 15.9382 7.665C15.8708 9.2842 15.2384 10.8287 14.1508 12.0301C13.0632 13.2316 11.5892 14.0141 9.98463 14.2419C8.38012 14.4696 6.74651 14.1282 5.36754 13.2768C3.98858 12.4255 2.95137 11.118 2.43606 9.58151V9.58151ZM0.933336 8.6487C0.933165 8.68529 0.9362 8.72182 0.942407 8.75788C1.28351 10.7502 2.34974 12.5458 3.93575 13.7989C5.52175 15.052 7.51534 15.6739 9.53252 15.5449C11.5497 15.4158 13.4478 14.545 14.8612 13.1C16.2746 11.655 17.1033 9.73813 17.1878 7.71859C17.1921 7.61606 17.189 7.51347 17.1897 7.41179C17.1985 6.10006 16.8914 4.80548 16.2943 3.6375C15.6972 2.46953 14.8276 1.4625 13.7591 0.701557C13.667 0.639835 13.5603 0.603476 13.4496 0.59614C13.339 0.588804 13.2284 0.610752 13.129 0.659772C13.0295 0.708793 12.9447 0.783154 12.8832 0.875366C12.8216 0.967579 12.7855 1.07438 12.7783 1.18503C12.661 2.43295 12.2583 3.63719 11.6014 4.70467C10.9444 5.77214 10.0508 6.67425 8.98959 7.34127C7.92838 8.00829 6.728 8.42235 5.48124 8.55146C4.23448 8.68056 2.97473 8.52125 1.79936 8.08583C1.70533 8.04882 1.60382 8.03482 1.50329 8.04497C1.40276 8.05513 1.30611 8.08916 1.22138 8.14423C1.13666 8.19929 1.06632 8.2738 1.01622 8.36155C0.966112 8.4493 0.937697 8.54775 0.933336 8.6487V8.6487Z" fill="white" fill-opacity="0.8" />
                                </svg>
                            </div>

                        </div>
                        <div class="single_action__haeader user_avatar__information openuptip" flow="down" tooltip="Profile">
                            <div class="avatar">
                                <img src="<?= $dataAkun['profile_image_path']?>" alt="avatar">
                            </div>
                            <div class="user_information_main_wrapper slide-down__click">
                                <div class="user_header">
                                    <div class="main-avatar">
                                        <img src="<?= $dataAkun['profile_image_path']?>" alt="user">
                                    </div>
                                    <div class="user_naim-information">
                                        <h3 class="title"><?= $dataAkun['first_name'] . " " . $dataAkun['last_name'] ?></h3>
                                        <span class="desig"><?= $dataAkun['username']?></span>
                                    </div>
                                </div>
                                <div class="user_body_content">
                                    <ul class="items">
                                        <li class="single_items">
                                            <a class="hader_popup_link" href="profile.php">
                                                <i class="fa-light fa-user"></i>
                                                Profile
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="popup-footer-btn">
                                    <a href="logout.php" class="geex-content__header__popup__footer__link">Logout
                                        <i class="fa-light fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>