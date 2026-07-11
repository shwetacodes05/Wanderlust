<?php
session_start();
include '../db_connect.php';
if (!isAdmin()) redirect('../login.php');

$msg = '';

// Handle bulk action POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['booking_ids'])) {
    $action     = $_POST['bulk_action'] ?? '';
    $raw_ids    = $_POST['booking_ids'];
    $safe_ids   = array_map('intval', (array)$raw_ids);
    $ids_str    = implode(',', $safe_ids);

    $status_map = [
        'confirm'  => 'confirmed',
        'complete' => 'completed',
        'cancel'   => 'cancelled',
        'planned'  => 'planned',
    ];

    if (isset($status_map[$action]) && !empty($ids_str)) {
        $new_status = $status_map[$action];
        mysqli_query($conn, "UPDATE bookings SET status='$new_status' WHERE id IN ($ids_str)");
        $affected = mysqli_affected_rows($conn);
        $msg = "✅ $affected booking(s) marked as <strong>$new_status</strong>.";
    }
}

// Filters
$status_filter = $_GET['status'] ?? 'all';
$where = $status_filter !== 'all' ? "WHERE b.status='".mysqli_real_escape_string($conn,$status_filter)."'" : '';

$bookings = mysqli_query($conn,
    "SELECT b.id, b.trip_name, b.budget, b.status, b.travel_date, b.return_date,
            b.num_travelers, b.created_at,
            u.username, u.email, u.name as user_name,
            d.name as dest_name
     FROM bookings b
     LEFT JOIN users u ON b.user_id = u.id
     LEFT JOIN destinations d ON b.destination_id = d.id
     $where
     ORDER BY b.created_at DESC"
);

