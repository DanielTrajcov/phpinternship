<?php
class User {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db->getConnection();
    }

    public function register($username, $email, $password, $password_confirm) {
        if ($password !== $password_confirm) {
            return ['status' => 'error', 'message' => 'Passwords do not match.'];
        }

        $password_hash = hash('sha512', $password);
        $stmt = $this->conn->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $username, $email, $password_hash);

        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Registration successful! You can now log in.'];
        } else {
            return ['status' => 'error', 'message' => 'Error: ' . $stmt->error];
        }
    }

    public function login($email, $password) {
        $password_hash = hash('sha512', $password);
        $stmt = $this->conn->prepare('SELECT id, username FROM users WHERE email = ? AND password_hash = ?');
        $stmt->bind_param('ss', $email, $password_hash);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return ['status' => 'success', 'message' => 'Login successful!', 'username' => $user['username']];
        } else {
            return ['status' => 'error', 'message' => 'Invalid email or password.'];
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
    }
}
?>
