/* Livelatch — Frutiger Aero theme
 * Rising glossy bubbles on a 2D canvas. No external libraries.
 */
(function () {
  'use strict';

  var AeroTheme = {
    _canvas: null,
    _ctx: null,
    _bubbles: [],
    _opts: {},
    _raf: null,

    init: function (opts) {
      this._opts = Object.assign({ bubbleDensity: 28, bubbleSpeed: 50 }, opts || {});
      this._canvas = document.getElementById('aero-canvas');
      if (!this._canvas || !this._canvas.getContext) { return; }
      this._ctx = this._canvas.getContext('2d');
      this._resize();
      this._make();
      window.addEventListener('resize', this._resize.bind(this));
      this._loop();
    },

    _resize: function () {
      this._w = this._canvas.width = window.innerWidth;
      this._h = this._canvas.height = window.innerHeight;
    },

    _make: function () {
      var n = Math.max(0, Math.min(120, parseInt(this._opts.bubbleDensity) || 0));
      this._bubbles = [];
      for (var i = 0; i < n; i++) { this._bubbles.push(this._spawn(true)); }
    },

    _spawn: function (initial) {
      return {
        x: Math.random() * this._w,
        y: initial ? Math.random() * this._h : this._h + 60,
        r: 8 + Math.random() * 40,
        sp: 0.3 + Math.random() * 1.3,
        wob: Math.random() * Math.PI * 2,
        op: 0.22 + Math.random() * 0.4,
      };
    },

    _loop: function () {
      var c = this._ctx;
      var speed = (parseInt(this._opts.bubbleSpeed) || 0) / 50;
      c.clearRect(0, 0, this._w, this._h);

      for (var i = 0; i < this._bubbles.length; i++) {
        var b = this._bubbles[i];
        b.y -= b.sp * speed;
        b.wob += 0.012;
        b.x += Math.sin(b.wob) * 0.45;

        if (b.y + b.r < -20) { this._bubbles[i] = this._spawn(false); continue; }

        // glassy body
        var g = c.createRadialGradient(b.x - b.r * 0.35, b.y - b.r * 0.35, b.r * 0.1, b.x, b.y, b.r);
        g.addColorStop(0, 'rgba(255,255,255,' + (b.op + 0.28) + ')');
        g.addColorStop(0.45, 'rgba(255,255,255,' + (b.op * 0.5) + ')');
        g.addColorStop(1, 'rgba(255,255,255,0)');
        c.beginPath(); c.fillStyle = g; c.arc(b.x, b.y, b.r, 0, Math.PI * 2); c.fill();

        // rim
        c.beginPath(); c.strokeStyle = 'rgba(255,255,255,' + (b.op * 0.55) + ')'; c.lineWidth = 1;
        c.arc(b.x, b.y, b.r, 0, Math.PI * 2); c.stroke();

        // sparkle highlight
        c.beginPath(); c.fillStyle = 'rgba(255,255,255,' + Math.min(0.9, b.op + 0.35) + ')';
        c.arc(b.x - b.r * 0.36, b.y - b.r * 0.36, Math.max(1.2, b.r * 0.16), 0, Math.PI * 2); c.fill();
      }

      this._raf = requestAnimationFrame(this._loop.bind(this));
    },
  };

  window.AeroTheme = AeroTheme;
}());
