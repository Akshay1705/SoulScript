<?php
include 'db.php';
include 'session.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $noteId = (int)$_POST['id'];
    $userId = $_SESSION['user_id'];

    // Delete only if the note belongs to the user
    $sql = "DELETE FROM notes WHERE id = $noteId AND user_id = $userId";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'error' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'invalid']);
}
