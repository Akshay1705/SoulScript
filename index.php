<!-- http://localhost/myphp/SoulScript/index.php -->
<?php 
include 'db.php';
include 'session.php';

if (isset($_GET['search']) && !isset($_GET['page'])) {
    $search = urlencode($_GET['search']);
    $filter = isset($_GET['filter']) ? urlencode($_GET['filter']) : '';
    header("Location: index.php?search=$search&filter=$filter&page=1");
    exit;
}

$user_id = $_SESSION['user_id']; // Get logged-in user's ID

// === PAGINATION SETUP ===
$limit = 6; // Notes per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;// is set then get the page number from URL, if not set then default to 1
if ($page < 1) $page = 1;//safty check to ensure page is at least 1 if user try -1,0-2 etc. ==> page == 1.
$offset = ($page - 1) * $limit;
//important line:it calc starting point(offset) in the DB to fetch notes strting from
//if page is 1->0(skips) , 2->6(skips), 3->12(skips), etc.
//offset is the number of records to skip before starting to fetch records

// === SEARCH FUNCTIONALITY ===
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Count total notes with search filter
$count_sql = "SELECT COUNT(*) AS total FROM notes WHERE user_id = $user_id";
if ($search !== '') {
    $count_sql .= " AND (title LIKE '%$search%' OR content LIKE '%$search%')";
}
if ($filter == 'today') {
    $count_sql .= " AND DATE(created_at) = CURDATE()";
} elseif ($filter == 'week') {
    $count_sql .= " AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter == 'month') {
    $count_sql .= " AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
}
$count_result = mysqli_query($conn, $count_sql);
$total_notes = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_notes / $limit);

// Fetch filtered notes
$sql = "SELECT * FROM notes WHERE user_id = $user_id";
if ($search !== '') {
    $sql .= " AND (title LIKE '%$search%' OR content LIKE '%$search%')";
}
if ($filter == 'today') {
    $sql .= " AND DATE(created_at) = CURDATE()";
} elseif ($filter == 'week') {
    $sql .= " AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter == 'month') {
    $sql .= " AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
}

// Sorting options
if ($filter == 'oldest') {
    $sql .= " ORDER BY created_at ASC";
} elseif ($filter == 'title_asc') {
    $sql .= " ORDER BY title ASC";
} elseif ($filter == 'title_desc') {
    $sql .= " ORDER BY title DESC";
} else {
    $sql .= " ORDER BY created_at DESC"; // default
}

$sql .= " LIMIT $offset, $limit";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>🧘‍♀️ SoulScript</title>
    <link rel="stylesheet" href="style.css">
    <style>
        
    </style>
</head>
<body>

<!-- Welcome Message -->
<p class="welcome">👋 Welcome, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong></p>

<!-- Header Bar -->
<div class="top-bar">
    <h1>📖 SoulScript — Reflect. Write. Grow. 🌱</h1>
    <div class="search-container">
        <form method="GET" action="index.php">
            <input type="text" name="search" placeholder="Search notes..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit">🔍</button>
            <select name="filter">
                <option value="">📅 Filter by</option>
                <option value="today" <?= (isset($_GET['filter']) && $_GET['filter'] == 'today') ? 'selected' : '' ?>>Today</option>
                <option value="week" <?= (isset($_GET['filter']) && $_GET['filter'] == 'week') ? 'selected' : '' ?>>This Week</option>
                <option value="month" <?= (isset($_GET['filter']) && $_GET['filter'] == 'month') ? 'selected' : '' ?>>This Month</option>
                <option value="oldest" <?= (isset($_GET['filter']) && $_GET['filter'] == 'oldest') ? 'selected' : '' ?>>Oldest First</option>
                <option value="title_asc" <?= (isset($_GET['filter']) && $_GET['filter'] == 'title_asc') ? 'selected' : '' ?>>A–Z (Title)</option>
                <option value="title_desc" <?= (isset($_GET['filter']) && $_GET['filter'] == 'title_desc') ? 'selected' : '' ?>>Z–A (Title)</option>
            </select>
        </form>
    </div>
    <div class="top-actions">
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>
<hr>

<!-- Notes Section -->
<a href="add.php" class="add-btn">+ New Journal Entry</a>
<?php if (mysqli_num_rows($result) > 0): ?>
    <div class="notes-container">
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="card">
            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
            <small>🕒 <?= date('d M Y, h:i A', strtotime($row['created_at'])) ?></small>
            <div class="note-actions">
                <a href="edit.php?id=<?= $row['id'] ?>" class="edit-btn">✏️ Edit</a>
                <a href="delete.php?id=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Delete this note?');">🗑️ Delete</a>
            </div>
        </div>
    <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>" class="<?= $i == $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>

<?php else: ?>
    <?php if (!empty($search)): ?>
        <div class="search-error">
            ❌ No notes found for "<strong><?= htmlspecialchars($search) ?></strong>"<br>
            <a href="index.php?page=1" class="back-link">🔙 Back to all notes</a>
        </div>
    <?php else: ?>
        <div class="message">🌱 Every journey begins with one thought. Start writing now. 💭</div>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>
