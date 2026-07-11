<?php
$page_title = "My Trips - WanderLust";
session_start();
include 'db_connect.php';
if (!isLoggedIn()) redirect('login.php');

$uid  = (int)$_SESSION['user_id'];
$name = $_SESSION['user_name'] ?? $_SESSION['user'];

// ===== Handle booking actions =====
$action_msg = '';
if (isset($_POST['action'], $_POST['booking_id'])) {
    $bid    = (int)$_POST['booking_id'];
    $action = $_POST['action'];

    // Security: verify this booking belongs to current user
    $chk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id,status FROM bookings WHERE id=$bid AND user_id=$uid"));
    if ($chk) {
        if ($action === 'confirm') {
            mysqli_query($conn, "UPDATE bookings SET status='confirmed' WHERE id=$bid");
            $action_msg = "✅ Booking confirmed successfully!";
        } elseif ($action === 'cancel') {
            mysqli_query($conn, "UPDATE bookings SET status='cancelled' WHERE id=$bid");
            $action_msg = "❌ Booking cancelled.";
        } elseif ($action === 'reschedule') {
            $new_travel = mysqli_real_escape_string($conn, $_POST['new_travel_date']);
            $new_return = mysqli_real_escape_string($conn, $_POST['new_return_date']);
            if ($new_travel && $new_return && strtotime($new_return) > strtotime($new_travel)) {
                mysqli_query($conn, "UPDATE bookings SET travel_date='$new_travel', return_date='$new_return', status='planned' WHERE id=$bid");
                $action_msg = "📅 Trip rescheduled successfully!";
            } else {
                $action_msg = "⚠️ Invalid dates. Return must be after travel date.";
            }
        }
    }
}

