<?php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'nutrisnap');

$koneksi = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($koneksi === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$gmailid = '';
$gmailpassword = '';
$gmailusername = '';

?>