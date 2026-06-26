/* Livelatch — Fireflies: a dusk swarm of glowing bokeh points (Three.js) that
 * drift in depth and gather gently toward the pointer. */
(function () {
  'use strict';

  function makeDot() {
    var c = document.createElement('canvas'); c.width = c.height = 64;
    var x = c.getContext('2d');
    var g = x.createRadialGradient(32, 32, 0, 32, 32, 32);
    g.addColorStop(0, 'rgba(255,255,255,1)'); g.addColorStop(0.25, 'rgba(255,255,255,0.85)');
    g.addColorStop(1, 'rgba(255,255,255,0)');
    x.fillStyle = g; x.fillRect(0, 0, 64, 64);
    return c;
  }

  function build(o) {
    if (!window.THREE) { return; }
    var THREE = window.THREE, canvas = document.getElementById('llx-canvas'); if (!canvas) { return; }
    var renderer; try { renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true }); } catch (e) { return; }
    var pr = Math.min(window.devicePixelRatio || 1, 2); renderer.setPixelRatio(pr);
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setClearColor(new THREE.Color(o.bgColor), 1);

    var scene = new THREE.Scene();
    var camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.set(0, 0, 7);

    var N = 60 + Math.round((Math.max(0, Math.min(100, o.intensity)) / 100) * 150);
    var pos = new Float32Array(N * 3), base = new Float32Array(N * 3), phase = new Float32Array(N), col = new Float32Array(N * 3);
    var ca = new THREE.Color(o.aColor), cb = new THREE.Color(o.bColor), tmp = new THREE.Color();
    for (var i = 0; i < N; i++) {
      var i3 = i * 3;
      base[i3] = (Math.random() - 0.5) * 12; base[i3 + 1] = (Math.random() - 0.5) * 8; base[i3 + 2] = (Math.random() - 0.5) * 6;
      pos[i3] = base[i3]; pos[i3 + 1] = base[i3 + 1]; pos[i3 + 2] = base[i3 + 2];
      phase[i] = Math.random() * 6.28;
      tmp.copy(ca).lerp(cb, Math.random()); col[i3] = tmp.r; col[i3 + 1] = tmp.g; col[i3 + 2] = tmp.b;
    }
    var geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    geo.setAttribute('color', new THREE.BufferAttribute(col, 3));
    var tex = new THREE.CanvasTexture(makeDot());
    var mat = new THREE.PointsMaterial({ size: 0.5, map: tex, vertexColors: true, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending });
    var pts = new THREE.Points(geo, mat); scene.add(pts);

    var drift = 0.4 + (Math.max(0, Math.min(100, o.speed)) / 100) * 1.0;
    var mx = 0, my = 0, tmX = 0, tmY = 0;
    window.addEventListener('pointermove', function (e) { tmX = ((e.clientX / window.innerWidth) * 2 - 1) * 6; tmY = -((e.clientY / window.innerHeight) * 2 - 1) * 4; }, { passive: true });
    window.addEventListener('resize', function () { camera.aspect = window.innerWidth / window.innerHeight; camera.updateProjectionMatrix(); renderer.setSize(window.innerWidth, window.innerHeight); });

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) { renderer.render(scene, camera); return; }

    var t0 = performance.now();
    function frame() {
      var t = (performance.now() - t0) / 1000;
      mx += (tmX - mx) * 0.04; my += (tmY - my) * 0.04;
      var arr = geo.attributes.position.array;
      for (var i = 0; i < N; i++) {
        var i3 = i * 3;
        var bx = base[i3] + Math.sin(t * drift + phase[i]) * 0.6;
        var by = base[i3 + 1] + Math.cos(t * drift * 0.8 + phase[i]) * 0.5;
        var bz = base[i3 + 2] + Math.sin(t * drift * 0.5 + phase[i] * 1.3) * 0.4;
        var dx = mx - bx, dy = my - by, d = Math.sqrt(dx * dx + dy * dy);
        if (d < 4) { var k = (1 - d / 4) * 0.6; bx += dx * k; by += dy * k; }
        arr[i3] = bx; arr[i3 + 1] = by; arr[i3 + 2] = bz;
      }
      geo.attributes.position.needsUpdate = true;
      pts.rotation.y = Math.sin(t * 0.1) * 0.1;
      renderer.render(scene, camera);
      requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  window.FirefliesBG = { init: function (o) { try { build(o || {}); } catch (e) {} } };
}());