$trips    = mysqli_query($conn, "SELECT b.*, d.name as dest_name, d.image as dest_img, d.avg_cost_per_day FROM bookings b LEFT JOIN destinations d ON b.destination_id=d.id WHERE b.user_id=$uid ORDER BY b.created_at DESC");
$trip_cnt = mysqli_num_rows($trips);
$plan_cnt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM bookings WHERE user_id=$uid AND status='planned'"))['c'];
$conf_cnt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM bookings WHERE user_id=$uid AND status='confirmed'"))['c'];
include 'includes/header.php';
?>
<div class="dashboard-layout">
  <aside class="dash-sidebar">
    <div class="dash-brand">✈ WanderLust</div>
    <nav class="dash-nav">
      <a href="dashboard.php" class="active"><i class="fa-solid fa-house"></i> My Dashboard</a>
      <a href="tools.php?tab=itinerary"><i class="fa-solid fa-robot"></i> AI Planner</a>
      <a href="destinations.php"><i class="fa-solid fa-map"></i> Destinations</a>
      <a href="tools.php"><i class="fa-solid fa-wand-magic-sparkles"></i> All Tools</a>
      <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
  </aside>
  <div class="dash-main">
    <div class="dash-title">Welcome back, <?= htmlspecialchars($name) ?>! 🌍</div>
    <div class="dash-sub">Manage your bookings, itineraries, and travel plans.</div>

    <?php if ($action_msg): ?>
    <div style="background:#d4edda;color:#155724;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-weight:600;">
      <?= $action_msg ?>
    </div>
    <?php endif; ?>

    <div class="dash-cards">
      <div class="dash-card">
        <div class="dash-card-icon">📅</div>
        <div><div class="dash-card-num"><?= $trip_cnt ?></div><div class="dash-card-label">Total Trips</div></div>
      </div>
      <div class="dash-card">
        <div class="dash-card-icon">✈️</div>
        <div><div class="dash-card-num"><?= $plan_cnt ?></div><div class="dash-card-label">Planned</div></div>
      </div>
      <div class="dash-card">
        <div class="dash-card-icon">✅</div>
        <div><div class="dash-card-num"><?= $conf_cnt ?></div><div class="dash-card-label">Confirmed</div></div>
      </div>
    </div>

    <div style="display:flex; gap:14px; margin-bottom:28px; flex-wrap:wrap;">
      <a href="tools.php?tab=itinerary" class="btn-primary">🤖 Generate New Itinerary</a>
      <a href="destinations.php" class="btn-primary" style="background:var(--accent2);">🗺️ Explore Destinations</a>
    </div>

    <div style="background:white; border-radius:14px; padding:28px; box-shadow:var(--shadow);">
      <h2 style="color:var(--primary); margin-bottom:20px; font-size:20px;">🎫 My Bookings</h2>

      <?php if ($trip_cnt === 0): ?>
        <div style="text-align:center; padding:40px; color:var(--muted);">
          <div style="font-size:48px; margin-bottom:12px;">🗺️</div>
          <p>No bookings yet. Explore destinations and book your first trip!</p>
          <a href="destinations.php" class="btn-primary" style="margin-top:16px; display:inline-block;">Browse Destinations</a>
        </div>
      <?php else: ?>
        <?php while ($t = mysqli_fetch_assoc($trips)):
          $status_color = [
            'planned'   => ['bg'=>'#fff3cd','color'=>'#856404'],
            'confirmed' => ['bg'=>'#d4edda','color'=>'#155724'],
            'cancelled' => ['bg'=>'#f8d7da','color'=>'#721c24'],
            'completed' => ['bg'=>'#cce5ff','color'=>'#004085'],
          ][$t['status']] ?? ['bg'=>'#e9ecef','color'=>'#495057'];

          // Calculate trip days & estimated cost
          $days = $t['travel_date'] && $t['return_date']
            ? max(1, (strtotime($t['return_date']) - strtotime($t['travel_date'])) / 86400)
            : 0;
          $est_cost = $t['budget'] ?: ($t['avg_cost_per_day'] * $t['num_travelers'] * $days);
        ?>
        <div id="booking-<?= $t['id'] ?>" style="border:1.5px solid #f0f0f0; border-radius:14px; padding:22px; margin-bottom:20px; transition:box-shadow .2s;"
          onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,0.09)'" onmouseout="this.style.boxShadow='none'">

          <!-- Booking Header -->
          <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
            <div>
              <h3 style="color:var(--primary); font-size:18px; margin-bottom:4px;"><?= htmlspecialchars($t['trip_name']) ?></h3>
              <?php if ($t['dest_name']): ?>
              <span style="font-size:13px;color:var(--muted);">📍 <?= htmlspecialchars($t['dest_name']) ?></span>
              <?php endif; ?>
            </div>
            <span style="background:<?= $status_color['bg'] ?>; color:<?= $status_color['color'] ?>;
                         padding:4px 14px; border-radius:50px; font-size:12px; font-weight:700; white-space:nowrap;">
              <?= ucfirst($t['status']) ?>
            </span>
          </div>

          <!-- Booking Details Grid -->
          <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); gap:12px; margin-bottom:16px;">
            <?php if ($t['travel_date']): ?>
            <div style="background:var(--light);padding:10px 12px;border-radius:8px;">
              <div style="font-size:11px;color:var(--muted);margin-bottom:2px;">Departure</div>
              <div style="font-size:13px;font-weight:600;">📅 <?= date('d M Y', strtotime($t['travel_date'])) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($t['return_date']): ?>
            <div style="background:var(--light);padding:10px 12px;border-radius:8px;">
              <div style="font-size:11px;color:var(--muted);margin-bottom:2px;">Return</div>
              <div style="font-size:13px;font-weight:600;">🏠 <?= date('d M Y', strtotime($t['return_date'])) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($days > 0): ?>
            <div style="background:var(--light);padding:10px 12px;border-radius:8px;">
              <div style="font-size:11px;color:var(--muted);margin-bottom:2px;">Duration</div>
              <div style="font-size:13px;font-weight:600;">🌙 <?= $days ?> night<?= $days>1?'s':'' ?></div>
            </div>
            <?php endif; ?>
            <div style="background:var(--light);padding:10px 12px;border-radius:8px;">
              <div style="font-size:11px;color:var(--muted);margin-bottom:2px;">Travelers</div>
              <div style="font-size:13px;font-weight:600;">👥 <?= $t['num_travelers'] ?></div>
            </div>
            <?php if ($t['travel_mode']): ?>
            <div style="background:var(--light);padding:10px 12px;border-radius:8px;">
              <div style="font-size:11px;color:var(--muted);margin-bottom:2px;">Mode</div>
              <div style="font-size:13px;font-weight:600;"><?= ['flight'=>'✈️ Flight','train'=>'🚂 Train','car'=>'🚗 Car','bus'=>'🚌 Bus'][$t['travel_mode']] ?? '🚀 '.$t['travel_mode'] ?></div>
            </div>
            <?php endif; ?>
            <?php if ($est_cost): ?>
            <div style="background:#e8f4fd;padding:10px 12px;border-radius:8px;">
              <div style="font-size:11px;color:var(--muted);margin-bottom:2px;">Est. Budget</div>
              <div style="font-size:13px;font-weight:700;color:var(--primary);">💰 ₹<?= number_format($est_cost) ?></div>
            </div>
            <?php endif; ?>
          </div>

          <!-- Action Buttons -->
          <?php if ($t['status'] !== 'cancelled' && $t['status'] !== 'completed'): ?>
          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
            <?php if ($t['status'] === 'planned'): ?>
            <form method="POST" style="margin:0;" onsubmit="return confirm('Confirm this booking?')">
              <input type="hidden" name="booking_id" value="<?= $t['id'] ?>">
              <input type="hidden" name="action" value="confirm">
              <button type="submit" style="background:#28a745;color:white;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                ✅ Confirm Booking
              </button>
            </form>
            <?php endif; ?>

            <button onclick="toggleReschedule(<?= $t['id'] ?>)"
              style="background:#0d6efd;color:white;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
              📅 Reschedule
            </button>

            <form method="POST" style="margin:0;" onsubmit="return confirm('Are you sure you want to cancel this booking?')">
              <input type="hidden" name="booking_id" value="<?= $t['id'] ?>">
              <input type="hidden" name="action" value="cancel">
              <button type="submit" style="background:#dc3545;color:white;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                ❌ Cancel
              </button>
            </form>

            <button onclick="printReceipt(<?= $t['id'] ?>)"
              style="background:#6c757d;color:white;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
              🧾 Receipt
            </button>
          </div>

          <!-- Reschedule Form (hidden by default) -->
          <div id="reschedule-<?= $t['id'] ?>" style="display:none;background:#f0f4ff;border-radius:10px;padding:16px;margin-bottom:14px;">
            <h4 style="color:var(--primary);margin-bottom:12px;font-size:14px;">📅 Reschedule Trip</h4>
            <form method="POST" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
              <input type="hidden" name="booking_id" value="<?= $t['id'] ?>">
              <input type="hidden" name="action" value="reschedule">
              <div>
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">New Travel Date</label>
                <input type="date" name="new_travel_date" min="<?= date('Y-m-d') ?>" value="<?= $t['travel_date'] ?>" required
                  style="padding:8px 12px;border:1.5px solid #d0d7ff;border-radius:7px;font-size:13px;">
              </div>
              <div>
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">New Return Date</label>
                <input type="date" name="new_return_date" min="<?= date('Y-m-d',strtotime('+1 day')) ?>" value="<?= $t['return_date'] ?>" required
                  style="padding:8px 12px;border:1.5px solid #d0d7ff;border-radius:7px;font-size:13px;">
              </div>
              <button type="submit" style="background:var(--primary);color:white;border:none;padding:9px 18px;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;">
                Save Dates
              </button>
              <button type="button" onclick="toggleReschedule(<?= $t['id'] ?>)"
                style="background:#6c757d;color:white;border:none;padding:9px 14px;border-radius:7px;font-size:13px;cursor:pointer;">
                Cancel
              </button>
            </form>
          </div>
          <?php elseif ($t['status'] === 'cancelled'): ?>
          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
            <button onclick="printReceipt(<?= $t['id'] ?>)"
              style="background:#6c757d;color:white;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
              🧾 View Receipt
            </button>
          </div>
          <?php else: ?>
          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
            <button onclick="printReceipt(<?= $t['id'] ?>)"
              style="background:#6c757d;color:white;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
              🧾 View Receipt
            </button>
          </div>
          <?php endif; ?>

          <!-- Itinerary Section -->
          <?php if ($t['itinerary']): ?>
          <details style="margin-top:4px;">
            <summary style="cursor:pointer; color:var(--primary); font-weight:600; font-size:14px;">📄 View Itinerary</summary>
            <div style="margin-top:12px; background:var(--light); border-radius:8px; padding:16px;
                        font-size:13px; line-height:1.9; white-space:pre-line; color:var(--text);">
              <?= htmlspecialchars($t['itinerary']) ?>
            </div>
          </details>
          <?php endif; ?>

          <!-- Hidden receipt data -->
          <div id="receipt-data-<?= $t['id'] ?>" style="display:none;"
            data-id="<?= $t['id'] ?>"
            data-trip="<?= htmlspecialchars($t['trip_name'], ENT_QUOTES) ?>"
            data-dest="<?= htmlspecialchars($t['dest_name'] ?? 'N/A', ENT_QUOTES) ?>"
            data-travel="<?= $t['travel_date'] ? date('d M Y', strtotime($t['travel_date'])) : 'N/A' ?>"
            data-return="<?= $t['return_date'] ? date('d M Y', strtotime($t['return_date'])) : 'N/A' ?>"
            data-days="<?= $days ?>"
            data-travelers="<?= $t['num_travelers'] ?>"
            data-mode="<?= htmlspecialchars($t['travel_mode'] ?? '', ENT_QUOTES) ?>"
            data-budget="<?= number_format($est_cost) ?>"
            data-status="<?= ucfirst($t['status']) ?>"
            data-booked="<?= date('d M Y', strtotime($t['created_at'])) ?>"
            data-name="<?= htmlspecialchars($name, ENT_QUOTES) ?>">
          </div>

        </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Receipt Modal -->
