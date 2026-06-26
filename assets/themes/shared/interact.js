/* Livelatch — shared interactivity for the immersive blade themes.
 * GSAP entrance (if GSAP is present), pointer card-tilt and link glare/tilt.
 * Guarded for reduced motion and coarse pointers. Call LLXInteract.init(). */
(function () {
  'use strict';

  window.LLXInteract = {
    init: function () {
      var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      var fine = !window.matchMedia || window.matchMedia('(pointer: fine)').matches;
      var card = document.querySelector('[data-llx-card]');
      var ready = true;

      // Entrance
      if (window.gsap && !reduce) {
        ready = false;
        gsap.set('[data-llx-card]', { opacity: 0, y: 24, scale: 0.97 });
        var tl = gsap.timeline({ delay: 0.12, defaults: { ease: 'power3.out' } });
        tl.to('[data-llx-card]', { opacity: 1, y: 0, scale: 1, duration: 0.7 })
          .from('.llx-avatar-wrap', { opacity: 0, y: 14, scale: 0.85, duration: 0.5 }, '-=0.45')
          .from('.llx-name', { opacity: 0, y: 12, duration: 0.45 }, '-=0.35')
          .from('.llx-bio', { opacity: 0, y: 10, duration: 0.4 }, '-=0.3')
          .from('.llx-link', { opacity: 0, y: 14, stagger: 0.06, duration: 0.4 }, '-=0.25')
          .from('.llx-hint', { opacity: 0, duration: 0.5 }, '-=0.2');
        tl.eventCallback('onComplete', function () { ready = true; });
      }

      if (reduce || !fine) { return; }

      // Link glare + tilt
      Array.prototype.slice.call(document.querySelectorAll('.llx-link')).forEach(function (link) {
        link.addEventListener('pointermove', function (e) {
          var r = link.getBoundingClientRect();
          var mx = (e.clientX - r.left) / r.width, my = (e.clientY - r.top) / r.height;
          link.style.setProperty('--mx', (mx * 100) + '%');
          link.style.setProperty('--my', (my * 100) + '%');
          link.style.transform = 'perspective(600px) rotateX(' + (-(my - 0.5) * 10) + 'deg) rotateY(' + ((mx - 0.5) * 10) + 'deg) translateZ(6px)';
        });
        link.addEventListener('pointerleave', function () { link.style.transform = ''; });
      });

      // Card tilt
      if (!card) { return; }
      var pointer = { x: 0, y: 0 }, cx = 0, cy = 0;
      window.addEventListener('pointermove', function (e) {
        pointer.x = (e.clientX / window.innerWidth) * 2 - 1;
        pointer.y = (e.clientY / window.innerHeight) * 2 - 1;
      }, { passive: true });
      function frame() {
        if (ready) {
          var rx = -pointer.y * 4, ry = pointer.x * 4;
          cx += (rx - cx) * 0.08; cy += (ry - cy) * 0.08;
          card.style.transform = 'rotateX(' + cx + 'deg) rotateY(' + cy + 'deg)';
        }
        requestAnimationFrame(frame);
      }
      requestAnimationFrame(frame);
    }
  };
}());
