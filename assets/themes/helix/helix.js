/* Livelatch — Helix: a rotating DNA double-helix of glowing instanced spheres
 * and rungs (Three.js). Spin from "Spin"; tilts toward the pointer. */
(function () {
  'use strict';

  function build(o) {
    if (!window.THREE) { return; }
    var THREE = window.THREE, canvas = document.getElementById('llx-canvas'); if (!canvas) { return; }
    var renderer; try { renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true }); } catch (e) { return; }
    var pr = Math.min(window.devicePixelRatio || 1, 2); renderer.setPixelRatio(pr);
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setClearColor(new THREE.Color(o.bgColor), 1);

    var scene = new THREE.Scene();
    var camera = new THREE.PerspectiveCamera(55, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.set(0, 0, 9);

    scene.add(new THREE.AmbientLight(0xffffff, 0.5));
    var pl = new THREE.PointLight(0xffffff, 1.0, 40); pl.position.set(4, 6, 8); scene.add(pl);

    var group = new THREE.Group(); scene.add(group);

    var seg = 20 + Math.round((Math.max(0, Math.min(100, o.intensity)) / 100) * 34);
    var R = 1.35, H = 4.4, twist = 0.42;
    var ca = new THREE.Color(o.aColor), cb = new THREE.Color(o.bColor);

    var sphere = new THREE.SphereGeometry(0.135, 16, 16);
    var im = new THREE.InstancedMesh(sphere, new THREE.MeshStandardMaterial({ metalness: 0.3, roughness: 0.35, vertexColors: false }), seg * 2);
    var dummy = new THREE.Object3D();
    var rungPos = [];
    for (var s = 0; s < seg; s++) {
      var y = -H + (s / (seg - 1)) * (H * 2);
      var a = s * twist;
      var ax = Math.cos(a) * R, az = Math.sin(a) * R;
      var bx = Math.cos(a + Math.PI) * R, bz = Math.sin(a + Math.PI) * R;
      dummy.position.set(ax, y, az); dummy.updateMatrix(); im.setMatrixAt(s * 2, dummy.matrix); im.setColorAt(s * 2, ca);
      dummy.position.set(bx, y, bz); dummy.updateMatrix(); im.setMatrixAt(s * 2 + 1, dummy.matrix); im.setColorAt(s * 2 + 1, cb);
      if (s % 2 === 0) { rungPos.push(ax, y, az, bx, y, bz); }
    }
    im.instanceMatrix.needsUpdate = true; if (im.instanceColor) { im.instanceColor.needsUpdate = true; }
    group.add(im);

    var rg = new THREE.BufferGeometry(); rg.setAttribute('position', new THREE.Float32BufferAttribute(rungPos, 3));
    var rungs = new THREE.LineSegments(rg, new THREE.LineBasicMaterial({ color: ca.clone().lerp(cb, 0.5), transparent: true, opacity: 0.4 }));
    group.add(rungs);

    var spin = 0.3 + (Math.max(0, Math.min(100, o.speed)) / 100) * 1.3;
    var px = 0, py = 0, tx = 0, ty = 0;
    window.addEventListener('pointermove', function (e) { tx = (e.clientX / window.innerWidth) * 2 - 1; ty = (e.clientY / window.innerHeight) * 2 - 1; }, { passive: true });
    window.addEventListener('resize', function () { camera.aspect = window.innerWidth / window.innerHeight; camera.updateProjectionMatrix(); renderer.setSize(window.innerWidth, window.innerHeight); });

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) { group.rotation.y = 0.6; renderer.render(scene, camera); return; }
    function frame() {
      group.rotation.y += spin * 0.01;
      px += (tx - px) * 0.05; py += (ty - py) * 0.05;
      group.rotation.x = py * 0.5; group.rotation.z = px * 0.2;
      renderer.render(scene, camera);
      requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  window.HelixBG = { init: function (o) { try { build(o || {}); } catch (e) {} } };
}());
