<?php
require_once '../../Controllers/DBController.php';
require_once '../../Models/person.php';
require_once '../../Models/user.php';
require_once '../../Models/tag.php';
require_once '../../Models/question.php';
require_once '../../Models/answer.php';

session_start();

if ($_SESSION['personID'] && ($_SESSION['personRole'] == '2')) {
    
}else{
    header("location: /SE_project/Views/Auth/login.php");
}
$user = new User();
$quest = new Question;
$tag = new Tag;
$ans = new Answer;

if (!isset($_SESSION["personID"])) {
    echo "You must be logged in to view this page.";
    exit();
}

$userId = $_SESSION["personID"];
$selectedTag = $_GET['tag'] ?? null;
$tags = $tag->getAllTags(); // Fetch all tags (assuming this method returns available tags)
$questions = $selectedTag ? $tag->searchQuestionsByTag($selectedTag) : [];

// Filter out duplicate tags based on name
$uniqueTags = [];
foreach ($tags as $tag) {
    $tagName = htmlspecialchars($tag['name']);
    if (!in_array($tagName, $uniqueTags)) {
        $uniqueTags[] = $tagName;
    }
}

// Handle adding to favorites
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fav'], $_POST['quest_id'])) {
    $questId = (int)$_POST['quest_id'];
    $quest->addToFav($questId, $userId);
    header("Location: " . $_SERVER['PHP_SELF'] . "?tag=" . urlencode($selectedTag));
    exit();
}

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["answer"], $_POST["question_id"])) {
      $answerContent = trim($_POST["answer"]);
      $questionId = (int)$_POST["question_id"];
      if (empty($answerContent)) {
          $_SESSION["Msg"] = "Answer content cannot be empty.";
          $_SESSION["AnsweredQuestionId"] = $questionId;
      } elseif (strlen($answerContent) > 500) {
          $_SESSION["Msg"] = "Answer content exceeds 500 characters.";
          $_SESSION["AnsweredQuestionId"] = $questionId;
      } else {
          $ans->answerQuestion($_SESSION["personID"], $questionId, $answerContent);
      }
      header("Location: " . $_SERVER['PHP_SELF']);
      exit;
  }
?>



<?php
if (!isset($_SESSION)) {
    session_start();
}

if (isset($_POST["search_submit"]) && !empty($_POST["search_tag"])) {
    $tagName = trim($_POST["search_tag"]);
    $user = new User;
    $searchResult = $tag->searchQuestionsByTag($tagName);
}
?>
<?php
if (!isset($_SESSION)) {
    session_start();
}

if (isset($_POST["search_submit"]) && !empty($_POST["search_tag"])) {
    $tagName = trim($_POST["search_tag"]);
    $searchResult = $tag->searchQuestionsByTag($tagName);
}
?>
<?php 
$userID = $_SESSION["personID"];
$user = new User();

