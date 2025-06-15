<?php
require_once '../../Models/person.php';
require_once '../../Controllers/AuthController.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp2/htdocs/SEproject/php_errors.log');

$errMsg = "";

if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirmPassword'])) {
    if (!empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['confirmPassword'])) {
        if ($_POST['password'] == $_POST['confirmPassword']) {
            $person = new Person;
            $auth = new AuthController;
            $person->email = $_POST['email'];
            $person->password = $_POST['password'];
            $person->role_id = $_SESSION["personRole"] ?? 2;
            if (!$auth->resetPass($person)) {
                $errMsg = $_SESSION["errMsg"] ?? "Failed to reset password.";
                error_log("resetPass failed for email: " . $_POST['email']);
            } else {
                $db = new DBController();
                if ($db->openConnection()) {
                    $table = $_SESSION["personRole"] == 1 ? 'admins' : 'users';
                    $query = "SELECT password FROM $table WHERE email = '" . mysqli_real_escape_string($db->connection, $_POST['email']) . "'";
                    $result = $db->select($query);
                    if ($result && count($result) > 0) {
                        error_log("Password in $table for email " . $_POST['email'] . ": " . $result[0]['password']);
                    } else {
                        error_log("No record found in $table for email: " . $_POST['email']);
                    }
                    $db->closeConnection();
                }

                if ($auth->roleCheck($person) == "Admin") {
                    header("Location: ../Admin/adminHome.php");
                } else {
                    header("Location: ../User/user_home.php");
                }
                exit;
            }
        } else {
            $errMsg = "The new Password is not matched!";
        }
    } else {
        $errMsg = "Please fill all fields";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <title>Reset Password</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    <title>
        Reset Password
    </title>
</head>

<body class="bg-gray-200">
    <div class="container position-sticky z-index-sticky top-0">
        <div class="row"></div>
    </div>
    <main class="main-content mt-0">
        <div class="page-header align-items-start min-vh-100" style="background-image: url('https://cdn.vanderbilt.edu/vu-news/files/20230520091339/GettyImages-1208305480-1.jpg');">
            <span class="mask bg-gradient-dark opacity-6"></span>
            <div class="container my-auto">
                <div class="row">
                    <div class="col-lg-4 col-md-8 col-12 mx-auto">
                        <div class="card z-index-0 fadeIn3 fadeInBottom">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-dark shadow-dark border-radius-lg py-3 pe-1">
                                    <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Reset Password</h4>
                                    <div class="row mt-3"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if ($errMsg != ""): ?>
                                    <div class="alert alert-danger alert-dismissible text-white" role="alert">
                                        <span class="text-sm"><?php echo htmlspecialchars($errMsg); ?></span>
                                        <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                <?php endif; ?>
                                <form role="form" class="text-start" action="forgetPass.php" method="POST">
                                    <div class="input-group input-group-outline my-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email">
                                    </div>
                                    <div class="input-group input-group-outline mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="password">
                                    </div>
                                    <div class="input-group input-group-outline mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" name="confirmPassword">
                                    </div>
                                    <div class="text-center">
                                        <input type="submit" class="btn bg-gradient-dark w-100 my-4 mb-2" value="Reset Password">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="footer position-absolute bottom-2 py-2 w-100">
                <div class="container">
                    <div class="row align-items-center justify-content-lg-between">
                        <div class="col-12 col-md-6 my-auto">
                            <div class="copyright text-center text-sm text-white text-lg-start">
                                © <script>document.write(new Date().getFullYear())</script>,
                                made with <i class="fa fa-heart" aria-hidden="true"></i> by
                                <a href="https://www.creative-tim.com" class="font-weight-bold text-white" target="_blank">Creative Tim</a>
                                for a better web.
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                                <li class="nav-item">
                                    <a href="https://www.creative-tim.com" class="nav-link text-white" target="_blank">Creative Tim</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://www.creative-tim.com/presentation" class="nav-link text-white" target="_blank">About Us</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://www.creative-tim.com/blog" class="nav-link text-white" target="_blank">Blog</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://www.creative-tim.com/license" class="nav-link pe-0 text-white" target="_blank">License</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </main>
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = { damping: '0.5' };
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>