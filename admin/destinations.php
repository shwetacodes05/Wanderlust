<?php
session_start();
include '../db_connect.php';
if (!isAdmin()) redirect('../login.php');

$msg = '';
if (isset($_POST['add'])) {
    $n  = mysqli_real_escape_string($conn,$_POST['name']);
    $co = mysqli_real_escape_string($conn,$_POST['country']);
    $d  = mysqli_real_escape_string($conn,$_POST['description']);
    $im = mysqli_real_escape_string($conn,$_POST['image']);
    $ca = mysqli_real_escape_string($conn,$_POST['category']);
    $mt = mysqli_real_escape_string($conn,$_POST['mood_tags']);
    $bm = mysqli_real_escape_string($conn,$_POST['best_months']);
    $ac = (int)$_POST['avg_cost_per_day'];
    $yt = mysqli_real_escape_string($conn,$_POST['youtube_tour_id']);
    $lat = ($_POST['latitude'] !== '') ? (float)$_POST['latitude'] : 'NULL';
    $lng = ($_POST['longitude'] !== '') ? (float)$_POST['longitude'] : 'NULL';
    $ok = mysqli_query($conn,"INSERT INTO destinations (name,country,description,image,category,mood_tags,best_months,avg_cost_per_day,latitude,longitude,youtube_tour_id) VALUES ('$n','$co','$d','$im','$ca','$mt','$bm',$ac,$lat,$lng,'$yt')");
    if ($ok) {
        $msg = "Destination added!";
    } elseif (mysqli_errno($conn) === 1062) {
        $msg = "⚠️ \"{$_POST['name']}\" already exists — edit its row below instead of adding it again.";
    } else {
        $msg = "⚠️ Could not add destination: " . mysqli_error($conn);
    }
}
if (isset($_POST['set_geo'])) {
    $gid = (int)$_POST['geo_id'];
    $lat = (float)$_POST['geo_lat'];
    $lng = (float)$_POST['geo_lng'];
    mysqli_query($conn,"UPDATE destinations SET latitude=$lat, longitude=$lng WHERE id=$gid");
    $msg = "Coordinates updated — transport cost estimates for this destination will now use real distance.";
}
if (isset($_GET['delete'])) {
    mysqli_query($conn,"DELETE FROM destinations WHERE id=".(int)$_GET['delete']);
}
if (isset($_GET['toggle'])) {
    mysqli_query($conn,"UPDATE destinations SET is_active=!is_active WHERE id=".(int)$_GET['toggle']);
}
$dests = mysqli_query($conn,"SELECT * FROM destinations ORDER BY id DESC");
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Destinations - Admin</title>
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
.msg{background:#d4edda;color:#155724;padding:12px 18px;border-radius:8px;margin-bottom:16px;}
.card{background:white;border-radius:12px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,.06);margin-bottom:24px;}
.card h2{font-size:16px;color:#1a3c5e;margin-bottom:16px;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.fg{display:flex;flex-direction:column;gap:5px;}
.fg label{font-size:12px;font-weight:600;color:#1a3c5e;}
.fg input,.fg select,.fg textarea{border:1.5px solid #e0e0e0;border-radius:8px;padding:9px 12px;font-size:14px;outline:none;font-family:inherit;}
.fg input:focus,.fg select:focus{border-color:#1a3c5e;}
.full{grid-column:1/-1;}
.btn-add{background:#1a3c5e;color:white;border:none;padding:11px 28px;border-radius:8px;font-size:15px;cursor:pointer;margin-top:8px;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{background:#1a3c5e;color:white;padding:9px 11px;text-align:left;}
td{padding:9px 11px;border-bottom:1px solid #f0f0f0;}
tr:hover td{background:#fafafa;}
.ab{padding:5px 11px;border-radius:5px;border:none;cursor:pointer;font-size:12px;font-weight:500;}
.ab-del{background:#e45858;color:white;} .ab-tog{background:#2ec4b6;color:white;} .ab-view{background:#e8a045;color:white;}
.badge-on{background:#d4edda;color:#155724;padding:2px 9px;border-radius:20px;font-size:11px;}
.badge-off{background:#e2e3e5;color:#555;padding:2px 9px;border-radius:20px;font-size:11px;}
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
      Manage Destinations
      <a href="export_excel.php?type=destinations" style="background:#1a3c5e;color:white;padding:9px 20px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600;">
        📥 Export Destinations CSV
      </a>
    </div>
    <?php if($msg): $isErr = str_starts_with($msg, '⚠️'); ?>
      <div class="msg" style="<?= $isErr ? 'background:#f8d7da;color:#721c24;' : '' ?>"><?=$msg?></div>
    <?php endif;?>
    <div class="card">
      <h2>Add New Destination</h2>
      <form method="POST">
        <div class="form-grid">
          <div class="fg"><label>Destination Name</label><input type="text" name="name" id="new-name" required placeholder="e.g. Goa"></div>
          <div class="fg"><label>Country</label><input type="text" name="country" value="India" required></div>
          <div class="fg"><label>Category</label>
            <select name="category">
              <option>Beach</option><option>Mountain</option><option>Cultural</option>
              <option>Nature</option><option>Spiritual</option><option>Adventure</option>
            </select>
          </div>
          <div class="fg"><label>Avg Cost/Day (₹)</label><input type="number" name="avg_cost_per_day" placeholder="3000"></div>
          <div class="fg full"><label>Image URL (Unsplash or your path)</label><input type="text" name="image" placeholder="https://images.unsplash.com/..."></div>
          <div class="fg full"><label>Description</label><textarea name="description" rows="3" placeholder="Describe the destination..."></textarea></div>
          <div class="fg"><label>Mood Tags (comma separated)</label><input type="text" name="mood_tags" placeholder="Adventure,Relaxation,Family"></div>
          <div class="fg"><label>Best Months to Visit</label><input type="text" name="best_months" placeholder="October,November,December"></div>
          <div class="fg"><label>Latitude</label><input type="number" step="0.000001" name="latitude" id="new-lat" placeholder="e.g. 15.2993"></div>
          <div class="fg"><label>Longitude</label><input type="number" step="0.000001" name="longitude" id="new-lng" placeholder="e.g. 74.1240"></div>
          <div class="fg"><label>&nbsp;</label><button type="button" class="ab ab-view" onclick="findCoords()" style="padding:9px;">📍 Auto-fill from name above</button></div>
        </div>
        <button class="btn-add" name="add">Add Destination</button>
      </form>
    </div>
    <div class="card">
      <h2>All Destinations</h2>
      <table>
        <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Cost/Day</th><th>Coordinates</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($d=mysqli_fetch_assoc($dests)):?>
        <tr>
          <td><?=$d['id']?></td>
          <td><strong><?=htmlspecialchars($d['name'])?></strong><br><small style="color:#888"><?=htmlspecialchars($d['country'])?></small></td>
          <td><?=htmlspecialchars($d['category'])?></td>
          <td>₹<?=number_format($d['avg_cost_per_day'])?></td>
          <td>
            <?php if ($d['latitude'] !== null && $d['longitude'] !== null): ?>
              <span style="color:#155724;font-size:12px;">✅ <?=number_format($d['latitude'],4)?>, <?=number_format($d['longitude'],4)?></span>
            <?php else: ?>
              <span style="color:#e45858;font-size:12px;">⚠️ Not set — transport cost won't work for this one</span>
            <?php endif; ?>
            <br>
            <form method="POST" style="display:flex;gap:4px;margin-top:4px;align-items:center;">
              <input type="hidden" name="geo_id" value="<?=$d['id']?>">
              <input type="number" step="0.000001" name="geo_lat" value="<?=$d['latitude']!==null?$d['latitude']:''?>" placeholder="lat" style="width:80px;font-size:11px;padding:4px;">
              <input type="number" step="0.000001" name="geo_lng" value="<?=$d['longitude']!==null?$d['longitude']:''?>" placeholder="lng" style="width:80px;font-size:11px;padding:4px;">
              <button type="button" class="ab ab-view" style="font-size:11px;" onclick="findCoordsFor('<?=htmlspecialchars($d['name'],ENT_QUOTES)?>', this)">📍</button>
              <button type="submit" name="set_geo" class="ab ab-tog" style="font-size:11px;">Save</button>
            </form>
          </td>
          <td><?=$d['is_active']?'<span class="badge-on">Active</span>':'<span class="badge-off">Inactive</span>'?></td>
          <td style="display:flex;gap:6px;flex-wrap:wrap;">
            <a href="?toggle=<?=$d['id']?>" class="ab ab-tog">Toggle</a>
            <a href="../destination.php?id=<?=$d['id']?>" target="_blank" class="ab ab-view">View</a>
            <a href="?delete=<?=$d['id']?>" class="ab ab-del" onclick="return confirm('Delete?')">Delete</a>
          </td>
        </tr>
        <?php endwhile;?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
async function findCoords() {
  const name = document.getElementById('new-name').value.trim();
  if (!name) { alert('Enter a destination name first.'); return; }
  try {
    const res = await fetch('geocode_helper.php?place=' + encodeURIComponent(name));
    const data = await res.json();
    if (data.error) { alert(data.error); return; }
    document.getElementById('new-lat').value = data.lat;
    document.getElementById('new-lng').value = data.lng;
  } catch (e) {
    alert('Lookup failed — enter coordinates manually.');
  }
}

async function findCoordsFor(name, btn) {
  const row = btn.closest('form');
  try {
    const res = await fetch('geocode_helper.php?place=' + encodeURIComponent(name));
    const data = await res.json();
    if (data.error) { alert(data.error); return; }
    row.querySelector('input[name="geo_lat"]').value = data.lat;
    row.querySelector('input[name="geo_lng"]').value = data.lng;
  } catch (e) {
    alert('Lookup failed — enter coordinates manually.');
  }
}
</script>
</body></html>