/* Livelatch — Portal blade theme
 * Three.js r158 (UMD global build expected)
 */
(function () {
  'use strict';

  var PortalTheme = {
    _scene:     null,
    _camera:    null,
    _renderer:  null,
    _clock:     null,
    _raf:       null,
    _ring:      null,
    _innerRing: null,
    _outerGlow: null,
    _disc:      null,
    _nebula:    null,
    _stars:     null,
    _opts:      {},

    init: function (opts) {
      if (typeof THREE === 'undefined') {
        console.warn('[PortalTheme] Three.js not loaded');
        return;
      }
      this._opts = Object.assign({
        portalColor:    '#8b5cf6',
        accentColor:    '#0ce5de',
        particleCount:  800,
        animationSpeed: 60,
      }, opts || {});

      this._setup();
      this._buildStarField();
      this._buildNebula();
      this._buildRing();
      this._startLoop();
      window.addEventListener('resize', this._onResize.bind(this));
    },

    _setup: function () {
      var canvas = document.getElementById('portal-canvas');
      if (!canvas) { return; }

      this._clock  = new THREE.Clock();
      this._scene  = new THREE.Scene();
      this._scene.fog = new THREE.FogExp2(0x000008, 0.032);

      this._camera = new THREE.PerspectiveCamera(68, window.innerWidth / window.innerHeight, 0.01, 300);
      this._camera.position.set(0, 0, 5.2);

      this._renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: false });
      this._renderer.setClearColor(0x000010, 1);
      this._renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
      this._renderer.setSize(window.innerWidth, window.innerHeight);
    },

    _buildStarField: function () {
      var count = 4000;
      var pos   = new Float32Array(count * 3);
      for (var i = 0; i < count; i++) {
        var theta = Math.random() * Math.PI * 2;
        var phi   = Math.acos(2 * Math.random() - 1);
        var r     = 50 + Math.random() * 80;
        pos[i * 3]     = r * Math.sin(phi) * Math.cos(theta);
        pos[i * 3 + 1] = r * Math.sin(phi) * Math.sin(theta);
        pos[i * 3 + 2] = r * Math.cos(phi);
      }
      var geo = new THREE.BufferGeometry();
      geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
      this._stars = new THREE.Points(geo, new THREE.PointsMaterial({
        color: 0xffffff, size: 0.07, sizeAttenuation: true, transparent: true, opacity: 0.65,
      }));
      this._scene.add(this._stars);
    },

    _buildNebula: function () {
      var count = Math.max(100, Math.min(3000, parseInt(this._opts.particleCount) || 800));
      var pos   = new Float32Array(count * 3);
      for (var i = 0; i < count; i++) {
        var angle  = Math.random() * Math.PI * 2;
        var radius = 1.5 + (Math.random() - 0.5) * 5;
        var spread = (Math.random() - 0.5) * 4;
        pos[i * 3]     = Math.cos(angle) * radius + (Math.random() - 0.5) * spread;
        pos[i * 3 + 1] = Math.sin(angle) * radius + (Math.random() - 0.5) * spread;
        pos[i * 3 + 2] = (Math.random() - 0.5) * 8;
      }
      var geo = new THREE.BufferGeometry();
      geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
      this._nebula = new THREE.Points(geo, new THREE.PointsMaterial({
        color: new THREE.Color(this._opts.accentColor),
        size: 0.022, sizeAttenuation: true, transparent: true, opacity: 0.5,
      }));
      this._scene.add(this._nebula);
    },

    _buildRing: function () {
      var color = new THREE.Color(this._opts.portalColor);

      // Atmospheric outer glow
      this._outerGlow = new THREE.Mesh(
        new THREE.TorusGeometry(1.5, 0.28, 20, 180),
        new THREE.MeshBasicMaterial({ color: color, transparent: true, opacity: 0.10 })
      );
      this._outerGlow.rotation.x = 0.18;
      this._scene.add(this._outerGlow);

      // Main ring
      this._ring = new THREE.Mesh(
        new THREE.TorusGeometry(1.5, 0.055, 32, 220),
        new THREE.MeshBasicMaterial({ color: color, transparent: true, opacity: 0.92 })
      );
      this._ring.rotation.x = 0.18;
      this._scene.add(this._ring);

      // Inner highlight
      this._innerRing = new THREE.Mesh(
        new THREE.TorusGeometry(1.5, 0.018, 16, 220),
        new THREE.MeshBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.32 })
      );
      this._innerRing.rotation.x = 0.18;
      this._scene.add(this._innerRing);

      // Portal fill disc
      this._disc = new THREE.Mesh(
        new THREE.CircleGeometry(1.47, 72),
        new THREE.MeshBasicMaterial({ color: color, transparent: true, opacity: 0.045, side: THREE.DoubleSide })
      );
      this._disc.rotation.x = 0.18;
      this._scene.add(this._disc);
    },

    _startLoop: function () {
      var self        = this;
      var speedFactor = Math.max(0, Math.min(100, parseInt(this._opts.animationSpeed) || 60)) / 100;

      function tick() {
        self._raf = requestAnimationFrame(tick);
        var t  = self._clock.getElapsedTime() * speedFactor;
        var rz = t * 0.28;

        if (self._ring) {
          self._ring.rotation.z = rz;
          self._ring.rotation.x = 0.18 + Math.sin(t * 0.22) * 0.05;
        }
        if (self._innerRing) {
          self._innerRing.rotation.z = -rz * 1.6;
          self._innerRing.rotation.x = self._ring ? self._ring.rotation.x : 0.18;
        }
        if (self._disc) {
          self._disc.rotation.z = rz;
          self._disc.rotation.x = self._ring ? self._ring.rotation.x : 0.18;
        }
        if (self._outerGlow) {
          self._outerGlow.rotation.z     = -rz * 0.65;
          self._outerGlow.rotation.x     = self._ring ? self._ring.rotation.x : 0.18;
          self._outerGlow.material.opacity = 0.07 + Math.sin(t * 0.9) * 0.035;
        }
        if (self._nebula) {
          self._nebula.rotation.y = t * 0.045;
          self._nebula.rotation.x = Math.sin(t * 0.08) * 0.14;
        }
        if (self._stars) {
          self._stars.rotation.y = t * 0.006;
        }

        self._camera.position.x = Math.sin(t * 0.09) * 0.22;
        self._camera.position.y = Math.cos(t * 0.07) * 0.14;
        self._camera.lookAt(0, 0, 0);

        self._renderer.render(self._scene, self._camera);
      }

      tick();
    },

    _onResize: function () {
      if (!this._renderer || !this._camera) { return; }
      this._camera.aspect = window.innerWidth / window.innerHeight;
      this._camera.updateProjectionMatrix();
      this._renderer.setSize(window.innerWidth, window.innerHeight);
    },

    destroy: function () {
      if (this._raf)      { cancelAnimationFrame(this._raf); }
      if (this._renderer) { this._renderer.dispose(); }
    },
  };

  window.PortalTheme = PortalTheme;
}());
