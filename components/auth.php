<?php
require 'Database.php';
session_start();

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    if ($action === 'register') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $password_confirm = trim($_POST['password_confirm']);

        if ($password !== $password_confirm) {
            $message = '<div class="alert alert-danger">Passwords do not match.</div>';
        } else {
            $password_hash = hash('sha512', $password);
            $stmt = $conn->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $username, $email, $password_hash);

            if ($stmt->execute()) {
                echo "<script>
                        alert('Registration successful! ');
                        window.location.href = '$baseUrl/index.php?vote';
                      </script>";
                exit; 
            } else {
                echo "<script>
                alert('This account already exists !');
                window.location.href = '$baseUrl/index.php?vote';
              </script>";
            }

            $stmt->close();
        }
    } elseif ($action === 'login') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $password_hash = hash('sha512', $password);

        $stmt = $conn->prepare('SELECT id, username FROM users WHERE email = ? AND password_hash = ?');
        $stmt->bind_param('ss', $email, $password_hash);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo "<script>
                    alert('Login successful! Welcome, " . htmlspecialchars($user['username']) . "');
                    window.location.href = '$baseUrl/index.php?vote';
                  </script>";
        } else {
            echo "<script>
            alert('Invalid name or password');
            window.location.href = '$baseUrl/index.php?vote';
          </script>";
        }

        $stmt->close();
    }
}

$database->closeConnection();
?>
