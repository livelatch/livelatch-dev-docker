/* Livelatch — Console / Matrix theme
 * Classic falling green code rain on a 2D canvas, plus a typewriter reveal of
 * the display name. No external libraries.
 */
(function () {
  'use strict';

  var GLYPHS = 'ﾊﾐﾋｰｳｼﾅﾓﾆｻﾜﾂｵﾘｱﾎﾃﾏｹﾒｴｶｷﾑﾕﾗｾﾈ0123456789ABCDEFZ:.=*+-<>'.split('');

  var ConsoleTheme = {
    init: function (opts) {
      var o = Object.assign({ rainColor: '#00ff66', rainDensity: 50, rainSpeed: 50 }, opts || {});
      this._rain(o);
      this._typeName();
    },

    _rain: function (o) {
      var canvas = document.getElementById('cs-rain');
      if (!canvas || !canvas.getContext) { return; }
      var ctx = canvas.getContext('2d');
      var W, H, fontSize, cols, drops = [];
      var dens = Math.max(0, Math.min(100, o.rainDensity)) / 100;
      var speed = (Math.max(0, Math.min(100, o.rainSpeed)) / 50);

      function build() {
        W = canvas.width = window.innerWidth;
        H = canvas.height = window.innerHeight;
        fontSize = Math.round(16 - dens * 4);       // denser = smaller glyphs
        if (fontSize < 10) { fontSize = 10; }
        cols = Math.floor(W / fontSize);
        drops = [];
        for (var i = 0; i < cols; i++) { drops[i] = Math.random() * -H / fontSize; }
      }

      var frame = 0;
      function draw() {
        // fade trail
        ctx.fillStyle = 'rgba(0,0,0,0.08)';
        ctx.fillRect(0, 0, W, H);
        ctx.font = fontSize + 'px monospace';

        for (var i = 0; i < cols; i++) {
          var ch = GLYPHS[(Math.random() * GLYPHS.length) | 0];
          var x = i * fontSize;
          var y = drops[i] * fontSize;
          // bright leading glyph
          ctx.fillStyle = '#dfffe6';
          ctx.fillText(ch, x, y);
          ctx.fillStyle = o.rainColor;
          ctx.fillText(GLYPHS[(Math.random() * GLYPHS.length) | 0], x, y - fontSize);

          if (y > H && Math.random() > 0.975) { drops[i] = 0; }
          drops[i] += 0.45 * speed;
        }
        raf = requestAnimationFrame(draw);
      }

      var raf;
      build();
      draw();
      window.addEventListener('resize', build);
    },

    _typeName: function () {
      if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
      var el = document.querySelector('.cs-name');
      if (!el) { return; }
      var full = el.getAttribute('data-type') || '';
      if (!full) { return; }
      var cursor = '<span class="cs-cursor">_</span>';
      var i = 0;
      el.innerHTML = cursor;
      function step() {
        i++;
        el.innerHTML = escapeHtml(full.slice(0, i)) + cursor;
        if (i < full.length) { setTimeout(step, 55); }
      }
      setTimeout(step, 400);
    },
  };

  function escapeHtml(s) {
    return s.replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  window.ConsoleTheme = ConsoleTheme;
}());
