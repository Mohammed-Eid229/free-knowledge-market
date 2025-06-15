<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
error_log('PHP script executed');



$statusMessage = "";
$statusType = "";

require_once '../../Controllers/DBController.php';
require_once '../../Models/person.php';
require_once '../../models/user.php';
require_once '../../models/voter.php';
require_once '../../models/question.php';
require_once '../../models/answer.php';

$DbUserProfile = new DBController();
session_start();
if ($_SESSION['personID'] && ($_SESSION['personRole'] == '2')) {
    
}else{
    header("location: /SE_project/Views/Auth/login.php");
}
if (!isset($_SESSION["personID"]) || empty($_SESSION["personID"])) {
    header("Location: ../Auth/login.php");
    exit();
} 


$userID = $_SESSION["personID"];

$person = null;
if ($DbUserProfile->openConnection() && isset($userID) && !empty($userID)) {
    $query = "SELECT u.id, u.name, u.email, u.badge AS role_name, u.points, u.profile_picture, u.username, u.password
              FROM users u
              JOIN roles r ON u.role_id = r.ID
              WHERE u.id = ?";
    $stmt = $DbUserProfile->connection->prepare($query);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    $person = new Person();
    if ($result && $result->num_rows > 0) {
        $userData = $result->fetch_assoc();
        $person->ID = $userData['id'];
        $person->name = $userData['name'];
        $person->email = $userData['email'];
        $person->role_id = $userData['role_name'];
        $person->points = $userData['points'];
        $person->profile_picture = $userData['profile_picture'] ?? "../assets/img/bruce-mars.jpg";
        $person->username = $userData['username'];
        $person->password = $userData['password']; 
    } else {
        echo "Error fetching user data.";
    }
    $stmt->close();
} else {
    echo "Database connection error";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_username']) && $person) {
  $result = $person->changeUsername($DbUserProfile, $_POST['new_username'],$person);
  if ($result === "Username updated successfully!") {
      $_SESSION['statusMessage'] = $result; 
      $_SESSION['statusType'] = 'success';   
      $_SESSION['username'] = $person->username;
  } else {
      $_SESSION['statusMessage'] = $result; 
      $_SESSION['statusType'] = 'error'; 
  }
  header("Location: userprofile.php");
  exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['old_password']) && isset($_POST['new_password']) && isset($_POST['confirm_password']) && $person) {
  if ($_POST['new_password'] === $_POST['confirm_password']) {
      $result = $person->changePassword($DbUserProfile, $_POST['old_password'], $_POST['new_password'], $person);
      if ($result === true) {
          $_SESSION['statusMessage'] = "Password updated successfully!";
          $_SESSION['statusType'] = 'success';
      } else {
          $_SESSION['statusMessage'] = $result; 
          $_SESSION['statusType'] = 'error';
      }
  } else {
      $_SESSION['statusMessage'] = "New password and confirm password do not match.";
      $_SESSION['statusType'] = 'error';
  }
  header("Location: userprofile.php");
  exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES["new_photo"]) && $_FILES["new_photo"]["error"] === UPLOAD_ERR_OK && $person) {
  $result = $person->changeProfilePicture($DbUserProfile, $_FILES["new_photo"], $person);
  if ($result === "Photo updated successfully!") {
      $_SESSION['statusMessage'] = $result; 
      $_SESSION['statusType'] = 'success';   
  } else {
      $_SESSION['statusMessage'] = $result; 
      $_SESSION['statusType'] = 'error'; 
  }
  header("Location: userprofile.php");
  exit();
}


$user = new User();
$questHist = new Question;
$questionHistory = $questHist->viewQuestionHistory($DbUserProfile, $userID);


$badgeQuery = "SELECT badge FROM users WHERE ID = " . $userID;
$badgeResult = $DbUserProfile->select($badgeQuery);


$isNewUser = false;
if ($badgeResult) {
    $badge = $badgeResult[0]['badge'];
    if ($badge == "newUser") {
        $isNewUser = true;
    }
}




