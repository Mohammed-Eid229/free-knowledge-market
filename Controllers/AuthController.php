<?php
    require_once '../../Models/user.php';
    require_once '../../Controllers/DBController.php';
    class AuthController
    {
        protected $db;

        public function __construct()
    {
        $this->db = new DBController();
    }

        public function login(Person $person)
        {
            if($this->db->openConnection())
            {
                $query = "SELECT id,username, email, password, role_id , badge FROM users
                            WHERE email = '$person->email' AND password = '$person->password'
                            UNION
                            SELECT id,username, email, password, role_id , NULL AS badge FROM admins
                            WHERE email = '$person->email' AND password = '$person->password';";
                $result = $this->db->select($query);
                if(count($result) == 0)
                {
                    session_start();
                    $_SESSION["errMsg"]="You have entered wrong email or password";
                    return false;
                }
                else
                {
                    session_start();
                    $_SESSION["personID"]=$result[0]["id"];
                    $_SESSION["username"]=$result[0]["username"];
                    $_SESSION["personRole"]=$result[0]["role_id"];
                    $_SESSION["userBadge"]=$result[0]["badge"];
                    return true;
                }
            }
            else
            {
                echo "Error in Database Connection";
                return false;
            }
        }

        public function roleCheck(Person $person){
            if($this->db->openConnection())
            {
                $query1 = "SELECT username FROM users
                WHERE email = '$person->email' AND password = '$person->password'
                UNION
                SELECT username FROM admins
                WHERE email = '$person->email' AND password = '$person->password';";
                $result1 = $this->db->select($query1);

                session_start();
                $_SESSION["username"]=$result1[0]["username"];

                $query2 = "SELECT roles.name FROM roles
                    LEFT JOIN users ON roles.ID = users.role_id
                    LEFT JOIN admins ON roles.ID = admins.role_id
                    WHERE users.username = '$_SESSION[username]' OR admins.username = '$_SESSION[username]'";
                $result2 = $this->db->select($query2);
                return $result2[0]["name"];
            }
                
        }

        public function register(User $user)
        {
            if ($this->db->openConnection()) {
                $query = "SELECT email FROM users WHERE email = '$user->email'";
                $result = $this->db->select($query);

                if (count($result) > 0) {
                    $_SESSION["errMsg"] = "This email is not available. Please use a different email.";
                    $_SESSION["formData"] = [
                        'name' => $user->name,
                        'email' => $user->email,
                        'username' => $user->username
                    ];
                    $this->db->closeConnection();
                    return false;
                }

               $query = "INSERT INTO users (ID, name, email, password, username, points, badge, joinDate, role_id, profile_picture) 
                        VALUES (NULL, '$user->name', '$user->email', '$user->password', '$user->username', DEFAULT, DEFAULT, NOW(), '2', '');";
                $result = $this->db->insert($query);
                if ($result !== false) {
                    session_start();
                    $_SESSION["personID"] = $result;
                    $_SESSION["username"] = $user->username;
                    $_SESSION["role_id"] = 2;
                    $_SESSION["name"] = $user->name;
                    $this->db->closeConnection();
                    return true;
                } else {
                    session_start();
                    $_SESSION["errMsg"] = "Something went wrong... try again later";
                    $this->db->closeConnection();
                    return false;
                }
            } else {
                echo "Error in Database Connection";
                return false;
            }
        }

        public function resetPass(Person $person)
        {
            if ($this->db->openConnection()) {
                $query = "SELECT id,username, email, password, role_id , badge FROM users
                        WHERE email = '$person->email'
                        UNION
                        SELECT id,username, email, password, role_id , NULL AS badge FROM admins
                        WHERE email = '$person->email';";
                $result = $this->db->select($query);
                if (count($result) == 0) {
                    session_start();
                    $_SESSION["errMsg"] = "Email not Found!";
                    return false;
                } else {
                    session_start();
                    $_SESSION["personID"] = $result[0]["id"];
                    $_SESSION["personRole"] = $result[0]["role_id"];
                    $update = "";
                    if ($result[0]["role_id"] == 1) {
                        $update = "UPDATE admins SET password = '$person->password' WHERE admins.email = '$person->email';";
                    } else {
                        $update = "UPDATE users SET password = '$person->password' WHERE users.email = '$person->email';";
                    }
                    $this->db->update($update);
                    return true;
                }
            }
        }
}


?>