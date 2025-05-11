<?php
// Include config file
require_once "partials/config.php";

// Initialize variables with empty values
$useremail = $username = $password = $confirm_password = "";
$useremail_err = $username_err = $password_err = $confirm_password_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate useremail
    if (empty(trim($_POST["useremail"]))) {
        $useremail_err = "Please enter an email.";
    } elseif (!filter_var($_POST["useremail"], FILTER_VALIDATE_EMAIL)) {
        $useremail_err = "Invalid email format.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE useremail = ?";

        if ($stmt = mysqli_prepare($koneksi, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_useremail);

            // Set parameter
            $param_useremail = trim($_POST["useremail"]);

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $useremail_err = "This email is already taken.";
                } else {
                    $useremail = trim($_POST["useremail"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 8) {
        $password_err = "Password must have at least 8 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // // Validate confirm password
    // if (empty(trim($_POST["confirm_password"]))) {
    //     $confirm_password_err = "Please confirm password.";
    // } else {
    //     $confirm_password = trim($_POST["confirm_password"]);
    //     if ($password != $confirm_password) {
    //         $confirm_password_err = "Password did not match.";
    //     }
    // }

    // Check input errors before inserting into database
    if (empty($useremail_err) && empty($username_err) && empty($password_err) && empty($confirm_password_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO users (useremail, username, password, token) VALUES (?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($koneksi, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssss", $param_useremail, $param_username, $param_password, $param_token);

            // Set parameters
            $param_useremail = $useremail;
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_token = bin2hex(random_bytes(50)); // generate unique token

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to index page
                header("location: index.php");
                exit;
            } else {
                echo "Something went wrong. Please try again later.";
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

<body class="register">

    <div class="dash-board-main-wrapper">
        <div class="main-center-content-m-left center-content">
            <div class="rts-register-area">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- single login area start -->
                            <div class="single-form-s-wrapper">
                                <div class="head">
                                    <span>Yuk Mulai Hidup Sehat!</span>
                                    <h5 class="title">Registrasi Akun NutriSnap!</h5>
                                </div>
                                <div class="body">
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                        <div class="input-wrapper text-start">
                                            <div <?= !empty($username_err) ? 'has-error' : ''; ?>>
                                                <input type="text" name="username" placeholder="Name" >
                                                <span class="text-danger"><?=$username_err?></span>
                                            </div>
                                            <div <?= !empty($useremail_err) ? 'has-error' : ''; ?>>
                                                <input type="text" name="useremail" placeholder="Enter your mail">
                                                <span class="text-danger"><?=$useremail_err?></span>
                                            </div>
                                            <div <?= !empty($password_err) ? 'has-error' : ''; ?>>
                                                <input type="password" name="password" placeholder="Enter your Password">
                                                <span class="text-danger"><?=$password_err?></span>
                                            </div>
                                        </div>
                                        <div class="check-wrapper">
                                            <div class="form-check">
                                            </div>
                                        </div>
                                        <button class="rts-btn btn-primary">Buat Sekarang!</button>
                                        <p>Sudah Punya Akun?? <a class="ml--5" href="login.php">Login Disini!</a></p>
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