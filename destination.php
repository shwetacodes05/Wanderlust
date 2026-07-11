<?php
include 'db_connect.php';

$id = (int)($_GET['id'] ?? 0);

$dest = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM destinations WHERE id=$id AND is_active=1")
);

if (!$dest) {
    header("Location: destinations.php");
    exit();
}

$page_title = htmlspecialchars($dest['name']) . " - WanderLust";
include 'includes/header.php';

// ================= REVIEWS =================
$reviews = mysqli_query($conn,
    "SELECT r.*, u.username 
     FROM reviews r 
     JOIN users u ON r.user_id = u.id 
     WHERE r.destination_id = $id 
     ORDER BY r.created_at DESC 
     LIMIT 10"
);

$avg = mysqli_fetch_assoc(
    mysqli_query($conn,
        "SELECT AVG(rating) as avg, COUNT(*) as cnt 
         FROM reviews 
         WHERE destination_id = $id"
    )
);

// ================= SUBMIT REVIEW =================
$rev_msg = '';
if (isset($_POST['submit_review']) && isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $rat = (int)$_POST['rating'];
    $txt = mysqli_real_escape_string($conn, trim($_POST['review_text']));
    if ($rat >= 1 && $rat <= 5 && $txt) {
        mysqli_query($conn,
            "INSERT INTO reviews (user_id, destination_id, rating, review_text) 
             VALUES ($uid, $id, $rat, '$txt')"
        );
        header("Location: destination.php?id=$id#reviews");
        exit();
    }
}

// ================= BOOKING =================
$book_msg  = '';
$book_type = '';

if (isset($_POST['book_tour']) && isset($_SESSION['user_id'])) {
    $uid2        = (int)$_SESSION['user_id'];
    $trip_name   = mysqli_real_escape_string($conn, trim($_POST['trip_name']));
    $travel_date = mysqli_real_escape_string($conn, $_POST['travel_date']);
    $return_date = mysqli_real_escape_string($conn, $_POST['return_date']);
    $num_t       = max(1, (int)$_POST['num_travelers']);
    $tmode       = mysqli_real_escape_string($conn, $_POST['travel_mode']);

    if ($trip_name && $travel_date && $return_date && strtotime($return_date) > strtotime($travel_date)) {
        $days   = max(1, (strtotime($return_date) - strtotime($travel_date)) / 86400);
        $budget = $dest['avg_cost_per_day'] * $num_t * $days;

        mysqli_query($conn,
            "INSERT INTO bookings 
             (user_id, destination_id, trip_name, travel_date, return_date, num_travelers, budget, travel_mode, status)
             VALUES ($uid2, $id, '$trip_name', '$travel_date', '$return_date', $num_t, $budget, '$tmode', 'planned')"
        );

        $new_id = mysqli_insert_id($conn);

        // Redirect to dashboard after successful booking
        header("Location: dashboard.php?booking=success&id=$new_id");
        exit();

    } else {
        $book_msg  = '⚠️ Please fill all fields. Return date must be after travel date.';
        $book_type = 'error';
    }
}
?>

<!-- ===================== HERO ===================== -->
<div style="
    height: 70vh;
    background: url('<?= htmlspecialchars($dest['image']) ?>') center/cover;
    position: relative;
">
    <div style="position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,0.2),rgba(0,0,0,0.7));"></div>

    <div style="position:absolute;bottom:0;left:0;right:0;padding:40px 5%;">
        <span style="background:var(--accent);color:white;padding:5px 14px;border-radius:50px;font-size:13px;font-weight:600;">
            <?= htmlspecialchars($dest['category']) ?>
        </span>

        <h1 style="color:white;font-size:clamp(32px,5vw,60px);margin-top:12px;margin-bottom:8px;">
            <?= htmlspecialchars($dest['name']) ?>, <?= htmlspecialchars($dest['country']) ?>
        </h1>

        <div style="display:flex;gap:24px;flex-wrap:wrap;align-items:center;">
            <span style="color:rgba(255,255,255,0.85);font-size:16px;">
                ⭐ <?= number_format((float)$avg['avg'], 1) ?>/5 (<?= $avg['cnt'] ?> reviews)
            </span>
            <span style="color:rgba(255,255,255,0.85);font-size:16px;">
                💰 ₹<?= number_format($dest['avg_cost_per_day']) ?>/day
            </span>
        </div>
    </div>
</div>


