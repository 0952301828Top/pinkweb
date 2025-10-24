<?php
session_start(); // เรียก session_start() ก่อนส่ง headers ใด ๆ
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // เตรียมคำสั่ง SQL ให้ถูกต้อง
    $stmt = $conn->prepare("SELECT * FROM user WHERE user = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password); // bind ทั้ง username และ password
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit;
    } else {
        echo "Invalid username or password";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
<?php if(isset($_SESSION['error'])): ?>
    <p style="color:red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
<?php endif; ?>
<form action="login.php" method="post">
    <label>Username: <input type="text" name="username"></label><br>
    <label>Password: <input type="password" name="password"></label><br>
    <button type="submit">Login</button>
</form>
</body>
</html>
