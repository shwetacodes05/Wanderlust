<?php
session_start();
include '../db_connect.php';

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$users = mysqli_query($conn, "
    SELECT u.*, 
    (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) AS trips 
    FROM users u 
    WHERE role='user' 
    ORDER BY created_at DESC
");
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Users - Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap');
*{margin:0;padding:0;box-sizing:border-box;font-family:'DM Sans',sans-serif;}
body{background:#f4f6f8;}.layout{display:flex;min-height:100vh;}
.sidebar{width:240px;background:#1a3c5e;color:white;padding:28px 0;flex-shrink:0;}
.brand{font-size:22px;font-weight:700;padding:0 24px 24px;border-bottom:1px solid rgba(255,255,255,.15);}
.sidebar ul{list-style:none;padding:20px 0;}
.sidebar ul li a{display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.75);text-decoration:none;padding:12px 24px;font-size:14px;}
.sidebar ul li a:hover,.active{background:rgba(255,255,255,.12)!important;color:white!important;}
.sidebar ul li a i{width:18px;}.main{flex:1;padding:32px;}
.page-title{font-size:22px;color:#1a3c5e;margin-bottom:20px;font-weight:600;}
.card{background:white;border-radius:12px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,.06);}
.card h2{font-size:16px;color:#1a3c5e;margin-bottom:16px;}
table{width:100%;border-collapse:collapse;font-size:14px;}
th{background:#1a3c5e;color:white;padding:10px 12px;text-align:left;}
td{padding:10px 12px;border-bottom:1px solid #f0f0f0;}
tr:hover td{background:#fafafa;}
.badge{background:#d4edda;color:#155724;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;}
</style></head><body>
<div class="layout">
  <aside class="sidebar">
    <div class="brand">✈ WanderLust</div>
    <ul>
      <li><a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
      <li><a href="destinations.php"><i class="fa-solid fa-map-pin"></i> Destinations</a></li>
      <li><a href="enquiries.php"><i class="fa-solid fa-envelope"></i> Enquiries</a></li>
      <li><a href="users.php"><i class="fa-solid fa-users"></i> Users</a></li>
      <li><a href="bulk_bookings.php"><i class="fa-solid fa-suitcase"></i> Bulk Bookings</a></li>
      <li><a href="ai_itineraries.php" class="active"><i class="fa-solid fa-robot"></i> AI Itineraries</a></li>
      <li><a href="../index.php" target="_blank"><i class="fa-solid fa-store"></i> View Site</a></li>
      <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
  </aside>
  <div class="main">
    <div class="page-title" style="display:flex;align-items:center;justify-content:space-between;">
      All Users
      <a href="export_excel.php?type=users" style="background:#1a3c5e;color:white;padding:9px 20px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600;">
        📥 Export Users CSV
      </a>
    </div>
    <div class="card">
      <h2>Registered Users (<?=mysqli_num_rows($users)?>)</h2>
      <table>
        <thead><tr><th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Trips Planned</th><th>Joined</th></tr></thead>
        <tbody>
        <?php while($u=mysqli_fetch_assoc($users)):?>
        <tr>
          <td><?=$u['id']?></td>
          <td><strong><?=htmlspecialchars($u['username'])?></strong></td>
          <td><?=htmlspecialchars($u['name']??'-')?></td>
          <td><?=htmlspecialchars($u['email'])?></td>
          <td><span class="badge"><?=$u['trips']?> trips</span></td>
          <td><?=date('d M Y',strtotime($u['created_at']))?></td>
        </tr>
        <?php endwhile;?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body></html>