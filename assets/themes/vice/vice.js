/* Livelatch — Vice City (GTA VI) theme
 * A synthwave sun with horizontal slits, a perspective neon grid horizon and a
 * scatter of stars. Pure 2D canvas, no external libraries.
 */
(function () {
  'use strict';

  var ViceTheme = {
    init: function (opts) {
      var o = Object.assign({ pinkColor: '#ff2e8b', cyanColor: '#23e0e0', sunColor: '#ffd23c', sunGlow: 50 }, opts || {});
      var canvas = document.getElementById('vc-canvas');
      if (!canvas || !canvas.getContext) { return; }
      var ctx = canvas.getContext('2d');
      var W, H, dpr, horizon, sunR, sunCx, sunCy, stars = [];
      var glow = Math.max(0, Math.min(100, o.sunGlow)) / 100;
      var t = 0;

      function build() {
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        W = window.innerWidth; H = window.innerHeight;
        canvas.width = W * dpr; canvas.height = H * dpr;
        canvas.style.width = W + 'px'; canvas.style.height = H + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        horizon = H * 0.62;
        sunR = Math.min(W * 0.32, 190);
        sunCx = W / 2;
        sunCy = horizon - sunR * 0.55;

        stars = [];
        var n = Math.round(W / 12);
        for (var i = 0; i < n; i++) {
          stars.push({ x: Math.random() * W, y: Math.random() * (horizon - sunR * 1.1), r: Math.random() * 1.3 + 0.2, ph: Math.random() * 6.28 });
        }
      }

      function drawSun() {
        // outer glow
        var g = ctx.createRadialGradient(sunCx, sunCy, sunR * 0.2, sunCx, sunCy, sunR * (1.7 + glow * 0.6));
        g.addColorStop(0, hexA(o.sunColor, 0.55 + glow * 0.3));
        g.addColorStop(0.5, hexA(o.pinkColor, 0.18));
        g.addColorStop(1, 'transparent');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, W, horizon);

        // sun disc (top→bottom pink→yellow gradient)
        var gd = ctx.createLinearGradient(sunCx, sunCy - sunR, sunCx, sunCy + sunR);
        gd.addColorStop(0, o.sunColor);
        gd.addColorStop(1, o.pinkColor);

        ctx.save();
        ctx.beginPath();
        ctx.arc(sunCx, sunCy, sunR, 0, Math.PI * 2);
        ctx.clip();
        ctx.fillStyle = gd;
        ctx.fillRect(sunCx - sunR, sunCy - sunR, sunR * 2, sunR * 2);

        // horizontal slits in the lower half
        ctx.fillStyle = 'rgba(20,10,36,0.9)';
        var startY = sunCy + sunR * 0.12;
        var gap = 7, h = 4, idx = 0;
        for (var y = startY; y < sunCy + sunR; y += gap) {
          h = 3 + idx * 1.1; gap = 7 + idx * 1.4; idx++;
          ctx.fillRect(sunCx - sunR, y, sunR * 2, h);
          y += idx * 1.4;
        }
        ctx.restore();
      }

      function drawGrid() {
        ctx.strokeStyle = hexA(o.cyanColor, 0.55);
        ctx.lineWidth = 1.2;
        ctx.shadowColor = o.cyanColor;
        ctx.shadowBlur = 6;

        // horizontal lines receding to horizon with motion
        var scroll = (t * 0.4) % 1;
        for (var i = 0; i < 16; i++) {
          var p = (i + scroll) / 16;
          var y = horizon + Math.pow(p, 2.2) * (H - horizon);
          ctx.globalAlpha = 1 - p * 0.6;
          ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(W, y); ctx.stroke();
        }
        // vertical converging lines
        var vanish = W / 2;
        for (var j = -10; j <= 10; j++) {
          var x = vanish + j * (W / 10);
          ctx.globalAlpha = 0.5;
          ctx.beginPath(); ctx.moveTo(vanish + j * 18, horizon); ctx.lineTo(x, H); ctx.stroke();
        }
        ctx.globalAlpha = 1;
        ctx.shadowBlur = 0;
      }

      function drawStars() {
        for (var i = 0; i < stars.length; i++) {
          var s = stars[i];
          var a = 0.35 + 0.45 * Math.abs(Math.sin(s.ph + t * 1.5));
          ctx.fillStyle = 'rgba(255,255,255,' + a.toFixed(3) + ')';
          ctx.beginPath(); ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2); ctx.fill();
        }
      }

      function frame() {
        t += 0.016;
        ctx.clearRect(0, 0, W, H);
        drawStars();
        drawSun();
        drawGrid();
        raf = requestAnimationFrame(frame);
      }

      function hexA(hex, a) {
        var c = hex.replace('#', '');
        if (c.length === 3) { c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2]; }
        var n = parseInt(c.slice(0, 6), 16);
        return 'rgba(' + ((n >> 16) & 255) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + a + ')';
      }

      var raf;
      build();
      frame();
      window.addEventListener('resize', build);
    },
  };

  window.ViceTheme = ViceTheme;
}());
