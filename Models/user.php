<?php
require_once __DIR__ . '/../Models/person.php';
require_once __DIR__ . '/../Controllers/DBController.php';

class User extends Person
{
    public $points;
    public $badge;
    protected $db; 

    public function __construct()
    {
        $this->db = new DBController(); 
    }

    public function viewLeaderBoard()
    {
        if ($this->db->openConnection()) {
            $query = "SELECT username, email, points, badge, joinDate ,profile_picture FROM users ORDER BY points DESC";
            return $this->db->select($query);
        }
        return false;
    }

    public function earnPoints(User $user ,$value)
    {
        if ($this->db->openConnection()) {
            $query1 = "SELECT points FROM users WHERE ID = " . $user->ID;
            $result1 = $this->db->select($query1);
            $currentPoints = $result1[0]["points"];
            $currentPoints += $value;

            if ($currentPoints < 200) {
                $badge = "New user";
            } elseif ($currentPoints >= 200 && $currentPoints < 400) {
                $badge = "Voter";
            } elseif ($currentPoints >= 400 && $currentPoints < 600) {
                $badge = "Editor";
            } else {
                $badge = "Moderator";
            }

            $user->badge = $badge;

            $query = "UPDATE users 
                    SET points = " . $currentPoints . ", badge = '" . $badge . "' 
                    WHERE ID = " . $user->ID;

            $result = $this->db->update($query);

            if (!$result) {
                die("Failed to update user points and badge.");
            }

            $this->db->closeConnection();
            return true;
        } else {
            die("Connection failed.");
        }
    }

}
?>