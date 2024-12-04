<?php
$baseUrl = "http://" . $_SERVER['HTTP_HOST'] . "/phpinternship";
class Database {
    private $conn;
    
    public function __construct($host = 'localhost', $username = 'root', $password = '', $dbname = 'voting_system') {
        $this->conn = new mysqli($host, $username, $password, $dbname);
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        $this->conn->close();
    }
}
?>
