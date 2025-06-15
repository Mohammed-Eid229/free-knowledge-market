<?php

require_once __DIR__.'../../Controllers/DBController.php';
class Question
{
    public $ID;
    public $content;
    public $timeStamp;
    public $status;
    public $n_upvotes;
    public $n_downvotes;
    public $user_id;

    protected $db;

    public function __construct()
    {
         $this->db = new DBController;
    }

    public function viewQuestions()
    {
        if ($this->db->openConnection()) {
            $query = "SELECT questions.user_id, users.username,questions.ID,questions.timestamp,questions.content,questions.n_upvotes,questions.n_downvotes, COUNT(answers.ID) AS n_answers 
                        FROM questions INNER JOIN users ON questions.user_id = users.ID 
                        LEFT JOIN answers ON questions.ID = answers.quest_id 
                        WHERE questions.status = 'Approved'
                        GROUP BY users.username,questions.timestamp,questions.content,questions.n_upvotes,questions.n_downvotes
                        ORDER BY questions.timestamp DESC;";
                        
            return $this->db->select($query);
        }
    }


     public function sortQuestions($method)
    {
        $query = "" ;
        if ($this->db->openConnection()){

            if($method == "oldest"){
                $query = "SELECT users.username, questions.ID, questions.timestamp, questions.content, questions.n_upvotes, questions.n_downvotes, COUNT(answers.ID) AS n_answers FROM questions 
                INNER JOIN users ON questions.user_id = users.ID 
                LEFT JOIN answers ON questions.ID = answers.quest_id 
                WHERE questions.status = 'Approved'
                GROUP BY users.username, questions.timestamp, questions.content, questions.n_upvotes, questions.n_downvotes 
                ORDER BY questions.timestamp ASC;";
            }

            else if($method == "newest") {
                 $query = "SELECT users.username, questions.ID, questions.timestamp, questions.content, questions.n_upvotes, questions.n_downvotes, COUNT(answers.ID) AS n_answers FROM questions 
                 INNER JOIN users ON questions.user_id = users.ID 
                 LEFT JOIN answers ON questions.ID = answers.quest_id 
                 WHERE questions.status = 'Approved'
                 GROUP BY users.username, questions.timestamp, questions.content, questions.n_upvotes, questions.n_downvotes 
                 ORDER BY questions.timestamp DESC;";
            }   

            else if($method == "no_upvotes") {
                $query = "SELECT users.username, questions.ID, questions.timestamp, questions.content, questions.n_upvotes, questions.n_downvotes, COUNT(answers.ID) AS n_answers FROM questions 
                INNER JOIN users ON questions.user_id = users.ID 
                LEFT JOIN answers ON questions.ID = answers.quest_id 
				WHERE questions.status = 'Approved'
                GROUP BY users.username, questions.timestamp, questions.content, questions.n_upvotes, questions.n_downvotes 
                ORDER BY questions.n_upvotes DESC;";
            }

            else if($method == "no_downvotes"){
                $query = "SELECT users.username, questions.ID, questions.timestamp, questions.content, questions.n_upvotes, questions.n_downvotes, COUNT(answers.ID) AS n_answers FROM questions 
                INNER JOIN users ON questions.user_id = users.ID 
                LEFT JOIN answers ON questions.ID = answers.quest_id 
                WHERE questions.status = 'Approved'
                GROUP BY users.username, questions.timestamp, questions.content, questions.n_upvotes, questions.n_downvotes 
                ORDER BY questions.n_downvotes DESC;";
            }
        $result = $this->db->select($query);
        return $result;
        }
    }


    public function filterQuestions($method)
    {
        $query = "";
        if ($this->db->openConnection()) {
            if($method == "Answered"){
               $query = "SELECT q.*,users.username,COUNT(answers.ID) AS n_answers
               FROM questions q
               INNER JOIN answers
               ON q.ID = answers.quest_id
               INNER JOIN users
               ON q.user_id = users.ID
               WHERE q.status = 'Approved'
               GROUP BY q.ID
               ORDER BY q.timestamp DESC;";
            }
            else if($method == "Unanswered") {
               $query = "SELECT q.*, users.username, COUNT(answers.ID) AS n_answers
               FROM questions q
               LEFT JOIN answers ON q.ID = answers.quest_id
               INNER JOIN users ON q.user_id = users.ID
               WHERE answers.ID IS NULL AND q.status = 'Approved'
               GROUP BY q.ID
               ORDER BY q.timestamp DESC;";
            }
            else{ 
                $query = "SELECT users.username, questions.timestamp, questions.content, questions.n_upvotes, questions.n_downvotes, COUNT(answers.ID) AS n_answers FROM questions 
                INNER JOIN users ON questions.user_id = users.ID 
                LEFT JOIN answers ON questions.ID = answers.quest_id 
                GROUP BY users.username, questions.timestamp, questions.content, questions.n_upvotes, questions.n_downvotes 
                ORDER BY questions.timestamp DESC;";
            }
            $result = $this->db->select($query);
            return $result;
        }
    }


    public function viewFavoriteQuestions($userId)
    {
        if ($this->db->openConnection()) {
            $userId = (int)$userId;
            $query = "SELECT users.ID AS user_id, questions.ID AS quest_id, username, content 
                    FROM users 
                    JOIN questions ON users.ID = questions.user_id 
                    JOIN favquestions ON questions.ID = favquestions.quest_id
                    WHERE favquestions.user_id= $userId ";
            $result = $this->db->select($query);
            $this->db->closeConnection();
            return $result;
        }
        return false;
    }

    public function deleteFavoriteQuestions($userId, $questId)
    {
        if ($this->db->openConnection()) {
            $userId = (int)$userId;
            $questId = (int)$questId;
            $query = "DELETE FROM favquestions WHERE quest_id = $questId";
            $result = $this->db->delete($query);
            $this->db->closeConnection();
            return $result;
        }
        return false;
    }


    public function addToFav($questID, $userID)
    {
        if ($this->db->openConnection()) {
            $query1 = "SELECT * FROM favquestions WHERE user_id = '$userID' AND quest_id = '$questID'";
            $result1 = $this->db->select($query1);

            if (!empty($result1)) {
                $_SESSION["addFavMsg"] = "Question has been already Added!";
                $_SESSION["questID"] = $questID;
                return;
            }

            $query = "INSERT INTO favquestions (user_id, quest_id) VALUES ('$userID', '$questID')";
            $result = $this->db->insert($query);

            $_SESSION["questID"] = $questID;

            if (!$result) {
                $_SESSION["addFavMsg"] = "Question is Added to Favourites Successfully:)";
            } else {
                $_SESSION["addFavMsg"] = "Error Adding Question:(";
            }
        }
    }


    public function viewQuestionHistory(DBController $dbController, int $userId)
    {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $query = "SELECT
                u.username as username,
                q.ID AS question_id,
                q.content,
                q.timestamp ,
                q.n_upvotes,
                q.n_downvotes,
                COUNT(a.quest_id) AS answer_count
            FROM
                questions q
            INNER JOIN users u ON q.user_id = u.ID
            LEFT JOIN
                answers a ON q.ID = a.quest_id
            WHERE
                q.user_ID = " . $userId . "
            GROUP BY
                q.ID
            ORDER BY
                q.timestamp DESC";

            $stmt = $this->db->connection->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();

            $questions = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {

                    $row['user_vote'] = 'none'; 
                    $questions[] = $row;
                }
            }
            return $questions;
        }
    }


    
}
?>