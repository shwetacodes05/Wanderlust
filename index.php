<?php
$page_title = "WanderLust - AI-Powered Tours & Travels";
include 'includes/header.php';
include 'db_connect.php';

$destinations = mysqli_query($conn, "SELECT * FROM destinations WHERE is_active=1 GROUP BY id ORDER BY id LIMIT 8");
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-tag">✨ AI-Powered Travel Planning</div>
    <h1>Explore the World<br>with <em>Smart Travel</em><br>by Your Side</h1>
    <p>Get AI-generated itineraries, real-time weather, budget breakdowns, and carbon footprint analysis — all in one place.</p>
    <div class="hero-btns">
      <a href="tools.php?tab=itinerary" class="btn-primary">🤖 Plan with AI</a>
      <a href="destinations.php" class="btn-outline">🗺️ Browse Destinations</a>
    </div>
    <div class="hero-stats">
      <div>
        <div class="hero-stat-num">50+</div>
        <div class="hero-stat-label">Destinations</div>
      </div>
      <div>
        <div class="hero-stat-num">10K+</div>
        <div class="hero-stat-label">Happy Travellers</div>
      </div>
      <div>
        <div class="hero-stat-num">AI</div>
        <div class="hero-stat-label">Itinerary Generator</div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="section">
  <div class="section-header fade-up">
    <div class="section-label">Why WanderLust</div>
    <h2 class="section-title">Travel Planning Reimagined</h2>
    <p class="section-sub">We use cutting-edge AI and live data to make your trip planning effortless.</p>
  </div>
  <div class="features-grid ">
    <div class="feature-card fade-up">
      <div class="feature-icon">🤖</div>
      <h3>AI Itinerary Generator</h3>
      <p>Enter your destination, budget, and days — Gemini AI builds a complete day-by-day travel plan instantly.</p>
    </div>
    <div class="feature-card fade-up real-time">
      <div class="feature-icon feature-icon1">🌤️</div>
      <h3>Real-Time Weather</h3>
      <p>Live weather data for any destination. Know before you go — temperature, humidity, forecast.</p> <br>
      <!-- <button class="toolsbutton"><a href="tools.php">AI tool</a></button> -->
    </div>
    <div class="feature-card fade-up">
      <div class="feature-icon">💰</div>
      <h3>Smart Budget Splitter</h3>
      <p>Enter your total budget and the AI splits it smartly across hotels, food, transport, and activities.</p>
    </div>
    <div class="feature-card fade-up">
      <div class="feature-icon">🌿</div>
      <h3>Carbon Footprint</h3>
      <p>Calculate your trip's CO₂ impact and get eco-friendly alternatives to travel greener.</p>
    </div>
    <div class="feature-card fade-up">
      <div class="feature-icon">😊</div>
      <h3>Mood-Based Finder</h3>
      <p>Tell us your mood — Adventure, Relaxation, Romantic — and we suggest perfect matching destinations.</p>
    </div>
    
  </div>
</section>

<!-- DESTINATIONS -->
<section class="section" style="background: white; padding-top: 70px;">
  <div class="section-header fade-up">
    <div class="section-label">Popular Places</div>
    <h2 class="section-title">Trending Destinations</h2>
    <p class="section-sub">Hand-picked destinations loved by thousands of travellers.</p>
  </div>
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
          <span style="color: var(--accent); font-size:13px; font-weight:600">Explore →</span>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
  <div style="text-align:center; margin-top: 40px;">
    <a href="destinations.php" class="btn-primary">View All Destinations</a>
  </div>
</section>

<!-- MOOD SECTION -->
<section class="mood-section">
  <div class="section-label" style="color: var(--accent);">Personalize Your Trip</div>
  <h2 class="section-title" style="color:white; margin-bottom:8px;">What's Your Travel Mood?</h2>
  <p style="color:rgba(255,255,255,0.7); font-size:16px;">Pick your vibe and we'll find the perfect destination for you.</p>
  <div class="mood-grid">
    <div class="mood-card" onclick="findByMood('Adventure')">
      <div class="mood-emoji">🏔️</div>
      <h4>Adventure</h4>
      <p>Thrill & Explore</p>
    </div>
    <div class="mood-card" onclick="findByMood('Relaxation')">
      <div class="mood-emoji">🏖️</div>
      <h4>Relaxation</h4>
      <p>Chill & Unwind</p>
    </div>
    <div class="mood-card" onclick="findByMood('Romantic')">
      <div class="mood-emoji">💕</div>
      <h4>Romantic</h4>
      <p>Love & Escape</p>
    </div>
    <div class="mood-card" onclick="findByMood('Family')">
      <div class="mood-emoji">👨‍👩‍👧</div>
      <h4>Family</h4>
      <p>Fun Together</p>
    </div>
    <div class="mood-card" onclick="findByMood('Budget')">
      <div class="mood-emoji">💸</div>
      <h4>Budget</h4>
      <p>More for Less</p>
    </div>
    <div class="mood-card" onclick="findByMood('Spiritual')">
      <div class="mood-emoji">🕌</div>
      <h4>Spiritual</h4>
      <p>Peace & Soul</p>
    </div>
  </div>
  <div id="mood-results">
    <h2 class="section-title" id="mood-title" style="color:white; margin-bottom:24px;"></h2>
    <div class="destinations-grid" id="mood-dest-grid"></div>
  </div>
</section>

<!-- CTA -->
<section class="section" style="background: var(--light); text-align: center;">
  <div class="fade-up">
    <div class="section-label" style="justify-content:center">Start Today</div>
    <h2 class="section-title" style="text-align:center">Ready to Plan Your Dream Trip?</h2>
    <p style="color:var(--muted); max-width:500px; margin:12px auto 32px; font-size:16px;">
      Join thousands of smart travellers using AI to plan unforgettable journeys.
    </p>
    <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap;">
      <a href="signup.php" class="btn-primary">Get Started Free 🚀</a>
      <a href="tools.php" class="btn-outline" style="color:var(--primary); border-color:var(--primary)">Try AI Tools</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
function findByMood(mood) {
  document.querySelectorAll('.mood-card').forEach(c => c.classList.remove('active'));
  event.currentTarget.classList.add('active');

  fetch('api/mood.php?mood=' + encodeURIComponent(mood))
    .then(r => r.json())
    .then(data => {
      const grid = document.getElementById('mood-dest-grid');
      document.getElementById('mood-title').textContent = `${mood} Destinations For You`;
      grid.innerHTML = data.map(d => `
        <div class="dest-card" onclick="window.location='destination.php?id=${d.id}'">
          <div class="dest-card-img-wrap">
            <img src="${d.image}" alt="${d.name}" class="dest-card-img" loading="lazy">
            <span class="dest-card-badge">${d.category}</span>
          </div>
          <div class="dest-card-body">
            <h3>${d.name}, ${d.country}</h3>
            <p>${d.description.substring(0,90)}...</p>
            <div class="dest-card-footer">
              <div class="dest-price">₹${parseInt(d.avg_cost_per_day).toLocaleString()} <span>/ day</span></div>
              <span style="color:var(--accent);font-size:13px;font-weight:600">Explore →</span>
            </div>
          </div>
        </div>
      `).join('');
      document.getElementById('mood-results').style.display = 'block';
      document.getElementById('mood-results').scrollIntoView({ behavior: 'smooth' });
    });
}
</script>
