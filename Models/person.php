<?php
require_once '../../Controllers/DBController.php';
require_once '../../Controllers/AuthController.php';
class Person
{
    public $ID;
    public $name;
    public $email;
    public $password;
    public $username;
    public $points;
    public $joindate;
    public $role_id;
    public $profile_picture; 

    public function changeUsername(DBController $db, $newUsername , Person $person)
    {
        if ($this->isUsernameExists($db, $newUsername,$person)) { 
            return "Username already exists!";
        }
        $auth = new AuthController;
        $query = "";
        if($auth->roleCheck($person) == "Admin"){
            $query = "UPDATE admins SET username = '$newUsername' WHERE id = $this->ID";
        } else{
            $query = "UPDATE users SET username = '$newUsername' WHERE id = $this->ID";
        }
        error_log("Person->changeUsername: Executing UPDATE query: " . $query);
        $result = $db->connection->query($query);

        if ($result) {
            error_log("Person->changeUsername: Username updated successfully for user ID: " . $this->ID);
            $this->username = $newUsername;
            return "Username updated successfully!";
        } else {
            error_log("Person->changeUsername: Error updating username for user ID: " . $this->ID . " - " . $db->connection->error);
            return "Error updating username.";
        }
    }

 
    private function isUsernameExists(DBController $db, $username, Person $person)
    {
        $auth = new AuthController;
        if($auth->roleCheck($person) == "Admin"){
            $query = "SELECT id FROM admins WHERE username = '$username'";
        } else{
            $query = "SELECT id FROM users WHERE username = '$username'";
        }
        error_log("Person->isUsernameExists: Executing SELECT query: " . $query);
        $result = $db->select($query);
        return $result ? true : false;
    }


    public function changePassword(DBController $db, $oldPassword, $newPassword,Person $person) {
        if ($db->openConnection()) {
             $auth = new AuthController;
        if($auth->roleCheck($person) == "Admin"){
            $query = "SELECT password FROM admins WHERE id = ?";
        } else{
            $query = "SELECT password FROM users WHERE id = ?";
        }
            $stmt = $db->connection->prepare($query);
            $stmt->bind_param("i", $this->ID);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($oldPassword === $row['password']) {
        $updateQuery = "";
        $auth = new AuthController;
        if($auth->roleCheck($person) == "Admin"){
            $updateQuery = "UPDATE admins SET password = ? WHERE id = ?";;
        } else{
            $updateQuery = "UPDATE users SET password = ? WHERE id = ?";
        }
                    $updateStmt = $db->connection->prepare($updateQuery);
                    $updateStmt->bind_param("si", $newPassword, $this->ID);
                    if ($updateStmt->execute()) {
                        $updateStmt->close();
                        return true; 
                    } else {
                        $updateStmt->close();
                        return "Error updating password."; 
                    }
                } else {
                    return "Incorrect old password."; 
                }
            } else {
                return "User not found."; 
            }
            $stmt->close();
            $db->closeConnection();
        } else {
            return "Database connection error."; 
        }
    }

   
    public function changeProfilePicture(DBController $db, $file,Person $person)
    {
        $targetDir = "uploads/"; 
        $targetFile = $targetDir . basename($file["name"]);

        if (move_uploaded_file($file["tmp_name"], $targetFile)) {

        $auth = new AuthController;
        if($auth->roleCheck($person) == "Admin"){
            $query = "UPDATE admins SET profile_picture = '$targetFile' WHERE id = $this->ID";
        } else{
            $query = "UPDATE users SET profile_picture = '$targetFile' WHERE id = $this->ID";
        }
            error_log("Person->changeProfilePicture: Executing UPDATE query: " . $query);
            if ($db->update($query)) {
                error_log("Person->changeProfilePicture: Photo path updated successfully for user ID: " . $this->ID . " to: " . $targetFile);
                $this->profile_picture = $targetFile;
                return "Photo updated successfully!";
            } else {
                error_log("Person->changeProfilePicture: Error updating photo path for user ID: " . $this->ID . " - " . $db->connection->error);
                return "Error updating photo path.";
            }
        } else {
            error_log("Person->changeProfilePicture: Error uploading photo.");
            return "Error uploading photo.";
        }
    }
}
?>