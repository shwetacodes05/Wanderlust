<?php
$page_title = "AI Travel Tools - WanderLust";
include 'includes/header.php';
include 'db_connect.php';

$active_tab = $_GET['tab'] ?? 'itinerary';
?>

<div style="paddaysg-top: 90px; background: var(--primary); min-height: 200px; display:flex; align-items:center;">
  <div style="paddaysg: 40px 5%;">
    <div class="section-label" style="color:var(--accent)">Powered by AI</div>
    <h1 style="color:white; font-size:clamp(28px,4vw,44px); margin-bottom:8px;">Smart Travel Tools</h1>
    <p style="color:rgba(255,255,255,0.7); font-size:16px;">AI Itinerary · Weather · Budget · Carbon · Mood Finder</p>
  </div>
</div>+

<section class="section">
  <div class="tools-tabs">
    <button class="tool-tab <?= $active_tab==='itinerary'?'active':'' ?>" onclick="switchTab('itinerary',this)">🤖 AI Itinerary</button>
    <button class="tool-tab <?= $active_tab==='weather'?'active':'' ?>"   onclick="switchTab('weather',this)">🌤️ Weather</button>
    <button class="tool-tab <?= $active_tab==='budget'?'active':'' ?>"    onclick="switchTab('budget',this)">💰 Budget Splitter</button>
    <button class="tool-tab <?= $active_tab==='carbon'?'active':'' ?>"    onclick="switchTab('carbon',this)">🌿 Carbon Calculator</button>
    <button class="tool-tab <?= $active_tab==='mood'?'active':'' ?>"      onclick="switchTab('mood',this)">😊 Mood Finder</button>
  </div>

  <!-- ====== AI ITINERARY ====== -->
  <div class="tool-panel <?= $active_tab==='itinerary'?'active':'' ?>" id="tab-itinerary">
    <div class="tool-card">
      <h3>🤖 AI Itinerary Generator</h3>
      <p class="sub">Powered by Google Gemini AI — enter your trip details and get a complete day-by-day plan.</p>
      <div class="form-row">
        <div class="form-group">
          <label>Destination</label>
          <input type="text" id="it-dest" placeholder="e.g. Goa, Manali, Jaipur">
        </div>
        <div class="form-group">
          <label>Number of Days</label>
          <input type="number" id="it-days" value="3" min="1" max="15">
        </div>
        <div class="form-group">
          <label>Total Budget (₹)</label>
          <input type="number" id="it-budget" value="15000" step="1000">
        </div>
        <div class="form-group">
          <label>Number of Travelers</label>
          <input type="number" id="it-travelers" value="2" min="1" max="20">
        </div>
        <div class="form-group form-full">
          <label>Interests (e.g. food, adventure, history, beaches)</label>
          <input type="text" id="it-interests" placeholder="food, beaches, adventure sports, photography">
        </div>
      </div>
      <button class="btn-primary" onclick="generateItinerary(this)">✨ Generate My Itinerary</button>
      <div class="result-box" id="it-result">
        <h4>📅 Your AI-Generated Itinerary</h4>
        <div id="it-text" style="white-space:pre-line; line-height:1.9; font-size:14px;"></div>
        <div style="margin-top:20px; display:flex; gap:12px;">
          <button class="btn-primary" onclick="window.print()" style="font-size:13px; paddaysg:9px 20px;">🖨️ Print / Save PDF</button>
          <a href="signup.php" class="btn-primary" style="font-size:13px; paddaysg:9px 20px; background:var(--accent2);">💾 Save Trip</a>
        </div>
      </div>
    </div>
  </div>

  <!-- ====== WEATHER ====== -->
  <div class="tool-panel <?= $active_tab==='weather'?'active':'' ?>" id="tab-weather">
    <div class="tool-card">
      <h3>🌤️ Real-Time Weather Checker</h3>
      <p class="sub">Get live weather and best-time-to-visit info for any Indian destination.</p>
      <div class="form-row" style="max-width:500px">
        <div class="form-group form-full">
          <label>City / Destination</label>
          <input type="text" id="w-city" placeholder="e.g. Goa, Manali, Mumbai, Jaipur">
        </div>
      </div>
      <button class="btn-primary" onclick="getWeather(this)">🔍 Check Weather</button>
      <div id="weather-result" style="display:none; margin-top:24px;">
        <div class="weather-card" id="w-card"></div>
        <div class="result-box show" id="w-advice" style="margin-top:16px;"></div>
      </div>
    </div>
  </div>

  <!-- ====== BUDGET ====== -->
  <div class="tool-panel <?= $active_tab==='budget'?'active':'' ?>" id="tab-budget">
    <div class="tool-card">
      <h3>💰 Smart Budget Splitter</h3>
      <p class="sub">Select a destination — we'll calculate the exact cost and tell you what you can afford.</p>
      <div class="form-row">
        <div class="form-group form-full">
          <label>Select Destination</label>
          <select id="b-dest" onchange="onDestOrCityChange()">
            <option value="">-- Choose a destination --</option>
            <?php
            $dest_list = mysqli_query($conn, "SELECT id, name, avg_cost_per_day, category FROM destinations WHERE is_active=1 GROUP BY id ORDER BY name");
            while ($dl = mysqli_fetch_assoc($dest_list)):
            ?>
            <option value="<?= $dl['avg_cost_per_day'] ?>" data-id="<?= $dl['id'] ?>" data-name="<?= htmlspecialchars($dl['name']) ?>" data-cat="<?= htmlspecialchars($dl['category']) ?>">
              <?= htmlspecialchars($dl['name']) ?> (<?= htmlspecialchars($dl['category']) ?>) — ₹<?= number_format($dl['avg_cost_per_day']) ?>/day
            </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group form-full">
          <label>Your City (for transport cost)</label>
          <input type="text" id="b-city" placeholder="e.g. Satara, Mumbai, Pune" onchange="onDestOrCityChange()">
          <small id="b-city-status" style="color:var(--muted);"></small>
        </div>
        <div class="form-group">
          <label>Your Total Budget (₹)</label>
          <input type="number" id="b-total" value="15000" step="500" oninput="splitBudget()">
        </div>
        <div class="form-group">
          <label>Number of Days</label>
          <input type="number" id="b-days" value="3" min="1" oninput="splitBudget()">
        </div>
        <div class="form-group">
          <label>Number of People</label>
          <input type="number" id="b-people" value="1" min="1" oninput="splitBudget()">
        </div>
        <div class="form-group">
          <label>Trip Type</label>
          <select id="b-type" onchange="splitBudget()">
            <option value="budget">Budget Travel 🎒</option>
            <option value="mid" selected>Mid-Range 🏨</option>
            <option value="luxury">Luxury ✨</option>
          </select>
        </div>
      </div>
      <div id="afford-alert" style="display:none; paddaysg:14px 18px; border-radius:10px; margin-bottom:16px; font-size:14px; font-weight:500;"></div>
      <div id="budget-output" class="result-box show" style="display:none;">
        <h4>💡 Your Smart Budget Breakdown</h4>
        <div id="dest-cost-info" style="background:rgba(26,60,94,0.07); border-radius:8px; paddaysg:12px 16px; margin-bottom:16px; font-size:13px; color:var(--primary);"></div>
        <div class="budget-breakdown" id="budget-cards"></div>
        <div style="margin-top:20px; paddaysg:16px; background:white; border-radius:10px; max-width:380px; margin-left:auto; margin-right:auto;">
          <canvas id="budget-chart" style="max-height:280px;"></canvas>
        </div>
        <p id="budget-tip" style="margin-top:14px; font-size:13px; color:var(--muted); font-style:italic;"></p>
        <div style="margin-top:20px; display:flex; gap:12px; flex-wrap:wrap;">
          <button class="btn-primary" onclick="exportBudgetCSV()" style="font-size:13px; paddaysg:9px 20px;">
            📥 Download Budget CSV
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ====== CARBON ====== -->
  <div class="tool-panel <?= $active_tab==='carbon'?'active':'' ?>" id="tab-carbon">
    <div class="tool-card">
      <h3>🌿 Carbon Footprint Calculator</h3>
      <p class="sub">Understand your trip's environmental impact and discover greener travel options.</p>
      <div class="form-row">
        <div class="form-group">
          <label>Distance (km)</label>
          <input type="number" id="c-dist" value="1000" min="1" oninput="calcCarbon()">
        </div>
        <div class="form-group">
          <label>Travel Mode</label>
          <select id="c-mode" onchange="calcCarbon()">
            <option value="flight">✈️ Flight</option>
            <option value="train" selected>🚂 Train</option>
            <option value="bus">🚌 Bus</option>
            <option value="car">🚗 Car</option>
            <option value="bike">🏍️ Bike</option>
          </select>
        </div>
        <div class="form-group">
          <label>Passengers</label>
          <input type="number" id="c-pax" value="2" min="1" oninput="calcCarbon()">
        </div>
        <div class="form-group">
          <label>Return Trip?</label>
          <select id="c-return" onchange="calcCarbon()">
            <option value="1">Yes (Round Trip)</option>
            <option value="0">No (One Way)</option>
          </select>
        </div>
      </div>
      <div class="result-box show" id="carbon-output" style="display:none;">
        <h4>🌍 Carbon Impact Report</h4>
        <div class="carbon-display">
          <div class="carbon-circle" id="c-circle">
            <div class="carbon-num">
              <strong id="c-kg">0</strong>
              <small>kg CO₂</small>
            </div>
          </div>
          <div class="carbon-tips" id="c-tips"></div>
        </div>
        <div id="c-comparison" style="margin-top:16px; font-size:14px; color:var(--muted);"></div>
      </div>
    </div>
  </div>

  <!-- ====== MOOD ====== -->
  <div class="tool-panel <?= $active_tab==='mood'?'active':'' ?>" id="tab-mood">
    <div class="tool-card">
      <h3>😊 Mood-Based Destination Finder</h3>
      <p class="sub">Not sure where to go? Tell us how you feel and we'll find the perfect match.</p>
      <div class="mood-grid" style="margin-top:24px; max-width:700px;">
        <div class="mood-card" onclick="findMood('Adventure',this)" style="background:rgba(26,60,94,0.1); border-color:#e0e0e0;">
          <div class="mood-emoji">🏔️</div><h4 style="color:var(--text)">Adventure</h4><p style="color:var(--muted)">Thrill seeker</p>
        </div>
        <div class="mood-card" onclick="findMood('Relaxation',this)" style="background:rgba(26,60,94,0.1); border-color:#e0e0e0;">
          <div class="mood-emoji">🏖️</div><h4 style="color:var(--text)">Relaxation</h4><p style="color:var(--muted)">Rest & recharge</p>
        </div>
        <div class="mood-card" onclick="findMood('Romantic',this)" style="background:rgba(26,60,94,0.1); border-color:#e0e0e0;">
          <div class="mood-emoji">💕</div><h4 style="color:var(--text)">Romantic</h4><p style="color:var(--muted)">Love & escape</p>
        </div>
        <div class="mood-card" onclick="findMood('Family',this)" style="background:rgba(26,60,94,0.1); border-color:#e0e0e0;">
          <div class="mood-emoji">👨‍👩‍👧</div><h4 style="color:var(--text)">Family</h4><p style="color:var(--muted)">Fun for all</p>
        </div>
        <div class="mood-card" onclick="findMood('Budget',this)" style="background:rgba(26,60,94,0.1); border-color:#e0e0e0;">
          <div class="mood-emoji">💸</div><h4 style="color:var(--text)">Budget</h4><p style="color:var(--muted)">More for less</p>
        </div>
        <div class="mood-card" onclick="findMood('Spiritual',this)" style="background:rgba(26,60,94,0.1); border-color:#e0e0e0;">
          <div class="mood-emoji">🕌</div><h4 style="color:var(--text)">Spiritual</h4><p style="color:var(--muted)">Peace & soul</p>
        </div>
      </div>
      <div class="result-box" id="mood-tool-result" style="margin-top:24px;">
        <h4 id="mood-tool-title"></h4>
        <div class="destinations-grid" id="mood-tool-grid"></div>
      </div>
    </div>
  </div>

