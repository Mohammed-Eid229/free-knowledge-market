<?php
require_once __DIR__.'../tag.php';
require_once __DIR__.'../../Controllers/DBController.php';
require_once __DIR__.'../editor.php';

class Moderator extends Editor
{
    protected $db;

    public function __construct()
    {
        $this->db = new DBController;
    }

    public function editQuestion($userID, $userBadge, $quest_id, string $questionText) {
    if ($userBadge !== "Moderator") {
        $_SESSION["editMsg"] = "You don't have the authority to Edit Questions!";
        return;
    }

    if (!$this->db->openConnection()) {
        die("Connection failed.");
    }

    $conn = $this->db->connection;
    $questionText = mysqli_real_escape_string($conn, $questionText);

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