<!-- ===================== MAIN CONTENT ===================== -->
<section class="section" style="padding-top:50px;">
    <div class="dest-detail-grid" style="display:grid;grid-template-columns:2fr 1fr;gap:40px;">


        <!-- ========== LEFT COLUMN ========== -->
        <div>

            <!-- DESCRIPTION -->
            <div style="background:white;border-radius:var(--radius);padding:32px;box-shadow:var(--shadow);margin-bottom:24px;">
                <h2 style="color:var(--primary);margin-bottom:16px;">
                    About <?= htmlspecialchars($dest['name']) ?>
                </h2>
                <p style="line-height:1.8;color:var(--text);font-size:16px;">
                    <?= htmlspecialchars($dest['description']) ?>
                </p>
                <?php if (!empty($dest['mood_tags'])): ?>
                <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;">
                    <?php foreach (explode(',', $dest['mood_tags']) as $tag): ?>
                    <span class="dest-mood-tag"><?= trim(htmlspecialchars($tag)) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- WEATHER -->
            <div style="background:white;border-radius:var(--radius);padding:32px;box-shadow:var(--shadow);margin-bottom:24px;">
                <h2 style="color:var(--primary);margin-bottom:16px;">🌤️ Current Weather</h2>
                <div id="dest-weather">
                    <button class="btn-primary"
                        onclick="loadDestWeather('<?= htmlspecialchars($dest['name']) ?>', this)">
                        Load Live Weather
                    </button>
                </div>
                <?php if (!empty($dest['best_months'])): ?>
                <p style="margin-top:14px;font-size:14px;color:var(--muted);">
                    📅 Best time to visit: <strong><?= htmlspecialchars($dest['best_months']) ?></strong>
                </p>
                <?php endif; ?>
            </div>

            <!-- AI ITINERARY -->
            <div style="background:linear-gradient(135deg,var(--primary),#2563eb);border-radius:var(--radius);padding:32px;margin-bottom:24px;">
                <h2 style="color:white;margin-bottom:8px;">
                    🤖 Get AI Itinerary for <?= htmlspecialchars($dest['name']) ?>
                </h2>
                <p style="color:rgba(255,255,255,0.8);margin-bottom:20px;">
                    Let AI plan your entire trip in seconds.
                </p>
                <a href="tools.php?tab=itinerary&dest=<?= urlencode($dest['name']) ?>"
                    class="btn-primary" style="background:var(--accent);">
                    ✨ Generate Itinerary
                </a>
            </div>

            <!-- REVIEWS -->
            <div id="reviews" style="background:white;border-radius:var(--radius);padding:32px;box-shadow:var(--shadow);">
                <h2 style="color:var(--primary);margin-bottom:20px;">⭐ Traveller Reviews</h2>

                <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" style="background:var(--light);border-radius:12px;padding:20px;margin-bottom:24px;">
                    <h4 style="color:var(--primary);margin-bottom:12px;">Write a Review</h4>
                    <div style="margin-bottom:12px;">
                        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Rating</label>
                        <select name="rating"
                            style="width:200px;padding:9px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;">
                            <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                            <option value="4">⭐⭐⭐⭐ Very Good</option>
                            <option value="3">⭐⭐⭐ Good</option>
                            <option value="2">⭐⭐ Fair</option>
                            <option value="1">⭐ Poor</option>
                        </select>
                    </div>
                    <div style="margin-bottom:12px;">
                        <textarea name="review_text" placeholder="Share your experience..." rows="3"
                            style="width:100%;padding:11px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:inherit;font-size:14px;box-sizing:border-box;"></textarea>
                    </div>
                    <button type="submit" name="submit_review" class="btn-primary"
                        style="padding:10px 24px;font-size:14px;">
                        Submit Review
                    </button>
                </form>
                <?php else: ?>
                <p style="margin-bottom:20px;font-size:14px;color:var(--muted);">
                    <a href="login.php" style="color:var(--primary);font-weight:600;">Login</a> to write a review.
                </p>
                <?php endif; ?>

                <?php if (mysqli_num_rows($reviews) === 0): ?>
                <p style="color:var(--muted);font-size:14px;">No reviews yet. Be the first!</p>
                <?php endif; ?>

                <?php while ($rev = mysqli_fetch_assoc($reviews)): ?>
                <div style="border-bottom:1px solid #f0f0f0;padding:16px 0;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <strong style="font-size:15px;"><?= htmlspecialchars($rev['username']) ?></strong>
                        <span style="color:#f59e0b;font-size:15px;">
                            <?= str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']) ?>
                        </span>
                    </div>
                    <p style="font-size:14px;color:var(--text);line-height:1.6;margin:0 0 6px;">
                        <?= htmlspecialchars($rev['review_text']) ?>
                    </p>
                    <small style="color:var(--muted);">
                        <?= date('d M Y', strtotime($rev['created_at'])) ?>
                    </small>
                </div>
                <?php endwhile; ?>
            </div>

        </div><!-- end LEFT COLUMN -->


        <!-- ========== RIGHT COLUMN ========== -->
        <div>

            <!-- QUICK INFO CARD  -->
            <div style="background:white;border-radius:var(--radius);padding:28px;box-shadow:var(--shadow);margin-bottom:20px;">
                <h3 style="color:var(--primary);margin-bottom:16px;font-size:20px;">Quick Info</h3>

                <div style="display:flex;flex-direction:column;gap:14px;">
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f0f0f0;padding-bottom:10px;">
                        <span style="color:var(--muted);font-size:14px;">Category</span>
                        <span style="font-weight:600;font-size:14px;"><?= htmlspecialchars($dest['category']) ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f0f0f0;padding-bottom:10px;">
                        <span style="color:var(--muted);font-size:14px;">Avg Cost/Day</span>
                        <span style="font-weight:600;font-size:14px;color:var(--primary);">
                            ₹<?= number_format($dest['avg_cost_per_day']) ?>
                        </span>
                    </div>
                    <?php if (!empty($dest['best_months'])): ?>
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f0f0f0;padding-bottom:10px;">
                        <span style="color:var(--muted);font-size:14px;">Best Months</span>
                        <span style="font-weight:600;font-size:13px;text-align:right;">
                            <?= htmlspecialchars($dest['best_months']) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- BOOK TRIP BUTTON — opens modal -->
                <a onclick="openBookingModal()"
                    class="btn-primary"
                    style="width:100%;text-align:center;margin-top:20px;display:block;cursor:pointer;">
                    🎫 Book Trip
                </a>

                <a href="tools.php?tab=budget" class="btn-primary"
                    style="width:100%;text-align:center;margin-top:10px;display:block;">
                    💰 Calculate Budget
                </a>
                <a href="tools.php?tab=carbon" class="btn-primary"
                    style="width:100%;text-align:center;margin-top:10px;display:block;background:var(--accent2);">
                    🌿 Carbon Calculator
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn-primary"
                    style="width:100%;text-align:center;margin-top:10px;display:block;background:#7c3aed;">
                    📅 My Trips
                </a>
                <?php else: ?>
                <a href="signup.php" class="btn-primary"
                    style="width:100%;text-align:center;margin-top:10px;display:block;background:#7c3aed;">
                    🚀 Sign Up to Save
                </a>
                <?php endif; ?>
            </div>

        </div><!-- end RIGHT COLUMN -->


    </div>
