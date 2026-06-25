/* Livelatch — Ancient Runes (Skyrim) theme
 * Counter-rotating rings of glowing Elder Futhark runes near the top of the
 * page, with embers drifting upward. Pure 2D canvas, no external libraries.
 */
(function () {
  'use strict';

  // Elder Futhark (Unicode Runic block)
  var RUNES = 'ᚠᚢᚦᚨᚱᚲᚷᚹᚺᚾᛁᛃᛇᛈᛉᛊᛏᛒᛖᛗᛚᛜᛞᛟ'.split('');

  var SkyrimTheme = {
    init: function (opts) {
      var o = Object.assign({ runeColor: '#9fd8ff', emberColor: '#ff8a3c', runeDensity: 50, emberRate: 50 }, opts || {});
      var canvas = document.getElementById('sk-canvas');
      if (!canvas || !canvas.getContext) { return; }
      var ctx = canvas.getContext('2d');

      var W, H, cx, cy, R, dpr;
      var rings = [];
      var embers = [];
      var t = 0;

      function build() {
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        W = window.innerWidth; H = window.innerHeight;
        canvas.width = W * dpr; canvas.height = H * dpr;
        canvas.style.width = W + 'px'; canvas.style.height = H + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        cx = W / 2;
        cy = Math.min(H * 0.22, 200);
        R  = Math.min(W, H) * 0.34;

        var density = Math.max(0, Math.min(100, o.runeDensity)) / 100;
        rings = [
          { r: R,        n: Math.round(18 + density * 18), sp:  0.10, size: 26, op: 0.9 },
          { r: R * 0.74, n: Math.round(12 + density * 14), sp: -0.16, size: 20, op: 0.65 },
          { r: R * 0.52, n: Math.round(9  + density * 9),  sp:  0.24, size: 16, op: 0.45 }
        ];
        rings.forEach(function (ring) {
          ring.glyphs = [];
          for (var i = 0; i < ring.n; i++) { ring.glyphs.push(RUNES[(Math.random() * RUNES.length) | 0]); }
        });

        var emberCount = Math.round((Math.max(0, Math.min(100, o.emberRate)) / 100) * 90);
        embers = [];
        for (var e = 0; e < emberCount; e++) { embers.push(spawn(true)); }
      }

      function spawn(initial) {
        return {
          x: Math.random() * W,
          y: initial ? Math.random() * H : H + 10,
          r: 0.8 + Math.random() * 2.4,
          sp: 0.3 + Math.random() * 1.1,
          dx: (Math.random() - 0.5) * 0.4,
          life: Math.random()
        };
      }

      function frame() {
        t += 0.016;
        ctx.clearRect(0, 0, W, H);

        // rune rings
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        for (var k = 0; k < rings.length; k++) {
          var ring = rings[k];
          var a0 = t * ring.sp;
          var pulse = 0.55 + 0.45 * Math.sin(t * 1.2 + k);
          ctx.font = ring.size + 'px serif';
          ctx.fillStyle = o.runeColor;
          ctx.shadowColor = o.runeColor;
          ctx.shadowBlur = 10 + pulse * 14;
          ctx.globalAlpha = ring.op * (0.5 + pulse * 0.5);
          for (var i = 0; i < ring.n; i++) {
            var a = a0 + (i / ring.n) * Math.PI * 2;
            var x = cx + Math.cos(a) * ring.r;
            var y = cy + Math.sin(a) * ring.r;
            ctx.fillText(ring.glyphs[i], x, y);
          }
        }
        ctx.globalAlpha = 1;
        ctx.shadowBlur = 0;

        // embers
        for (var e = 0; e < embers.length; e++) {
          var p = embers[e];
          p.y -= p.sp; p.x += p.dx; p.life += 0.01;
          if (p.y < -10) { embers[e] = spawn(false); continue; }
          var flick = 0.6 + 0.4 * Math.sin(p.life * 6);
          ctx.beginPath();
          ctx.fillStyle = o.emberColor;
          ctx.shadowColor = o.emberColor;
          ctx.shadowBlur = 8;
          ctx.globalAlpha = 0.7 * flick;
          ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
          ctx.fill();
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

  window.SkyrimTheme = SkyrimTheme;
}());