$DbUserProfile = new DBController();
$DbUserProfile->openConnection();
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
    
    if (isset($_POST['question_id']) && isset($_POST['vote_type'])) {
        $questionId = $_POST['question_id'] ?? null;
        $voteType = $_POST['vote_type'] ?? null;
        $removeOpposite = isset($_POST['remove_opposite']) ? (int)$_POST['remove_opposite'] : 0;

        if (!$questionId || !$voteType) {
            echo 'invalid input';
            exit;
        }

        $voter = new Voter(); 
        $success = false;

        $voted = "SELECT vote_type FROM question_votes WHERE user_id = $userID AND question_id = $questionId";

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
    } else {
        
        if (isset($_POST['search_tag'])) {
            $tagName = $_POST['search_tag'];
            
        }

        if (isset($_POST['filter'])) {
            $filterMethod = $_POST['filter'];
           
        }

        if (isset($_POST['sort'])) {
            $sortMethod = $_POST['sort'];
            
        }
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
    <title>Recommended Tags</title>
    <!-- Fonts and Icons -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
    <!-- Sidebar -->
    <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand px-4 py-3 m-0" href="https://demos.creative-tim.com/material-dashboard/pages/dashboard" target="_blank">
                <img src="../assets/img/logo-ct-dark.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
                <span class="ms-1 text-sm text-dark">Menu</span>
            </a>
        </div>
        <hr class="horizontal dark mt-0 mb-2">
        <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="../../Views/User/user_home.php">
                        <i class="material-symbols-rounded opacity-5">Home</i>
                        <span class="nav-link-text ms-1">Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active bg-gradient-dark text-white" href="recommendedTags.php">
                        <i class="material-symbols-rounded opacity-5">receipt_long</i>
                        <span class="nav-link-text ms-1">Recommended Tags</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="../../Views/User/Favorite.php">
                        <i class="material-symbols-rounded opacity-5">favorite</i>
                        <span class="nav-link-text ms-1">Favorites</span>
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

    <!-- Main Content -->
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
                        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Recommended Tags</li>
                    </ol>
                </nav>
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                       
                    </div>
                </div>
            </div>
        </nav>

        <!-- Recommended Tags Section -->
        <div class="card mt-4">
            <div class="card-header pb-0 px-3">
                <h6 class="mb-0">Recommended Tags</h6>
            </div>
            <div class="card-body pt-4 p-3">
                <div class="d-flex flex-wrap gap-2">
                    <?php
                    if ($uniqueTags && !empty($uniqueTags)) {
                        foreach ($uniqueTags as $tagName) {
                            $isActive = $selectedTag === $tagName ? 'bg-primary text-white' : 'bg-light text-dark';
                            ?>
                            <a href="?tag=<?php echo urlencode($tagName); ?>" 
                               class="badge <?php echo $isActive; ?> p-2" 
                               style="text-decoration: none; border-radius: 20px;">
                                <?php echo $tagName; ?>
                            </a>
                            <?php
                        }
                    } else {
                        ?>
                        <p class="text-sm">No tags available.</p>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Questions for Selected Tag -->
        <?php if ($selectedTag): ?>
        <div class="card mt-4">
            <div class="card-header pb-0 px-3">
                <h6 class="mb-0">Questions Tagged: <?php echo htmlspecialchars($selectedTag); ?></h6>
            </div>
            <div class="card-body pt-4 p-3">
                <ul class="list-group">
                    <?php
                    if ($questions && !empty($questions)) {
                        foreach ($questions as $question) {
                            /*$isLiked = isset($_SESSION['votes'][$question['ID']]) && $_SESSION['votes'][$question['ID']] === 'like';
                            $isDisliked = isset($_SESSION['votes'][$question['ID']]) && $_SESSION['votes'][$question['ID']] === 'dislike';*/
                        $userID = $_SESSION['personID'];
                        $questionID = $question['ID'];
                        $voteCheckQuery = "SELECT vote_type FROM question_votes WHERE user_id = ? AND question_id = ?";
                        $voteStmt = $DbUserProfile->connection->prepare($voteCheckQuery);
                        $voteStmt->bind_param("ii", $userID, $questionID);
                        $voteStmt->execute();
                        $voteResult = $voteStmt->get_result();
                        $voteRow = $voteResult->fetch_assoc();
                        $isLiked = ($voteRow && $voteRow['vote_type'] === 'like');
                        $isDisliked = ($voteRow && $voteRow['vote_type'] === 'dislike');
                        ?>
                            <li class="list-group-item border-0 mb-4 p-3 bg-light shadow-sm" >
                                <?php
                                    $userID = $question['user_id'];  
                                    $userQuery = "SELECT profile_picture FROM users WHERE ID = $userID";
                                    $userResult = $DbUserProfile->select($userQuery);
                                    $profilePicture = $userResult ? $userResult[0]['profile_picture'] : 'default-profile.jpg';
                                ?>  
                                <div class="question-card"  data-question-id="<?php echo $question['ID']; ?>">
                                    <div class="rounded-circle overflow-hidden" style="width: 50px; height: 50px; margin-right: 10px;">
                                        <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="User Image" class="w-100 h-100 object-fit-contain">
                                    </div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($question["username"]); ?></h6>
                                    <span style="color: #9b9a9a;"><?php echo htmlspecialchars($question["timestamp"]); ?></span>
                                    <p class="fs-5 mb-3 text-dark"><?php echo htmlspecialchars($question["content"]); ?></p>
                                    <div class="position-absolute bottom-0 end-0 m-3 d-flex gap-2">
                                        <button class="btn btn-primary">
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
                                        <button class="btn" onclick="toggleAnswer('answer-form-<?php echo $question['ID']; ?>')">
                                            <i class="material-symbols-rounded">chat_bubble</i>
                                            <span><?php echo $question["n_answers"]; ?></span>
                                        </button>
                                        <form method="POST" action="#">
                                            <input type="hidden" name="quest_id" value="<?= htmlspecialchars($question['ID']); ?>">
                                            <input type="submit" class="btn btn-sm btn-primary" name="fav" value="Add to Fav ❤" style="border-radius:20px;color:white;">
                                        </form>
                                    </div>
                                </div>
                            </li>
                            <?php
            if (
                isset($_SESSION["addFavMsg"]) &&
                isset($_SESSION["questID"]) &&
                $_SESSION["questID"] == $question["ID"]
            ) {
                $msg = $_SESSION["addFavMsg"];
                unset($_SESSION["addFavMsg"]);
                unset($_SESSION["questID"]);

                $alertType = ($msg == "Question is Added to Favourites Successfully:)") ? "success" : "danger";
            ?>
                <div class="alert alert-<?= $alertType ?> alert-dismissible text-white mt-2" role="alert">
                    <span class="text-sm"><?php echo htmlspecialchars($msg); ?></span>
                    <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
            <?php
            }
            ?>
            <!-- Add answer form and alert here -->
            <div id="answer-form-<?php echo $question['ID']; ?>" style="background-color: rgb(241 239 239); display:none; width:40%; margin-left: 15px; padding: 10px;">
                <form action="#" method="POST" style="padding-left: 5px;">
                    <label for="answer-<?php echo $question['ID']; ?>" style="color: black;font-weight:bold;font-size:large;">Your Answer:</label><br>
                    <textarea id="answer-<?php echo $question['ID']; ?>" name="answer" rows="5" cols="50" placeholder="Type your Answer..."></textarea><br><br>
                    <input type="hidden" name="question_id" value="<?php echo $question['ID']; ?>">
                    <input type="submit" class="btn btn-primary" name="submit_answer">
                </form>
            </div>
            <?php
            if (isset($_SESSION["AnsweredQuestionId"]) && $_SESSION["AnsweredQuestionId"] == $question["ID"] && isset($_SESSION["Msg"]) && $_SESSION["Msg"] != "") {
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
            <div class="answers mt-2" style="width: 98%;margin-left: 15px; padding: 10px; max-height: 200px; overflow-y: auto; border-top: 1px solid #ddd;">
                <h6>Answers (<?php echo htmlspecialchars($question['n_answers']); ?>):</h6>
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
        }
            else{
          ?>
                        <li class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                            <p class="text-sm">No questions found for this tag.</p>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <footer class="footer py-4">
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
    </main>

    <!-- Fixed Plugin (Settings) -->
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
                    <button class="btn bg-gradient-dark px-3 mb-2 active ms-2" data-class="bg-white" onclick="sidebarType(this)">White</button>
                </div>
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
            </div>
        </div>
    </div>

    <!-- Core JS Files -->
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
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Material Dashboard JS -->
    <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
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
      <script>
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
                fetch('recommendedTags.php', {
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
                fetch('recommendedTags.php', {
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
</body>

</html>