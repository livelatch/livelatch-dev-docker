<?php
$pageTitle = "Livelatch GSAP Demo";
$year = date("Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $pageTitle ?></title>

  <style>
    :root {
      --bg: #080812;
      --card: rgba(255,255,255,0.08);
      --text: #ffffff;
      --muted: #b8b8d8;
      --accent: #6c7cff;
      --accent2: #ff4fd8;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      min-height: 100vh;
      font-family: Arial, Helvetica, sans-serif;
      background:
        radial-gradient(circle at top left, rgba(108,124,255,0.35), transparent 35%),
        radial-gradient(circle at bottom right, rgba(255,79,216,0.25), transparent 35%),
        var(--bg);
      color: var(--text);
      overflow-x: hidden;
    }

    .hero {
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 40px 20px;
      text-align: center;
      position: relative;
    }

    .badge {
      display: inline-block;
      padding: 10px 16px;
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 999px;
      color: var(--muted);
      margin-bottom: 24px;
      background: rgba(255,255,255,0.05);
      backdrop-filter: blur(12px);
    }

    h1 {
      font-size: clamp(3rem, 9vw, 8rem);
      letter-spacing: -0.08em;
      line-height: 0.9;
      margin-bottom: 24px;
    }

    .gradient-text {
      background: linear-gradient(90deg, var(--accent), var(--accent2));
      -webkit-background-clip: text;
      color: transparent;
    }

    .subtitle {
      max-width: 680px;
      margin: 0 auto 32px;
      color: var(--muted);
      font-size: 1.2rem;
      line-height: 1.6;
    }

    .buttons {
      display: flex;
      justify-content: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    .btn {
      border: none;
      padding: 14px 22px;
      border-radius: 14px;
      font-weight: bold;
      cursor: pointer;
      color: white;
      background: linear-gradient(90deg, var(--accent), var(--accent2));
      box-shadow: 0 20px 60px rgba(108,124,255,0.3);
      text-decoration: none;
    }

    .btn.secondary {
      background: var(--card);
      border: 1px solid rgba(255,255,255,0.15);
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      max-width: 1000px;
      margin: 0 auto;
      padding: 80px 20px;
    }

    .card {
      min-height: 220px;
      padding: 24px;
      border-radius: 26px;
      background: var(--card);
      border: 1px solid rgba(255,255,255,0.12);
      backdrop-filter: blur(16px);
      transform: translateY(80px);
      opacity: 0;
    }

    .card h2 {
      margin-bottom: 12px;
    }

    .card p {
      color: var(--muted);
      line-height: 1.5;
    }

    .orb {
      position: absolute;
      width: 220px;
      height: 220px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      filter: blur(20px);
      opacity: 0.45;
      z-index: -1;
    }

    .orb.one {
      top: 12%;
      left: 8%;
    }

    .orb.two {
      bottom: 12%;
      right: 8%;
    }

    footer {
      text-align: center;
      color: var(--muted);
      padding: 40px 20px;
    }
  </style>
</head>

<body>

  <main class="hero">
    <div class="orb one"></div>
    <div class="orb two"></div>

    <section>
      <div class="badge">GSAP + PHP demo page</div>

      <h1>
        Live<span class="gradient-text">latch</span>
      </h1>

      <p class="subtitle">
        A modern animated landing page template you can edit inside Dreamweaver.
        Built with plain PHP, HTML, CSS, and GSAP.
      </p>

      <div class="buttons">
        <a href="#" class="btn">Enter Alpha</a>
        <a href="#cards" class="btn secondary">See Effects</a>
      </div>
    </section>
  </main>

  <section class="cards" id="cards">
    <article class="card">
      <h2>Animated Hero</h2>
      <p>The title, badge, buttons, and background orbs animate when the page loads.</p>
    </article>

    <article class="card">
      <h2>Scroll Reveal</h2>
      <p>Cards fade and slide into view when the user scrolls down the page.</p>
    </article>

    <article class="card">
      <h2>Hover Motion</h2>
      <p>Move your mouse over cards and buttons for subtle interactive motion.</p>
    </article>
  </section>

  <footer>
    &copy; <?= $year ?> Livelatch. Built in the neon shed.
  </footer>

  <!-- GSAP CDN -->
  <script src="https://cdn.jsdelivr.net/npm/gsap@3/dist/gsap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3/dist/ScrollTrigger.min.js"></script>

  <script>
    gsap.registerPlugin(ScrollTrigger);

    gsap.from(".badge", {
      opacity: 0,
      y: -20,
      duration: 0.8,
      ease: "power3.out"
    });

    gsap.from("h1", {
      opacity: 0,
      y: 50,
      scale: 0.95,
      duration: 1.1,
      delay: 0.2,
      ease: "power4.out"
    });

    gsap.from(".subtitle", {
      opacity: 0,
      y: 30,
      duration: 0.9,
      delay: 0.5,
      ease: "power3.out"
    });

    gsap.from(".btn", {
      opacity: 0,
      y: 20,
      duration: 0.8,
      delay: 0.8,
      stagger: 0.15,
      ease: "back.out(1.7)"
    });

    gsap.to(".orb.one", {
      x: 80,
      y: 40,
      duration: 6,
      repeat: -1,
      yoyo: true,
      ease: "sine.inOut"
    });

    gsap.to(".orb.two", {
      x: -80,
      y: -40,
      duration: 7,
      repeat: -1,
      yoyo: true,
      ease: "sine.inOut"
    });

    gsap.to(".card", {
      scrollTrigger: {
        trigger: ".cards",
        start: "top 80%"
      },
      opacity: 1,
      y: 0,
      duration: 0.9,
      stagger: 0.2,
      ease: "power3.out"
    });

    document.querySelectorAll(".card, .btn").forEach((item) => {
      item.addEventListener("mouseenter", () => {
        gsap.to(item, {
          scale: 1.04,
          duration: 0.25,
          ease: "power2.out"
        });
      });

      item.addEventListener("mouseleave", () => {
        gsap.to(item, {
          scale: 1,
          duration: 0.25,
          ease: "power2.out"
        });
      });
    });
  </script>

</body>
</html>