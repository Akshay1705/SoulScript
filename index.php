<!-- http://localhost/myphp/SoulScript/index.php -->
<?php 
include 'db.php';
include 'session.php';//session start in db.php

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (isset($_GET['search']) && !isset($_GET['page'])) {// If search is set but page is not, redirect to the first page
    $search = urlencode($_GET['search']);// Encode the search term to make it URL-safe
    $filter = isset($_GET['filter']) ? urlencode($_GET['filter']) : '';// If filter is set, encode it too
    header("Location: index.php?search=$search&filter=$filter&page=1");// Redirect to the 1st page with search and filter parameters
    exit;
}

$user_id = $_SESSION['user_id']; // Get logged-in user's ID

// === PAGINATION SETUP ==========================================================================
$limit = 6; // Notes per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;// if set then get the page number from URL, if not set then (default to 1)
if ($page < 1) $page = 1;//ensure if user (try -1,0-2 etc.) ==>( page == 1).
$offset = ($page - 1) * $limit;
//important line:it calc starting point(offset) in the DB to fetch notes strting from
//if page is 1->0(skips) , 2->6(skips), 3->12(skips), etc.
//offset == how many notes to skip

// ======= SEARCH & FILTER FUNCTIONALITY ====================================================================
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';//get search from URL
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';//get filter from URL
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;//get page no. from URL, defualt to 1
if ($page < 1) $page = 1;// Ensure page is at least 1
$offset = ($page - 1) * $limit;

//-------------- Count total notes with search & filter ----------------
$count_sql = "SELECT COUNT(*) AS total FROM notes WHERE user_id = $user_id";//count of total notes of specific user
if ($search !== '') {//if search has value
    $count_sql .= " AND (title LIKE '%$search%' OR content LIKE '%$search%')";//add search condition
}
if ($filter == 'today') {
    $count_sql .= " AND DATE(created_at) = CURDATE()";//today's filter
} elseif ($filter == 'week') {
    $count_sql .= " AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";//week filter
} elseif ($filter == 'month') {
    $count_sql .= " AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";//month filter
}
$count_result = mysqli_query($conn, $count_sql);//contains 1 column and 1 row [total][23] - 23 needs to be !
$total_notes = mysqli_fetch_assoc($count_result)['total'];// Fetch total =23
$total_pages = ceil($total_notes / $limit);// Calculate total pages 13/6 =(2.33) => 3 pages

// ----------Fetch filtered notes ------------------------------------------------
$sql = "SELECT * FROM notes WHERE user_id = $user_id";// Fetch all notes for the logged-in user
if ($search !== '') {//it should has search value
    $sql .= " AND (title LIKE '%$search%' OR content LIKE '%$search%')";//now only fetch notes thet match search
}
if ($filter == 'today') {
    $sql .= " AND DATE(created_at) = CURDATE()";//created_at ==> CURDATE() means today
} elseif ($filter == 'week') {
    $sql .= " AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";//YEARWEEK(current date, 1) means this week 1 is for Monday
} elseif ($filter == 'month') {
    $sql .= " AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";// Filter for this month's notes
}

// Sorting
switch ($filter) {
    case 'oldest':
        $sql .= " ORDER BY created_at ASC";
        break;
    case 'title_asc':
        $sql .= " ORDER BY title ASC";
        break;
    case 'title_desc':
        $sql .= " ORDER BY title DESC";
        break;
    default:
        $sql .= " ORDER BY created_at DESC";
        break;
}

$sql .= " LIMIT $offset, $limit";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>🧘‍♀️ SoulScript</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
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
            <select name="filter" onchange="this.form.submit()">
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
<a href="add.php" class="add-btn">+ New Journal Entry</a>
<a href="index.php" class="home-link">Home</a>
<!-- END Header Bar -->

<!-- Notes will be displayed here -->
<div id="notes-area">
<?php if (mysqli_num_rows($result) > 0): ?>
    <div class="notes-container">
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="card">
            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
            <small>🕒 <?= date('d M Y, h:i A', strtotime($row['created_at'])) ?></small>
            <div class="note-actions">
                <a href="edit.php?id=<?= $row['id'] ?>" class="edit-btn">✏️ Edit</a>
                <a href="delete.php" class="delete-btn" data-id="<?= $row['id'] ?>" onclick="return false;">🗑️ Delete</a>
            </div>
        </div>
    <?php endwhile; ?>
    </div>

    <!-- ✅ AJAX-READY Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>" 
               class="pagination-link">&laquo; Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>"
               class="pagination-link <?= $i == $page ? 'active' : '' ?>"
               data-page="<?= $i ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>" 
               class="pagination-link">Next &raquo;</a>
        <?php endif; ?>
    </div>

<?php else: ?>
    <?php if (!empty($search)): ?>
        <div class="search-error">
            ❌ No notes found for "<strong><?= htmlspecialchars($search) ?></strong>"<br>
        </div>
    <?php else: ?>
        <div class="message">🌱 Every journey begins with one thought. Start writing now. 💭</div>
    <?php endif; ?>
<?php endif; ?>
</div> <!-- END #notes-area -->


</body>
</html>
