<?php
require_once __DIR__.'../voter.php';
require_once __DIR__.'../tag.php';
require_once __DIR__.'../../Controllers/DBController.php';
class Editor extends Voter
{
    protected $db;


    public function __construct()
    {
        $this->db = new DBController;
    }


    public function postQuestions($userID, $userBadge,string $questionText) {
        if ($userBadge == "Editor" || $userBadge == "Moderator") {
            if (!$this->db->openConnection()) {
                die("Connection failed.");
            }
            if($questionText =="" || $questionText == " "){
                $_SESSION["Msg"]="Question Cannot be empty!";
                return;
            }
    
            $query1 = "INSERT INTO questions (ID, content, timestamp, status, n_upvotes, n_downvotes, user_id)
                        VALUES (NULL, '$questionText', NOW() , DEFAULT, DEFAULT, DEFAULT, $userID)";
    
            $result1 = $this->db->insert($query1);
            $query2 = "SELECT SUBSTRING(content, LOCATE('#', content)) AS tag
           FROM questions
           WHERE id = $result1 AND content LIKE '%#%'";
            $result2 = $this->db->select($query2);
            $tag = new Tag;
            $tag->name = $result2[0]["tag"];
            $tag->addTag($tag->name,$result1); 

            if (!$result1) {
                die("Error posting question.");
            } else {
                    $_SESSION["Msg"]="Question posted successfully:)";
            }
    
        } else {
            $_SESSION["Msg"]="You don't have the authority to post!";
        }
    }

    public function editQuestion($userID, $userBadge, $quest_id, string $questionText) {
    if ($userBadge !== "Editor") {
        $_SESSION["editMsg"] = "You don't have the authority to Edit Questions!";
        return;
    }

    if (!$this->db->openConnection()) {
        die("Connection failed.");
    }

    $conn = $this->db->connection;
    $questionText = mysqli_real_escape_string($conn, $questionText);

    $query1 = "SELECT user_id FROM questions WHERE ID = $quest_id";
    $result1 = $this->db->select($query1);

    if ($userID != $result1[0]["user_id"]) {
        $_SESSION["editMsg"] = "You don't have the authority to Edit other's Questions!";
        return;
    }

    $query2 = "UPDATE questions SET content = '$questionText' WHERE ID = $quest_id";
    $result2 = $this->db->update($query2);

    $query3 = "SELECT SUBSTRING(content, LOCATE('#', content)) AS tag
               FROM questions
               WHERE id = $quest_id AND content LIKE '%#%'";
    $result3 = $this->db->select($query3);

    $tag = new Tag;
    $tag->name = $result3[0]["tag"];
    $tag->addTag($tag->name, $quest_id);

    $_SESSION["editMsg"] = $result2 ? "Question has been Edited successfully:)" : "Error Editing question.";
}

}
?>