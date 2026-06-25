/* Livelatch — Space Station theme
 * A parallax starfield drifting behind the viewport. Pure 2D canvas.
 */
(function () {
  'use strict';

  var SpaceStationTheme = {
    init: function (opts) {
      var o = Object.assign({ starDensity: 50, orbitSpeed: 50 }, opts || {});
      var canvas = document.getElementById('ss-stars');
      if (!canvas || !canvas.getContext) { return; }
      var ctx = canvas.getContext('2d');
      var W, H, dpr, stars = [];
      var drift = (Math.max(0, Math.min(100, o.orbitSpeed)) / 100) * 0.10 + 0.02;

      function build() {
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        W = window.innerWidth; H = window.innerHeight;
        canvas.width = W * dpr; canvas.height = H * dpr;
        canvas.style.width = W + 'px'; canvas.style.height = H + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        var n = Math.round((Math.max(0, Math.min(100, o.starDensity)) / 100) * 280) + 40;
        stars = [];
        for (var i = 0; i < n; i++) {
          stars.push({
            x: Math.random() * W,
            y: Math.random() * H,
            r: Math.random() < 0.85 ? Math.random() * 1.1 + 0.3 : Math.random() * 1.8 + 1,
            z: Math.random() * 0.7 + 0.3,
            tw: Math.random() * Math.PI * 2
          });
        }
      }

      var t = 0;
      function frame() {
        t += 0.016;
        ctx.clearRect(0, 0, W, H);
        for (var i = 0; i < stars.length; i++) {
          var st = stars[i];
          st.x -= drift * st.z;
          if (st.x < -2) { st.x = W + 2; st.y = Math.random() * H; }
          var a = 0.45 + 0.55 * Math.abs(Math.sin(st.tw + t * (1.5 + st.z)));
          ctx.beginPath();
          ctx.fillStyle = 'rgba(255,255,255,' + a.toFixed(3) + ')';
          ctx.arc(st.x, st.y, st.r, 0, Math.PI * 2);
          ctx.fill();
        }
        raf = requestAnimationFrame(frame);
      }

      var raf;
      build();
      frame();
      window.addEventListener('resize', build);
    },
  };

  window.SpaceStationTheme = SpaceStationTheme;
}());