</section>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Tab switcher
function switchTab(id, btn) {
  document.querySelectorAll('.tool-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tool-tab').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + id).classList.add('active');
  btn.classList.add('active');
}

// ==============================
// AI ITINERARY
// ==============================
async function generateItinerary(btn) {
  const dest      = document.getElementById('it-dest').value.trim();
  const days      = parseInt(document.getElementById('it-days').value) || 1;
  const travelers = parseInt(document.getElementById('it-travelers').value) || 1;
  const budget    = parseFloat(document.getElementById('it-budget').value) || 0;

  if (!dest) { alert('Please enter a destination!'); return; }

  // DB se real min cost fetch karo
  const checkRes = await fetch('api/check_budget.php', {
    method: 'POST',
    body: (() => { const f = new FormData(); f.append('destination', dest); return f; })()
  });
  const checkData = await checkRes.json();
  const minPerDay   = checkData.min_per_day || 1000;
  const minRequired = minPerDay * days * travelers;

  if (budget < minRequired) {
    const box = document.getElementById('it-result');
    document.getElementById('it-text').innerHTML =
      `❌ <strong>Budget too low!</strong><br><br>
       For <strong>${dest}</strong> (${days} days, ${travelers} traveler${travelers>1?'s':''}):<br>
       • Minimum required: <strong>₹${minRequired.toLocaleString()}</strong><br>
       • You entered: <strong>₹${budget.toLocaleString()}</strong><br>
       • Minimum per person/day: <strong>₹${minPerDay.toLocaleString()}</strong><br><br>
       💡 Please increase your budget or reduce days/travelers.`;
    box.classList.add('show');
    box.scrollIntoView({ behavior: 'smooth' });
    return;
  }

  btn.innerHTML = '<span class="loadaysg-spinner"></span> Generating...';
  btn.disabled = true;

  const fd = new FormData();
  fd.append('destination', dest);
  fd.append('days', document.getElementById('it-days').value);
  fd.append('budget', document.getElementById('it-budget').value);
  fd.append('travelers', document.getElementById('it-travelers').value);
  fd.append('interests', document.getElementById('it-interests').value);

  try {
    const res  = await fetch('api/itinerary.php', { method: 'POST', body: fd });
    const data = await res.json();
    const box  = document.getElementById('it-result');
    document.getElementById('it-text').textContent = data.itinerary || data.error || 'Error generating itinerary.';
    box.classList.add('show');
    box.scrollIntoView({ behavior: 'smooth' });
  } catch(e) {
    alert('Error: ' + e.message);
  }
  btn.innerHTML = '✨ Generate My Itinerary';
  btn.disabled = false;
}

