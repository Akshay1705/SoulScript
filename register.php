<?php
include 'db.php';

$registered = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST["username"]);// Get the username from the form and escape it to prevent SQL injection
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);// Hash the password for security

    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username already exists!";
    } else {
        $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
        if (mysqli_query($conn, $sql)) {// If the query is successful
            // Registration successful
            $registered = true;
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="register-container">
        <?php if ($registered): ?>
            <div class="message" style="color: green; text-align:center;">
                ✅ Registration successful! <a href="login.php">Login now</a>
            </div>
            
        <?php else: ?>
            <h2>Register</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required><br><br>
                <input type="password" name="password" placeholder="Password" required><br><br>
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="login.php" style="display:inline;">Login</a></p>

            <?php if ($error): ?>
                <p style="color:red;"><?php echo $error; ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
