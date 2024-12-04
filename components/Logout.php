<?php
require 'Database.php';
require 'User.php';

session_start();

$db = new Database();

session_unset();
session_destroy();

header("Location: $baseUrl/index.php");
exit;
?>
