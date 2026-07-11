<?php
$page_title = "Contact Us - WanderLust";
include 'includes/header.php';
include 'db_connect.php';

$msg = '';
if (isset($_POST['submit'])) {
    $name  = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $text  = mysqli_real_escape_string($conn, trim($_POST['message']));
    if ($name && $email && $text) {
        mysqli_query($conn, "INSERT INTO enquiries (name, email, phone, message) VALUES ('$name','$email','$phone','$text')");
        $msg = 'success';
    }
}
?>
<div style="padding-top:90px; background:var(--primary); padding-bottom:60px;">
  <div style="padding: 40px 5% 0;">
    <div class="section-label" style="color:var(--accent)">Get In Touch</div>
    <h1 style="color:white; font-size:clamp(28px,4vw,44px);">Contact Us</h1>
  </div>
</div>

<section class="section">
  <div style="display:grid; grid-template-columns:1fr 1fr; gap:48px; flex-wrap:wrap;" class="contact-grid">
    <div>
      <h2 style="color:var(--primary); margin-bottom:8px;">Plan Your Dream Trip</h2>
      <p style="color:var(--muted); margin-bottom:28px;">Fill the form and our travel experts will get back to you within 24 hours.</p>
      <?php if ($msg === 'success'): ?>
        <div class="alert alert-success">✅ Message received! We'll reply within 24 hours.</div>
      <?php endif; ?>
      <div style="background:white; border-radius:var(--radius); padding:32px; box-shadow:var(--shadow);">
        <form method="POST">
          <div class="form-row">
            <div class="form-group"><label>Full Name</label><input type="text" name="name" placeholder="Your name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="you@email.com" required></div>
            <div class="form-group form-full"><label>Phone (optional)</label><input type="text" name="phone" placeholder="+91 XXXXX XXXXX"></div>
            <div class="form-group form-full">
              <label>Message</label>
              <textarea name="message" rows="5" placeholder="Tell us about your dream trip..."></textarea>
            </div>
          </div>
          <button class="btn-primary" name="submit" style="margin-top:8px;">Send Message ✉️</button>
        </form>
      </div>
    </div>
    <div>
      <h2 style="color:var(--primary); margin-bottom:24px;">Why Travel With Us?</h2>
      <?php $features = [['🤖','AI Itinerary','Gemini AI generates custom day-by-day plans'],['🌤️','Live Weather','Real-time weather for any destination'],['💰','Budget Planning','Smart budget breakdown for your trip'],['🌿','Eco Travel','Carbon footprint calculator & green tips'],['⭐','Trusted Reviews','Real reviews from real travellers']];
      foreach ($features as $f): ?>
      <div style="display:flex; gap:16px; align-items:flex-start; margin-bottom:20px;">
        <div style="width:44px;height:44px;background:linear-gradient(135deg,var(--primary),#2563eb);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
          <?= $f[0] ?>
        </div>
        <div>
          <h4 style="color:var(--primary);margin-bottom:3px;"><?= $f[1] ?></h4>
          <p style="font-size:14px;color:var(--muted);"><?= $f[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<style>@media(max-width:768px){.contact-grid{grid-template-columns:1fr !important;}}</style>
<?php include 'includes/footer.php'; ?>
