/* Livelatch — Topographic: animated contour map (Three.js fullscreen shader)
 * with a sonar ripple from the pointer. */
(function () {
  'use strict';
  var VERT = 'void main(){ gl_Position = vec4(position, 1.0); }';
  var FRAG = [
    'uniform float uTime; uniform vec2 uRes; uniform vec2 uMouse;',
    'uniform vec3 uColorA; uniform vec3 uColorB; uniform vec3 uBg;',
    'uniform float uIntensity; uniform float uSpeed;',
    'float hash(vec2 p){ return fract(sin(dot(p, vec2(127.1,311.7)))*43758.5453123); }',
    'float noise(vec2 p){ vec2 i=floor(p); vec2 f=fract(p); f=f*f*(3.0-2.0*f);',
    '  float a=hash(i), b=hash(i+vec2(1.0,0.0)), c=hash(i+vec2(0.0,1.0)), d=hash(i+vec2(1.0,1.0));',
    '  return mix(mix(a,b,f.x), mix(c,d,f.x), f.y); }',
    'float fbm(vec2 p){ float v=0.0,a=0.5; for(int i=0;i<5;i++){ v+=a*noise(p); p*=2.02; a*=0.5; } return v; }',
    'void main(){',
    '  vec2 uv = gl_FragCoord.xy / uRes;',
    '  float aspect = uRes.x / uRes.y; vec2 p = vec2(uv.x*aspect, uv.y);',
    '  float t = uTime * 0.06 * (0.4 + uSpeed);',
    '  float h = fbm(p*3.0 + vec2(t, t*0.5));',
    '  vec2 m = vec2(uMouse.x*aspect, uMouse.y);',
    '  h += 0.12 * sin(distance(p,m)*24.0 - uTime*2.0*(0.4+uSpeed));',
    '  float lines = 6.0 + uIntensity*22.0;',
    '  float c = abs(fract(h*lines) - 0.5);',
    '  float line = smoothstep(0.06, 0.0, c);',
    '  vec3 base = mix(uBg, uColorB, smoothstep(0.2, 0.9, h));',
    '  vec3 col = mix(base, uColorA, line);',
    '  gl_FragColor = vec4(col, 1.0);',
    '}'
  ].join('\n');

  function build(o) {
    if (!window.THREE) { return; }
    var THREE = window.THREE, canvas = document.getElementById('llx-canvas'); if (!canvas) { return; }
    var renderer; try { renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true }); } catch (e) { return; }
    var pr = Math.min(window.devicePixelRatio || 1, 2); renderer.setPixelRatio(pr);
    var scene = new THREE.Scene(), camera = new THREE.Camera();
    var u = { uTime: { value: 0 }, uRes: { value: new THREE.Vector2(1, 1) }, uMouse: { value: new THREE.Vector2(0.5, 0.5) },
      uColorA: { value: new THREE.Color(o.aColor) }, uColorB: { value: new THREE.Color(o.bColor) }, uBg: { value: new THREE.Color(o.bgColor) },
      uIntensity: { value: (o.intensity || 50) / 50 }, uSpeed: { value: (o.speed || 50) / 50 } };
    scene.add(new THREE.Mesh(new THREE.PlaneGeometry(2, 2), new THREE.ShaderMaterial({ uniforms: u, vertexShader: VERT, fragmentShader: FRAG })));
    function resize() { var w = window.innerWidth, h = window.innerHeight; renderer.setSize(w, h); u.uRes.value.set(w * pr, h * pr); }
    resize(); window.addEventListener('resize', resize);
    var tmx = 0.5, tmy = 0.5;
    window.addEventListener('pointermove', function (e) { tmx = e.clientX / window.innerWidth; tmy = 1.0 - e.clientY / window.innerHeight; }, { passive: true });
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) { u.uTime.value = 2.0; renderer.render(scene, camera); return; }
    var t0 = performance.now();
    function frame() { u.uTime.value = (performance.now() - t0) / 1000; u.uMouse.value.x += (tmx - u.uMouse.value.x) * 0.07; u.uMouse.value.y += (tmy - u.uMouse.value.y) * 0.07; renderer.render(scene, camera); requestAnimationFrame(frame); }
    requestAnimationFrame(frame);
  }
  window.TopographicBG = { init: function (o) { try { build(o || {}); } catch (e) {} } };
}());
