<?php
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    session_destroy();
    
    header("location: login.php");
    exit;
}