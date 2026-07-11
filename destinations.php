<?php
$page_title = "Destinations - WanderLust";
include 'includes/header.php';
include 'db_connect.php';

$cat    = mysqli_real_escape_string($conn, $_GET['cat'] ?? '');
$search = mysqli_real_escape_string($conn, $_GET['q'] ?? '');

$where = "WHERE is_active=1";
if ($cat)    $where .= " AND category='$cat'";
if ($search) $where .= " AND (name LIKE '%$search%' OR description LIKE '%$search%' OR mood_tags LIKE '%$search%')";

$destinations = mysqli_query($conn, "SELECT * FROM destinations $where GROUP BY id ORDER BY id");
$categories   = mysqli_query($conn, "SELECT DISTINCT category FROM destinations WHERE is_active=1");
?>

<div style="padding-top:90px; background:var(--primary); padding-bottom:50px;">
  <div style="padding: 40px 5% 0;">
    <div class="section-label" style="color:var(--accent)">Explore India</div>
    <h1 style="color:white; font-size:clamp(28px,4vw,44px); margin-bottom:20px;">All Destinations</h1>
    <form method="GET" style="display:flex; gap:12px; max-width:500px; flex-wrap:wrap;">
      <input type="text" name="q" value="<?= htmlspecialchars($_GET['q']??'') ?>"
             placeholder="Search destinations, moods..."
             style="flex:1; padding:12px 18px; border-radius:50px; border:none; font-size:15px; min-width:200px;">
      <button type="submit" class="btn-primary" style="padding:12px 24px;">Search</button>
    </form>
  </div>
</div>

<section class="section" style="padding-top:40px;">
  <!-- Category filters -->
  <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:32px;">
    <a href="destinations.php" class="tool-tab <?= !$cat?'active':'' ?>">All</a>
    <?php while ($c = mysqli_fetch_assoc($categories)): ?>
    <a href="destinations.php?cat=<?= urlencode($c['category']) ?>" class="tool-tab <?= $cat===$c['category']?'active':'' ?>">
      <?= htmlspecialchars($c['category']) ?>
    </a>
    <?php endwhile; ?>
  </div>

  <?php $count = mysqli_num_rows($destinations); ?>
  <p style="color:var(--muted); margin-bottom:24px; font-size:14px;"><?= $count ?> destination<?= $count!=1?'s':'' ?> found</p>

  <div class="destinations-grid">
    <?php while ($d = mysqli_fetch_assoc($destinations)): ?>
    <div class="dest-card fade-up" onclick="window.location='destination.php?id=<?= $d['id'] ?>'">
      <div class="dest-card-img-wrap">
        <img src="<?= htmlspecialchars($d['image']) ?>" alt="<?= htmlspecialchars($d['name']) ?>" class="dest-card-img" loading="lazy">
        <span class="dest-card-badge"><?= htmlspecialchars($d['category']) ?></span>
      </div>
      <div class="dest-card-body">
        <h3><?= htmlspecialchars($d['name']) ?>, <?= htmlspecialchars($d['country']) ?></h3>
        <p><?= htmlspecialchars(substr($d['description'], 0, 90)) ?>...</p>
        <div class="dest-mood-tags">
          <?php foreach (explode(',', $d['mood_tags']) as $tag): ?>
          <span class="dest-mood-tag"><?= trim($tag) ?></span>
          <?php endforeach; ?>
        </div>
        <div class="dest-card-footer">
          <div class="dest-price">₹<?= number_format($d['avg_cost_per_day']) ?> <span>/ day</span></div>
          <span style="color:var(--accent);font-size:13px;font-weight:600">View Details →</span>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>

  <?php if ($count === 0): ?>
  <div style="text-align:center; padding:60px 20px; color:var(--muted);">
    <div style="font-size:48px; margin-bottom:16px;">🔍</div>
    <h3 style="color:var(--primary)">No destinations found</h3>
    <p>Try a different search term or category.</p>
    <a href="destinations.php" class="btn-primary" style="margin-top:20px; display:inline-block;">View All</a>
  </div>
  <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
