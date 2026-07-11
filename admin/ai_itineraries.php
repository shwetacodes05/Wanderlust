<?php
session_start();
include '../db_connect.php';
if (!isAdmin()) redirect('../login.php');

// Filters
$search        = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? 'all';
$has_ai_filter = $_GET['has_ai'] ?? 'all'; // all | yes | no

$where_parts = ["b.itinerary IS NOT NULL AND b.itinerary != ''"];

if ($status_filter !== 'all') {
    $sf = mysqli_real_escape_string($conn, $status_filter);
    $where_parts[] = "b.status = '$sf'";
}
if ($has_ai_filter === 'yes') {
    $where_parts[] = "LENGTH(b.itinerary) > 50";
} elseif ($has_ai_filter === 'no') {
    $where_parts[] = "(b.itinerary IS NULL OR LENGTH(b.itinerary) <= 50)";
    // remove the first condition since we want all
    $where_parts = array_slice($where_parts, 1);
    $where_parts[] = "(b.itinerary IS NULL OR LENGTH(b.itinerary) <= 50)";
}
if (!empty($search)) {
    $s = mysqli_real_escape_string($conn, $search);
    $where_parts[] = "(b.trip_name LIKE '%$s%' OR u.username LIKE '%$s%' OR u.email LIKE '%$s%')";
}

$where_sql = count($where_parts) ? 'WHERE ' . implode(' AND ', $where_parts) : '';

$bookings = mysqli_query($conn,
    "SELECT b.id, b.trip_name, b.budget, b.status, b.travel_date, b.num_travelers,
            b.created_at, b.itinerary,
            u.username, u.email, u.name AS user_name
     FROM bookings b
     LEFT JOIN users u ON b.user_id = u.id
     $where_sql
     ORDER BY b.created_at DESC"
);

