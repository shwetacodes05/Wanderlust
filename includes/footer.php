<footer>
  <div class="footer-grid">
    <div>
      <div class="footer-brand">✈ WanderLust</div>
      <p class="footer-brand-text">Your AI-powered travel companion. Plan smarter, travel better, explore more.</p>
      <div class="socials">
        <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-facebook"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-twitter"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-youtube"></i></a>
      </div>
    </div>
    <div class="footer-col">
      <h4>Destinations</h4>
      <ul>
        <li><a href="destinations.php?cat=Beach">Beach Getaways</a></li>
        <li><a href="destinations.php?cat=Mountain">Mountain Trips</a></li>
        <li><a href="destinations.php?cat=Cultural">Cultural Tours</a></li>
        <li><a href="destinations.php?cat=Nature">Nature Escapes</a></li>
        <li><a href="destinations.php?cat=Spiritual">Spiritual Tours</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>AI Tools</h4>
      <ul>
        <li><a href="tools.php?tab=itinerary">AI Itinerary Planner</a></li>
        <li><a href="tools.php?tab=budget">Budget Splitter</a></li>
        <li><a href="tools.php?tab=carbon">Carbon Calculator</a></li>
        <li><a href="tools.php?tab=mood">Mood Finder</a></li>
        <li><a href="tools.php?tab=weather">Weather Info</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Company</h4>
      <ul>
        <li><a href="contact.php">Contact Us</a></li>
        <li><a href="#">About Us</a></li>
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Terms of Service</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2026 WanderLust. All rights reserved.</p>
    <p>Built with ❤️ for explorers everywhere</p>
  </div>
</footer>

<script>
// Fade-up animation on scroll
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.1 });
document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
</script>
</body>
</html>
