<?php
require_once '../../Models/user.php';
require_once '../../Controllers/DBController.php';
session_start();
if ($_SESSION['personID'] && ($_SESSION['personRole'] == '2')) {
    
}else{
    header("location: /SE_project/Views/Auth/login.php");
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
LeaderBoard
</title>
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
<!-- Nucleo Icons -->
<link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
<link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
<!-- Font Awesome Icons -->
<script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
<!-- Material Icons -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
<!-- CSS Files -->
<link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show  bg-gray-100">
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2  bg-white my-2" id="sidenav-main">
<div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand px-4 py-3 m-0" href=" https://demos.creative-tim.com/material-dashboard/pages/dashboard " target="_blank">
    <img src="../assets/img/logo-ct-dark.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
    <span class="ms-1 text-sm text-dark">Creative Tim</span>
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
        <a class="nav-link text-dark" href="../../Views/User/recommendedTags.php">
        <i class="material-symbols-rounded opacity-5">receipt_long</i>
        <span class="nav-link-text ms-1">Recommended Tags</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-dark" href="../../Views/User/Favorite.php">
        <i class="material-symbols-rounded opacity-5">favorite</i>
        <span class="nav-link-text ms-1">favorites</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active bg-gradient-dark text-white" href="../../Views/User/leaderBoard.php">
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
        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">LeaderBoard</li>
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

<div class="container-fluid py-2">
    <div class="row">
    <div class="col-12">
        <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
            <h6 class="text-white text-capitalize ps-3">leaderBoard</h6>
            </div>
        </div>
        <div class="card-body px-0 pb-2">
            <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
                <thead>
                <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">User</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Points</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Badges</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">JoinDate</th>
                </tr>
                </thead>
                <tbody>
    <?php
    $user = new User();
    $users = $user->viewLeaderBoard();

    if ($users) {
        foreach ($users as $user) {
            $joinDate = date("d/m/y", strtotime($user['joinDate']));
            ?>
            <tr>
                <td>
                    <div class="d-flex px-2 py-1">
                        <?php
                            $DbUserProfile = new DBController;
                            if (isset($user['user_id'])) {
                                $userID = $user['user_id'];  
                            } else {
                                $userID = isset($_SESSION['personID']) ? intval($_SESSION['personID']) : 0;
                            }
                        ?>   
                        <div class="rounded-circle overflow-hidden" style="width: 50px; height: 50px; margin-right: 10px;">
                            <img src="<?php echo htmlspecialchars($user["profile_picture"]); ?>" alt="User Image" class="w-100 h-100 object-fit-contain">
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($user['username']); ?></h6>
                        <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                </td>
                <td>
                    <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($user['points']); ?> Points</p>
                </td>
                <td class="align-middle text-center text-sm">
                    <span class="badge badge-sm <?php echo $user['badge'] === 'Online' ? 'bg-gradient-success' : 'bg-gradient-secondary'; ?>">
                        <?php echo htmlspecialchars($user['badge']); ?>
                    </span>
                </td>
                <td class="align-middle text-center">
                    <span class="text-secondary text-xs font-weight-bold"><?php echo $joinDate; ?></span>
                </td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td colspan="5" class="text-center">No users found.</td>
        </tr>
        <?php
    }
    ?>
</tbody>
            </div>
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