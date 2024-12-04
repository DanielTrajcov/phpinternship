<?php
class Vote {
    private $conn;

    public function __construct($db) {
        $this->conn = $db->getConnection();
    }

    public function submitVote($voter_username, $nominee_username, $category_name, $comment) {
        if ($voter_username === $nominee_username) {
            return ["status" => "error", "message" => "You cannot vote for yourself!"];
        }

        $stmt = $this->conn->prepare("INSERT INTO votes (voter_username, nominee_username, category_name, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $voter_username, $nominee_username, $category_name, $comment);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Vote submitted successfully!"];
        } else {
            return ["status" => "error", "message" => "Error submitting vote!"];
        }
    }

    public function getVoteResults() {
        $query = "
            SELECT category_name, nominee_username, COUNT(*) AS total_votes
            FROM votes
            GROUP BY category_name, nominee_username
            ORDER BY category_name, total_votes DESC
        ";
        $result = $this->conn->query($query);
        if (!$result) {
            die("Error fetching vote results: " . $this->conn->error);
        }
        return $result;
    }

    public function getMostFrequentVoters() {
        $query = "
            SELECT voter_username, COUNT(*) AS total_votes
            FROM votes
            GROUP BY voter_username
            ORDER BY total_votes DESC
            LIMIT 5
        ";
        $result = $this->conn->query($query);
        if (!$result) {
            die("Error fetching frequent voters: " . $this->conn->error);
        }
        return $result;
    }
}
?>
