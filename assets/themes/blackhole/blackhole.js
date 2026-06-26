/* Livelatch — Black Hole: a glowing accretion disk of orbiting points spiralling
 * around a dark core + photon ring (Three.js). Tilts toward the pointer. */
(function () {
  'use strict';

  function makeDot() {
    var c = document.createElement('canvas'); c.width = c.height = 32;
    var x = c.getContext('2d'); var g = x.createRadialGradient(16, 16, 0, 16, 16, 16);
    g.addColorStop(0, 'rgba(255,255,255,1)'); g.addColorStop(1, 'rgba(255,255,255,0)');
    x.fillStyle = g; x.fillRect(0, 0, 32, 32); return c;
  }

  function build(o) {
    if (!window.THREE) { return; }
    var THREE = window.THREE, canvas = document.getElementById('llx-canvas'); if (!canvas) { return; }
    var renderer; try { renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true }); } catch (e) { return; }
    var pr = Math.min(window.devicePixelRatio || 1, 2); renderer.setPixelRatio(pr);
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setClearColor(new THREE.Color(o.bgColor), 1);

    var scene = new THREE.Scene();
    var camera = new THREE.PerspectiveCamera(58, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.set(0, 2.4, 7);
    camera.lookAt(0, 0, 0);

    var group = new THREE.Group(); group.rotation.x = -1.05; scene.add(group);

    var N = 1200 + Math.round((Math.max(0, Math.min(100, o.intensity)) / 100) * 2600);
    var pos = new Float32Array(N * 3), col = new Float32Array(N * 3);
    var ang = new Float32Array(N), rad = new Float32Array(N), spd = new Float32Array(N), yof = new Float32Array(N);
    var ca = new THREE.Color(o.aColor), cb = new THREE.Color(o.bColor), tmp = new THREE.Color();
    for (var i = 0; i < N; i++) {
      var i3 = i * 3;
      var r = 1.35 + Math.pow(Math.random(), 0.7) * 2.9;
      rad[i] = r; ang[i] = Math.random() * 6.2832; spd[i] = 0.9 / Math.sqrt(r);
      yof[i] = (Math.random() - 0.5) * 0.18 * Math.exp(-(r - 1.35) * 0.5);
      tmp.copy(ca).lerp(cb, Math.min(1, (r - 1.35) / 2.9)); col[i3] = tmp.r; col[i3 + 1] = tmp.g; col[i3 + 2] = tmp.b;
    }
    var geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    geo.setAttribute('color', new THREE.BufferAttribute(col, 3));
    var tex = new THREE.CanvasTexture(makeDot());
    var disk = new THREE.Points(geo, new THREE.PointsMaterial({ size: 0.09, map: tex, vertexColors: true, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending }));
    group.add(disk);

    // dark core + photon ring
    group.add(new THREE.Mesh(new THREE.SphereGeometry(1.15, 32, 32), new THREE.MeshBasicMaterial({ color: 0x000000 })));
    var ring = new THREE.Mesh(new THREE.RingGeometry(1.16, 1.32, 64), new THREE.MeshBasicMaterial({ color: new THREE.Color(o.aColor), transparent: true, opacity: 0.85, side: THREE.DoubleSide, blending: THREE.AdditiveBlending, depthWrite: false }));
    group.add(ring);

    var spin = 0.4 + (Math.max(0, Math.min(100, o.speed)) / 100) * 1.4;
    var px = 0, py = 0, tx = 0, ty = 0;
    window.addEventListener('pointermove', function (e) { tx = (e.clientX / window.innerWidth) * 2 - 1; ty = (e.clientY / window.innerHeight) * 2 - 1; }, { passive: true });
    window.addEventListener('resize', function () { camera.aspect = window.innerWidth / window.innerHeight; camera.updateProjectionMatrix(); renderer.setSize(window.innerWidth, window.innerHeight); });

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    function step(dt) {
      var arr = geo.attributes.position.array;
      for (var i = 0; i < N; i++) {
        ang[i] += spd[i] * spin * dt;
        var i3 = i * 3, a = ang[i], r = rad[i];
        arr[i3] = Math.cos(a) * r; arr[i3 + 1] = yof[i]; arr[i3 + 2] = Math.sin(a) * r;
      }
      geo.attributes.position.needsUpdate = true;
    }
    if (reduce) { step(0); renderer.render(scene, camera); return; }
    function frame() {
      step(0.016);
      px += (tx - px) * 0.04; py += (ty - py) * 0.04;
      group.rotation.x = -1.05 + py * 0.4; group.rotation.z = px * 0.3;
      renderer.render(scene, camera);
      requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  window.BlackholeBG = { init: function (o) { try { build(o || {}); } catch (e) {} } };
}());
