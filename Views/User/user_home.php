<?php

session_start();

if ($_SESSION['personID'] && ($_SESSION['personRole'] == '2')) {
    
}else{
    header("location: /SE_project/Views/Auth/login.php");
}

require_once __DIR__ . '/../../Controllers/AuthController.php';
require_once __DIR__ . '/../../Controllers/DBController.php';
require_once __DIR__ . '/../../Models/editor.php';
require_once __DIR__ . '/../../Models/moderator.php';
require_once __DIR__ . '/../../Models/person.php';
require_once __DIR__ . '/../../Models/user.php';
require_once __DIR__ . '/../../Models/question.php';
require_once __DIR__ . '/../../Models/Answer.php';

ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp2/htdocs/SEproject/php_errors.log');
ini_set('display_errors', 1); 
error_reporting(E_ALL);

error_log("Session at start: " . print_r($_SESSION, true));

if (isset($_POST["post"]) && isset($_POST["submit"])) {
    $questContent = $_POST["post"];
    $editor = new Editor;
    $editor->postQuestions($_SESSION["personID"], $_SESSION["userBadge"], $questContent);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST["edit"]) && isset($_POST["edit_submit"])) {

    error_log("Edit form submitted: " . print_r($_POST, true));
    
    $questContent = trim($_POST["edit"]);
    $questID = isset($_POST["question_id"]) ? (int)$_POST["question_id"] : 0;
    $userID = isset($_SESSION["personID"]) ? (int)$_SESSION["personID"] : 0;
    $userBadge = isset($_SESSION["userBadge"]) ? $_SESSION["userBadge"] : '';

    error_log("Edit question: questID=$questID, userID=$userID, badge=$userBadge, content=$questContent");

    if (empty($questContent)) {
        $_SESSION["editMsg"] = "Question content cannot be empty.";
        $_SESSION["questID"] = $questID;
        error_log("Empty question content for questID=$questID");
    } elseif ($questID <= 0) {
        $_SESSION["editMsg"] = "Invalid question ID.";
        $_SESSION["questID"] = $questID;
        error_log("Invalid question ID: $questID");
    } elseif (empty($userID) || empty($userBadge)) {
        $_SESSION["editMsg"] = "User session invalid.";
        $_SESSION["questID"] = $questID;
        error_log("Invalid session: userID=$userID, badge=$userBadge");
    } else {

        $db = new DBController();
        if ($db->openConnection()) {
            $query = "SELECT user_id FROM questions WHERE ID = " . (int)$questID;
            $result = $db->select($query);
            if (empty($result)) {
                $_SESSION["editMsg"] = "Question ID $questID does not exist.";
                $_SESSION["questID"] = $questID;
                error_log("Question ID $questID not found");
            } else {

                if ($userBadge === "Moderator") {
                    $moderator = new Moderator;
                    $moderator->editQuestion($userID, $userBadge, $questID, $questContent);
                } elseif ($userBadge === "Editor") {
                    $editor = new Editor;
                    $editor->editQuestion($userID, $userBadge, $questID, $questContent);
                } else {
                    $_SESSION["editMsg"] = "You don't have the authority to edit questions.";
                    $_SESSION["questID"] = $questID;
                    error_log("Unauthorized edit attempt by userID=$userID, badge=$userBadge");
                }

                $query = "SELECT content FROM questions WHERE ID = " . (int)$questID;
                $result = $db->select($query);
                if ($result) {
                    error_log("Question ID $questID new content: " . $result[0]["content"]);
                } else {
                    error_log("Failed to verify question ID $questID content");
                }
            }
            $db->closeConnection();
        } else {
            $_SESSION["editMsg"] = "Database connection failed.";
            error_log("Database connection failed in edit question");
        }
    }

    error_log("Session before redirect: " . print_r($_SESSION, true));
    

    session_write_close();
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$questions = new Question;
$allQuestions = $questions->viewQuestions();

$user = new User;
$quest = new Question;
$ans = new Answer;
$tag = new Tag;
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
if (!isset($_SESSION["personID"])) {
    session_start();
}
$questFav = new Question;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["quest_id"], $_SESSION["personID"])) {
    $questId = $_POST["quest_id"];
    $userId = $_SESSION["personID"];
    $questFav->addToFav($questId, $userId);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<?php
if (!isset($_SESSION)) {
    session_start();
}

$filterMethod = "";
if (isset($_POST["filter"]) && isset($_POST["apply"])) {
    $filterMethod = $_POST['filter'];
    $filterResult = $quest->filterQuestions($filterMethod);
}
?>

<?php
if (!isset($_SESSION)) {
    session_start();
}

$sortMethod = "";
if (isset($_POST["sort"]) && isset($_POST['apply'])) {
    $sortMethod = $_POST["sort"];
    $sortResult = $quest->sortQuestions($sortMethod);
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
    <title>Home</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
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
                    <a class="nav-link active bg-gradient-dark text-white" href="../../Views/User/user_home.php">
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
                    <a class="nav-link text-dark" href="../../Views/User/leaderBoard.php">
                        <i class="material-symbols-rounded opacity-5">table_view</i>
                        <span class="nav-link-text ms-1">Leaderboard</span>
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Account pages</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="../../Views/User/userprofile.php">
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
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
                        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Home</li>
                    </ol>
                </nav>
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                        <form method="POST" action="../User/user_home.php">
                            <div class="input-group input-group-outline">
                                <label class="form-label">search here...( #tag's name )</label>
                                <input type="text" class="form-control" name="search_tag">
                                <input class="btn btn-primary btn-sm mb-0 me-3" type="submit" name="search_submit" value="Search">
                            </div>
                        </form>
                    </div>
                    <ul class="navbar-nav d-flex align-items-center justify-content-end">
                        <li class="nav-item d-flex align-items-center">
                            <button class="btn btn-outline-primary btn-sm mb-0 me-3" onclick="document.getElementById('sortModal').style.display='block'">Sort</button>
                            <div id="sortModal" style="background-color:#c7c7c7; display:none; position:absolute; padding:20px; border:1px solid #e91e63; z-index:10; top:60px; right:250px; border-radius:10px">
                                <?php
                                $selectedSort = $_POST['sort'] ?? 'newest';
                                ?>
                                <form action="../User/user_home.php" method="POST">
                                    <p style="color: black; text-align: center;">Select Sorting type:</p>
                                    <div class="line" style=" width: 70%; height: 1px; background-color: #e91e63; margin: 10px auto;"></div>
                                    <label>
                                        <input type="radio" name="sort" value="newest" <?php if ($selectedSort == 'newest') echo 'checked'; ?>>
                                        <span style="color:black">by Newest</span>
                                    </label>
                                    <hr style="border: 0; height: 1px; background: #888;">
                                    <label>
                                        <input type="radio" name="sort" value="oldest" <?php if ($selectedSort == 'oldest') echo 'checked'; ?>>
                                        <span style="color:black">by Oldest</span>
                                    </label>
                                    <hr style="border: 0; height: 1px; background: #888;">
                                    <label>
                                        <input type="radio" name="sort" value="no_upvotes" <?php if ($selectedSort == 'no_upvotes') echo 'checked'; ?>>
                                        <span style="color:black">by Number of Upvotes</span>
                                    </label>
                                    <hr style="border: 0; height: 1px; background: #888;">
                                    <label>
                                        <input type="radio" name="sort" value="no_downvotes" <?php if ($selectedSort == 'no_downvotes') echo 'checked'; ?>>
                                        <span style="color:black">by Number of Downvotes</span>
                                    </label>
                                    <br><br>
                                    <input type="submit" name="apply" value="Apply" class="btn btn-primary btn-sm mb-0 me-3" style="border: solid 0.5px #888; border-radius: 5px;">
                                    <button type="button" class="btn btn-outline-primary btn-sm mb-0 me-3" style="background-color: #EEE; border-radius: 5px;" onclick="document.getElementById('sortModal').style.display='none'">Cancel</button>
                                </form>
                            </div>
                        </li>
                        <li class="mt-1">
                            <button class="btn btn-primary btn-sm mb-0 me-3" onclick="document.getElementById('filterModal').style.display='block'">Filter</button>
                            <div id="filterModal" style="background-color:#c7c7c7; display:none; position:absolute; padding:20px; border:1px solid ; z-index:10; top:60px; right:10px; border-radius:10px">
                                <?php
                                $selectedFilter = $_POST['filter'] ?? '';
                                ?>
                                <form action="../User/user_home.php" method="POST">
                                    <p style="color: black; text-align: center;">Select question type:</p>
                                    <div class="line" style=" width: 70%; height: 1px; background-color: #e91e63; margin: 10px auto;"></div>
                                    <label>
                                        <input type="radio" name="filter" value="Answered" <?php if ($selectedFilter == 'Answered') echo 'checked'; ?>>
                                        <span style="color:black">Answered</span>
                                    </label>
                                    <hr style="border: 0; height: 1px; background: #888;">
                                    <label>
                                        <input type="radio" name="filter" value="Unanswered" <?php if ($selectedFilter == 'Unanswered') echo 'checked'; ?>>
                                        <span style="color:black">Unanswered</span>
                                    </label>
                                    <br><br>
                                    <input type="submit" name="apply" value="Apply" class="btn btn-primary btn-sm mb-0 me-3" style="border: solid 0.5px #e91e63; border-radius: 5px;">
                                    <button type="button" class="btn btn-outline-primary btn-sm mb-0 me-3" style="background-color: #EEE; border-radius: 5px;" onclick="document.getElementById('filterModal').style.display='none'">Cancel</button>
                                </form>
                            </div>
                        </li>
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
                <div class="ms-3">
                    <h3 class="mb-0 h4 font-weight-bolder">Home</h3>
                    <p class="mb-4">Welcome to Our Community!</p>
                </div>
                <div class="post">
                    <button class="btn btn-primary" onclick="toggleElement()">
                        <i class="material-symbols-rounded me-1">send</i> Post
                    </button>
                    <?php
                    if (isset($_SESSION["Msg"]) && $_SESSION["Msg"] != "") {
                        $msg = $_SESSION["Msg"];
                        unset($_SESSION["Msg"]);
                        $alertType = ($msg == "Question posted successfully:)"|| $msg == "Answer posted successfully.") ? "success" : "danger";
                    ?>
                        <div class="alert alert-<?php echo $alertType; ?> alert-dismissible text-white" role="alert">
                            <span class="text-sm"><?php echo htmlspecialchars($msg); ?></span>
                            <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    <?php
                    }
                    if (isset($_SESSION["editMsg"]) && $_SESSION["editMsg"] != "") {
                        $msg = $_SESSION["editMsg"];
                        $alertType = ($msg == "Question has been Edited successfully:)") ? "success" : "danger";
                    ?>
                        <div class="alert alert-<?php echo $alertType; ?> alert-dismissible text-white" role="alert">
                            <span class="text-sm"><?php echo htmlspecialchars($msg); ?></span>
                            <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    <?php
                        unset($_SESSION["editMsg"]);
                        unset($_SESSION["questID"]);
                    }
                    ?>
                    <div id="post-form" style="background-color: rgb(241 239 239);display:none;">
                        <form action="user_home.php" method="POST" style="padding-left: 5px;">
                            <label for="post" style="color: black;font-weight:bold;font-size:large;">Your Question:</label><br>
                            <textarea id="post" name="post" rows="5" cols="70" placeholder="Type your Question..." style="border-radius: 10px;"></textarea><br><br>
                            <input type="submit" class="btn btn-primary" name="submit">
                        </form>
                    </div>
                </div>
                <div class="questions">
                    <?php
                    if (!empty($filterResult)) {
                        $allQuestions = $filterResult;
                    }
                    if (!empty($sortResult)) {
                        $allQuestions = $sortResult;
                    }
                    if (!empty($searchResult)) {
                        $allQuestions = $searchResult;
                    }
                    foreach ($allQuestions as $question) {
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
                        <div class="col-12 mt-5 mb-5">
                            <div class="card question-card" data-question-id="<?php echo $question['ID']; ?>">
                                <div class="card-body">
                                    <?php
                                        if (isset($question['user_id'])) {
                                            $userID = $question['user_id'];  
                                        } else {
                                            $userID = isset($_SESSION['personID']) ? intval($_SESSION['personID']) : 0;
                                        }
                                        $userQuery = "SELECT profile_picture FROM users WHERE ID = $userID";
                                        $userResult = $DbUserProfile->select($userQuery);
                                        $profilePicture = $userResult ? $userResult[0]['profile_picture'] : 'default-profile.jpg';
                                    ?>                                    
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle overflow-hidden" style="width: 50px; height: 50px; margin-right: 10px;">
                                                 <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="User Image" class="w-100 h-100 object-fit-contain">
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($question["username"]); ?></h6>
                                                <span style="color: #9b9a9a;"><?php echo htmlspecialchars($question["timestamp"]); ?></span>
                                            </div>
                                            </div>
                                                <button class="btn btn-primary" style="position: absolute; top: 10px; right: 10px;" onclick="toggleAnswer('edit-form-<?php echo $question['ID']; ?>')">
                                                    <i class="material-symbols-rounded me-1">edit</i> Edit
                                                </button>
                                            </div>                       
                                    <p class="fs-5 mb-3 text-dark mt-2"><?php echo htmlspecialchars($question["content"]); ?></p>
                                    <hr class="dark horizontal">
                                    <div class="d-flex gap-3 align-items-center">
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
                                            <i class="material-symbols-rounded me-1">chat_bubble</i>
                                        </button>
                                        <span style="margin-left: -33px; position: relative; top: -5px;"><?php echo htmlspecialchars($question["n_answers"]); ?></span>
                                        <form method="POST" action="#">
                                            <input type="hidden" name="quest_id" value="<?php echo htmlspecialchars($question['ID']); ?>">
                                            <input type="submit" class="btn btn-primary" name="fav" value="Add to Fav ♥" style="border-radius: 20px; color: #FFF;">
                                        </form>
                                    </div>
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
                                        <div class="alert alert-<?php echo $alertType; ?> alert-dismissible text-white mt-2" role="alert">
                                            <span class="text-sm"><?php echo htmlspecialchars($msg); ?></span>
                                            <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">× </span>
                                            </button>
                                        </div>
                                    <?php
                                    }
                                    ?>

                                    <div id="edit-form-<?php echo $question['ID']; ?>" style="background-color: rgb(241 239 239); display:none; width:40%; margin-left: 15px; padding: 10px;">
                                        <form action="user_home.php" method="POST" style="padding-left: 5px;">
                                            <label for="edit-<?php echo $question['ID']; ?>" style="color: black;font-weight:bold;font-size:large;">Your Question:</label><br>
                                            <textarea id="edit-<?php echo $question['ID']; ?>" name="edit" rows="5" cols="50" placeholder="Type your Question..."><?php echo htmlspecialchars($question['content']); ?></textarea><br><br>
                                            <input type="hidden" name="question_id" value="<?php echo $question['ID']; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $question['user_id']; ?>">
                                            <input type="submit" class="btn btn-primary" name="edit_submit">
                                        </form>
                                    </div>

                                    <div id="answer-form-<?php echo $question['ID']; ?>" style="background-color: rgb(241 239 239); display:none; width:40%; margin-left: 15px; padding: 10px;">
                                        <form action="user_home.php" method="POST" style="padding-left: 5px;">
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
                                                <span aria-hidden="true"></span>
                                            </button>
                                        </div>
                                        <?php
                                        unset($_SESSION["Msg"]);
                                        unset($_SESSION["AnsweredQuestionId"]);
                                    }
                                    ?>
                                    <div class="answers mt-2" style="margin-left: 15px; padding: 10px; max-height: 200px; overflow-y: auto; border-top: 1px solid #ddd;">
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
                    ?>
            <footer class="footer py-4">
                <div class="container-fluid">
                    <div class="row align-items-center justify-content-lg-between">
                        <div class="col-lg-6 mb-lg-0 mb-4">
                            <div class="copyright text-center text-sm text-muted text-lg-start">
                                © <script>document.write(new Date().getFullYear())</script>,
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
                    <button class="btn bg-gradient-dark px-3 mb-2 active ms-2" data-class="bg-white" onclick="sidebarType(this)">White</button>
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
                    <a href="https://twitter.com/intent/tweet?text=Check%20Material%20UI%20Dashboard%20made%20by%20%40CreativeTim%20%23webdesign%20%23dashboard%20%23bootstrap5&url=https%3A%2F%2Fwww.creative-tim.com%2Fproduct%2Fsoft-ui-dashboard" class="btn btn-dark mb-0 me-2" target="_blank">
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
    <script src="../assets/js/plugins/chartjs.min.js"></script>
    <script>
        var ctx = document.getElementById("chart-bars").getContext("2d");
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: ["M", "T", "W", "T", "F", "S", "S"],
                datasets: [{
                    label: "Views",
                    tension: 0.4,
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                    backgroundColor: "#43A047",
                    data: [50, 45, 22, 28, 50, 60, 76],
                    barThickness: 'flex'
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: '#e5e5e5'
                        },
                        ticks: {
                            suggestedMin: 0,
                            suggestedMax: 500,
                            beginAtZero: true,
                            padding: 10,
                            font: {
                                size: 14,
                                lineHeight: 2
                            },
                            color: "#737373"
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#737373',
                            padding: 10,
                            font: {
                                size: 14,
                                lineHeight: 2
                            }
                        }
                    }
                }
            }
        });

        var ctx2 = document.getElementById("chart-line").getContext("2d");
        new Chart(ctx2, {
            type: "line",
            data: {
                labels: ["J", "F", "M", "A", "M", "J", "J", "A", "S", "O", "N", "D"],
                datasets: [{
                    label: "Sales",
                    tension: 0,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: "#43A047",
                    pointBorderColor: "transparent",
                    borderColor: "#43A047",
                    backgroundColor: "transparent",
                    fill: true,
                    data: [120, 230, 130, 440, 250, 360, 270, 180, 90, 300, 310, 220],
                    maxBarThickness: 6
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                const fullMonths = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                                return fullMonths[context[0].dataIndex];
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [4, 4],
                            color: '#e5e5e5'
                        },
                        ticks: {
                            display: true,
                            color: '#737373',
                            padding: 10,
                            font: {
                                size: 12,
                                lineHeight: 2
                            }
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#737373',
                            padding: 10,
                            font: {
                                size: 12,
                                lineHeight: 2
                            }
                        }
                    }
                }
            }
        });

        var ctx3 = document.getElementById("chart-line-tasks").getContext("2d");
        new Chart(ctx3, {
            type: "line",
            data: {
                labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                datasets: [{
                    label: "Tasks",
                    tension: 0,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: "#43A047",
                    pointBorderColor: "transparent",
                    borderColor: "#43A047",
                    backgroundColor: "transparent",
                    fill: true,
                    data: [50, 40, 300, 220, 500, 250, 400, 230, 500],
                    maxBarThickness: 6
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [4, 4],
                            color: '#e5e5e5'
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#737373',
                            font: {
                                size: 14,
                                lineHeight: 2
                            }
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [4, 4]
                        },
                        ticks: {
                            display: true,
                            color: '#737373',
                            padding: 10,
                            font: {
                                size: 14,
                                lineHeight: 2
                            }
                        }
                    }
                }
            }
        });
    </script>
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
    <script>
        function toggleElement() {
            var element = document.getElementById("post-form");
            element.style.display = (element.style.display === "none") ? "block" : "none";
        }

        function toggleAnswer(elementId) {
            var element = document.getElementById(elementId);
            element.style.display = (element.style.display === "none") ? "block" : "none";
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
                fetch('user_home.php', {
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
                fetch('user_home.php', {
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