if (!isset($_SESSION['votes'])) {
  $_SESSION['votes'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $questionId = $_POST['question_id'] ?? null;
  $voteType = $_POST['vote_type'] ?? null;
  $removeOpposite = isset($_POST['remove_opposite']) ? (int)$_POST['remove_opposite'] : 0;

  if (!$questionId || !$voteType) {
      echo 'invalid input';
      exit;
  }

  $voter = new Voter(); 
  $success = false;

  $voted = "select vote_type frome question_votes where user_id = $userId and question_id = $questionId";
  
  $previousVote = $_SESSION['votes'][$questionId] ?? null;

if ($voteType === $previousVote) {
    echo 'same vote';
    exit;
}

  if ($removeOpposite === 1 && $previousVote !== null) {
      if ($previousVote === 'like') {
          $voter->unupVoteQuestion($DbUserProfile, $questionId);
          unset($_SESSION['votes'][$questionId]);
      } elseif ($previousVote === 'dislike') {
          $voter->undownVoteQuestion($DbUserProfile, $questionId);
          unset($_SESSION['votes'][$questionId]);
      }
  }

 
  switch ($voteType) {
        case 'like':
            $success = $voter->upVoteQuestion($DbUserProfile, $questionId);
            if ($success) {
                $_SESSION['votes'][$questionId] = 'like';
                $voter->recordVote($DbUserProfile, $userID, $questionId, 'like');
            }
            break;

        case 'dislike':
            $success = $voter->downVOteQuestion($DbUserProfile, $questionId);
            if ($success) {
                $_SESSION['votes'][$questionId] = 'dislike';
                $voter->recordVote($DbUserProfile, $userID, $questionId, 'dislike');
            }
            break;

        case 'unlike':
            $success = $voter->unupVoteQuestion($DbUserProfile, $questionId);
            if ($success) {
                unset($_SESSION['votes'][$questionId]);
                $voter->recordVote($DbUserProfile, $userID, $questionId, null);
            }
            break;

        case 'undislike':
            $success = $voter->undownVoteQuestion($DbUserProfile, $questionId);
            if ($success) {
                unset($_SESSION['votes'][$questionId]);
               $voter->recordVote($DbUserProfile, $userID, $questionId, null);
            }
            break;

        default:
            echo 'invalid vote type';
            exit;
    }


  echo $success ? 'success' : 'fail';
}



$userName = $person ? $person->name : "User";
$userEmail = $person ? $person->email : "";
$userRole = $person ? $person->role_id : "Unknown";
$userPoints = $person ? $person->points : 0;
$profilePicture = $person ? $person->profile_picture : "../assets/img/bruce-mars.jpg";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>
    Profile
  </title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />

  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />

  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />

  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
  <style>
        .form-container {
            display: none;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .questio-card{
          margin-bottom:20px;
          border: 1px solid #f0f0f0;
          border-radius:8px;
        }
        .question-body{
          padding: 15px;
        }
        .card-text{
          font-size:22px;
          color: black;
        }
        .question-meta{
          padding:10px 15px;
          border-top: 1px solid #f0f0f0;
          border-radius:0 8px;
          display: flex;
          align-items : center;
          font-size : 18px;
          color: #777;
        }
        .meta-item{
          display: flex;
          align-items:center;
          margin-inline-end: 15px;
        }
        .meta-icon{
          margin-inline-start:5px;
          color: rgb(105, 69, 90) ;
          font-size: 18px;
        }
        @media (max-width:576px){
          .question-meta{
            flex-direction: column;
            align-items: flex-start;
          }
          .meta-item{
            margin-inline-end: 0;
            margin-bottom: 5px;
          }
        }
    </style>
    
</head>

<body class="g-sidenav-show bg-gray-100">
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2  bg-white my-2" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand px-4 py-3 m-0" href=" https://demos.creative-tim.com/material-dashboard/pages/dashboard " target="_blank">
        <img src="../assets/img/logo-ct-dark.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
        <span class="ms-1 text-sm text-dark">Menu</span>
      </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link text-dark " href="../../Views/User/user_home.php">
            <i class="material-symbols-rounded opacity-5">Home</i>
            <span class="nav-link-text ms-1">Home</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="../../Views/User/Favorite.php">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Favourite & saved</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="../../Views/User/recommendedTags.php">
            <i class="material-symbols-rounded opacity-5">receipt_long</i>
            <span class="nav-link-text ms-1">Recommended Tags</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="../../Views/User/leaderBoard.php">
            <i class="material-symbols-rounded opacity-5">view_in_ar</i>
            <span class="nav-link-text ms-1">Leaderboard</span>
          </a>
        </li>
        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Account pages</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link active bg-gradient-dark text-white" href="../../Views/User/userprofile.php">
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
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Profile</li>
          </ol>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
            <li class="nav-item px-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0">
                <i class="material-symbols-rounded fixed-plugin-button-nav">settings</i>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  
    <div class="container-fluid px-2 px-md-4">
      <div class="page-header min-height-300 border-radius-xl mt-4"
        style="background-image: url('https://cdn.prod.website-files.com/659f77ad8e06050cc27ed531/65ef63f6bd30ab838939a4ae_Developer%20productivity%20tools%202024.webp');">
        <span class="mask  bg-gradient-dark  opacity-6"></span>
      </div>
      <div class="card card-body mx-2 mx-md-2 mt-n6">
        <?php
          if (isset($_SESSION['statusMessage']) && !empty($_SESSION['statusMessage'])) {
            $alertClass = ($_SESSION['statusType'] === 'success') ? 'alert-success' : 'alert-danger';
            echo '<div id="tempMessage" class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
            echo $_SESSION['statusMessage'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            unset($_SESSION['statusMessage']); 
            unset($_SESSION['statusType']);    
          }
        ?>
        <div class="row justify-content-between gx-4 mb-2">
          <div class="col-auto">
            <div class="avatar avatar-xl position-relative">
            <img src="<?php echo $profilePicture; ?>" alt="profile_image"
            class="w-100 border-radius-lg shadow-sm">
            </div>
          </div>
          <div class="col-auto my-auto">
            <div class="h-100">
               <h5 class="mb-1">
                  Username : <?php echo $person->username; ?>
               </h5>
               <p class="mb-0 font-weight-normal text-sm">
                  Name : <?php echo $userName; ?>
                </p>
               <p class="mb-0 font-weight-normal text-sm">
                  badge : <?php echo $userRole; ?>
               </p>
                <p class="mb-0 font-weight-normal text-sm">
                   points : <?php echo $userPoints; ?>
               </p>
               <p class="mb-0 font-weight-normal text-sm">
                   Email : <?php echo $userEmail; ?>
               </p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 my-sm-auto ms-sm-auto me-sm-0 mx-auto mt-3">
            <div class="nav-wrapper position-relative end-0">
              <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="collapse" href="#settingsMenu" role="button"
                aria-expanded="false" aria-controls="settingsMenu">
                <i class="material-symbols-rounded text-lg position-relative">settings</i>
                <span class="ms-1">Manage User</span>
              </a>
              <ul class="collapse list-unstyled ps-4 mt-1" id="settingsMenu">
                <li>
                  <a href="#" class="nav-link mb-0 px-0 py-1" onclick="showForm('changePassword')" role="button">
                    <i class="material-symbols-rounded text-lg position-relative">lock</i>
                    <span class="ms-1">Change Password</span>
                 </a>
                </li>
                <li>
                  <a href="#" class="nav-link mb-0 px-0 py-1" onclick="showForm('changeUsername')" role="button">
                    <i class="material-symbols-rounded text-lg position-relative">person</i>
                    <span class="ms-1">Change Username</span>
                  </a>
                </li>
                <li>
                  <a href="#" class="nav-link mb-0 px-0 py-1" onclick="showForm('changePhoto')"
                    role="button">
                    <i class="material-symbols-rounded text-lg position-relative">photo_camera</i>
                    <span class="ms-1">Change Photo</span>
                  </a>
                </li>
              </ul>
              <div id="changePassword" class="form-container">
                  <form method="POST" action="">
                    <input type="password" name="old_password" placeholder="Current Password" required>
                    <input type="password" name="new_password" placeholder="New Password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                    <button type="submit" class="btn btn-sm btn-primary">Change Password</button>
                  </form>
              </div>
              <div id="changeUsername" class="form-container">
                <form method="POST" action="">
                  <input type="text" name="new_username" placeholder="New Username" required>
                  <button type="submit" class="btn btn-sm btn-primary">Change Username</button>
                </form>
              </div>
              <div id="changePhoto" class="form-container">
                <form method="POST" action="" enctype="multipart/form-data">
                  <input type="file" name="new_photo" accept="image/*" required> 
                  <button type="submit" class="btn btn-sm btn-primary">Change Photo</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container">
    <?php if (!empty($questionHistory)): ?>
        <?php
        foreach ($questionHistory as $question) {
          /*$isLiked = isset($_SESSION['votes'][$question['question_id']]) && $_SESSION['votes'][$question['question_id']] === 'like';
          $isDisliked = isset($_SESSION['votes'][$question['question_id']]) && $_SESSION['votes'][$question['question_id']] === 'dislike';*/
           $userID = $_SESSION['personID'];
          $questionID = $question['question_id'];
          $voteCheckQuery = "SELECT vote_type FROM question_votes WHERE user_id = ? AND question_id = ?";
          $voteStmt = $DbUserProfile->connection->prepare($voteCheckQuery);
          $voteStmt->bind_param("ii", $userID, $questionID);
          $voteStmt->execute();
          $voteResult = $voteStmt->get_result();
          $voteRow = $voteResult->fetch_assoc();
          $isLiked = ($voteRow && $voteRow['vote_type'] === 'like');
          $isDisliked = ($voteRow && $voteRow['vote_type'] === 'dislike');
        ?>
            <div class="col-12 mt-5 mb-5">
                <div class="card question-card" data-question-id='<?php echo $question['question_id']; ?>'>
                    <div class="card-body question-body">
                        <h6 class="mb-0 "><?php echo $question["username"]?></h6>
                        <span style="color: #9b9a9a;"><?php echo $question["timestamp"]?></span>
                        <p class="fs-5 mb-3 text-dark"><?php echo $question["content"]?></p>
                        <div class="position-absolute bottom-0 end-0 m-3 d-flex gap-2">
                            <button class="btn btn-primary ">
                                <i class="material-symbols-rounded me-1">edit</i> Edit
                            </button>
                        </div>
                        <hr class="dark horizontal">

                        <div class="d-flex">
                        <button class="btn like-button <?php echo $isNewUser ? 'disabled' : ''; ?>"
                          data-liked="<?php echo $isLiked ? 'true' : 'false'; ?>"
                          style="color: <?php echo $isLiked ? 'rgb(214, 20, 133)' : 'rgb(105, 69, 90)'; ?>"
                          <?php echo $isNewUser ? 'disabled' : ''; ?>>
                          <i class="material-symbols-rounded">thumb_up</i>
                          <span class="like-count"><?php echo $question["n_upvotes"]; ?></span>
                        </button>
                        <button class="btn dislike-button <?php echo $isNewUser ? 'disabled' : ''; ?>"
                          data-disliked="<?php echo $isDisliked ? 'true' : 'false'; ?>"
                          style="color: <?php echo $isDisliked ? 'rgb(214, 20, 133)' : 'rgb(105, 69, 90)'; ?>"
                          <?php echo $isNewUser ? 'disabled' : ''; ?>>
                          <i class="material-symbols-rounded">thumb_down</i>
                          <span class="dislike-count"><?php echo $question["n_downvotes"]; ?></span>
                        </button>
                        <button class="btn" onclick="toggleAnswer('answer-form-<?php echo $question['question_id']; ?>')">
                             <i class="material-symbols-rounded me-1">chat_bubble</i>
                             <span class="dislike-count"><?php echo $question["answer_count"]; ?></span>
                        </button>
                      </div>
                      <div id="answer-form-<?php echo $question['question_id']; ?>" style="background-color: rgb(241 239 239); display:none; width:40%; margin-left: 15px; padding: 10px;">
                        <form action="user_home.php" method="POST" style="padding-left: 5px;">
                            <label for="answer-<?php echo $question['question_id']; ?>" style="color: black;font-weight:bold;font-size:large;">Your Answer:</label><br>
                            <textarea id="answer-<?php echo $question['question_id']; ?>" name="answer" rows="5" cols="50" placeholder="Type your Answer..."></textarea><br><br>
                            <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
                            <input type="submit" class="btn btn-primary" name="submit_answer">
                        </form>
                    </div>
            <?php
            if (isset($_SESSION["AnsweredQuestionId"]) && $_SESSION["AnsweredQuestionId"] == $question["question_id"] && isset($_SESSION["Msg"]) && $_SESSION["Msg"] != "") {
                $msg = $_SESSION["Msg"];
                $alertType = ($msg === "Answer content cannot be empty." || strpos($msg, "Error") !== false || strpos($msg, "Invalid") !== false || strpos($msg, "exceeds") !== false) ? "danger" : "success";
            ?>
                <div class="alert alert-<?php echo $alertType; ?> alert-dismissible text-white mt-2" role="alert">
                    <span class="text-sm"><?php echo htmlspecialchars($msg); ?></span>
                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <?php
                unset($_SESSION["Msg"]);
                unset($_SESSION["AnsweredQuestionId"]);
            }
            ?>
            <!-- Display existing answers -->
            <div class="answers mt-2" style="margin-left: 15px; padding: 10px; max-height: 200px; overflow-y: auto; border-top: 1px solid #ddd;">
                <h6>Answers (<?php echo htmlspecialchars($question['answer_count']); ?>):</h6>
                <?php
                $answers = new Answer;
                $questionAnswers = $answers->viewAnswers($question);
                if (empty($questionAnswers)) {
                    echo '<p>No answers yet.</p>';
                } else {
                    foreach ($questionAnswers as $answer) {
                ?>
                        <div class="answer" style="margin-top: 10px; padding: 10px; border-bottom: 1px solid #eee;">
                            <h6><?php echo htmlspecialchars($answer['username']); ?></h6>
                            <span style="color: #9b9a9a; font-size: 0.9em;"><?php echo htmlspecialchars($answer['timestamp']); ?></span>
                            <p style="margin-top: 5px;"><?php echo htmlspecialchars($answer['content']); ?></p>
                        </div>
                <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>
              <?php
            }
          ?>
                    </div>
                </div>
            </div>
    <?php else: ?>
        <p>No questions found for this user.</p>
    <?php endif; ?>
</div>

</div>
  </footer>
  </div>
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

        <div class="mt-3">
          <h6 class="mb-0">Sidenav Type</h6>
          <p class="text-sm">Choose between different sidenav types.</p>
        </div>
        <div class="d-flex">
          <button class="btn bg-gradient-dark px-3 mb-2" data-class="bg-gradient-dark"
            onclick="sidebarType(this)">Dark</button>
          <button class="btn bg-gradient-dark px-3 mb-2 ms-2" data-class="bg-transparent"
            onclick="sidebarType(this)">Transparent</button>
          <button class="btn bg-gradient-dark px-3 mb-2  active ms-2" data-class="bg-white"
            onclick="sidebarType(this)">White</button>
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
        <a class="btn bg-gradient-info w-100" href="https://www.creative-tim.com/product/material-dashboard-pro">Free
          Download</a>
        <a class="btn btn-outline-dark w-100"
          href="https://www.creative-tim.com/learning-lab/bootstrap/overview/material-dashboard">View documentation</a>
        <div class="w-100 text-center">
          <a class="github-button" href="https://github.com/creativetimofficial/material-dashboard"
            data-icon="octicon-star" data-size="large" data-show-count="true"
            aria-label="Star creativetimofficial/material-dashboard on GitHub">Star</a>
          <h6 class="mt-3">Thank you for sharing!</h6>
          <a href="https://twitter.com/intent/tweet?text=Check%20Material%20UI%20Dashboard%20made%20by%20%40CreativeTim%20%23webdesign%20%23dashboard%20%23bootstrap5&amp;url=https%3A%2F%2Fwww.creative-tim.com%2Fproduct%2Fsoft-ui-dashboard"
            class="btn btn-dark mb-0 me-2" target="_blank">
            <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
          </a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.creative-tim.com/product/material-dashboard"
            class="btn btn-dark mb-0 me-2" target="_blank">
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
    document.addEventListener('DOMContentLoaded', function() {
        const tempMessageDiv = document.getElementById('tempMessage');

        if (tempMessageDiv) {

            setTimeout(function() {
                tempMessageDiv.style.display = 'none';
            }, 5000);
        }

        
        <?php
        $statusMessage = "";
        $statusType = "";
        ?>
    });

    function showForm(formId) {

        var forms = document.querySelectorAll('.form-container');
        forms.forEach(function(form) {
            form.style.display = 'none';
        });

        
        var selectedForm = document.getElementById(formId);
        if (selectedForm) {
            selectedForm.style.display = 'block';
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
    const likeButtons = document.querySelectorAll('.like-button');
    const dislikeButtons = document.querySelectorAll('.dislike-button');
    const originalColor = 'rgb(105, 69, 90)'; 
    const likedColor = 'rgb(214, 20, 133)'; 
    const dislikedColor = 'rgb(214, 20, 133)'; 

    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const questionCard = this.closest('.question-card');
            const questionId = questionCard.dataset.questionId;
            const likeCountSpan = this.querySelector('.like-count');
            const isLiked = this.getAttribute('data-liked') === 'true';
            const dislikeButton = questionCard.querySelector('.dislike-button');
            const dislikeCountSpan = dislikeButton ? dislikeButton.nextElementSibling : null;
            const isDisliked = dislikeButton ? dislikeButton.getAttribute('data-disliked') === 'true' : false;
            let voteType = '';

            if (isLiked) {
                voteType = 'unlike';
                this.style.color = originalColor;
                this.setAttribute('data-liked', 'false');
                likeCountSpan.textContent = parseInt(likeCountSpan.textContent) - 1;
            } else {
                voteType = 'like';
                this.style.color = likedColor;
                this.setAttribute('data-liked', 'true');
                likeCountSpan.textContent = parseInt(likeCountSpan.textContent) + 1;
                if (isDisliked && dislikeButton) {
                    dislikeButton.style.color = originalColor;
                    dislikeButton.setAttribute('data-disliked', 'false');
                    const dislikeCount = dislikeButton.querySelector('.dislike-count');
                    dislikeCount.textContent = parseInt(dislikeCount.textContent) - 1;
                }
            }

            if (questionId && voteType) {
                fetch('userprofile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `question_id=${questionId}&vote_type=${voteType}&remove_opposite=${isDisliked ? 1 : 0}`

                })
                .then(data => {
                  if (data === 'same vote') {
                    console.log('You already voted the same way.');
                    return;
                  }
                  if (data !== 'success') {
                    console.error('Failed to process vote on the server.');
                  }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    });

    dislikeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const questionCard = this.closest('.question-card');
            const questionId = questionCard.dataset.questionId;
            const dislikeCountSpan = this.querySelector('.dislike-count');
            const isDisliked = this.getAttribute('data-disliked') === 'true';
            const likeButton = questionCard.querySelector('.like-button');
            const likeCountSpan = likeButton ? likeButton.nextElementSibling : null;
            const isLiked = likeButton ? likeButton.getAttribute('data-liked') === 'true' : false;
            let voteType = '';

            if (isDisliked) {
                voteType = 'undislike';
                this.style.color = originalColor;
                this.setAttribute('data-disliked', 'false');
                dislikeCountSpan.textContent = parseInt(dislikeCountSpan.textContent) - 1;
            } else {
                voteType = 'dislike';
                this.style.color = dislikedColor;
                this.setAttribute('data-disliked', 'true');
                dislikeCountSpan.textContent = parseInt(dislikeCountSpan.textContent) + 1;
                if (isLiked && likeButton) {
                    likeButton.style.color = originalColor;
                    likeButton.setAttribute('data-liked', 'false');
                    const likeCount = likeButton.querySelector('.like-count');
                    likeCount.textContent = parseInt(likeCount.textContent) - 1;
                }
            }

            if (questionId && voteType) {
                fetch('userprofile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `question_id=${questionId}&vote_type=${voteType}&remove_opposite=${isLiked ? 1 : 0}`

                })
                .then(data => {
                  if (data === 'same vote') {
                    console.log('You already voted the same way.');
                    return;
                  }
                  if (data !== 'success') {
                    console.error('Failed to process vote on the server.');
                  }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    });

    
});
</script>
  <script>
    function toggleAnswer(elementId) {
      var element = document.getElementById(elementId);
      if (element.style.display === "none") {
        element.style.display = "block";
      } else {
        element.style.display = "none";
      }
    }
  </script>
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>