</section>


<!-- ===================== BOOKING MODAL ===================== -->
<div id="bookingModal" style="
    display:none;
    position:fixed;
    inset:0;
    z-index:9999;
    background:rgba(0,0,0,0.55);
    backdrop-filter:blur(4px);
    align-items:center;
    justify-content:center;
    padding:20px;
">
    <div style="
        background:white;
        border-radius:16px;
        padding:36px;
        width:100%;
        max-width:480px;
        max-height:90vh;
        overflow-y:auto;
        box-shadow:0 20px 60px rgba(0,0,0,0.3);
        position:relative;
        animation:modalSlideIn 0.3s ease;
    ">
        <!-- Close button -->
        <button onclick="closeBookingModal()" style="
            position:absolute;
            top:16px;
            right:16px;
            background:#f3f4f6;
            border:none;
            border-radius:50%;
            width:34px;
            height:34px;
            font-size:18px;
            cursor:pointer;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#666;
        ">✕</button>

        <h3 style="color:var(--primary);margin-bottom:4px;font-size:22px;">🎫 Book This Tour</h3>
        <p style="color:var(--muted);font-size:13px;margin-bottom:20px;">
            <?= htmlspecialchars($dest['name']) ?>, <?= htmlspecialchars($dest['country']) ?>
        </p>

        <?php if ($book_msg): ?>
        <div style="
            padding:12px 16px;
            border-radius:8px;
            margin-bottom:16px;
            font-size:14px;
            background:<?= $book_type === 'success' ? '#d4edda' : '#fff3cd' ?>;
            color:<?= $book_type === 'success' ? '#155724' : '#856404' ?>;
        ">
            <?= $book_msg ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
        <form method="POST" id="bookingForm">

            <div style="margin-bottom:13px;">
                <label style="font-size:13px;font-weight:600;color:var(--text);display:block;margin-bottom:5px;">
                    Trip Name *
                </label>
                <input type="text" name="trip_name"
                    value="<?= htmlspecialchars($dest['name']) ?> Trip"
                    required
                    style="width:100%;padding:10px 13px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:13px;">
                <label style="font-size:13px;font-weight:600;color:var(--text);display:block;margin-bottom:5px;">
                    Travel Date *
                </label>
                <input type="date" name="travel_date"
                    min="<?= date('Y-m-d') ?>"
                    required
                    style="width:100%;padding:10px 13px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:13px;">
                <label style="font-size:13px;font-weight:600;color:var(--text);display:block;margin-bottom:5px;">
                    Return Date *
                </label>
                <input type="date" name="return_date"
                    min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                    required
                    style="width:100%;padding:10px 13px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:13px;">
                <label style="font-size:13px;font-weight:600;color:var(--text);display:block;margin-bottom:5px;">
                    Number of Travelers *
                </label>
                <input type="number" name="num_travelers"
                    min="1" max="20" value="2"
                    required
                    style="width:100%;padding:10px 13px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:600;color:var(--text);display:block;margin-bottom:5px;">
                    Travel Mode
                </label>
                <select name="travel_mode"
                    style="width:100%;padding:10px 13px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:14px;box-sizing:border-box;">
                    <option value="flight">✈️ Flight</option>
                    <option value="train">🚂 Train</option>
                    <option value="car">🚗 Car</option>
                    <option value="bus">🚌 Bus</option>
                </select>
            </div>

            <p style="font-size:12px;color:var(--muted);margin-bottom:16px;background:var(--light);padding:10px 12px;border-radius:8px;">
                💡 Estimated: ₹<?= number_format($dest['avg_cost_per_day']) ?>/person/day · Final cost calculated at checkout.
            </p>

            <button type="submit" name="book_tour" class="btn-primary"
                style="width:100%;padding:14px;font-size:15px;border:none;cursor:pointer;border-radius:10px;">
                ✅ Confirm Booking
            </button>
        </form>

        <?php else: ?>
        <!-- Not logged in -->
        <div style="text-align:center;padding:20px 0;">
            <div style="font-size:48px;margin-bottom:12px;">🔐</div>
            <h4 style="color:var(--primary);margin-bottom:8px;">Login Required</h4>
            <p style="color:var(--muted);font-size:14px;margin-bottom:20px;">
                Please login to book this trip.
            </p>
            <a href="login.php?redirect=destination.php?id=<?= $id ?>"
                class="btn-primary"
                style="display:inline-block;padding:12px 32px;font-size:15px;">
                Login to Book
            </a>
        </div>
        <?php endif; ?>

    </div>
