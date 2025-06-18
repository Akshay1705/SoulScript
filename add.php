<?php 
include 'db.php'; 
include 'session.php';
?>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {//post method come from down the form
    // Handle form submission
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    //INSERT INTO notes (title) VALUES ('Hello\'); =>text only (DROP TABLE notes; --');) 
    // Always use mysqli_real_escape_string to prevent SQL injection
    $user_id = $_SESSION['user_id']; // Get the user ID from the session
    $sql = "INSERT INTO notes (title, content, user_id) VALUES ('$title', '$content', $user_id)";
    // Insert the new note into the database
    mysqli_query($conn, $sql);
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Note</title>
    <link rel="stylesheet" href="add.css"> <!-- ✅ Link your modern CSS -->
</head>
<body>
    <div class="add-note-container">
        <h2>📝 Add a New Note</h2>
        <form method="post">
            <label for="title">Title:</label><br>
            <input type="text" id="title" name="title" required><br><br>

            <label for="content">Content:</label><br>
            <textarea id="content" name="content" rows="6" required></textarea><br><br>

            <button type="submit">Save Note</button>
        </form>

        <a href="index.php" class="back-link">← Back to Notes</a>
    </div>
</body>
</html>
