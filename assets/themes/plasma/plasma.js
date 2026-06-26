/* Livelatch — Plasma theme: a classic flowing plasma field (Three.js fullscreen
 * fragment shader) that ripples from the pointer. */
(function () {
  'use strict';

  var VERT = 'void main(){ gl_Position = vec4(position, 1.0); }';
  var FRAG = [
    'uniform float uTime; uniform vec2 uRes; uniform vec2 uMouse;',
    'uniform vec3 uColorA; uniform vec3 uColorB; uniform vec3 uBg;',
    'uniform float uIntensity; uniform float uSpeed;',
    'void main(){',
    '  vec2 uv = gl_FragCoord.xy / uRes;',
    '  vec2 p = uv * 6.0;',
    '  float t = uTime * (0.4 + uSpeed * 0.8);',
    '  float v = 0.0;',
    '  v += sin(p.x + t);',
    '  v += sin((p.y + t) * 0.9);',
    '  v += sin((p.x + p.y + t) * 0.7);',
    '  vec2 c = p + vec2(sin(t * 0.33) * 2.0, cos(t * 0.41) * 2.0);',
    '  v += sin(length(c) + t);',
    '  v += sin(distance(uv, uMouse) * 22.0 - t * 2.5) * (0.6 * uIntensity);',
    '  v *= 0.4 * (0.6 + uIntensity);',
    '  float m = 0.5 + 0.5 * sin(v * 3.14159);',
    '  vec3 col = mix(uColorA, uColorB, m);',
    '  col = mix(uBg, col, 0.9);',
    '  gl_FragColor = vec4(col, 1.0);',
    '}'
  ].join('\n');

  function build(o) {
    if (!window.THREE) { return; }
    var THREE = window.THREE;
    var canvas = document.getElementById('llx-canvas');
    if (!canvas) { return; }
    var renderer;
    try { renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true }); } catch (e) { return; }
    var pr = Math.min(window.devicePixelRatio || 1, 2);
    renderer.setPixelRatio(pr);
    var scene = new THREE.Scene();
    var camera = new THREE.Camera();
    var u = {
      uTime: { value: 0 }, uRes: { value: new THREE.Vector2(1, 1) }, uMouse: { value: new THREE.Vector2(0.5, 0.5) },
      uColorA: { value: new THREE.Color(o.aColor) }, uColorB: { value: new THREE.Color(o.bColor) }, uBg: { value: new THREE.Color(o.bgColor) },
      uIntensity: { value: (o.intensity || 50) / 50 }, uSpeed: { value: (o.speed || 50) / 50 }
    };
    var mat = new THREE.ShaderMaterial({ uniforms: u, vertexShader: VERT, fragmentShader: FRAG });
    scene.add(new THREE.Mesh(new THREE.PlaneGeometry(2, 2), mat));

    function resize() { var w = window.innerWidth, h = window.innerHeight; renderer.setSize(w, h); u.uRes.value.set(w * pr, h * pr); }
    resize(); window.addEventListener('resize', resize);

    var tmx = 0.5, tmy = 0.5;
    window.addEventListener('pointermove', function (e) { tmx = e.clientX / window.innerWidth; tmy = 1.0 - e.clientY / window.innerHeight; }, { passive: true });

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) { u.uTime.value = 2.0; renderer.render(scene, camera); return; }

    var t0 = performance.now();
    function frame() {
      u.uTime.value = (performance.now() - t0) / 1000;
      u.uMouse.value.x += (tmx - u.uMouse.value.x) * 0.06;
      u.uMouse.value.y += (tmy - u.uMouse.value.y) * 0.06;
      renderer.render(scene, camera);
      requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  window.PlasmaBG = { init: function (o) { try { build(o || {}); } catch (e) {} } };
}());
