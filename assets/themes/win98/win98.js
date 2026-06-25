/* Livelatch — Windows 98 theme
 * Start-menu toggle, live taskbar clock, and a parade of desktop icons.
 * No external libraries.
 */
(function () {
  'use strict';

  var ICON_GLYPHS = ['📁', '🖥️', '📝', '🎵', '🌐', '💾', '🖼️', '📧', '🎮', '📷'];
  var ICON_NAMES  = ['My Stuff', 'My PC', 'ReadMe', 'Tunes', 'Internet', 'Backup', 'Photos', 'Inbox', 'Games', 'Camera'];

  var Win98Theme = {
    init: function (opts) {
      opts = opts || {};
      this._wireStart();
      this._clock();
      this._icons(opts.iconParade);
    },

    _wireStart: function () {
      var btn  = document.getElementById('w9-start');
      var menu = document.getElementById('w9-startmenu');
      if (!btn || !menu) { return; }

      function open()  { menu.classList.add('w9-open'); menu.setAttribute('aria-hidden', 'false'); btn.setAttribute('aria-expanded', 'true'); }
      function close() { menu.classList.remove('w9-open'); menu.setAttribute('aria-hidden', 'true'); btn.setAttribute('aria-expanded', 'false'); }

      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.classList.contains('w9-open') ? close() : open();
      });
      document.addEventListener('click', function (e) {
        if (!menu.contains(e.target) && e.target !== btn) { close(); }
      });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { close(); } });
    },

    _clock: function () {
      var el = document.getElementById('w9-clock');
      if (!el) { return; }
      function tick() {
        var d = new Date();
        var h = d.getHours(); var m = d.getMinutes();
        var ap = h >= 12 ? 'PM' : 'AM';
        h = h % 12; if (h === 0) { h = 12; }
        el.textContent = h + ':' + (m < 10 ? '0' + m : m) + ' ' + ap;
      }
      tick();
      setInterval(tick, 10000);
    },

    _icons: function (parade) {
      var host = document.getElementById('w9-icons');
      if (!host) { return; }
      var pct = Math.max(0, Math.min(100, parseInt(parade, 10) || 0));
      var count = Math.round((pct / 100) * 10);
      var cols = Math.max(1, Math.floor((window.innerWidth - 24) / 86));
      for (var i = 0; i < count; i++) {
        var col = i % Math.max(1, Math.min(cols, 3));
        var row = Math.floor(i / Math.max(1, Math.min(cols, 3)));
        var el = document.createElement('div');
        el.className = 'w9-icon';
        el.style.left = (10 + col * 86) + 'px';
        el.style.top  = (10 + row * 76) + 'px';
        el.style.animationDelay = (i * 0.05) + 's';
        el.innerHTML = '<span class="w9-icon-img">' + ICON_GLYPHS[i % ICON_GLYPHS.length] + '</span>' + ICON_NAMES[i % ICON_NAMES.length];
        host.appendChild(el);
      }
    },
  };

  window.Win98Theme = Win98Theme;
}());
