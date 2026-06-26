/* Livelatch — Outrun: an endless neon grid highway racing toward a synth sun
 * (Three.js). Steer the horizon with the pointer. */
(function () {
  'use strict';

  function build(o) {
    if (!window.THREE) { return; }
    var THREE = window.THREE, canvas = document.getElementById('llx-canvas'); if (!canvas) { return; }
    var renderer; try { renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true }); } catch (e) { return; }
    var pr = Math.min(window.devicePixelRatio || 1, 2); renderer.setPixelRatio(pr);
    renderer.setSize(window.innerWidth, window.innerHeight);
    var bg = new THREE.Color(o.bgColor); renderer.setClearColor(bg, 1);

    var scene = new THREE.Scene();
    scene.fog = new THREE.Fog(bg.getHex(), 12, 58);
    var camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 0.1, 200);
    camera.position.set(0, 1.2, 6);

    var CELL = 2.5;
    function makeGrid() {
      var g = new THREE.GridHelper(200, 80, new THREE.Color(o.aColor), new THREE.Color(o.aColor));
      g.material.transparent = true; g.material.opacity = 0.45 + (Math.max(0, Math.min(100, o.intensity)) / 100) * 0.4;
      g.position.y = -1.5; return g;
    }
    var grid = makeGrid(); scene.add(grid);

    // synth sun (flat glowing disc + soft halo)
    var sun = new THREE.Mesh(new THREE.CircleGeometry(6, 48), new THREE.MeshBasicMaterial({ color: new THREE.Color(o.bColor) }));
    sun.position.set(0, 4.5, -62); scene.add(sun);
    var halo = new THREE.Mesh(new THREE.CircleGeometry(9, 48), new THREE.MeshBasicMaterial({ color: new THREE.Color(o.bColor), transparent: true, opacity: 0.18 }));
    halo.position.set(0, 4.5, -63); scene.add(halo);

    // stars
    var sCount = 300, sPos = new Float32Array(sCount * 3);
    for (var i = 0; i < sCount; i++) { var i3 = i * 3; sPos[i3] = (Math.random() - 0.5) * 120; sPos[i3 + 1] = 4 + Math.random() * 40; sPos[i3 + 2] = -20 - Math.random() * 90; }
    var sGeo = new THREE.BufferGeometry(); sGeo.setAttribute('position', new THREE.BufferAttribute(sPos, 3));
    scene.add(new THREE.Points(sGeo, new THREE.PointsMaterial({ color: 0xffffff, size: 0.18, transparent: true, opacity: 0.7, depthWrite: false })));

    var speed = 0.18 + (Math.max(0, Math.min(100, o.speed)) / 100) * 0.5;
    var px = 0, camX = 0;
    window.addEventListener('pointermove', function (e) { px = (e.clientX / window.innerWidth) * 2 - 1; }, { passive: true });
    window.addEventListener('resize', function () { camera.aspect = window.innerWidth / window.innerHeight; camera.updateProjectionMatrix(); renderer.setSize(window.innerWidth, window.innerHeight); });

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) { renderer.render(scene, camera); return; }

    function frame() {
      grid.position.z += speed;
      if (grid.position.z >= CELL) { grid.position.z -= CELL; }
      camX += ((px * 1.4) - camX) * 0.04;
      camera.position.x = camX;
      camera.lookAt(px * 2.2, 0.7, -12);
      renderer.render(scene, camera);
      requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  window.OutrunBG = { init: function (o) { try { build(o || {}); } catch (e) {} } };
}());
