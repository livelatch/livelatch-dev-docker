/* Livelatch — J.A.R.V.I.S. HUD theme
 * Rotating reticle rings + tick scales around the arc reactor, a sweeping
 * radar scan, corner brackets and floating data motes. Pure 2D canvas.
 */
(function () {
  'use strict';

  var JarvisTheme = {
    init: function (opts) {
      var o = Object.assign({ hudColor: '#37d6ff', accentColor: '#ffcc44', ringSpeed: 50, scanRate: 50 }, opts || {});
      var canvas = document.getElementById('jv-canvas');
      if (!canvas || !canvas.getContext) { return; }
      var ctx = canvas.getContext('2d');

      var W, H, cx, cy, R, dpr;
      var motes = [];
      var spin = (Math.max(0, Math.min(100, o.ringSpeed)) / 50);
      var scan = (Math.max(0, Math.min(100, o.scanRate)) / 50);
      var t = 0;

      function build() {
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        W = window.innerWidth; H = window.innerHeight;
        canvas.width = W * dpr; canvas.height = H * dpr;
        canvas.style.width = W + 'px'; canvas.style.height = H + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        cx = W / 2;
        cy = Math.min(H * 0.27, 230);
        R  = Math.min(W * 0.42, 200);

        motes = [];
        for (var i = 0; i < 28; i++) {
          motes.push({ x: Math.random() * W, y: Math.random() * H, r: Math.random() * 1.6 + 0.4, sp: Math.random() * 0.5 + 0.1, ph: Math.random() * 6.28 });
        }
      }

      function ring(radius, segs, gap, rot, lw, alpha) {
        ctx.lineWidth = lw;
        ctx.globalAlpha = alpha;
        var step = (Math.PI * 2) / segs;
        for (var i = 0; i < segs; i++) {
          var a = rot + i * step;
          ctx.beginPath();
          ctx.arc(cx, cy, radius, a, a + step * (1 - gap));
          ctx.stroke();
        }
        ctx.globalAlpha = 1;
      }

      function ticks(radius, count, len, rot) {
        ctx.lineWidth = 1.5;
        for (var i = 0; i < count; i++) {
          var a = rot + (i / count) * Math.PI * 2;
          var x1 = cx + Math.cos(a) * radius, y1 = cy + Math.sin(a) * radius;
          var x2 = cx + Math.cos(a) * (radius + len), y2 = cy + Math.sin(a) * (radius + len);
          ctx.beginPath(); ctx.moveTo(x1, y1); ctx.lineTo(x2, y2); ctx.stroke();
        }
      }

      function bracket(x, y, sx, sy) {
        var len = 26;
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(x, y + sy * len); ctx.lineTo(x, y); ctx.lineTo(x + sx * len, y);
        ctx.stroke();
      }

      function frame() {
        t += 0.016;
        ctx.clearRect(0, 0, W, H);
        ctx.strokeStyle = o.hudColor;
        ctx.fillStyle = o.hudColor;
        ctx.shadowColor = o.hudColor;
        ctx.shadowBlur = 8;

        // concentric reticle rings
        ring(R,        3, 0.18, t * 0.3 * spin,   2,   0.8);
        ring(R * 0.86, 18, 0.45, -t * 0.5 * spin, 4,   0.35);
        ring(R * 0.68, 2, 0.30, t * 0.7 * spin,   2,   0.7);
        ticks(R * 0.52, 36, 6, t * 0.2 * spin);

        // accent inner ring
        ctx.strokeStyle = o.accentColor; ctx.shadowColor = o.accentColor;
        ring(R * 0.40, 4, 0.22, -t * 0.9 * spin, 2, 0.8);
        ctx.strokeStyle = o.hudColor; ctx.shadowColor = o.hudColor;

        // radar sweep
        var sa = t * scan * 1.1;
        var grad = ctx.createConicGradient ? null : null;
        ctx.save();
        ctx.globalAlpha = 0.5;
        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, R * 0.86, sa, sa + 0.5);
        ctx.closePath();
        var rg = ctx.createRadialGradient(cx, cy, 0, cx, cy, R * 0.86);
        rg.addColorStop(0, 'transparent');
        rg.addColorStop(1, o.hudColor);
        ctx.fillStyle = rg;
        ctx.fill();
        ctx.restore();
        ctx.fillStyle = o.hudColor;

        // corner brackets
        ctx.shadowBlur = 4; ctx.globalAlpha = 0.7;
        var m = 22;
        bracket(m, m, 1, 1);
        bracket(W - m, m, -1, 1);
        bracket(m, H - m, 1, -1);
        bracket(W - m, H - m, -1, -1);
        ctx.globalAlpha = 1;

        // floating data motes
        ctx.shadowBlur = 6;
        for (var i = 0; i < motes.length; i++) {
          var p = motes[i];
          p.y -= p.sp; p.ph += 0.03;
          if (p.y < -4) { p.y = H + 4; p.x = Math.random() * W; }
          ctx.globalAlpha = 0.3 + 0.4 * Math.abs(Math.sin(p.ph));
          ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2); ctx.fill();
        }
        ctx.globalAlpha = 1;
        ctx.shadowBlur = 0;

        raf = requestAnimationFrame(frame);
      }

      var raf;
      build();
      frame();
      window.addEventListener('resize', build);
    },
  };

  window.JarvisTheme = JarvisTheme;
}());