// ==============================
// WEATHER
// ==============================
async function getWeather(btn) {
  const city = document.getElementById('w-city').value.trim();
  if (!city) { alert('Please enter a city!'); return; }
  btn.innerHTML = '<span class="loadaysg-spinner"></span> Checking...';
  btn.disabled = true;

  try {
    const res  = await fetch('api/weather.php?city=' + encodeURIComponent(city));
    const d    = await res.json();
    const icons = { '01d':'☀️','01n':'🌙','02d':'⛅','02n':'🌑','03d':'☁️','04d':'☁️','09d':'🌧️','10d':'🌦️','11d':'⛈️','13d':'❄️','50d':'🌫️' };
    const icon  = icons[d.icon] || '🌡️';

    document.getElementById('w-card').innerHTML = `
      <div class="weather-icon">${icon}</div>
      <div>
        <div style="font-size:13px; opacity:0.7; margin-bottom:4px">${d.name || city}</div>
        <div class="weather-temp">${d.temp}°C</div>
        <div class="weather-desc">${d.desc}</div>
        <div class="weather-extras">
          <div class="weather-extra">Feels Like<span>${d.feels}°C</span></div>
          <div class="weather-extra">Humidity<span>${d.humidity}%</span></div>
          <div class="weather-extra">Wind<span>${d.wind} km/h</span></div>
        </div>
      </div>
    `;
    document.getElementById('w-advice').innerHTML = `<h4>📅 Best Time to Visit</h4>
      <p>${d.best_months || 'October to March is generally the best season for most Indian destinations.'}</p>
      ${d.demo ? '<p style="color:var(--accent);margin-top:8px;font-size:13px;">⚠️ Demo mode — add OpenWeatherMap API key for live data.</p>' : ''}`;
    document.getElementById('weather-result').style.display = 'block';
  } catch(e) { alert('Error fetching weather.'); }
  btn.innerHTML = '🔍 Check Weather';
  btn.disabled = false;
}

