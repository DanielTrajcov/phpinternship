<?php
$baseUrl = "http://" . $_SERVER['HTTP_HOST'] . "/phpinternship";

class Database {
    private $conn;

    public function __construct($host = 'localhost', $username = 'root', $password = '', $dbname = 'voting_system') {
        $this->conn = new mysqli($host, $username, $password);
        
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
        
        $this->conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`");
        
        $this->conn->select_db($dbname);
        
        $this->createTables();
    }

    private function createTables() {
        // Create `categories` table
        $createCategoriesTable = "
        CREATE TABLE IF NOT EXISTS `categories` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    $this->conn->query($createCategoriesTable);
    
    // Insert data into the categories table
    $insertCategoriesData = "
        INSERT INTO `categories` (`name`) VALUES
        ('Makes Work Fun'),
        ('Team Player'),
        ('Culture Champion'),
        ('Difference Maker')";
    $this->conn->query($insertCategoriesData);

        // Create `users` table
        $createUsersTable = "
        CREATE TABLE IF NOT EXISTS `users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `username` varchar(50) NOT NULL,
          `email` varchar(100) NOT NULL,
          `password_hash` varchar(128) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `username` (`username`),
          UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        $this->conn->query($createUsersTable);

        // Create `votes` table
        $createVotesTable = "
        CREATE TABLE IF NOT EXISTS `votes` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `voter_username` varchar(255) NOT NULL,
          `nominee_username` varchar(255) NOT NULL,
          `category_name` varchar(255) NOT NULL,
          `comment` text NOT NULL,
          `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `voter_username` (`voter_username`),
          KEY `nominee_username` (`nominee_username`),
          KEY `category_name` (`category_name`),
          CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`voter_username`) REFERENCES `users` (`username`),
          CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`nominee_username`) REFERENCES `users` (`username`),
          CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`category_name`) REFERENCES `categories` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        $this->conn->query($createVotesTable);
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        $this->conn->close();
    }
}
?>
