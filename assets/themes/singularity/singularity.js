/* Livelatch — Singularity theme
 * A live, steerable WebGL galaxy: a custom-GLSL spiral built from a Points
 * cloud (Three.js), pointer-parallax camera + galaxy tilt, click-to-warp,
 * a trailing custom cursor, 3D-tilt holographic link cards, and a GSAP
 * entrance. Everything degrades gracefully — if WebGL/Three/GSAP are missing
 * or the visitor prefers reduced motion, the page is still a polished static
 * card on a gradient.
 */
(function () {
  'use strict';

  var VERT = [
    'uniform float uTime; uniform float uSize; uniform float uWarp;',
    'attribute float aScale; attribute vec3 aColor; varying vec3 vColor;',
    'void main(){',
    '  vec4 mp = modelMatrix * vec4(position, 1.0);',
    '  float d = length(mp.xz);',
    '  float angle = atan(mp.x, mp.z) + (1.0 / (d + 0.6)) * uTime;',
    '  mp.x = cos(angle) * d; mp.z = sin(angle) * d;',
    '  mp.xyz *= 1.0 + uWarp * 0.7 * smoothstep(0.0, 9.0, d);',
    '  vec4 vp = viewMatrix * mp;',
    '  gl_Position = projectionMatrix * vp;',
    '  gl_PointSize = uSize * aScale * (1.0 + uWarp * 1.5);',
    '  gl_PointSize *= (1.0 / -vp.z);',
    '  vColor = aColor;',
    '}'
  ].join('\n');

  var FRAG = [
    'varying vec3 vColor;',
    'void main(){',
    '  float s = 1.0 - distance(gl_PointCoord, vec2(0.5)) * 2.0;',
    '  s = clamp(s, 0.0, 1.0); s = pow(s, 2.0);',
    '  gl_FragColor = vec4(vColor, s);',
    '}'
  ].join('\n');

  function buildGalaxy(o) {
    if (!window.THREE) { return null; }
    var THREE = window.THREE;
    var canvas = document.getElementById('sg-canvas');
    if (!canvas) { return null; }

    var W = window.innerWidth, H = window.innerHeight;
    var scene = new THREE.Scene();
    var camera = new THREE.PerspectiveCamera(62, W / H, 0.1, 140);
    camera.position.set(0, 3.2, 9);
    camera.lookAt(0, 0, 0);

    var renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true });
    var pr = Math.min(window.devicePixelRatio || 1, 2);
    renderer.setPixelRatio(pr);
    renderer.setSize(W, H);
    renderer.setClearColor(new THREE.Color(o.bgColor), 1);

    var group = new THREE.Group();
    scene.add(group);

    // ---- galaxy ----
    var count = Math.max(1000, Math.min(20000, o.particleCount | 0));
    var branches = Math.max(2, Math.min(8, o.branches | 0));
    var radius = 9, randomness = 0.5, power = 2.6;
    var positions = new Float32Array(count * 3);
    var colors = new Float32Array(count * 3);
    var scales = new Float32Array(count);
    var cIn = new THREE.Color(o.coreColor);
    var cOut = new THREE.Color(o.edgeColor);

    for (var i = 0; i < count; i++) {
      var i3 = i * 3;
      var r = Math.pow(Math.random(), 1.6) * radius;
      var branch = (i % branches) / branches * Math.PI * 2;
      var sgn = function () { return Math.random() < 0.5 ? 1 : -1; };
      var rx = Math.pow(Math.random(), power) * sgn() * randomness * r;
      var ry = Math.pow(Math.random(), power) * sgn() * randomness * r * 0.5;
      var rz = Math.pow(Math.random(), power) * sgn() * randomness * r;
      positions[i3]     = Math.cos(branch) * r + rx;
      positions[i3 + 1] = ry;
      positions[i3 + 2] = Math.sin(branch) * r + rz;
      var col = cIn.clone().lerp(cOut, Math.min(1, r / radius));
      colors[i3] = col.r; colors[i3 + 1] = col.g; colors[i3 + 2] = col.b;
      scales[i] = Math.random() * 0.9 + 0.25;
    }

    var geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    geo.setAttribute('aColor', new THREE.BufferAttribute(colors, 3));
    geo.setAttribute('aScale', new THREE.BufferAttribute(scales, 1));

    var uSize = ((Math.max(0, Math.min(100, o.glow)) / 100) * 16 + 8) * pr;
    var mat = new THREE.ShaderMaterial({
      uniforms: { uTime: { value: 0 }, uSize: { value: uSize }, uWarp: { value: 0 } },
      vertexShader: VERT,
      fragmentShader: FRAG,
      transparent: true,
      depthWrite: false,
      blending: THREE.AdditiveBlending
    });
    group.add(new THREE.Points(geo, mat));

    // ---- starfield backdrop ----
    var sCount = 600;
    var sPos = new Float32Array(sCount * 3);
    for (var j = 0; j < sCount; j++) {
      var j3 = j * 3;
      var rr = 30 + Math.random() * 60;
      var th = Math.random() * Math.PI * 2;
      var ph = Math.acos(2 * Math.random() - 1);
      sPos[j3]     = rr * Math.sin(ph) * Math.cos(th);
      sPos[j3 + 1] = rr * Math.cos(ph);
      sPos[j3 + 2] = rr * Math.sin(ph) * Math.sin(th);
    }
    var sGeo = new THREE.BufferGeometry();
    sGeo.setAttribute('position', new THREE.BufferAttribute(sPos, 3));
    var sMat = new THREE.PointsMaterial({
      size: 0.13, sizeAttenuation: true, color: new THREE.Color(o.edgeColor),
      transparent: true, opacity: 0.7, depthWrite: false, blending: THREE.AdditiveBlending
    });
    var stars = new THREE.Points(sGeo, sMat);
    scene.add(stars);

    var spinBase = 0.3 + (Math.max(0, Math.min(100, o.spinSpeed)) / 100) * 1.4;
    var camX = 0, camY = 3.2, punch = 0;

    function update(pointer, elapsed) {
      mat.uniforms.uTime.value = elapsed * spinBase;
      mat.uniforms.uWarp.value *= 0.93;
      punch *= 0.9;

      group.rotation.y += 0.0016 * (1 + spinBase * 0.2);
      group.rotation.x += ((pointer.y * 0.45) - group.rotation.x) * 0.04;
      group.rotation.z += ((-pointer.x * 0.25) - group.rotation.z) * 0.04;
      stars.rotation.y -= 0.0004;

      camX += ((pointer.x * 1.6) - camX) * 0.04;
      camY += ((3.2 + pointer.y * 1.3) - camY) * 0.04;
      camera.position.set(camX, camY, 9 - punch);
      camera.lookAt(0, 0, 0);

      renderer.render(scene, camera);
    }

    window.addEventListener('resize', function () {
      camera.aspect = window.innerWidth / window.innerHeight;
      camera.updateProjectionMatrix();
      renderer.setSize(window.innerWidth, window.innerHeight);
    });

    return {
      update: update,
      render: function () { renderer.render(scene, camera); },
      warp: function () { mat.uniforms.uWarp.value = 1.0; punch = 1.5; }
    };
  }

  function buildCursor() {
    var dot = document.getElementById('sg-cursor');
    var ring = document.getElementById('sg-cursor-ring');
    if (!dot || !ring) { return null; }
    document.body.classList.add('sg-has-cursor');
    return { dot: dot, ring: ring, shown: false };
  }

  function setupTilt() {
    var links = Array.prototype.slice.call(document.querySelectorAll('.sg-link'));
    var ring = document.getElementById('sg-cursor-ring');
    links.forEach(function (link) {
      link.addEventListener('pointermove', function (e) {
        var r = link.getBoundingClientRect();
        var mx = (e.clientX - r.left) / r.width;
        var my = (e.clientY - r.top) / r.height;
        link.style.setProperty('--mx', (mx * 100) + '%');
        link.style.setProperty('--my', (my * 100) + '%');
        var ry = (mx - 0.5) * 12;
        var rx = -(my - 0.5) * 12;
        link.style.transform = 'perspective(600px) rotateX(' + rx + 'deg) rotateY(' + ry + 'deg) translateZ(8px)';
      });
      link.addEventListener('pointerenter', function () { if (ring) { ring.style.width = '58px'; ring.style.height = '58px'; } });
      link.addEventListener('pointerleave', function () {
        link.style.transform = '';
        if (ring) { ring.style.width = '38px'; ring.style.height = '38px'; }
      });
    });
  }

  function entrance(reduce) {
    if (!window.gsap || reduce) { return true; } // already visible
    gsap.set('#sg-card', { opacity: 0, y: 26, scale: 0.96 });
    var tl = gsap.timeline({ delay: 0.15, defaults: { ease: 'power3.out' } });
    tl.to('#sg-card', { opacity: 1, y: 0, scale: 1, duration: 0.8 })
      .from('.sg-avatar-wrap', { opacity: 0, y: 16, scale: 0.82, duration: 0.6 }, '-=0.5')
      .from('.sg-name', { opacity: 0, y: 14, duration: 0.5 }, '-=0.4')
      .from('.sg-bio', { opacity: 0, y: 12, duration: 0.4 }, '-=0.35')
      .from('.sg-link', { opacity: 0, y: 16, stagger: 0.07, duration: 0.45 }, '-=0.3')
      .from('.sg-hint', { opacity: 0, duration: 0.5 }, '-=0.2');
    return false; // tilt should wait for completion
  }

  var SingularityTheme = {
    init: function (opts) {
      var o = Object.assign({
        coreColor: '#ffd9a0', edgeColor: '#5b6cff', bgColor: '#05030f',
        particleCount: 8000, spinSpeed: 45, branches: 4, glow: 55
      }, opts || {});

      var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      var fine = !window.matchMedia || window.matchMedia('(pointer: fine)').matches;

      var pointer = { x: 0, y: 0, tx: window.innerWidth / 2, ty: window.innerHeight / 2, moved: false };
      window.addEventListener('pointermove', function (e) {
        pointer.x = (e.clientX / window.innerWidth) * 2 - 1;
        pointer.y = (e.clientY / window.innerHeight) * 2 - 1;
        pointer.tx = e.clientX; pointer.ty = e.clientY; pointer.moved = true;
      }, { passive: true });

      var galaxy = null;
      try { galaxy = buildGalaxy(o); } catch (err) { galaxy = null; }

      var cursor = (fine && !reduce) ? buildCursor() : null;
      var tiltReady = entrance(reduce); // true when there's no entrance to wait for

      if (galaxy && !reduce) {
        window.addEventListener('pointerdown', function () { galaxy.warp(); }, { passive: true });
      }
      if (fine && !reduce) {
        setupTilt();
        if (!tiltReady && window.gsap) {
          gsap.delayedCall(2.6, function () { tiltReady = true; });
        }
      }

      // Reduced motion / no animation loop: draw one frame and stop.
      if (reduce) { if (galaxy) { galaxy.render(); } return; }

      var card = document.getElementById('sg-card');
      var cx = 0, cy = 0, ringX = pointer.tx, ringY = pointer.ty;
      var clock = (window.THREE) ? new THREE.Clock() : null;
      var t0 = performance.now();

      function frame() {
        var elapsed = clock ? clock.getElapsedTime() : (performance.now() - t0) / 1000;

        if (galaxy) { galaxy.update(pointer, elapsed); }

        if (cursor) {
          if (pointer.moved && !cursor.shown) { cursor.dot.style.opacity = 1; cursor.ring.style.opacity = 1; cursor.shown = true; }
          cursor.dot.style.transform = 'translate(' + pointer.tx + 'px,' + pointer.ty + 'px) translate(-50%,-50%)';
          ringX += (pointer.tx - ringX) * 0.18;
          ringY += (pointer.ty - ringY) * 0.18;
          cursor.ring.style.transform = 'translate(' + ringX + 'px,' + ringY + 'px) translate(-50%,-50%)';
        }

        if (card && tiltReady && fine) {
          var rx = -pointer.y * 4, ry = pointer.x * 4;
          cx += (rx - cx) * 0.08; cy += (ry - cy) * 0.08;
          card.style.transform = 'rotateX(' + cx + 'deg) rotateY(' + cy + 'deg)';
        }

        requestAnimationFrame(frame);
      }
      requestAnimationFrame(frame);
    }
  };

  window.SingularityTheme = SingularityTheme;
}());
