<?php
include 'db.php';
$id = $_GET['id'];// Get the note ID from the URL parameter
$sql = "DELETE FROM notes WHERE id=$id";
// Prepare the SQL query to delete the note with the given ID
mysqli_query($conn, $sql);
header("Location: index.php");
exit;
