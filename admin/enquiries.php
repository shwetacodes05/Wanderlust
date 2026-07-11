<?php
session_start(); include '../db_connect.php';
if (!isAdmin()) redirect('../login.php');
if (isset($_GET['mark'])) {
    mysqli_query($conn,"UPDATE enquiries SET status='read' WHERE id=".(int)$_GET['mark']);
}
$enqs = mysqli_query($conn,"SELECT * FROM enquiries ORDER BY created_at DESC");
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Enquiries - Admin</title>
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
td{padding:10px 12px;border-bottom:1px solid #f0f0f0;vertical-align:top;}
tr:hover td{background:#fafafa;}
.b-new{background:#d4edda;color:#155724;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;}
.b-read{background:#e2e3e5;color:#555;padding:3px 10px;border-radius:20px;font-size:12px;}
.ab{padding:5px 11px;border-radius:5px;text-decoration:none;font-size:12px;background:#1a3c5e;color:white;}
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
    <div class="page-title">Contact Enquiries</div>
    <div class="card">
      <h2>All Enquiries (<?=mysqli_num_rows($enqs)?>)</h2>
      <table>
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Message</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php while($e=mysqli_fetch_assoc($enqs)):?>
        <tr>
          <td><strong><?=htmlspecialchars($e['name'])?></strong></td>
          <td><?=htmlspecialchars($e['email'])?></td>
          <td><?=htmlspecialchars($e['phone']??'-')?></td>
          <td style="max-width:220px;font-size:13px;"><?=htmlspecialchars(substr($e['message'],0,100))?>...</td>
          <td style="white-space:nowrap;font-size:13px;"><?=date('d M Y',strtotime($e['created_at']))?></td>
          <td><span class="<?=$e['status']==='new'?'b-new':'b-read'?>"><?=$e['status']?></span></td>
          <td><?php if($e['status']==='new'):?><a href="?mark=<?=$e['id']?>" class="ab">Mark Read</a><?php endif;?></td>
        </tr>
        <?php endwhile;?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body></html>
