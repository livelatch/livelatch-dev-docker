/* Livelatch — Crystal: a faceted gem (Three.js) with orbiting coloured lights
 * and a wireframe overlay. Turns toward the pointer; gem detail = "Facets". */
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
    camera.position.set(0, 0, 4.6);

    var detail = Math.max(0, Math.min(3, Math.round((o.intensity || 50) / 34)));
    var geo = new THREE.IcosahedronGeometry(1.45, detail);
    var mat = new THREE.MeshStandardMaterial({ color: new THREE.Color(o.aColor), metalness: 0.45, roughness: 0.16, flatShading: true });
    var gem = new THREE.Mesh(geo, mat);
    var group = new THREE.Group(); group.add(gem);
    var wire = new THREE.LineSegments(new THREE.WireframeGeometry(geo), new THREE.LineBasicMaterial({ color: new THREE.Color(o.bColor), transparent: true, opacity: 0.35 }));
    group.add(wire);
    scene.add(group);

    scene.add(new THREE.AmbientLight(0xffffff, 0.35));
    var l1 = new THREE.PointLight(new THREE.Color(o.aColor), 1.3, 30); scene.add(l1);
    var l2 = new THREE.PointLight(new THREE.Color(o.bColor), 1.3, 30); scene.add(l2);
    var l3 = new THREE.PointLight(0xffffff, 0.5, 30); l3.position.set(0, 4, 4); scene.add(l3);

    var spin = 0.2 + (Math.max(0, Math.min(100, o.speed)) / 100) * 1.1;
    var px = 0, py = 0, rx = 0, ry = 0;
    window.addEventListener('pointermove', function (e) { px = (e.clientX / window.innerWidth) * 2 - 1; py = (e.clientY / window.innerHeight) * 2 - 1; }, { passive: true });
    window.addEventListener('resize', function () { camera.aspect = window.innerWidth / window.innerHeight; camera.updateProjectionMatrix(); renderer.setSize(window.innerWidth, window.innerHeight); });

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) { l1.position.set(3, 2, 3); l2.position.set(-3, -2, 3); renderer.render(scene, camera); return; }

    var t0 = performance.now();
    function frame() {
      var t = (performance.now() - t0) / 1000;
      l1.position.set(Math.cos(t * 0.9) * 3.2, Math.sin(t * 0.7) * 2.4, 3);
      l2.position.set(Math.cos(t * 0.9 + 2.1) * 3.2, Math.sin(t * 0.7 + 2.1) * 2.4, 3);
      ry += ((px * 0.6) - ry) * 0.05; rx += ((py * 0.5) - rx) * 0.05;
      group.rotation.y += spin * 0.01; group.rotation.y += (ry - 0) * 0.0;
      group.rotation.x = rx; group.rotation.z = ry * 0.3;
      renderer.render(scene, camera);
      requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  window.CrystalBG = { init: function (o) { try { build(o || {}); } catch (e) {} } };
}());
