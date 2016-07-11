
<?php
include_once '../config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
$id= $_REQUEST["id"];
$sql = "UPDATE site_users SET `user_pending` = '0' WHERE `site_users`.`user_id` = ".$id;
//echo $sql;
if ($conn->query($sql) === TRUE) {
    echo "Your Account has been activated.<br> <a href='http://mangaisolutions.com/dev/TicketSystem/'>Login Now to view your account</a>";
} else {
    echo "Something went Wrong.";
}

$conn->close();
?>