// ==============================
// BUDGET SPLITTER
// ==============================
let budgetChart = null;
// "transport" % here is only the FALLBACK used when we don't yet have a
// real distance-based estimate (no city entered, or lookup failed/pending).
const splits = {
  budget: { hotel:30, food:25, transport:25, activities:10, misc:10 },
  mid:    { hotel:40, food:20, transport:20, activities:15, misc:5  },
  luxury: { hotel:50, food:15, transport:15, activities:18, misc:2  }
};
const budgetIcons  = { hotel:'🏨', food:'🍽️', transport:'🚌', activities:'🎡', misc:'🛍️' };
const budgetColors = { hotel:'#1a3c5e', food:'#e8a045', transport:'#2ec4b6', activities:'#7c3aed', misc:'#e45858' };

// Holds the real, distance-based transport estimate once fetched:
// { origin, destination, road_km, mode, transport_cost_per_person }
let transportEstimate = null;
let transportFetchToken = 0;

async function onDestOrCityChange() {
  const destSel = document.getElementById('b-dest');
  const destId  = destSel.value ? destSel.options[destSel.selectedIndex].dataset.id : null;
  const city    = document.getElementById('b-city').value.trim();
  const status  = document.getElementById('b-city-status');

  transportEstimate = null;

  if (!destId || !city) {
    status.textContent = city && !destId ? 'Select a destination too.' : (destId && !city ? 'Enter your city to get a real transport estimate — otherwise we\'ll use a rough default.' : '');
    splitBudget();
    return;
  }

  const myToken = ++transportFetchToken;
  status.textContent = '📍 Calculating distance & transport cost…';

  try {
    const fd = new FormData();
    fd.append('origin', city);
    fd.append('dest_id', destId);
    const res  = await fetch('api/transport_cost.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (myToken !== transportFetchToken) return; // a newer request superseded this one

    if (data.error) {
      status.textContent = '⚠️ ' + data.error + ' — using a rough default instead.';
      transportEstimate = null;
    } else {
      transportEstimate = data;
      status.textContent = `✅ ~${data.road_km} km from ${city} via ${data.mode} — est. ₹${data.transport_cost_per_person.toLocaleString()}/person round trip.`;
    }
  } catch (e) {
    if (myToken !== transportFetchToken) return;
    status.textContent = '⚠️ Could not reach transport estimator — using a rough default instead.';
    transportEstimate = null;
  }

  splitBudget();
}

function splitBudget() {
  const total   = parseFloat(document.getElementById('b-total').value) || 15000;
  const days    = parseInt(document.getElementById('b-days').value) || 3;
  const people  = parseInt(document.getElementById('b-people').value) || 1;
  const type    = document.getElementById('b-type').value;
  const s       = splits[type];

  const destSel        = document.getElementById('b-dest');
  const destCostPerDay = parseFloat(destSel.value) || 0;
  const destName       = destSel.value ? destSel.options[destSel.selectedIndex].dataset.name : null;
  const alertBox       = document.getElementById('afford-alert');
  const infoBox        = document.getElementById('dest-cost-info');

  // Real, distance-based transport cost if we have it; otherwise a rough
  // fallback of 20% of budget (same as before) so the tool still works
  // when no city has been entered yet.
  const realTransportPerPerson = transportEstimate ? transportEstimate.transport_cost_per_person : null;
  const transportTotal = realTransportPerPerson !== null
    ? realTransportPerPerson * people
    : Math.round(total * (s.transport / 100));

  if (destCostPerDay > 0 && destName) {
    const onGround  = destCostPerDay * days * people;
    const required  = onGround + transportTotal;
    const shortage  = required - total;
    const transportLine = realTransportPerPerson !== null
      ? ` + ₹${transportTotal.toLocaleString()} transport (${transportEstimate.road_km} km via ${transportEstimate.mode})`
      : ` + ~₹${transportTotal.toLocaleString()} transport (rough estimate — enter your city above for an accurate figure)`;

    if (shortage > 0) {
      alertBox.style.display  = 'block';
      alertBox.style.background = '#fff3cd';
      alertBox.style.border   = '1.5px solid #ffc107';
      alertBox.style.color    = '#856404';
      alertBox.innerHTML = `⚠️ <strong>${destName}</strong> requires ₹${required.toLocaleString()} (₹${onGround.toLocaleString()} on-ground${transportLine}).<br>
        Your budget is <strong>₹${shortage.toLocaleString()} short</strong>. See budget-friendly suggestions below 👇<br><br>
        <strong>💡 What you can do:</strong>
        <ul style="margin:8px 0 0 18px; line-height:1.8;">
          <li>Reduce days — you can afford roughly <strong>${Math.max(0, Math.floor((total - transportTotal) / (destCostPerDay * people)))} days</strong> on-ground once transport is covered</li>
          <li>Select Budget Travel type — reduces on-ground cost by ~30%</li>
          <li>Scroll down — see affordable alternatives</li>
        </ul>`;

      fetch('api/mood.php?mood=Budget')
        .then(r => r.json())
        .then(data => {
          const affordable = data.filter(d => (d.avg_cost_per_day * days * people) <= total);
          if (affordable.length > 0) {
            alertBox.innerHTML += `<br><strong>✅ With ₹${total.toLocaleString()} you can afford these destinations on-ground:</strong><br>` +
              affordable.map(d => `&nbsp;&nbsp;• <a href="destination.php?id=${d.id}" style="color:#1a3c5e;font-weight:600">${d.name}</a> — ₹${(d.avg_cost_per_day * days * people).toLocaleString()} total (₹${parseInt(d.avg_cost_per_day).toLocaleString()}/day, transport not included)`).join('<br>');
          }
        });
    } else {
      alertBox.style.display  = 'block';
      alertBox.style.background = '#d4edda';
      alertBox.style.border   = '1.5px solid #28a745';
      alertBox.style.color    = '#155724';
      alertBox.innerHTML = `✅ <strong>Great!</strong> ${destName} needs ₹${required.toLocaleString()} (₹${onGround.toLocaleString()} on-ground${transportLine}) — you still have <strong>₹${(total-required).toLocaleString()} extra</strong> to spare! 🎉`;
    }

    infoBox.innerHTML = `📍 <strong>${destName}</strong> — Avg cost: <strong>₹${destCostPerDay.toLocaleString()}/person/day</strong> &nbsp;|&nbsp; ${days} days on-ground: <strong>₹${onGround.toLocaleString()}</strong> (${people} people)${realTransportPerPerson !== null ? ` &nbsp;|&nbsp; Transport: <strong>₹${transportTotal.toLocaleString()}</strong>` : ''}`;
  } else {
    alertBox.style.display = 'none';
    infoBox.innerHTML = '👆 Select a destination to see real cost analysis';
  }

  let cats;
  if (realTransportPerPerson !== null) {
    // We have a real transport figure — treat it as a fixed cost, and
    // redistribute the remaining budget across the other categories using
    // their relative proportions (transport excluded).
    const remaining = Math.max(0, total - transportTotal);
    const otherKeys = Object.keys(s).filter(k => k !== 'transport');
    const otherPctSum = otherKeys.reduce((sum, k) => sum + s[k], 0);

    cats = otherKeys.map(k => {
      const share = s[k] / otherPctSum;
      const amount = Math.round(remaining * share);
      return {
        key: k, label: k.charAt(0).toUpperCase() + k.slice(1),
        amount, perPerson: Math.round(amount / people),
        pct: Math.round(share * 100)
      };
    });
    cats.push({
      key: 'transport', label: 'Transport (real)',
      amount: transportTotal, perPerson: Math.round(transportTotal / people),
      pct: total > 0 ? Math.round((transportTotal / total) * 100) : 0
    });
  } else {
    cats = Object.entries(s).map(([k, pct]) => ({
      key: k, label: k.charAt(0).toUpperCase() + k.slice(1),
      amount: Math.round(total * pct / 100),
      perPerson: Math.round((total / people) * pct / 100),
      pct
    }));
  }

  document.getElementById('budget-cards').innerHTML = cats.map(c => `
    <div class="budget-item">
      <div class="icon">${budgetIcons[c.key]}</div>
      <div class="budget-bar" style="background:${budgetColors[c.key]}; width:${c.pct}%;"></div>
      <div class="amount">₹${c.amount.toLocaleString()}
        ${people > 1 ? `<small style="color:#888;font-size:11px;display:block;">₹${c.perPerson.toLocaleString()}/person</small>` : ''}
      </div>
      <div class="label">${c.label} (${c.pct}%)</div>
    </div>
  `).join('');

  document.getElementById('budget-tip').textContent =
    `💡 ₹${Math.round(total/days).toLocaleString()} per day · ₹${Math.round(total/people).toLocaleString()} per person total`;

  if (budgetChart) budgetChart.destroy();
  budgetChart = new Chart(document.getElementById('budget-chart'), {
    type: 'doughnut',
    data: {
      labels: cats.map(c => c.label),
      datasets: [{ data: cats.map(c => c.amount), backgroundColor: Object.values(budgetColors), borderWidth: 2 }]
    },
    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'right' } } }
  });

  document.getElementById('budget-output').style.display = 'block';
}

