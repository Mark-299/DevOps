<?php
SESSION_START();
require_once 'dbcon.php';

$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];
$elemName = $_POST['elemName'];
$elemYear = $_POST['elemYear'];
$juniorName = $_POST['juniorName'];
$juniorYear = $_POST['juniorYear'];
$seniorName = $_POST['seniorName'];
$seniorYear = $_POST['seniorYear'];
$lastname = $_POST['lastname'];
$firstname = $_POST['firstname'];
$middlename = $_POST['middlename'];
$sex = $_POST['sex'];
$dob = $_POST['dob'];
$phonenumber = $_POST['phoneNumber'];
$guardianName = $_POST['guardianName'];
$guardianPhoneNumber = $_POST['guardianPhoneNumber'];
$guardianAddress = $_POST['guardianAddress'];
$course = $_POST['course'];
$year = $_POST['year'];
$section = isset($_POST['section']) ? $_POST['section'] : null;
$appointment_date = $_POST['appointment_date'];
$time = $_POST['time'];
$appointment_id = isset($_POST['appointmentID']) ? intval($_POST['appointmentID']) : 0;


$sql = "INSERT INTO enroll (username, password, email, elemName, elemYear, juniorName, juniorYear, seniorName, seniorYear, lastname, firstname, middlename, sex, dob, phonenumber, guardianName, guardianPhoneNumber, guardianAddress, course, year, section, status, appointment_date, time)
VALUES ('$username', '$password', '$email', '$elemName', '$elemYear', '$juniorName', '$juniorYear', '$seniorName', '$seniorYear', '$lastname', '$firstname', '$middlename', '$sex', '$dob', '$phonenumber', '$guardianName', '$guardianPhoneNumber', '$guardianAddress', '$course', '$year', '". $section ."','PENDING','$appointment_date','$time')";
if ($conn->query($sql) === TRUE) {
  // decrement slots if appointment was selected
  if ($appointment_id > 0) {
    $u = $conn->prepare("UPDATE appointments SET slots = GREATEST(slots - 1, 0) WHERE id = ?");
    if ($u) { $u->bind_param('i', $appointment_id); $u->execute(); $u->close(); }
  }
  $_SESSION['status'] = "Enrollment Successful";
  header("Location: ../enroll.php");
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>