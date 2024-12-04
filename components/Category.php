<?php
class Category {
    private $conn;

    public function __construct($db) {
        $this->conn = $db->getConnection();
    }

    public function fetchOptions($table, $column) {
        $query = "SELECT $column FROM $table";
        $result = $this->conn->query($query);
        if (!$result) {
            die("Error fetching $table: " . $this->conn->error);
        }
        return $result;
    }
}
?>
