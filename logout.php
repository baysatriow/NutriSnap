<?php
// Initialize the session
session_start();

// Check if the user is already logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    // Destroy the session
    session_destroy();
    
    // Redirect to the login page
    header("location: login.php");
    exit;
}