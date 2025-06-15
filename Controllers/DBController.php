<?php
    class DBController
    {
        private $dbHost = "localhost";
        private $dbUser = "root";
        private $dbpassword = "";
        private $dbName = "knowledgemarket";
        public $connection;


        public function openConnection()
        {
            $this->connection = new mysqli($this->dbHost,$this->dbUser,$this->dbpassword,$this->dbName);
            if($this->connection->connect_error)
            {
                echo "Error in Connection : ".$this->connection->connect_error;
                return false;
            }
            else
            {
                return true;
            }
        }

        public function closeConnection()
        {
            if($this->connection)
            {
                $this->connection->close();
            }
            else
            {
                echo "Connection is not Opened";
            }
        }

        public function select($qry)
        {
            $result=$this->connection->query($qry);
            if(!$result)
            {
                echo "Error : ".mysqli_error($this->connection);
                return false;
            }
            else
            {
                return $result->fetch_all(MYSQLI_ASSOC);
            }

        }

        public function insert($qry)
        {
            $result=$this->connection->query($qry);
            if(!$result)
            {
                echo "Error : ".mysqli_error($this->connection);
                return false;
            }
            else
            {
                return $this->connection->insert_id;
            }
        }

        public function delete($qry)
        {
            $result = $this->connection->query($qry);
            if (!$result) {
                echo "Error: " . mysqli_error($this->connection);
                return false;
            }
            return true;
        }

        public function update($qry)
        {
            $result = $this->connection->query($qry);
            if (!$result) 
            {
                echo "Error: " . mysqli_error($this->connection);
                 return false;
            } else {
                return true; 
            }
        }



    }


?>