</div>


<!-- ===================== STYLES ===================== -->
<style>
@media (max-width: 768px) {
    .dest-detail-grid {
        grid-template-columns: 1fr !important;
    }
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.97);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>


<!-- ===================== SCRIPTS ===================== -->
<script>

// ---- Modal open/close ----

function openBookingModal() {
    const modal = document.getElementById('bookingModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // prevent background scroll
}

function closeBookingModal() {
    const modal = document.getElementById('bookingModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

// Close modal if user clicks outside the white box
document.getElementById('bookingModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBookingModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeBookingModal();
});

// If booking error occurred (POST came back with error), auto-open modal
<?php if ($book_msg && $book_type === 'error'): ?>
window.addEventListener('DOMContentLoaded', function() {
    openBookingModal();
});
<?php endif; ?>


// ---- Weather loader ----

async function loadDestWeather(city, btn) {
    btn.innerHTML = '⏳ Loading...';
    btn.disabled  = true;

    try {
        const res = await fetch('api/weather.php?city=' + encodeURIComponent(city));
        const d   = await res.json();

        const icons = {
            '01d':'☀️','02d':'⛅','03d':'☁️','04d':'☁️',
            '09d':'🌧️','10d':'🌦️','11d':'⛈️','13d':'❄️','50d':'🌫️'
        };
        const icon = icons[d.icon] || '🌡️';

        document.getElementById('dest-weather').innerHTML = `
            <div class="weather-card">
                <div class="weather-icon">${icon}</div>
                <div>
                    <div class="weather-temp">${d.temp}°C</div>
                    <div class="weather-desc">${d.desc}</div>
                    <div class="weather-extras">
                        <div class="weather-extra">Feels like <span>${d.feels}°C</span></div>
                        <div class="weather-extra">Humidity <span>${d.humidity}%</span></div>
                        <div class="weather-extra">Wind <span>${d.wind} km/h</span></div>
                    </div>
                </div>
            </div>
        `;
    } catch (err) {
        document.getElementById('dest-weather').innerHTML =
            '<p style="color:var(--muted);font-size:14px;">⚠️ Could not load weather data.</p>';
    }
}

// Pre-fill itinerary destination from URL param
const urlP = new URLSearchParams(window.location.search);
if (urlP.get('dest')) {
    const el = document.getElementById('it-dest');
    if (el) el.value = urlP.get('dest');
}

</script>

<?php include 'includes/footer.php'; ?>