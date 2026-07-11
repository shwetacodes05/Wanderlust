<?php
session_start();
include '../db_connect.php';
if (!isAdmin()) redirect('../login.php');

$total_users    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='user'"))['c'];
$total_dest     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM destinations"))['c'];
$total_trips    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM bookings"))['c'];
$total_enquiries= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM enquiries WHERE status='new'"))['c'];

$recent_users   = mysqli_query($conn, "SELECT * FROM users WHERE role='user' ORDER BY created_at DESC LIMIT 8");
$recent_enquiries = mysqli_query($conn, "SELECT * FROM enquiries ORDER BY created_at DESC LIMIT 8");

// Trips per day last 7 days
$daily = mysqli_query($conn, "SELECT DATE(created_at) as day, COUNT(*) as cnt FROM bookings WHERE created_at >= DATE_SUB(NOW(),INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY day ASC");
$days=[]; $counts=[];
while($r=mysqli_fetch_assoc($daily)){ $days[]=date('d M',strtotime($r['day'])); $counts[]=$r['cnt']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - WanderLust</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap');
    *{margin:0;padding:0;box-sizing:border-box;font-family:'DM Sans',sans-serif;}
    body{background:#f4f6f8;}
    .layout{display:flex;min-height:100vh;}
    .sidebar{width:240px;background:#1a3c5e;color:white;padding:28px 0;flex-shrink:0;}
    .brand{font-size:22px;font-weight:700;padding:0 24px 24px;border-bottom:1px solid rgba(255,255,255,.15);}
    .sidebar ul{list-style:none;padding:20px 0;}
    .sidebar ul li a{display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.75);text-decoration:none;padding:12px 24px;font-size:14px;transition:.2s;}
    .sidebar ul li a:hover,.sidebar ul li a.active{background:rgba(255,255,255,.12);color:white;}
    .sidebar ul li a i{width:18px;}
    .main{flex:1;padding:32px;}
    .page-title{font-size:22px;color:#1a3c5e;margin-bottom:24px;font-weight:600;}
    .stat-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:18px;margin-bottom:28px;}
    .stat-card{background:white;border-radius:12px;padding:22px;box-shadow:0 2px 10px rgba(0,0,0,.06);display:flex;align-items:center;gap:16px;}
    .stat-icon{width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;color:white;flex-shrink:0;}
    .s1{background:#1a3c5e;} .s2{background:#e8a045;} .s3{background:#2ec4b6;} .s4{background:#e45858;}
    .stat-num{font-size:28px;font-weight:700;color:#222;}
    .stat-label{font-size:13px;color:#888;}
    .card{background:white;border-radius:12px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,.06);margin-bottom:24px;}
    .card h2{font-size:16px;color:#1a3c5e;margin-bottom:16px;border-bottom:2px solid #e8a045;padding-bottom:8px;}
    table{width:100%;border-collapse:collapse;font-size:14px;}
    th{background:#1a3c5e;color:white;padding:10px 12px;text-align:left;}
    td{padding:10px 12px;border-bottom:1px solid #f0f0f0;color:#444;}
    tr:hover td{background:#fafafa;}
    .badge{padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;}
    .b-new{background:#d4edda;color:#155724;} .b-read{background:#e2e3e5;color:#383d41;}
    .row2{display:grid;grid-template-columns:3fr 2fr;gap:20px;}
    a.btn{background:#1a3c5e;color:white;padding:7px 16px;border-radius:6px;text-decoration:none;font-size:13px;}
  </style>
</head>
<body>
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
    <div class="page-title">Admin Dashboard</div>
    <div class="stat-cards">
      <div class="stat-card"><div class="stat-icon s1"><i class="fa-solid fa-users"></i></div><div><div class="stat-num"><?=$total_users?></div><div class="stat-label">Total Users</div></div></div>
      <div class="stat-card"><div class="stat-icon s2"><i class="fa-solid fa-map-pin"></i></div><div><div class="stat-num"><?=$total_dest?></div><div class="stat-label">Destinations</div></div></div>
      <div class="stat-card"><div class="stat-icon s3"><i class="fa-solid fa-suitcase"></i></div><div><div class="stat-num"><?=$total_trips?></div><div class="stat-label">Trips Planned</div></div></div>
      <div class="stat-card"><div class="stat-icon s4"><i class="fa-solid fa-envelope"></i></div><div><div class="stat-num"><?=$total_enquiries?></div><div class="stat-label">New Enquiries</div></div></div>
    </div>
    <div class="card">
      <h2>Trips Planned – Last 7 Days</h2>
      <canvas id="chart" height="80"></canvas>
    </div>
    <div class="row2">
      <div class="card">
        <h2>Recent Users &nbsp;<a href="users.php" class="btn" style="float:right;font-size:12px">View All</a></h2>
        <table>
          <thead><tr><th>Username</th><th>Email</th><th>Joined</th></tr></thead>
          <tbody>
          <?php while($u=mysqli_fetch_assoc($recent_users)):?>
          <tr><td><?=htmlspecialchars($u['username'])?></td><td><?=htmlspecialchars($u['email'])?></td><td><?=date('d M Y',strtotime($u['created_at']))?></td></tr>
          <?php endwhile;?>
          </tbody>
        </table>
      </div>
      <div class="card">
        <h2>Recent Enquiries &nbsp;<a href="enquiries.php" class="btn" style="float:right;font-size:12px">View All</a></h2>
        <table>
          <thead><tr><th>Name</th><th>Status</th></tr></thead>
          <tbody>
          <?php while($e=mysqli_fetch_assoc($recent_enquiries)):?>
          <tr><td><?=htmlspecialchars($e['name'])?><br><small style="color:#888;"><?=date('d M',strtotime($e['created_at']))?></small></td>
          <td><span class="badge <?=$e['status']==='new'?'b-new':'b-read'?>"><?=$e['status']?></span></td></tr>
          <?php endwhile;?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script>
new Chart(document.getElementById('chart'),{
  type:'line',
  data:{labels:<?=json_encode($days)?>,datasets:[{label:'Trips',data:<?=json_encode($counts)?>,borderColor:'#1a3c5e',backgroundColor:'rgba(26,60,94,.1)',tension:.4,fill:true,pointBackgroundColor:'#1a3c5e',pointRadius:5}]},
  options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
});
</script>
</body></html>