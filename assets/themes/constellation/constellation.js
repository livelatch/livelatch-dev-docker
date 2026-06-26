/* Livelatch — Constellation: a drifting star network on 2D canvas. Stars wire
 * into constellations near each other and near the pointer; click adds a star. */
(function () {
  'use strict';

  function hexToRgb(h) {
    h = String(h || '').replace('#', '');
    if (h.length === 3) { h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2]; }
    var n = parseInt(h.slice(0, 6), 16);
    return ((n >> 16) & 255) + ',' + ((n >> 8) & 255) + ',' + (n & 255);
  }

  function build(o) {
    var canvas = document.getElementById('llx-canvas');
    if (!canvas || !canvas.getContext) { return; }
    var ctx = canvas.getContext('2d');
    var W, H, dpr, pts = [], mouse = { x: -999, y: -999 };
    var aRGB = hexToRgb(o.aColor || '#cfe0ff'), bRGB = hexToRgb(o.bColor || '#5b8cff');
    var dens = (o.intensity || 50) / 100, reach = (o.speed || 50) / 100, linkDist = 110;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function spawn(x, y) {
      return { x: x != null ? x : Math.random() * W, y: y != null ? y : Math.random() * H,
        vx: (Math.random() - 0.5) * 0.35, vy: (Math.random() - 0.5) * 0.35, r: Math.random() * 1.6 + 0.6 };
    }
    function resize() {
      dpr = Math.min(window.devicePixelRatio || 1, 2);
      W = window.innerWidth; H = window.innerHeight;
      canvas.width = W * dpr; canvas.height = H * dpr; canvas.style.width = W + 'px'; canvas.style.height = H + 'px';
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      linkDist = 88 + reach * 120;
      var target = Math.round((W * H / 15000) * (0.5 + dens));
      pts = []; for (var i = 0; i < target; i++) { pts.push(spawn()); }
    }
    resize(); window.addEventListener('resize', resize);
    window.addEventListener('pointermove', function (e) { mouse.x = e.clientX; mouse.y = e.clientY; }, { passive: true });
    window.addEventListener('pointerdown', function (e) { pts.push(spawn(e.clientX, e.clientY)); if (pts.length > 420) { pts.shift(); } }, { passive: true });

    function draw() {
      ctx.clearRect(0, 0, W, H);
      for (var i = 0; i < pts.length; i++) {
        var p = pts[i];
        p.x += p.vx; p.y += p.vy;
        if (p.x < 0 || p.x > W) { p.vx *= -1; } if (p.y < 0 || p.y > H) { p.vy *= -1; }
        var dxm = mouse.x - p.x, dym = mouse.y - p.y, dm = Math.sqrt(dxm * dxm + dym * dym);
        if (dm < 170 && dm > 0.001) { p.vx += dxm / dm * 0.02; p.vy += dym / dm * 0.02; }
        p.vx *= 0.99; p.vy *= 0.99;
        ctx.beginPath(); ctx.fillStyle = 'rgba(' + aRGB + ',0.9)'; ctx.arc(p.x, p.y, p.r, 0, 6.2832); ctx.fill();
      }
      for (var i = 0; i < pts.length; i++) {
        for (var j = i + 1; j < pts.length; j++) {
          var a = pts[i], b = pts[j], dx = a.x - b.x, dy = a.y - b.y, d2 = dx * dx + dy * dy;
          if (d2 < linkDist * linkDist) {
            var al = 1 - Math.sqrt(d2) / linkDist;
            ctx.strokeStyle = 'rgba(' + bRGB + ',' + (al * 0.45).toFixed(3) + ')'; ctx.lineWidth = 1;
            ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b.x, b.y); ctx.stroke();
          }
        }
      }
      if (mouse.x > 0) {
        var lr = linkDist * 1.4;
        for (var k = 0; k < pts.length; k++) {
          var q = pts[k], dx2 = q.x - mouse.x, dy2 = q.y - mouse.y, d = Math.sqrt(dx2 * dx2 + dy2 * dy2);
          if (d < lr) { var a2 = 1 - d / lr; ctx.strokeStyle = 'rgba(' + bRGB + ',' + (a2 * 0.7).toFixed(3) + ')'; ctx.lineWidth = 1; ctx.beginPath(); ctx.moveTo(q.x, q.y); ctx.lineTo(mouse.x, mouse.y); ctx.stroke(); }
        }
      }
      if (!reduce) { requestAnimationFrame(draw); }
    }
    draw();
  }

  window.ConstellationBG = { init: function (o) { try { build(o || {}); } catch (e) {} } };
}());