$counts = [];
foreach (['all','planned','confirmed','completed','cancelled'] as $s) {
    $w = $s === 'all' ? '' : "WHERE status='$s'";
    $counts[$s] = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM bookings $w"))['c'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Bulk Bookings – WanderLust Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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
.main{flex:1;padding:32px;overflow-x:auto;}
.page-title{font-size:22px;color:#1a3c5e;margin-bottom:6px;font-weight:600;}
.page-sub{color:#888;font-size:14px;margin-bottom:24px;}
.filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;}
.filter-btn{padding:7px 16px;border-radius:20px;border:2px solid #ddd;background:white;cursor:pointer;font-size:13px;font-weight:500;color:#555;text-decoration:none;transition:.2s;}
.filter-btn:hover,.filter-btn.active{background:#1a3c5e;color:white;border-color:#1a3c5e;}
.count-badge{background:#e45858;color:white;border-radius:50px;padding:1px 8px;font-size:11px;margin-left:4px;}
.toolbar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:16px;background:white;padding:14px 18px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.06);}
.toolbar select{padding:8px 14px;border:1px solid #ddd;border-radius:6px;font-size:13px;background:white;}
.btn{padding:9px 20px;border-radius:6px;border:none;cursor:pointer;font-size:13px;font-weight:600;transition:.2s;}
.btn-primary{background:#1a3c5e;color:white;} .btn-primary:hover{background:#0f2540;}
.btn-success{background:#28a745;color:white;} .btn-danger{background:#e45858;color:white;}
.btn-export{background:#e8a045;color:white;text-decoration:none;padding:9px 18px;border-radius:6px;font-size:13px;font-weight:600;}
.msg{background:#d4edda;color:#155724;padding:12px 18px;border-radius:8px;margin-bottom:18px;font-size:14px;}
.card{background:white;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden;}
table{width:100%;border-collapse:collapse;font-size:13px;min-width:900px;}
th{background:#1a3c5e;color:white;padding:11px 12px;text-align:left;white-space:nowrap;}
td{padding:11px 12px;border-bottom:1px solid #f0f0f0;color:#444;vertical-align:middle;}
tr:hover td{background:#fafbff;}
.badge{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;}
.b-planned{background:#fff3cd;color:#856404;}
.b-confirmed{background:#d4edda;color:#155724;}
.b-completed{background:#cce5ff;color:#004085;}
.b-cancelled{background:#f8d7da;color:#721c24;}
input[type=checkbox]{width:16px;height:16px;accent-color:#1a3c5e;cursor:pointer;}
.select-all-wrap{display:flex;align-items:center;gap:8px;font-size:13px;color:#555;font-weight:500;}
.empty{text-align:center;padding:48px;color:#aaa;}
.empty i{font-size:36px;margin-bottom:12px;display:block;}
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
    <div class="page-title">Bulk Booking Management</div>
    <div class="page-sub">Select multiple bookings and change their status in one click.</div>

    <?php if ($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>

    <!-- Status filter tabs -->
    <div class="filters">
      <?php foreach ($counts as $s => $c): ?>
      <a href="?status=<?= $s ?>" class="filter-btn <?= $status_filter===$s?'active':'' ?>">
        <?= ucfirst($s) ?>
        <?php if ($c > 0): ?><span class="count-badge"><?= $c ?></span><?php endif; ?>
      </a>
      <?php endforeach; ?>

      <!-- Export buttons -->
      <a href="export_excel.php?type=users" class="btn-export" style="margin-left:auto;">
        <i class="fa-solid fa-file-excel"></i> Export Users CSV
      </a>
    </div>

    <form method="POST">
      <!-- Bulk action toolbar -->
      <div class="toolbar">
        <label class="select-all-wrap">
          <input type="checkbox" id="selectAll" onchange="toggleAll(this)"> Select All
        </label>
        <select name="bulk_action" required>
          <option value="">-- Choose Action --</option>
          <option value="confirm">✅ Mark as Confirmed</option>
          <option value="complete">🏁 Mark as Completed</option>
          <option value="planned">🗓️ Mark as Planned</option>
          <option value="cancel">❌ Cancel</option>
        </select>
        <button type="submit" class="btn btn-primary" onclick="return confirmBulk()">
          <i class="fa-solid fa-bolt"></i> Apply to Selected
        </button>
        <span id="selectedCount" style="font-size:13px;color:#888;margin-left:4px;"></span>
      </div>

      <div class="card">
        <?php if (mysqli_num_rows($bookings) === 0): ?>
        <div class="empty"><i class="fa-solid fa-suitcase-rolling"></i>No bookings found.</div>
        <?php else: ?>
        <table>
          <thead>
            <tr>
              <th style="width:40px;">#</th>
              <th>Trip / Destination</th>
              <th>User</th>
              <th>Budget (Rs.)</th>
              <th>Travelers</th>
              <th>Travel Date</th>
              <th>Status</th>
              <th>Created</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($b = mysqli_fetch_assoc($bookings)): ?>
          <tr>
            <td><input type="checkbox" name="booking_ids[]" value="<?= $b['id'] ?>" class="row-cb" onchange="updateCount()"></td>
            <td>
              <strong><?= htmlspecialchars($b['trip_name'] ?: ($b['dest_name'] ?: '—')) ?></strong>
              <?php if ($b['dest_name'] && $b['trip_name']): ?>
              <div style="font-size:11px;color:#999;margin-top:2px;"><?= htmlspecialchars($b['dest_name']) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <strong><?= htmlspecialchars($b['username'] ?? '—') ?></strong>
              <div style="font-size:11px;color:#999;"><?= htmlspecialchars($b['email'] ?? '') ?></div>
            </td>
            <td><?= $b['budget'] ? 'Rs.' . number_format($b['budget']) : '—' ?></td>
            <td style="text-align:center;"><?= $b['num_travelers'] ?: 1 ?></td>
            <td><?= $b['travel_date'] ? date('d M Y', strtotime($b['travel_date'])) : '—' ?></td>
            <td><span class="badge b-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
            <td style="white-space:nowrap;"><?= date('d M Y', strtotime($b['created_at'])) ?></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<script>
function toggleAll(cb) {
    document.querySelectorAll('.row-cb').forEach(c => c.checked = cb.checked);
    updateCount();
}
function updateCount() {
    const n = document.querySelectorAll('.row-cb:checked').length;
    document.getElementById('selectedCount').textContent = n > 0 ? n + ' selected' : '';
    document.getElementById('selectAll').indeterminate =
        n > 0 && n < document.querySelectorAll('.row-cb').length;
}
function confirmBulk() {
    const n = document.querySelectorAll('.row-cb:checked').length;
    const action = document.querySelector('[name=bulk_action]').value;
    if (!n) { alert('Please select at least one booking.'); return false; }
    if (!action) { alert('Please choose an action.'); return false; }
    return confirm(`Apply "${action}" to ${n} booking(s)?`);
}
</script>
</body>
</html>
