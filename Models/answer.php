<?php
class Answer
{
    public $ID;
    public $content;
    public $timestamp;
    public $quest_id;
    public $user_id;
    protected $db;

    public function __construct()
    {
        $this->db = new DBController;
    }

    public function viewAnswers($question)
    {
        if ($this->db->openConnection()) {
            $this->timestamp = $question['timestamp'];
            $query = "SELECT ID FROM questions WHERE timestamp = ?";
            $stmt = $this->db->connection->prepare($query);
            if (!$stmt) {
                error_log("Prepare failed in viewAnswers (quest_id lookup): " . $this->db->connection->error);
                $this->db->closeConnection();
                return [];
            }
            $stmt->bind_param("s", $this->timestamp);
            $stmt->execute();
            $result = $stmt->get_result();
            $questionRow = $result->fetch_assoc();
            $stmt->close();

            if ($questionRow && isset($questionRow['ID'])) {
                $quest_id = $questionRow['ID'];
                $query = "SELECT answers.ID, answers.content, answers.timestamp, answers.quest_id, users.username 
                          FROM answers 
                          INNER JOIN users ON answers.user_id = users.ID 
                          WHERE answers.quest_id = ? 
                          ORDER BY answers.timestamp DESC";
                $stmt = $this->db->connection->prepare($query);
                if (!$stmt) {
                    error_log("Prepare failed in viewAnswers: " . $this->db->connection->error);
                    $this->db->closeConnection();
                    return [];
                }
                $stmt->bind_param("i", $quest_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $answers = [];
                while ($row = $result->fetch_assoc()) {
                    $answers[] = $row;
                }
                $stmt->close();
                $this->db->closeConnection();
                return $answers;
            } else {
                error_log("No quest_id found for timestamp: $this->timestamp");
                $this->db->closeConnection();
                return [];
            }
        }
        return [];
    }


    public function answerQuestion($userID, $questionID, string $answerContent)
    {
        if (!$this->db->openConnection()) {
            $_SESSION["Msg"] = "Database connection failed.";
            error_log("Database connection failed in answerQuestion.");
            return false;
        }

        $userID = (int)$userID;
        $questionID = (int)$questionID;

        $questCheck = $this->db->select("SELECT ID FROM questions WHERE ID = $questionID");
        $userCheck = $this->db->select("SELECT ID FROM users WHERE ID = $userID");

        if (empty($questCheck)) {
            $_SESSION["Msg"] = "Invalid question ID: $questionID does not exist.";
            error_log("Invalid question ID: $questionID");
            $this->db->closeConnection();
            return false;
        }
        if (empty($userCheck)) {
            $_SESSION["Msg"] = "Invalid user ID: $userID does not exist.";
            error_log("Invalid user ID: $userID");
            $this->db->closeConnection();
            return false;
        }

        $query = "INSERT INTO answers (content, timestamp, quest_id, user_id) VALUES (?, NOW(), ?, ?)";
        $stmt = $this->db->connection->prepare($query);
        if (!$stmt) {
            $_SESSION["Msg"] = "Prepare failed: " . $this->db->connection->error;
            error_log("Prepare failed: " . $this->db->connection->error);
            $this->db->closeConnection();
            return false;
        }

        $stmt->bind_param("sii", $answerContent, $questionID, $userID);
        if ($stmt->execute()) {
            $stmt->close();
            $user = new User();
            $user->ID = $userID;
            $user->earnPoints($user,10); 
            $_SESSION["Msg"] = "Answer posted successfully.";
            return true;
        } else {
            $_SESSION["Msg"] = "Error posting answer: " . $stmt->error;
            error_log("Answer insert failed: " . $stmt->error);
            $stmt->close();
            $this->db->closeConnection();
            return false;
        }
    }
}
?>