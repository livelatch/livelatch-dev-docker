/* Livelatch — Cute (kawaii) theme
 * Drifting paw prints + twinkling sparkles on a 2D canvas. No libraries.
 */
(function () {
  'use strict';

  function hexToRgb(hex) {
    hex = String(hex || '#ffffff').replace('#', '');
    if (hex.length === 3) { hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2]; }
    var n = parseInt(hex, 16);
    return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
  }

  var CuteTheme = {
    _canvas: null,
    _ctx: null,
    _items: [],
    _opts: {},
    _rgb: [255, 255, 255],
    _raf: null,

    init: function (opts) {
      this._opts = Object.assign({ pawColor: '#ffffff', pawDensity: 18, driftSpeed: 45 }, opts || {});
      this._rgb = hexToRgb(this._opts.pawColor);
      this._canvas = document.getElementById('cute-canvas');
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
      var n = Math.max(0, Math.min(80, parseInt(this._opts.pawDensity) || 0));
      this._items = [];
      for (var i = 0; i < n; i++) { this._items.push(this._spawn(true)); }
    },

    _spawn: function (initial) {
      return {
        type: Math.random() < 0.55 ? 'paw' : 'sparkle',
        x: Math.random() * this._w,
        y: initial ? Math.random() * this._h : this._h + 40,
        s: 10 + Math.random() * 22,
        sp: 0.25 + Math.random() * 1.0,
        drift: (Math.random() - 0.5) * 0.6,
        rot: Math.random() * Math.PI * 2,
        rotSp: (Math.random() - 0.5) * 0.01,
        op: 0.3 + Math.random() * 0.45,
        tw: Math.random() * Math.PI * 2,
      };
    },

    _paw: function (c, a) {
      c.fillStyle = 'rgba(' + this._rgb[0] + ',' + this._rgb[1] + ',' + this._rgb[2] + ',' + a + ')';
      c.beginPath(); c.ellipse(0, 0.28, 0.55, 0.46, 0, 0, Math.PI * 2); c.fill();
      var toes = [[-0.5, -0.32, 0.22], [-0.17, -0.58, 0.2], [0.17, -0.58, 0.2], [0.5, -0.32, 0.22]];
      for (var i = 0; i < toes.length; i++) {
        c.beginPath(); c.ellipse(toes[i][0], toes[i][1], toes[i][2], toes[i][2] * 1.25, 0, 0, Math.PI * 2); c.fill();
      }
    },

    _sparkle: function (c, a) {
      c.fillStyle = 'rgba(' + this._rgb[0] + ',' + this._rgb[1] + ',' + this._rgb[2] + ',' + a + ')';
      c.beginPath();
      for (var i = 0; i < 4; i++) {
        var ang = i * Math.PI / 2;
        c.lineTo(Math.cos(ang), Math.sin(ang));
        c.lineTo(Math.cos(ang + Math.PI / 4) * 0.28, Math.sin(ang + Math.PI / 4) * 0.28);
      }
      c.closePath(); c.fill();
    },

    _loop: function () {
      var c = this._ctx;
      var speed = (parseInt(this._opts.driftSpeed) || 0) / 45;
      c.clearRect(0, 0, this._w, this._h);

      for (var i = 0; i < this._items.length; i++) {
        var it = this._items[i];
        it.y -= it.sp * speed;
        it.x += it.drift * speed;
        it.rot += it.rotSp;
        it.tw += 0.05;

        if (it.y + it.s < -30) { this._items[i] = this._spawn(false); continue; }

        var a = it.op;
        if (it.type === 'sparkle') { a = it.op * (0.5 + 0.5 * Math.abs(Math.sin(it.tw))); }

        c.save();
        c.translate(it.x, it.y);
        c.rotate(it.rot);
        c.scale(it.s, it.s);
        if (it.type === 'paw') { this._paw(c, a); } else { this._sparkle(c, a); }
        c.restore();
      }

      this._raf = requestAnimationFrame(this._loop.bind(this));
    },
  };

  window.CuteTheme = CuteTheme;
}());