function exportBudgetCSV() {
  const total  = parseFloat(document.getElementById('b-total').value) || 15000;
  const days   = parseInt(document.getElementById('b-days').value) || 3;
  const people = parseInt(document.getElementById('b-people').value) || 1;
  const type   = document.getElementById('b-type').value;
  const sp     = splits[type];
  const cats   = Object.keys(sp).map(k => ({
    key: k, label: k.charAt(0).toUpperCase()+k.slice(1),
    pct: sp[k], amount: Math.round(total * sp[k] / 100)
  }));
  let csv = "Category,Percentage,Total Amount (Rs.),Per Day (Rs.),Per Person (Rs.)\n";
  cats.forEach(c => {
    csv += `${c.label},${c.pct}%,${c.amount},${Math.round(c.amount/days)},${Math.round(c.amount/people)}\n`;
  });
  csv += `\nTotal,100%,${total},${Math.round(total/days)},${Math.round(total/people)}\n`;
  csv += `Trip Type,${type}\nDays,${days}\nPeople,${people}\n`;
  const blob = new Blob([csv], { type: 'text/csv' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'wanderlust_budget.csv';
  a.click();
}
splitBudget();

// ==============================
// CARBON CALCULATOR
// ==============================
const carbonFactors = { flight:0.255, train:0.041, bus:0.089, car:0.171, bike:0.103 };
const carbonTips = {
  flight: ['Consider taking a train for distances under 600km', 'Book direct flights — layovers increase emissions', 'Choose economy class over business class'],
  train:  ['Great choice! Trains emit 6x less CO₂ than flights', 'AC trains use more energy than non-AC', 'Off-peak trains are often greener'],
  bus:    ['Buses are one of the greenest travel options', 'State buses are greener than private AC coaches', 'Carpool if driving to the bus station'],
  car:    ['Share the ride to reduce per-person emissions', 'Drive at 60-80 km/h for best fuel efficiency', 'Consider renting an EV or hybrid'],
  bike:   ['Bikes are very efficient for short-medium trips', 'Maintain your bike for better mileage', 'Respect emission norms']
};

function calcCarbon() {
  const dist   = parseFloat(document.getElementById('c-dist').value) || 0;
  const mode   = document.getElementById('c-mode').value;
  const pax    = parseInt(document.getElementById('c-pax').value) || 1;
  const ret    = parseInt(document.getElementById('c-return').value);
  const factor = carbonFactors[mode];
  const total  = dist * factor * (ret === 1 ? 2 : 1);
  const perPax = (total / pax).toFixed(1);

  document.getElementById('c-kg').textContent = perPax;
  const pct = Math.min(100, (perPax / 500) * 100);
  document.getElementById('c-circle').style.setProperty('--pct', pct + '%');

  const tips = carbonTips[mode] || [];
  document.getElementById('c-tips').innerHTML =
    '<h4 style="color:var(--primary);margin-bottom:10px">🌱 Eco Tips</h4>' +
    tips.map(t => `<div class="carbon-tip">${t}</div>`).join('');

  const trees = (perPax / 21).toFixed(1);
  document.getElementById('c-comparison').innerHTML =
    `🌳 Equivalent to <strong>${trees} trees</strong> absorbing CO₂ for one year &nbsp;|&nbsp;
     Total for group: <strong>${total.toFixed(1)} kg CO₂</strong>`;

  document.getElementById('carbon-output').style.display = 'block';
}
calcCarbon();

// ==============================
// MOOD FINDER
// ==============================
async function findMood(mood, el) {
  document.querySelectorAll('#tab-mood .mood-card').forEach(c => {
    c.style.background  = 'rgba(26,60,94,0.1)';
    c.style.borderColor = '#e0e0e0';
    c.querySelector('h4').style.color = 'var(--text)';
  });
  el.style.background  = 'var(--accent)';
  el.style.borderColor = 'var(--accent)';
  el.querySelector('h4').style.color = 'white';

  const res  = await fetch('api/mood.php?mood=' + encodeURIComponent(mood));
  const data = await res.json();
  document.getElementById('mood-tool-title').textContent = `🗺️ ${mood} Destinations`;
  document.getElementById('mood-tool-grid').innerHTML = data.map(d => `
    <div class="dest-card" onclick="window.location='destination.php?id=${d.id}'" style="cursor:pointer;">
      <div class="dest-card-img-wrap">
        <img src="${d.image}" class="dest-card-img" loadaysg="lazy" alt="${d.name}">
        <span class="dest-card-badge">${d.category}</span>
      </div>
      <div class="dest-card-body">
        <h3>${d.name}</h3>
        <p>${d.description.substring(0,80)}...</p>
        <div class="dest-card-footer">
          <div class="dest-price">₹${parseInt(d.avg_cost_per_day).toLocaleString()} <span>/day</span></div>
          <span style="color:var(--accent);font-weight:600;font-size:13px">Explore →</span>
        </div>
      </div>
    </div>
  `).join('');
  document.getElementById('mood-tool-result').classList.add('show');
}
</script>