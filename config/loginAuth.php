<?php
SESSION_START();
require_once 'dbcon.php';

$username = $_POST['username'];
$password = $_POST['password'];
 
$sql = "SELECT * FROM accounts WHERE username='$username' AND password='$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    header("Location: ../content_management.php");
  }
} else {
  $_SESSION['error'] = 'Invalid username or password';
  header("Location: ../index.php");
}
$conn->close();
?>