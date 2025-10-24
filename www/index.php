<?php
session_start();
require 'config.php'; // เชื่อมต่อ DB

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$name = $_SESSION['name'];
$is_admin = $_SESSION['is_admin'];

// บันทึกอารมณ์
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mood'])){
    $mood = $_POST['mood'] ?? '';
    $detail = $_POST['detail'] ?? '';

    $stmt = $conn->prepare("INSERT INTO mood_log (username, mood, detail, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $username, $mood, $detail);
    $stmt->execute();
}

// ข้อมูลย้อนหลังรายวัน
$stmt = $conn->prepare("SELECT created_at, mood, detail FROM mood_log WHERE username = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $username);
$stmt->execute();
$user_mood_result = $stmt->get_result();

// ข้อมูลย้อนหลังรายสัปดาห์
$stmt_weekly = $conn->prepare("
    SELECT 
        YEARWEEK(created_at, 1) AS week,
        MIN(created_at) AS week_start,
        MAX(created_at) AS week_end,
        GROUP_CONCAT(CONCAT(mood, ': ', detail) SEPARATOR '<br>') AS details
    FROM mood_log
    WHERE username = ?
    GROUP BY YEARWEEK(created_at, 1)
    ORDER BY week DESC
");
$stmt_weekly->bind_param("s", $username);
$stmt_weekly->execute();
$weekly_result = $stmt_weekly->get_result();

// สำหรับ admin
if($is_admin){
    $all_users_result = $conn->query("SELECT user_id, user AS username, name, is_admin FROM user ORDER BY user_id ASC");
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<style>
body { font-family: 'Comic Sans MS', 'Segoe UI', sans-serif; background: linear-gradient(to right, #ffe6f0, #ffb3d9); margin:0; padding:0; }
nav { background-color: rgba(255, 105, 180, 0.9); padding:10px 20px; display:flex; justify-content:center; box-shadow:0 5px 15px rgba(255,105,180,0.3);}
nav a { color:white; text-decoration:none; font-weight:bold; margin:0 15px; padding:8px 15px; border-radius:15px; transition:0.3s;}
nav a:hover { background-color:#ff1a75; }
.container { margin:30px auto; background-color: rgba(255,255,255,0.95); padding:30px 40px; border-radius:20px; box-shadow:0 10px 25px rgba(255,105,180,0.3); max-width:900px; text-align:center;}
h2 { color:#ff69b4; margin-bottom:25px; font-size:28px; }
label { font-weight:bold; display:block; margin-bottom:10px; color:#ff4d94; }
select, textarea, input[type="submit"] { width:100%; padding:12px 15px; margin-bottom:20px; border-radius:15px; border:1px solid #ffb3d9; box-sizing:border-box; font-size:14px; background-color:#fff0f6; transition:0.3s;}
select:focus, textarea:focus { border-color:#ff4d94; outline:none; box-shadow:0 0 10px rgba(255,105,180,0.2);}
textarea { resize:vertical; min-height:80px; }
input[type="submit"] { background-color:#ff4d94; color:white; border:none; cursor:pointer; font-weight:bold; font-size:16px; transition: background-color 0.3s, transform 0.2s;}
input[type="submit"]:hover { background-color:#ff1a75; transform:scale(1.05);}
p { color:#ff66aa; font-size:14px; margin-top:10px;}
table { width:100%; border-collapse:collapse; margin-top:20px;}
th, td { border:1px solid #ffb3d9; padding:10px; text-align:center;}
th { background-color:#ff99cc; color:white; }
tr:nth-child(even) { background-color:#ffe6f2;}
</style>
</head>
<body>
<nav>
    <a href="#form">กรอกข้อมูล</a>
    <a href="#history_daily">ดูข้อมูลย้อนหลังรายวัน</a>
    <a href="#history_weekly">ดูข้อมูลย้อนหลังรายสัปดาห์</a>
    <?php if($is_admin): ?>
        <a href="#admin">Admin Dashboard</a>
    <?php endif; ?>
    <a href="#"><?php echo "สวัสดี: " . htmlspecialchars($name); ?></a>
    <a href="logout.php">ออกจากระบบ</a>
</nav>

<!-- ฟอร์มกรอกข้อมูล -->
<div class="container" id="form">
    <h2>วันนี้รู้สึกอย่างไร 💕</h2>
    <form method="post">
        <label for="mood">อารมณ์:</label>
        <select name="mood" id="mood" required>
            <option value="happy">มีความสุข 😊</option>
            <option value="sad">เศร้า 😢</option>
            <option value="angry">โกรธ 😠</option>
            <option value="excited">ตื่นเต้น 😍</option>
        </select>
        <textarea name="detail" placeholder="กรุณากรอกรายละเอียด" required></textarea>
        <input type="submit" value="ส่งข้อมูล 💖">
    </form>
</div>

<!-- ข้อมูลย้อนหลังรายวัน -->
<div class="container" id="history_daily">
    <h2>ข้อมูลย้อนหลังรายวัน 📊</h2>
    <table>
        <tr>
            <th>วันที่</th>
            <th>อารมณ์</th>
            <th>รายละเอียด</th>
        </tr>
        <?php while($row = $user_mood_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            <td><?php echo htmlspecialchars($row['mood']); ?></td>
            <td><?php echo htmlspecialchars($row['detail']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- ข้อมูลย้อนหลังรายสัปดาห์ -->
<div class="container" id="history_weekly">
    <h2>ข้อมูลย้อนหลังรายสัปดาห์ 📊</h2>
    <table>
        <tr>
            <th>สัปดาห์</th>
            <th>ช่วงวันที่</th>
            <th>รายละเอียดอารมณ์</th>
        </tr>
        <?php while($row = $weekly_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['week']); ?></td>
            <td><?php echo htmlspecialchars($row['week_start'] . ' ถึง ' . $row['week_end']); ?></td>
            <td><?php echo $row['details']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Admin Dashboard -->
<?php if($is_admin): ?>
<div class="container" id="admin">
    <h2>Admin Dashboard - จัดการผู้ใช้ทั้งหมด</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Name</th>
            <th>Admin</th>
            <th>Action</th>
        </tr>
        <?php while($row = $all_users_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['user_id']; ?></td>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo $row['is_admin'] ? 'Yes' : 'No'; ?></td>
            <td>
                <a href="edit_user.php?id=<?php echo $row['user_id']; ?>">Edit</a> |
                <a href="delete_user.php?id=<?php echo $row['user_id']; ?>" onclick="return confirm('ยืนยันการลบผู้ใช้นี้?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
<?php endif; ?>

</body>
</html>
