<?php
require_once __DIR__ . '/../../Controllers/DBController.php';
require_once __DIR__ . '/../../Models/person.php';
require_once __DIR__ . '/../../Models/user.php';
require_once __DIR__ . '/../../Models/question.php';

session_start();
if ($_SESSION['personID'] && ($_SESSION['personRole'] == '2')) {
    
}else{
    header("location: /SE_project/Views/Auth/login.php");
}

$user = new User();
$quest = new Question;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'], $_POST['user_id'], $_POST['quest_id'])) {
    $userId = (int) $_POST['user_id'];
    $questId = (int) $_POST['quest_id'];
    $quest->deleteFavoriteQuestions($userId, $questId);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_SESSION["personID"])){
    $userId = $_SESSION["personID"];
    $favoriteQuestions = $quest->viewFavoriteQuestions($userId); 
}
else{
    echo "you do not start session";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
<link rel="icon" type="image/png" href="../assets/img/favicon.png">
<title>
Favourites
</title>
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
<link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
<link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
<script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
<link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show  bg-gray-100">
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2  bg-white my-2" id="sidenav-main">
<div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand px-4 py-3 m-0" href=" https://demos.creative-tim.com/material-dashboard/pages/dashboard " target="_blank">
    <img src="../assets/img/logo-ct-dark.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
    <span class="ms-1 text-sm text-dark"> Menu </span>
    </a>
</div>
<hr class="horizontal dark mt-0 mb-2">
<div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
    <ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link text-dark" href="../../Views/User/user_home.php">
        <i class="material-symbols-rounded opacity-5">Home</i>
        <span class="nav-link-text ms-1">Home</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-dark" href="../../views/User/recommendedTags.php">
        <i class="material-symbols-rounded opacity-5">receipt_long</i>
        <span class="nav-link-text ms-1">Recommended Tags</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active bg-gradient-dark text-white" href="../../Views/User/Favorite.php">
        <i class="material-symbols-rounded opacity-5">favorite</i>
        <span class="nav-link-text ms-1">favorites</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-dark" href="../../Views/User/leaderBoard.php">
        <i class="material-symbols-rounded opacity-5">table_view</i>
        <span class="nav-link-text ms-1">Leaderboard</span>
        </a>
    </li>
    <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Account pages</h6>
    </li>
    <li class="nav-item">
          <a class="nav-link text-dark" href="userprofile.php">
            <i class="material-symbols-rounded opacity-5">person</i>
            <span class="nav-link-text ms-1">Profile</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="../Auth/logout.php">
            <i class="material-symbols-rounded opacity-5">login</i>
            <span class="nav-link-text ms-1">Logout</span>
          </a>
        </li>
    </ul>
</div>
</aside>
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
    <div class="container-fluid py-1 px-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Favourites</li>
        </ol>
    </nav>
        </div>
        <ul class="navbar-nav d-flex align-items-center  justify-content-end">
        <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
            <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
            <div class="sidenav-toggler-inner">
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
            </div>
            </a>
        </li>
        <li class="nav-item px-3 d-flex align-items-center">
            <a href="javascript:;" class="nav-link text-body p-0">
            <i class="material-symbols-rounded fixed-plugin-button-nav">settings</i>
            </a>
        </li>
        <li class="nav-item dropdown pe-3 d-flex align-items-center">
            <a href="javascript:;" class="nav-link text-body p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="material-symbols-rounded">notifications</i>
            </a>
        </li>
        <li class="nav-item d-flex align-items-center">
            <a href="../../Views/User/userprofile.php" class="nav-link text-body font-weight-bold px-0">
            <i class="material-symbols-rounded">account_circle</i>
            </a>
        </li>
        </ul>
    </div>
    </div>
</nav>
<div class="card">
    <div class="card-header pb-0 px-3">
        <h6 class="mb-0">Favorite Questions</h6>
    </div>

<div class="card">
    
    <div class="card-body pt-4 p-3">
        <ul class="list-group">
            <?php
            if ($favoriteQuestions && !empty($favoriteQuestions)) {
                foreach ($favoriteQuestions as $question) {
                    ?>
                    <li class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                        <div class="d-flex flex-column">
                            <h6 class="mb-3" style="color: #888;font-size: larger;"><?php echo htmlspecialchars($question['username']); ?></h6>
                            <h6 class="mb-3 text-sm"><?php echo htmlspecialchars($question['content']); ?></h6>
                        </div>
                        <div class="ms-auto text-end">
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this favorite question?');">
                            <input type="hidden" name="delete" value="1">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($question['user_id']); ?>">
                            <input type="hidden" name="quest_id" value="<?php echo htmlspecialchars($question['quest_id']); ?>">
                            <button type="submit" class="btn btn-link text-danger text-gradient px-3 mb-0">
                                <i class="material-symbols-rounded text-sm me-2">delete</i>Delete
                            </button>
                        </form>
</div>
        </li>
        <?php
                }
            } else {
                ?>
                <li class="list-group-item border-0 p-4 mb-2">
                    <p class="text-sm">No favorite questions found.</p>
                </li>
                <?php
            }
            ?>
        </ul>
    </div>
</div>
        </ul>
    </div>
</div>
    <footer class="footer py-4  ">
    <div class="container-fluid">
        <div class="row align-items-center justify-content-lg-between">
        <div class="col-lg-6 mb-lg-0 mb-4">
            <div class="copyright text-center text-sm text-muted text-lg-start">
            © <script>
                document.write(new Date().getFullYear())
            </script>,
            made with <i class="fa fa-heart"></i> by
            <a href="https://www.creative-tim.com" class="font-weight-bold" target="_blank">Creative Tim</a>
            for a better web.
            </div>
        </div>
        <div class="col-lg-6">
            <ul class="nav nav-footer justify-content-center justify-content-lg-end">
            <li class="nav-item">
                <a href="https://www.creative-tim.com" class="nav-link text-muted" target="_blank">Creative Tim</a>
            </li>
            <li class="nav-item">
                <a href="https://www.creative-tim.com/presentation" class="nav-link text-muted" target="_blank">About Us</a>
            </li>
            <li class="nav-item">
                <a href="https://www.creative-tim.com/blog" class="nav-link text-muted" target="_blank">Blog</a>
            </li>
            <li class="nav-item">
                <a href="https://www.creative-tim.com/license" class="nav-link pe-0 text-muted" target="_blank">License</a>
            </li>
            </ul>
        </div>
        </div>
    </div>
    </footer>
</div>
</main>
<div class="fixed-plugin">
<a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
    <i class="material-symbols-rounded py-2">settings</i>
</a>
<div class="card shadow-lg">
    <div class="card-header pb-0 pt-3">
    <div class="float-start">
        <h5 class="mt-3 mb-0">Material UI Configurator</h5>
        <p>See our dashboard options.</p>
    </div>
    <div class="float-end mt-4">
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
        <i class="material-symbols-rounded">clear</i>
        </button>
    </div>
    </div>
    <hr class="horizontal dark my-1">
    <div class="card-body pt-sm-3 pt-0">
    <div>
        <h6 class="mb-0">Sidebar Colors</h6>
    </div>
    <a href="javascript:void(0)" class="switch-trigger background-color">
        <div class="badge-colors my-2 text-start">
        <span class="badge filter bg-gradient-primary" data-color="primary" onclick="sidebarColor(this)"></span>
        <span class="badge filter bg-gradient-dark active" data-color="dark" onclick="sidebarColor(this)"></span>
        <span class="badge filter bg-gradient-info" data-color="info" onclick="sidebarColor(this)"></span>
        <span class="badge filter bg-gradient-success" data-color="success" onclick="sidebarColor(this)"></span>
        <span class="badge filter bg-gradient-warning" data-color="warning" onclick="sidebarColor(this)"></span>
        <span class="badge filter bg-gradient-danger" data-color="danger" onclick="sidebarColor(this)"></span>
        </div>
    </a>
    <div class="mt-3">
        <h6 class="mb-0">Sidenav Type</h6>
        <p class="text-sm">Choose between different sidenav types.</p>
    </div>
    <div class="d-flex">
        <button class="btn bg-gradient-dark px-3 mb-2" data-class="bg-gradient-dark" onclick="sidebarType(this)">Dark</button>
        <button class="btn bg-gradient-dark px-3 mb-2 ms-2" data-class="bg-transparent" onclick="sidebarType(this)">Transparent</button>
        <button class="btn bg-gradient-dark px-3 mb-2  active ms-2" data-class="bg-white" onclick="sidebarType(this)">White</button>
    </div>
    <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
    <div class="mt-3 d-flex">
        <h6 class="mb-0">Navbar Fixed</h6>
        <div class="form-check form-switch ps-0 ms-auto my-auto">
        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed" onclick="navbarFixed(this)">
        </div>
    </div>
    <hr class="horizontal dark my-3">
    <div class="mt-2 d-flex">
        <h6 class="mb-0">Light / Dark</h6>
        <div class="form-check form-switch ps-0 ms-auto my-auto">
        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version" onclick="darkMode(this)">
        </div>
    </div>
    <hr class="horizontal dark my-sm-4">
    <a class="btn bg-gradient-info w-100" href="https://www.creative-tim.com/product/material-dashboard-pro">Free Download</a>
    <a class="btn btn-outline-dark w-100" href="https://www.creative-tim.com/learning-lab/bootstrap/overview/material-dashboard">View documentation</a>
    <div class="w-100 text-center">
        <a class="github-button" href="https://github.com/creativetimofficial/material-dashboard" data-icon="octicon-star" data-size="large" data-show-count="true" aria-label="Star creativetimofficial/material-dashboard on GitHub">Star</a>
        <h6 class="mt-3">Thank you for sharing!</h6>
        <a href="https://twitter.com/intent/tweet?text=Check%20Material%20UI%20Dashboard%20made%20by%20%40CreativeTim%20%23webdesign%20%23dashboard%20%23bootstrap5&amp;url=https%3A%2F%2Fwww.creative-tim.com%2Fproduct%2Fsoft-ui-dashboard" class="btn btn-dark mb-0 me-2" target="_blank">
        <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.creative-tim.com/product/material-dashboard" class="btn btn-dark mb-0 me-2" target="_blank">
        <i class="fab fa-facebook-square me-1" aria-hidden="true"></i> Share
        </a>
    </div>
    </div>
</div>
</div>
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script>
var win = navigator.platform.indexOf('Win') > -1;
if (win && document.querySelector('#sidenav-scrollbar')) {
    var options = {
    damping: '0.5'
    }
    Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
}
</script>
<script async defer src="https://buttons.github.io/buttons.js"></script>
<script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>