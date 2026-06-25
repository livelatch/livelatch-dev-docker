/* Livelatch — Blueprint theme
 * anime.js drives an "engineering drawing being drafted" entrance: the technical
 * linework draws itself on (stroke-dashoffset), then the dimensioned avatar,
 * title block and schematic link components fade/slide in. Requires anime.js.
 */
(function () {
  'use strict';

  var BlueprintTheme = {
    init: function (opts) {
      opts = opts || {};

      var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      // Without anime (or with reduced motion) the CSS renders the finished
      // drawing statically — nothing to do.
      if (!window.anime || reduce) { return; }

      var speed = Math.max(0, Math.min(100, parseInt(opts.drawSpeed, 10) || 50));
      var scale = (130 - speed) / 65; // higher slider = faster (≈2.0 → 0.46)

      function hide(sel) {
        document.querySelectorAll(sel).forEach(function (el) { el.style.opacity = 0; });
      }
      hide('.bp-avatar, .bp-dim-label, .bp-name, .bp-bio, .bp-link, .bp-titleblock, .bp-tick, .bp-corner');
      var rule = document.querySelector('.bp-rule');
      if (rule) { rule.style.transformOrigin = 'center'; rule.style.transform = 'scaleX(0)'; }

      var tl = anime.timeline({ easing: 'easeOutQuad' });

      // 1) draft the linework
      tl.add({
        targets: '.bp-draw',
        strokeDashoffset: [anime.setDashoffset, 0],
        duration: 1100 * scale,
        delay: anime.stagger(90 * scale),
        easing: 'easeInOutSine'
      }, 0);

      tl.add({ targets: '.bp-corner', opacity: [0, 0.8], scale: [0.6, 1], duration: 500 * scale, delay: anime.stagger(60) }, 0);

      // 2) the part (avatar) + its dimensions
      tl.add({ targets: '.bp-avatar', opacity: [0, 1], scale: [0.85, 1], duration: 600 * scale }, 300 * scale);
      tl.add({ targets: '.bp-tick', opacity: [0, 1], duration: 300 * scale, delay: anime.stagger(80) }, '-=200');
      tl.add({ targets: '.bp-dim-label', opacity: [0, 1], translateY: [6, 0], duration: 400 * scale }, '-=150');

      // 3) title + rule + note
      tl.add({ targets: '.bp-name', opacity: [0, 1], translateY: [14, 0], duration: 500 * scale }, '-=200');
      tl.add({ targets: '.bp-rule', scaleX: [0, 1], duration: 500 * scale, easing: 'easeInOutSine' }, '-=300');
      tl.add({ targets: '.bp-bio', opacity: [0, 1], translateY: [10, 0], duration: 450 * scale }, '-=250');

      // 4) schematic components
      tl.add({ targets: '.bp-link', opacity: [0, 1], translateX: [-14, 0], duration: 420 * scale, delay: anime.stagger(70 * scale) }, '-=150');

      // 5) title block
      tl.add({ targets: '.bp-titleblock', opacity: [0, 1], translateY: [12, 0], duration: 500 * scale }, '-=300');

      // ambient: the registration ticks pulse like a live readout
      anime({
        targets: '.bp-tick',
        opacity: [1, 0.25],
        direction: 'alternate',
        loop: true,
        duration: 1200,
        easing: 'easeInOutSine',
        delay: anime.stagger(400)
      });
    }
  };

  window.BlueprintTheme = BlueprintTheme;
}());
