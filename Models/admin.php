<?php
require_once  '../../Controllers/DBController.php';
require_once 'person.php';
require_once 'question.php';


class Admin extends Person
{
    private $db;
    
    public function __construct() {
        $this->db = new DBController();
    }
    
    public function getTotalUsers() {
        if ($this->db->openConnection()) {
            $result = $this->db->select("SELECT COUNT(ID) as total FROM users");
            $this->db->closeConnection();
            return $result[0]['total'] ?? 0;
        }
        return 0;
    }
    
    public function getTotalQuestions() {
        if ($this->db->openConnection()) {
            $result = $this->db->select("SELECT COUNT(ID) as total FROM questions");
            $this->db->closeConnection();
            return $result[0]['total'] ?? 0;
        }
        return 0;
    }
    
    public function getTotalAnswers() {
        if ($this->db->openConnection()) {
            $result = $this->db->select("SELECT COUNT(ID) as total FROM answers");
            $this->db->closeConnection();
            return $result[0]['total'] ?? 0;
        }
        return 0;
    }
    
    public function getTotalTags() {
        if ($this->db->openConnection()) {
            $result = $this->db->select("SELECT COUNT(ID) as total FROM tags");
            $this->db->closeConnection();
            return $result[0]['total'] ?? 0;
        }
        return 0;
    }

    public function getAllUsers(){
        if ($this->db->openConnection()) {
            $query = "select username , email , name , points , badge ,ID , joinDate ,profile_picture FROM users";
            return $this->db->select($query);
        }
    }

    public function getAllQuestions(){
        if ($this->db->openConnection()) {
            $query = "select questions.content, questions.status, questions.ID, users.username ,profile_picture from questions JOIN users on questions.user_id=users.ID where questions.status='waiting' ORDER BY questions.timeStamp ASC;";
            return $this->db->select($query);
        }        
    }

    public function deleteUser($id){
        if ($this->db->openConnection()) {
            $query = "DELETE FROM users WHERE ID = $id;";
            return $this->db->delete($query);
        }   
        else{
            echo "Error in the database connection";
            return false;
        } 
    }

    public function rejectQuestion($id){
        if ($this->db->openConnection()) {
            $query = "DELETE FROM questions WHERE ID = $id;";
            return $this->db->delete($query);
        }   
        else{
            echo "Error in the database connection";
            return false;
        } 
    }

    public function approveQuestion($id){
        if ($this->db->openConnection()) {
            $query = "UPDATE questions SET status = 'approved' WHERE ID = $id;";
            return $this->db->update($query);
        }   
        else{
            echo "Error in the database connection";
            return false;
        } 
    }

    public function numberOfRejectedQuestions()
    {
        if ($this->db->openConnection()) {
            $result = $this->db->select("SELECT COUNT(ID) AS total FROM questions WHERE status = 'waiting';");
            $this->db->closeConnection();
            return $result[0]['total'] ?? 0;
        }
        return 0;
    }

    public function viewAllQuestions()
    {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $query = "SELECT questions.user_id, users.username,questions.ID,questions.timestamp,questions.content,questions.n_upvotes,questions.n_downvotes, COUNT(answers.ID) AS n_answers ,profile_picture  
                        FROM questions INNER JOIN users ON questions.user_id = users.ID 
                        LEFT JOIN answers ON questions.ID = answers.quest_id 
                        GROUP BY users.username,questions.timestamp,questions.content,questions.n_upvotes,questions.n_downvotes
                        ORDER BY questions.timestamp DESC;";
                        
            return $this->db->select($query);
    }
    }

    public function viewAllAnswers()
    {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $query = "SELECT answers.user_id, users.username,answers.ID,answers.timestamp,answers.content,profile_picture 
                        FROM answers INNER JOIN users ON answers.user_id = users.ID 
                        LEFT JOIN questions ON questions.ID = answers.quest_id 
                        GROUP BY users.username,answers.timestamp,answers.content
                        ORDER BY answers.timestamp DESC;";
                        
            return $this->db->select($query);
    }
    }


    public function viewAllTags()
    {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $query = "SELECT * from tags";
                        
            return $this->db->select($query);
    }
    }

    public function viewAdminLeaderBoard()
    {
        if ($this->db->openConnection()) {
            $query = "SELECT username, email, points, badge, joinDate ,profile_picture  FROM users ORDER BY points DESC";
            return $this->db->select($query);
        }
        return false;
    }

    
} 

?>

