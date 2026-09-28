<?php
// No vehicle table query needed — static cards are hardcoded below.
// Driver-uploaded vehicles are appended dynamically via JS from vehicles_api.php.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FLYME - Premium Vehicle Rental</title>
  <meta name="description" content="FLYME - Your premium ride-sharing and vehicle rental service. Explore a fleet of cars, vans, bikes, and more.">
  <link rel="stylesheet" href="css/style.css">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
  <header>
    <div class="navbar">
      <a href="index.php" class="logo-container">
        <img src="New folder/julm.jpg" alt="FLYME Logo" class="logo-img">
        <div class="logo">FLYME</div>
      </a>
      <nav>
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="#vehicles">Vehicles</a></li>
          <li><a href="#contact">Contact</a></li>
          <li><a href="login.html">Login / Signup</a></li>
        </ul>
      </nav>
      <i class='bx bx-menu menu-icon'></i>
    </div>
  </header>

  <section class="hero">
    <h1>Experience the Journey with <span class="highlight">FLYME</span></h1>
    <p>Your premium solution for reliable, fast, and comfortable ride-sharing and vehicle rentals. Book your next ride today.</p>
  </section>

  <main id="vehicles">
    <h2 class="section-title">Our Fleet</h2>
    <section class="events">

      <!-- ── STATIC ORIGINAL CARDS ─────────────────────────────────────── -->
      <a href="car.html" class="event">
        <img src="New folder/car.jpg" alt="Premium Sedan">
        <div class="event-content">
          <p>Premium Sedan</p>
          <span class="btn">View Details</span>
        </div>
      </a>

      <a href="bike.html" class="event">
        <img src="New folder/bike.jpg" alt="Sport Bike">
        <div class="event-content">
          <p>Sport Bike</p>
          <span class="btn">View Details</span>
        </div>
      </a>

      <a href="van.html" class="event">
        <img src="New folder/kdh.jpg" alt="Passenger Van">
        <div class="event-content">
          <p>Passenger Van (KDH)</p>
          <span class="btn">View Details</span>
        </div>
      </a>

      <a href="three-wheel.html" class="event">
        <img src="New folder/threeweel.jpg" alt="Three-Wheel">
        <div class="event-content">
          <p>Three-Wheel ABG2011</p>
          <span class="btn">View Details</span>
        </div>
      </a>

      <a href="double-cab.html" class="event">
        <img src="New folder/4.jpeg" alt="Double Cab">
        <div class="event-content">
          <p>4x4 Double Cab</p>
          <span class="btn">View Details</span>
        </div>
      </a>

      <a href="vip-van.html" class="event">
        <img src="New folder/5.jpeg" alt="VIP Executive Van">
        <div class="event-content">
          <p>VIP Executive Van</p>
          <span class="btn">View Details</span>
        </div>
      </a>

      <a href="camper-van.html" class="event">
        <img src="New folder/3.jpg" alt="Adventure Camper Van">
        <div class="event-content">
          <p>Adventure Camper Van</p>
          <span class="btn">View Details</span>
        </div>
      </a>
      <!-- ── END STATIC CARDS — dynamic driver cards appended here by JS ── -->

    </section>
  </main>


  <footer id="contact">
    <div class="footer-content">
      <h3>FLYME</h3>
      <p>Your one-stop solution for all your vehicle needs.</p>
      <ul class="socials">
        <li><a href="#" aria-label="Facebook"><i class='bx bxl-facebook'></i></a></li>
        <li><a href="#" aria-label="Twitter"><i class='bx bxl-twitter'></i></a></li>
        <li><a href="#" aria-label="Instagram"><i class='bx bxl-instagram'></i></a></li>
        <li><a href="#" aria-label="LinkedIn"><i class='bx bxl-linkedin'></i></a></li>
      </ul>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2026 FLYME. All rights reserved.</p>
    </div>
  </footer>

  <script src="js/project.js"></script>
  <script>
    // ── Append driver-uploaded vehicles to the existing fleet grid ──────────────
    (function () {
      const PLACEHOLDER = 'New folder/car.jpg';   // shown when image is missing
      const grid = document.querySelector('section.events');
      if (!grid) return;                           // safety: grid must exist

      fetch('../backend/vehicles_api.php')
        .then(res => res.json())
        .then(data => {
          if (!data.success || !data.vehicles || data.vehicles.length === 0) return;

          data.vehicles.forEach(v => {
            // ── Image src: stored as "uploads/xxx.jpg" relative to backend/ ──
            const imgSrc = v.vehicle_image
              ? '../backend/' + v.vehicle_image
              : PLACEHOLDER;

            // ── Build card using exact same structure as static cards ──────────
            const link        = document.createElement('a');
            link.href         = 'vehicle_details.html?id=' + encodeURIComponent(v.id);
            link.className    = 'event';

            const img         = document.createElement('img');
            img.src           = imgSrc;
            img.onerror       = () => { img.src = PLACEHOLDER; };

            const content     = document.createElement('div');
            content.className = 'event-content';

            // Title: uses .event-content p → gold, 1.5rem (same as static cards)
            const title       = document.createElement('p');
            title.textContent = v.vehicle_type || 'Vehicle';
            img.alt           = title.textContent;

            // Meta: small muted secondary line (plate + capacity)
            // Use a <small> so it doesn't inherit the large p style from CSS
            const meta        = document.createElement('small');
            meta.style.cssText = 'display:block;color:var(--text-muted);margin-top:-1rem;margin-bottom:1rem;font-size:0.82rem;letter-spacing:0.5px';
            meta.textContent  = 'Plate: ' + (v.plate_number || '—')
                              + '  ·  Seats: ' + (v.capacity || '—');

            const btn         = document.createElement('span');
            btn.className     = 'btn';
            btn.textContent   = 'View Details';

            content.append(title, meta, btn);
            link.append(img, content);
            grid.appendChild(link);
          });
        })
        .catch(() => {
          // API failed → static cards remain unchanged, do nothing
        });
    })();
  </script>
</body>
</html>