// Counts for tabs
$total_ai    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM bookings WHERE itinerary IS NOT NULL AND LENGTH(itinerary)>50"))['c'];
$total_empty = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM bookings WHERE itinerary IS NULL OR LENGTH(itinerary)<=50"))['c'];
$total_all   = $total_ai + $total_empty;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>AI Itineraries – WanderLust Admin</title>
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
.page-title{font-size:22px;color:#1a3c5e;margin-bottom:4px;font-weight:600;}
.page-sub{color:#888;font-size:14px;margin-bottom:24px;}

/* Stats bar */
.stats-row{display:flex;gap:16px;margin-bottom:24px;flex-wrap:wrap;}
.stat-pill{background:white;border-radius:10px;padding:14px 22px;box-shadow:0 2px 8px rgba(0,0,0,.06);display:flex;align-items:center;gap:12px;}
.stat-pill .icon{width:38px;height:38px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;color:white;}
.sp-blue{background:#1a3c5e;} .sp-green{background:#2ec4b6;} .sp-orange{background:#e8a045;} .sp-red{background:#e45858;}
.stat-pill .val{font-size:20px;font-weight:700;color:#222;}
.stat-pill .lbl{font-size:12px;color:#888;}

/* Filters */
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:18px;}
.filter-btn{padding:7px 16px;border-radius:20px;border:2px solid #ddd;background:white;cursor:pointer;font-size:13px;font-weight:500;color:#555;text-decoration:none;transition:.2s;}
.filter-btn:hover,.filter-btn.active{background:#1a3c5e;color:white;border-color:#1a3c5e;}
.count-badge{background:#e45858;color:white;border-radius:50px;padding:1px 7px;font-size:11px;margin-left:4px;}
.search-box{margin-left:auto;display:flex;gap:8px;}
.search-box input{padding:8px 14px;border:1.5px solid #ddd;border-radius:8px;font-size:13px;width:220px;outline:none;}
.search-box input:focus{border-color:#1a3c5e;}
.search-box button{padding:8px 16px;background:#1a3c5e;color:white;border:none;border-radius:8px;cursor:pointer;font-size:13px;}

/* Table */
.card{background:white;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden;}
table{width:100%;border-collapse:collapse;font-size:13px;min-width:800px;}
th{background:#1a3c5e;color:white;padding:11px 13px;text-align:left;white-space:nowrap;}
td{padding:11px 13px;border-bottom:1px solid #f0f0f0;color:#444;vertical-align:middle;}
tr:hover td{background:#fafbff;}
.badge{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;}
.b-planned{background:#fff3cd;color:#856404;}
.b-confirmed{background:#d4edda;color:#155724;}
.b-completed{background:#cce5ff;color:#004085;}
.b-cancelled{background:#f8d7da;color:#721c24;}
.ai-yes{background:#d4edda;color:#155724;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;}
.ai-no{background:#f8d7da;color:#721c24;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;}
.btn-view{background:#1a3c5e;color:white;border:none;padding:6px 14px;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600;}
.btn-view:hover{background:#0f2540;}
.empty{text-align:center;padding:56px;color:#aaa;}
.empty i{font-size:40px;margin-bottom:12px;display:block;}

/* Modal */
.modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1000;align-items:center;justify-content:center;}
.modal-backdrop.open{display:flex;}
.modal{background:white;border-radius:14px;width:780px;max-width:95vw;max-height:88vh;display:flex;flex-direction:column;overflow:hidden;}
.modal-header{padding:20px 24px;background:#1a3c5e;color:white;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.modal-header h2{font-size:17px;font-weight:600;}
.modal-close{background:none;border:none;color:white;font-size:22px;cursor:pointer;line-height:1;padding:0;}
.modal-meta{padding:14px 24px;background:#f8f9fa;border-bottom:1px solid #e0e0e0;display:flex;gap:20px;flex-wrap:wrap;font-size:13px;flex-shrink:0;}
.modal-meta span{color:#555;}
.modal-meta strong{color:#1a3c5e;}
.modal-body{padding:24px;overflow-y:auto;flex:1;}
.itinerary-text{white-space:pre-wrap;font-size:14px;line-height:1.75;color:#333;background:#fafafa;padding:20px;border-radius:10px;border:1px solid #e8e8e8;font-family:'DM Sans',sans-serif;}

/* Day highlighting */
.itinerary-text .day-header{color:#1a3c5e;font-weight:700;}

/* Quality check panel */
.quality-panel{margin-top:16px;background:#fff8e6;border:1.5px solid #f0c04a;border-radius:10px;padding:16px 20px;}
.quality-panel h3{font-size:14px;font-weight:600;color:#856404;margin-bottom:10px;}
.quality-checks{display:flex;flex-direction:column;gap:7px;}
.check-item{display:flex;align-items:center;gap:8px;font-size:13px;}
.check-item .ic{width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;}
.ic-pass{background:#d4edda;color:#155724;}
.ic-fail{background:#f8d7da;color:#721c24;}
.ic-warn{background:#fff3cd;color:#856404;}
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
    <div class="page-title">AI-Generated Itineraries</div>
    <div class="page-sub">View all users' AI trips — verify content and check quality.</div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-pill">
        <div class="icon sp-blue"><i class="fa-solid fa-list"></i></div>
        <div><div class="val"><?= $total_all ?></div><div class="lbl">Total Trips</div></div>
      </div>
      <div class="stat-pill">
        <div class="icon sp-green"><i class="fa-solid fa-robot"></i></div>
        <div><div class="val"><?= $total_ai ?></div><div class="lbl">AI Generated</div></div>
      </div>
      <div class="stat-pill">
        <div class="icon sp-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div><div class="val"><?= $total_empty ?></div><div class="lbl">No Itinerary</div></div>
      </div>
      <div class="stat-pill">
        <div class="icon sp-orange"><i class="fa-solid fa-percent"></i></div>
        <div>
          <div class="val"><?= $total_all > 0 ? round(($total_ai / $total_all) * 100) : 0 ?>%</div>
          <div class="lbl">AI Coverage</div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <form method="GET">
      <div class="filter-bar">
        <!-- Status filter -->
        <?php foreach (['all'=>'All', 'planned'=>'Planned', 'confirmed'=>'Confirmed', 'completed'=>'Completed', 'cancelled'=>'Cancelled'] as $k => $label): ?>
        <a href="?status=<?=$k?>&has_ai=<?=$has_ai_filter?>&search=<?=htmlspecialchars($search)?>"
           class="filter-btn <?= $status_filter===$k?'active':''?>"><?=$label?></a>
        <?php endforeach; ?>

        <span style="color:#ccc;margin:0 4px;">|</span>

        <!-- AI filter -->
        <a href="?status=<?=$status_filter?>&has_ai=all&search=<?=htmlspecialchars($search)?>"
           class="filter-btn <?= $has_ai_filter==='all'?'active':''?>">All</a>
        <a href="?status=<?=$status_filter?>&has_ai=yes&search=<?=htmlspecialchars($search)?>"
           class="filter-btn <?= $has_ai_filter==='yes'?'active':''?>">🤖 AI Available</a>
        <a href="?status=<?=$status_filter?>&has_ai=no&search=<?=htmlspecialchars($search)?>"
           class="filter-btn <?= $has_ai_filter==='no'?'active':''?>">⚠️ No AI</a>

        <!-- Search -->
        <div class="search-box">
          <input type="text" name="search" placeholder="User or trip name..." value="<?=htmlspecialchars($search)?>">
          <input type="hidden" name="status" value="<?=$status_filter?>">
          <input type="hidden" name="has_ai" value="<?=$has_ai_filter?>">
          <button type="submit"><i class="fa-solid fa-search"></i></button>
        </div>
      </div>
    </form>

    <!-- Table -->
    <div class="card">
      <?php $count = mysqli_num_rows($bookings); ?>
      <?php if ($count === 0): ?>
        <div class="empty">
          <i class="fa-solid fa-robot"></i>
          No itinerary found matching the selected filters.
        </div>
      <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#ID</th>
            <th>Trip / User</th>
            <th>Budget</th>
            <th>Status</th>
            <th>AI Content</th>
            <th>Chars</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php while($b = mysqli_fetch_assoc($bookings)):
            $has_content = !empty($b['itinerary']) && strlen($b['itinerary']) > 50;
            $char_count  = strlen($b['itinerary'] ?? '');
            $preview     = htmlspecialchars(substr($b['itinerary'] ?? '', 0, 80));
        ?>
        <tr>
          <td><strong>#<?=$b['id']?></strong></td>
          <td>
            <strong><?=htmlspecialchars($b['trip_name'] ?: 'Unnamed Trip')?></strong><br>
            <small style="color:#888;"><?=htmlspecialchars($b['username'] ?? 'Guest')?> &nbsp;·&nbsp; <?=htmlspecialchars($b['email'] ?? '')?></small>
          </td>
          <td>₹<?=number_format($b['budget'])?></td>
          <td><span class="badge b-<?=$b['status']?>"><?=ucfirst($b['status'])?></span></td>
          <td>
            <?php if ($has_content): ?>
              <span class="ai-yes">✅ Generated</span>
            <?php else: ?>
              <span class="ai-no">❌ Not Available</span>
            <?php endif; ?>
          </td>
          <td><?= $char_count > 0 ? number_format($char_count) . ' ch' : '—' ?></td>
          <td><?= $b['created_at'] ? date('d M Y', strtotime($b['created_at'])) : '—' ?></td>
          <td>
            <?php if ($has_content): ?>
            <button class="btn-view" onclick="openModal(<?=$b['id']?>, this)">
              <i class="fa-solid fa-eye"></i> View
            </button>
            <?php else: ?>
            <span style="color:#ccc;font-size:12px;">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <!-- Hidden data for modal -->
        <tr id="data-<?=$b['id']?>" style="display:none">
          <td colspan="8">
            <span class="trip_name"><?=htmlspecialchars($b['trip_name'] ?: 'Unnamed')?></span>
            <span class="username"><?=htmlspecialchars($b['username'] ?? 'Guest')?></span>
            <span class="budget">₹<?=number_format($b['budget'])?></span>
            <span class="status"><?=ucfirst($b['status'])?></span>
            <span class="travelers"><?=$b['num_travelers']?></span>
            <span class="travel_date"><?=$b['travel_date'] ?: '—'?></span>
            <span class="itinerary"><?=htmlspecialchars($b['itinerary'] ?? '')?></span>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <div style="padding:12px 16px;font-size:13px;color:#888;border-top:1px solid #f0f0f0;">
        <?=$count?> record(s) found
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal-backdrop" id="modalBackdrop" onclick="closeIfBackdrop(event)">
  <div class="modal">
    <div class="modal-header">
      <h2 id="modalTitle">AI Itinerary Preview</h2>
      <button class="modal-close" onclick="closeModal()">×</button>
    </div>
    <div class="modal-meta" id="modalMeta"></div>
    <div class="modal-body">
      <div class="itinerary-text" id="itineraryText"></div>

      <!-- Quality check panel -->
      <div class="quality-panel" id="qualityPanel">
        <h3><i class="fa-solid fa-clipboard-check"></i> &nbsp;Quality Check</h3>
        <div class="quality-checks" id="qualityChecks"></div>
      </div>
    </div>
  </div>
</div>

<script>
function openModal(id, btn) {
    const row   = document.getElementById('data-' + id);
    const get   = cls => row.querySelector('.' + cls).textContent.trim();

    const trip      = get('trip_name');
    const user      = get('username');
    const budget    = get('budget');
    const status    = get('status');
    const travelers = get('travelers');
    const tdate     = get('travel_date');
    const itin      = get('itinerary');

    document.getElementById('modalTitle').textContent = '🗺️ ' + trip;
    document.getElementById('modalMeta').innerHTML =
        `<span>👤 <strong>${user}</strong></span>` +
        `<span>💰 <strong>${budget}</strong></span>` +
        `<span>👥 <strong>${travelers} traveler(s)</strong></span>` +
        `<span>📅 <strong>${tdate}</strong></span>` +
        `<span>📌 <strong>${status}</strong></span>` +
        `<span>📝 <strong>${itin.length.toLocaleString()} chars</strong></span>`;

    // Highlight Day headers
    const highlighted = itin.replace(/(Day\s*\d+[:\s][^\n]*)/gi,
        '<span style="color:#1a3c5e;font-weight:700;">$1</span>');
    document.getElementById('itineraryText').innerHTML = highlighted;

    // Quality checks
    runQualityChecks(itin, budget);

    document.getElementById('modalBackdrop').classList.add('open');
}

function runQualityChecks(text, budget) {
    const checks = [];
    const t = text.toLowerCase();

    // 1. Day-by-day structure
    const dayCount = (text.match(/day\s*\d+/gi) || []).length;
    checks.push({
        pass: dayCount >= 1,
        label: `Day-by-day plan present (${dayCount} day(s) found)`
    });

    // 2. Morning / Afternoon / Evening
    const hasSlots = /morning|afternoon|evening/i.test(text);
    checks.push({
        pass: hasSlots,
        label: 'Morning / Afternoon / Evening slots present'
    });

    // 3. Cost mention
    const hasCost = /cost|₹|rs\.|rupee/i.test(text);
    checks.push({
        pass: hasCost,
        label: 'Cost / budget mentioned'
    });

    // 4. Food mention
    const hasFood = /food|eat|restaurant|cafe|breakfast|lunch|dinner|cuisine/i.test(text);
    checks.push({
        pass: hasFood,
        label: 'Food recommendations present'
    });

    // 5. Length check
    const lenOk = text.length > 400;
    checks.push({
        pass: lenOk,
        warn: text.length > 100 && !lenOk,
        label: `Content length: ${text.length} chars ${lenOk ? '(good ✓)' : '(too short ⚠️)'}`
    });

    // 6. Transport tip
    const hasTransport = /transport|cab|bus|train|flight|auto|taxi|drive/i.test(text);
    checks.push({
        pass: hasTransport,
        label: 'Transport tips present'
    });

    // 7. No error message
    const isError = /error|api key|groq|failed|not set/i.test(text);
    checks.push({
        pass: !isError,
        label: isError ? '⚠️ Itinerary appears to contain an error message!' : 'No error message found (looks good)'
    });

    const html = checks.map(c => {
        const cls = c.pass ? 'ic-pass' : (c.warn ? 'ic-warn' : 'ic-fail');
        const icon = c.pass ? '✓' : (c.warn ? '!' : '✗');
        return `<div class="check-item">
            <div class="ic ${cls}">${icon}</div>
            <span style="color:${c.pass ? '#155724' : '#721c24'}">${c.label}</span>
        </div>`;
    }).join('');

    document.getElementById('qualityChecks').innerHTML = html;
}

function closeModal() {
    document.getElementById('modalBackdrop').classList.remove('open');
}
function closeIfBackdrop(e) {
    if (e.target === document.getElementById('modalBackdrop')) closeModal();
}
document.addEventListener('keydown', e => { if(e.key==='Escape') closeModal(); });
</script>
</body>
</html>