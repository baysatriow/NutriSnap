<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect them to index page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

// Include config file
require_once "partials/config.php";

// Define variables and initialize with empty values
$useremail = $password = "";
$useremail_err = $password_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate useremail
    if (empty(trim($_POST["useremail"]))) {
        $useremail_err = "Please enter useremail.";
    } else {
        $useremail = trim($_POST["useremail"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Check for input errors before querying the database
    if (empty($useremail_err) && empty($password_err)) {

        // Prepare a SELECT statement
        $sql = "SELECT id_user, useremail, password FROM users WHERE useremail = ?";

        if ($stmt = mysqli_prepare($koneksi, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_useremail);

            // Set parameters
            $param_useremail = $useremail;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);

                // Check if useremail exists in the database
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $useremail, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        // Verify password
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id_user"] = $id;
                            $_SESSION["useremail"] = $useremail;

                            // Redirect user to welcome page
                            header("location: index.php");
                            exit;
                        } else {
                            // Password is not valid
                            $password_err = "The password you entered was not valid.";
                        }
                    }
                } else {
                    // No useremail found
                    $useremail_err = "No account found with that useremail.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close connection
    mysqli_close($koneksi);
}
?>


<?php include 'partials/main.php'; ?>

<head>
    <?php include 'partials/title-meta.php'; ?>
    <!-- bootstrap Css -->
    <?php include 'partials/head-css.php'; ?>
</head>

<body class="register page-login">

    <div class="dash-board-main-wrapper pt--40">
        <div class="main-center-content-m-left center-content">
            <div class="rts-register-area">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- single login area start -->
                            <div class="single-form-s-wrapper">
                                <div class="head">
                                    <span>Selamat Datang</span>
                                    <h5 class="title">Masuk Untuk Melanjutkan</h5>
                                </div>
                                <div class="body">
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                        <div class="input-wrapper text-start">
                                            <div <?php echo (!empty($useremail_err)) ? 'has-error' : ''; ?>>
                                                <input type="email" name="useremail" value="bayusatriowid@gmail.com" placeholder="Enter your mail" >
                                                <span class="text-danger"><?php echo $useremail_err; ?></span>
                                            </div>
                                            <div <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>>
                                                <input type="password" name="password" value="Admin123" placeholder="Password">
                                                <span class="text-danger"><?php echo $password_err; ?></span>
                                            </div>
                                        </div>
                                        <div class="check-wrapper">
                                            <div class="form-check">

                                            </div>
                                            <a href="#">Lupa Password?</a>
                                        </div>
                                        <button type="submit" class="rts-btn btn-primary">Masuk</button>
                                        <p>Belum Punya Akun? <a class="ml--5" href="register.php">Buat Disini!</a></p>
                                    </form>
                                </div>
                            </div>
                            <!-- single login area end -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- jquery Js -->
    <?php include 'partials/script.php'; ?>
</body>

</html>