<?php 
include 'db.php';
include 'session.php'; 
?>
<?php
$id = $_GET['id'];
$sql = "SELECT * FROM notes WHERE id=$id";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    // Prevent SQL injection \" using (mysqli_real_escape_string)
    $content = mysqli_real_escape_string($conn, $_POST['content']);

    $sql = "UPDATE notes SET title='$title', content='$content' WHERE id=$id";
    // Update the note with the new title and content 
    mysqli_query($conn, $sql);
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Note</title>
    <link rel="stylesheet" href="edit.css"> <!-- ✅ Link to CSS -->
</head>
<body>
    <div class="edit-note-container">
        <h2>✏️ Edit Note</h2>
        <form method="post">
            <label for="title">Title:</label><br>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($row['title']) ?>" required><br><br>
            <!-- pre inserted value in title field -->

            <label for="content">Content:</label><br>
            <textarea id="content" name="content" rows="6" required><?= htmlspecialchars($row['content']) ?></textarea><br><br>
            <!-- pre inserted value in textarea -->
            <!-- htmlspecialchars() is used to prevent XSS attacks by escaping special characters -->

            <button type="submit">Update Note</button>
        </form>

        <a href="index.php" class="back-link">← Back to Notes</a>
    </div>
</body>
</html>
