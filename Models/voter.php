<?php

require_once __DIR__.'../../Controllers/DBController.php';
class Voter extends User
{

    
    public function upVoteQuestion(DBController $dbController, int $questionId): bool
    {
        error_log("Voter->upVoteQuestion() called for question ID: " . $questionId);
        $query = "
            UPDATE questions
            SET n_upvotes = n_upvotes + 1
            WHERE ID = ?
        ";
        $userId = $_SESSION['personID'];
        $voteQuery = "INSERT INTO question_votes (question_id, user_id, vote_type)
                    VALUES (?, ?, 'like')
                    ON DUPLICATE KEY UPDATE vote_type = 'like'";
        $voteStmt = $dbController->connection->prepare($voteQuery);
        $voteStmt->bind_param("ii", $questionId, $userId);
        $voteStmt->execute();

        error_log("Voter->upVoteQuestion() - SQL: " . $query . ", questionId: " . $questionId);
        $stmt = $dbController->connection->prepare($query);
        $stmt->bind_param("i", $questionId);
        $result = $stmt->execute();
        if ($result) {
            $getOwnerQuery = "SELECT user_id FROM questions WHERE ID = ?";
            $stmt2 = $dbController->connection->prepare($getOwnerQuery);
            $stmt2->bind_param("i", $questionId);
            $stmt2->execute();
            $ownerResult = $stmt2->get_result();
            if ($ownerResult && $ownerResult->num_rows > 0) {
                $row = $ownerResult->fetch_assoc();
                $ownerId = $row['user_id'];
                $getUserQuery = "SELECT * FROM users WHERE ID = ?";
                $stmt3 = $dbController->connection->prepare($getUserQuery);
                $stmt3->bind_param("i", $ownerId);
                $stmt3->execute();
                $userResult = $stmt3->get_result();

                if ($userResult && $userResult->num_rows > 0) {
                    $userRow = $userResult->fetch_assoc();
                    $user = new User();
                    $user->ID = $userRow['ID'];
                    $user->badge = $userRow['badge'];
                    $user->db = $dbController; 
                    $user->earnPoints($user , 1); 
                }
            }
             return true;
            error_log("Voter->upVoteQuestion() - Success for question ID: " . $questionId);
            return true;
        } else {
            error_log("Voter->upVoteQuestion() - Failed for question ID: " . $questionId . ", Error: " . $dbController->connection->error);
            return false;
        }
        
    }

    public function downVOteQuestion(DBController $dbController, int $questionId): bool
    {
        error_log("Voter->downVOteQuestion() called for question ID: " . $questionId);
        $query = "
            UPDATE questions
            SET n_downvotes = n_downvotes + 1
            WHERE ID = ?
        ";
        $userId = $_SESSION['personID'];
        $voteQuery = "INSERT INTO question_votes (question_id, user_id, vote_type)
                    VALUES (?, ?, 'dislike')
                    ON DUPLICATE KEY UPDATE vote_type = 'dislike'";
        $voteStmt = $dbController->connection->prepare($voteQuery);
        $voteStmt->bind_param("ii", $questionId, $userId);
        $voteStmt->execute();

        error_log("Voter->downVOteQuestion() - SQL: " . $query . ", questionId: " . $questionId);
        $stmt = $dbController->connection->prepare($query);
        $stmt->bind_param("i", $questionId);
        $result = $stmt->execute();
        if ($result) {
            error_log("Voter->downVOteQuestion() - Success for question ID: " . $questionId);
            return true;
        } else {
            error_log("Voter->downVOteQuestion() - Failed for question ID: " . $questionId . ", Error: " . $dbController->connection->error);
            return false;
        }
    }

    public function unupVoteQuestion(DBController $dbController, int $questionId): bool
    {
        error_log("Voter->unupVoteQuestion() called for question ID: " . $questionId);
        $query = "
            UPDATE questions
            SET n_upvotes = n_upvotes - 1
            WHERE ID = ?
        ";
        error_log("Voter->unupVoteQuestion() - SQL: " . $query . ", questionId: " . $questionId);
        $stmt = $dbController->connection->prepare($query);
        $stmt->bind_param("i", $questionId);
        $result = $stmt->execute();
        if ($result) {
            error_log("Voter->unupVoteQuestion() - Success for question ID: " . $questionId);
            return true;
        } else {
            error_log("Voter->unupVoteQuestion() - Failed for question ID: " . $questionId . ", Error: " . $dbController->connection->error);
            return false;
        }
    }

    public function undownVoteQuestion(DBController $dbController, int $questionId): bool
    {
        error_log("Voter->undownVoteQuestion() called for question ID: " . $questionId);
        $query = "
            UPDATE questions
            SET n_downvotes = n_downvotes - 1
            WHERE ID = ?
        ";
        error_log("Voter->undownVoteQuestion() - SQL: " . $query . ", questionId: " . $questionId);
        $stmt = $dbController->connection->prepare($query);
        $stmt->bind_param("i", $questionId);
        $result = $stmt->execute();
        if ($result) {
            error_log("Voter->undownVoteQuestion() - Success for question ID: " . $questionId);
            return true;
        } else {
            error_log("Voter->undownVoteQuestion() - Failed for question ID: " . $questionId . ", Error: " . $dbController->connection->error);
            return false;
        }
    }

    public function recordVote(DBController $dbController, int $userId, int $questionId, string $voteType = null): bool
    {   
       

        $allowedVotes = ['like', 'dislike'];
        if($voteType !== null && !in_array($voteType, $allowedVotes)){
            error_log("Invalid vote_typr passed to redordVote: ".$voteType);
            return false;
        }
        if ($voteType === null) {
            $query = "DELETE FROM question_votes WHERE user_id = ? AND question_id = ?";
            $stmt = $dbController->connection->prepare($query);
            $stmt->bind_param("ii", $userId, $questionId);
        } else {
 
            $query = "
                INSERT INTO question_votes (user_id, question_id, vote_type)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE vote_type = ?
            ";
            $stmt = $dbController->connection->prepare($query);
            $stmt->bind_param("iiss", $userId, $questionId, $voteType, $voteType);
        }
        $result = $stmt->execute();
        if ($result) {
            error_log("Voter->recordVote() - Success: user_id=" . $userId . ", question_id=" . $questionId . ", vote_type=" . $voteType);
            return true;
        } else {
            error_log("Voter->recordVote() - Failed: " . $dbController->connection->error);
            return false;
        }
    }
}
?>