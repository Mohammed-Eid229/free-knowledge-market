<?php
class Tag
{
    public $ID;
    public $name;
    protected $db;

    public function __construct() {
        $this->db = new DBController();
    }

    public function addTag($tag, $quest_id) {
        if ($this->db->openConnection()) {
            $insertTagQuery = "INSERT IGNORE INTO tags (ID, name) VALUES (NULL, '$tag')";
            $this->db->insert($insertTagQuery);

            $getTagIdQuery = "SELECT ID FROM tags WHERE name = '$tag'";
            $tagResult = $this->db->select($getTagIdQuery);

            if ($tagResult && count($tagResult) > 0) {
                $tag_id = $tagResult[0]["ID"];

                $insertRelationQuery = "INSERT IGNORE INTO questiontags (quest_id, tag_id) VALUES ($quest_id, $tag_id)";
                $this->db->insert($insertRelationQuery);
            }
        }
    }

    public function getAllTags()
    {
        if ($this->db->openConnection()) {
            $query = "SELECT * FROM tags";
            $result = $this->db->select($query);
            $this->db->closeConnection();
            return $result;
        }
        return false;
    }

    public function searchQuestionsByTag($tagName)
    {
        if ($this->db->openConnection()) {
            $query = "" ;
            if ($this->db->openConnection()){

            $query = "
                SELECT 
                    q.*, 
                    u.username, 
                    COUNT(a.ID) AS n_answers
                FROM questions q
                INNER JOIN questiontags qt ON q.ID = qt.quest_id
                INNER JOIN tags t ON t.ID = qt.tag_id
                INNER JOIN users u ON q.user_id = u.ID
                LEFT JOIN answers a ON q.ID = a.quest_id
                WHERE t.name = '$tagName' AND q.status = 'Approved'
                GROUP BY q.ID

            ";

            $result = $this->db->select($query);
            $this->db->closeConnection();
            return $result;
        }
    }

        return false;
    }



}
?>