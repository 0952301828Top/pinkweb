<?php
session_start();
require 'config.php';

// ตรวจสอบว่าผู้ใช้ล็อกอิน
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// ดึงข้อมูลผู้ใช้ทั้งหมดจากตาราง user
$result = $conn->query("SELECT user_id, username, name, email, is_admin, created_at FROM user ORDER BY user_id ASC");

// ข้อมูลผู้ใช้ปัจจุบัน
$is_admin = $_SESSION['is_admin'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
th { background-color: #f08080; color: white; }
a { text-decoration: none; color: blue; }
a:hover { color: darkred; }
</style>
</head>
<body>
<h1>Dashboard</h1>
<p>สวัสดี, <?php echo htmlspecialchars($_SESSION['name']); ?>! (<?php echo $is_admin ? 'Admin' : 'User'; ?>)</p>

<h2>รายชื่อผู้ใช้ทั้งหมด</h2>

<table>
<tr>
    <th>ID</th>
    <th>Username</th>
    <th>Name</th>
    <th>Email</th>
    <th>Admin</th>
    <th>Created At</th>
    <?php if($is_admin): ?>
        <th>Action</th>
    <?php endif; ?>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?php echo $row['user_id']; ?></td>
    <td><?php echo htmlspecialchars($row['username']); ?></td>
    <td><?php echo htmlspecialchars($row['name']); ?></td>
    <td><?php echo htmlspecialchars($row['email']); ?></td>
    <td><?php echo $row['is_admin'] ? 'Yes' : 'No'; ?></td>
    <td><?php echo $row['created_at']; ?></td>
    <?php if($is_admin): ?>
    <td>
        <a href="edit_user.php?id=<?php echo $row['user_id']; ?>">Edit</a> |
        <a href="delete_user.php?id=<?php echo $row['user_id']; ?>" onclick="return confirm('ยืนยันการลบผู้ใช้นี้?');">Delete</a>
    </td>
    <?php endif; ?>
</tr>
<?php endwhile; ?>

</table>

<p><a href="logout.php">ออกจากระบบ
