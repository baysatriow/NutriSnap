<?php
require_once "partials/config.php";

$useremail = $username = $password = $confirm_password = "";
$useremail_err = $username_err = $password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["useremail"]))) {
        $useremail_err = "Please enter an email.";
    } elseif (!filter_var(trim($_POST["useremail"]), FILTER_VALIDATE_EMAIL)) {
        $useremail_err = "Invalid email format.";
    } else {
        $sql = "SELECT id_user FROM users WHERE useremail = ?";
        if ($stmt = mysqli_prepare($koneksi, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_useremail);
            $param_useremail = trim($_POST["useremail"]);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $useremail_err = "This email is already taken.";
                } else {
                    $useremail = trim($_POST["useremail"]);
                }
            } else {
                echo "Oops! Something went wrong with email check. Please try again later.";
                // error_log("MySQLi Execute Error: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Oops! Something went wrong preparing email check. Please try again later.";
            // error_log("MySQLi Prepare Error: " . mysqli_error($koneksi));
        }
    }

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
        $username_err = "Username can only contain letters, numbers, and underscores.";
    } else {
        // Prepare a select statement to check if username exists
        $sql_username = "SELECT id_user FROM users WHERE username = ?";
        if ($stmt_username = mysqli_prepare($koneksi, $sql_username)) {
            mysqli_stmt_bind_param($stmt_username, "s", $param_username_check);
            $param_username_check = trim($_POST["username"]);

            if (mysqli_stmt_execute($stmt_username)) {
                mysqli_stmt_store_result($stmt_username);
                if (mysqli_stmt_num_rows($stmt_username) == 1) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Something went wrong with username check. Please try again later.";
                // error_log("MySQLi Execute Error (username): " . mysqli_stmt_error($stmt_username));
            }
            mysqli_stmt_close($stmt_username);
        } else {
             echo "Oops! Something went wrong preparing username check. Please try again later.";
            // error_log("MySQLi Prepare Error (username): " . mysqli_error($koneksi));
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 8) {
        $password_err = "Password must have at least 8 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Passwords do not match.";
        }
    }

    // Check input errors before inserting in database
    if (empty($useremail_err) && empty($username_err) && empty($password_err) && empty($confirm_password_err)) {

        // Prepare an insert statement
        $sql_insert = "INSERT INTO users (useremail, username, password, token) VALUES (?, ?, ?, ?)";

        if ($stmt_insert = mysqli_prepare($koneksi, $sql_insert)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt_insert, "ssss", $param_useremail_insert, $param_username_insert, $param_password_insert, $param_token);

            // Set parameters
            $param_useremail_insert = $useremail;
            $param_username_insert = $username;
            $param_password_insert = password_hash($password, PASSWORD_DEFAULT);
            $param_token = bin2hex(random_bytes(50));

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt_insert)) {
                // Redirect to login page (or a success page)
                header("location: login.php");
                exit;
            } else {
                echo "Something went wrong with registration. Please try again later.";
                // error_log("MySQLi Execute Error (insert): " . mysqli_stmt_error($stmt_insert));
            }
            mysqli_stmt_close($stmt_insert);
        } else {
            echo "Something went wrong preparing registration. Please try again later.";
            // error_log("MySQLi Prepare Error (insert): " . mysqli_error($koneksi));
        }
    }

    // Close connection
    mysqli_close($koneksi);
}
?>

<?php include 'partials/main.php'; ?>

<head>
    <?php include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php'; ?>
</head>

<body class="register">

    <div class="dash-board-main-wrapper">
        <div class="main-center-content-m-left center-content">
            <div class="rts-register-area">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="single-form-s-wrapper">
                                <div class="head">
                                    <span>Yuk Mulai Hidup Sehat!</span>
                                    <h5 class="title">Registrasi Akun NutriSnap!</h5>
                                </div>
                                <div class="body">
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" novalidate>
                                        <div class="input-wrapper text-start">
                                            <div class="form-group <?= !empty($username_err) ? 'has-error' : ''; ?>">
                                                <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($username); ?>" class="form-control">
                                                <span class="text-danger help-block"><?=$username_err;?></span>
                                            </div>

                                            <div class="form-group <?= !empty($useremail_err) ? 'has-error' : ''; ?>">
                                                <input type="email" name="useremail" placeholder="Enter your mail" value="<?= htmlspecialchars($useremail); ?>" class="form-control">
                                                <span class="text-danger help-block"><?=$useremail_err;?></span>
                                            </div>

                                            <div class="form-group <?= !empty($password_err) ? 'has-error' : ''; ?>">
                                                <input type="password" name="password" placeholder="Enter your Password" class="form-control">
                                                <span class="text-danger help-block"><?=$password_err;?></span>
                                            </div>

                                            <div class="form-group <?= !empty($confirm_password_err) ? 'has-error' : ''; ?>">
                                                <input type="password" name="confirm_password" placeholder="Confirm your Password" class="form-control">
                                                <span class="text-danger help-block"><?=$confirm_password_err;?></span>
                                            </div>
                                        </div>
                                        <div class="check-wrapper">
                                            <div class="form-check">
                                                <!-- <input class="form-check-input" type="checkbox" value="" id="termsCheck" required>
                                                <label class="form-check-label" for="termsCheck">
                                                    I agree to the <a href="terms.php">terms and conditions</a>.
                                                </label> -->
                                            </div>
                                        </div>
                                        <button type="submit" class="rts-btn btn-primary">Buat Sekarang!</button>
                                        <p>Sudah Punya Akun?? <a class="ml--5" href="login.php">Login Disini!</a></p>
                                    </form>
                                </div>
                            </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'partials/script.php'; ?>
</body>

</html>