<div id="receipt-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:white;border-radius:16px;padding:0;max-width:520px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.3);overflow:hidden;">
    <div style="background:linear-gradient(135deg,var(--primary),#2563eb);padding:24px 28px;color:white;">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
          <div style="font-size:22px;font-weight:700;">✈ WanderLust</div>
          <div style="font-size:13px;opacity:0.85;margin-top:2px;">Booking Receipt</div>
        </div>
        <button onclick="closeReceipt()" style="background:rgba(255,255,255,0.2);border:none;color:white;width:34px;height:34px;border-radius:50%;font-size:18px;cursor:pointer;line-height:34px;text-align:center;">×</button>
      </div>
    </div>
    <div id="receipt-body" style="padding:24px 28px;"></div>
    <div style="padding:0 28px 24px;display:flex;gap:10px;">
      <button onclick="window.print()" style="flex:1;background:var(--primary);color:white;border:none;padding:11px;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;">🖨️ Print</button>
      <button onclick="closeReceipt()" style="flex:1;background:#f0f0f0;color:var(--text);border:none;padding:11px;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;">Close</button>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function toggleReschedule(id) {
  const el = document.getElementById('reschedule-' + id);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function printReceipt(id) {
  const d = document.getElementById('receipt-data-' + id).dataset;
  const statusBadge = {
    'Planned':   '#fff3cd:#856404',
    'Confirmed': '#d4edda:#155724',
    'Cancelled': '#f8d7da:#721c24',
    'Completed': '#cce5ff:#004085',
  }[d.status] || '#e9ecef:#495057';
  const [sbg, scolor] = statusBadge.split(':');

  document.getElementById('receipt-body').innerHTML = `
    <div style="border:2px dashed #e0e0e0;border-radius:10px;padding:18px;margin-bottom:18px;">
      <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
        <span style="font-size:12px;color:#888;">Booking ID</span>
        <span style="font-weight:700;font-size:13px;">#WL-${String(d.id).padStart(5,'0')}</span>
      </div>
      <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
        <span style="font-size:12px;color:#888;">Status</span>
        <span style="background:${sbg};color:${scolor};padding:2px 12px;border-radius:50px;font-size:12px;font-weight:700;">${d.status}</span>
      </div>
      <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
        <span style="font-size:12px;color:#888;">Booked On</span>
        <span style="font-size:13px;font-weight:600;">${d.booked}</span>
      </div>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:9px 0;color:#888;">Guest Name</td><td style="text-align:right;font-weight:600;">${d.name}</td></tr>
      <tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:9px 0;color:#888;">Trip</td><td style="text-align:right;font-weight:600;">${d.trip}</td></tr>
      <tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:9px 0;color:#888;">Destination</td><td style="text-align:right;font-weight:600;">📍 ${d.dest}</td></tr>
      <tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:9px 0;color:#888;">Departure</td><td style="text-align:right;font-weight:600;">📅 ${d.travel}</td></tr>
      <tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:9px 0;color:#888;">Return</td><td style="text-align:right;font-weight:600;">🏠 ${d.return}</td></tr>
      <tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:9px 0;color:#888;">Duration</td><td style="text-align:right;font-weight:600;">🌙 ${d.days} nights</td></tr>
      <tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:9px 0;color:#888;">Travelers</td><td style="text-align:right;font-weight:600;">👥 ${d.travelers}</td></tr>
      <tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:9px 0;color:#888;">Travel Mode</td><td style="text-align:right;font-weight:600;">${{'flight':'✈️ Flight','train':'🚂 Train','car':'🚗 Car','bus':'🚌 Bus'}[d.mode]||d.mode}</td></tr>
      <tr><td style="padding:9px 0;color:#888;font-weight:700;">Est. Total</td><td style="text-align:right;font-weight:700;font-size:16px;color:var(--primary);">₹${d.budget}</td></tr>
    </table>
    <p style="font-size:11px;color:#aaa;text-align:center;margin-top:14px;">Thank you for choosing WanderLust! ✈️</p>
  `;
  const modal = document.getElementById('receipt-modal');
  modal.style.display = 'flex';
}

function closeReceipt() {
  document.getElementById('receipt-modal').style.display = 'none';
}

// Close modal on backdrop click
document.getElementById('receipt-modal').addEventListener('click', function(e) {
  if (e.target === this) closeReceipt();
});

// Auto-scroll to booking if anchor in URL
if (window.location.hash) {
  const el = document.querySelector(window.location.hash);
  if (el) setTimeout(() => el.scrollIntoView({behavior:'smooth', block:'start'}), 300);
}
</script>
