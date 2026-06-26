/* Livelatch — Liquid: GLSL metaball blobs (Three.js fullscreen shader) that
 * merge/split and bulge toward the pointer. */
(function () {
  'use strict';
  var VERT = 'void main(){ gl_Position = vec4(position, 1.0); }';
  var FRAG = [
    'uniform float uTime; uniform vec2 uRes; uniform vec2 uMouse;',
    'uniform vec3 uColorA; uniform vec3 uColorB; uniform vec3 uBg;',
    'uniform float uIntensity; uniform float uSpeed;',
    'void main(){',
    '  vec2 uv = gl_FragCoord.xy / uRes;',
    '  float aspect = uRes.x / uRes.y;',
    '  vec2 p = vec2(uv.x*aspect, uv.y);',
    '  float t = uTime * (0.3 + uSpeed*0.6);',
    '  float field = 0.0;',
    '  for (int i=0;i<6;i++){',
    '    float fi=float(i);',
    '    vec2 c = vec2(0.5*aspect + sin(t*0.6+fi*1.7)*0.32*aspect, 0.5 + cos(t*0.5+fi*2.3)*0.30);',
    '    float r = 0.06 + 0.03*sin(t+fi);',
    '    field += r / (distance(p,c)+0.001);',
    '  }',
    '  vec2 m = vec2(uMouse.x*aspect, uMouse.y);',
    '  field += 0.11 / (distance(p,m)+0.001);',
    '  float thresh = 1.4 + (1.0-uIntensity)*0.6;',
    '  float v = smoothstep(thresh-0.25, thresh+0.25, field);',
    '  vec3 col = mix(uBg, mix(uColorA, uColorB, clamp(field*0.25,0.0,1.0)), v);',
    '  col += uColorB * pow(v,4.0) * 0.4;',
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
  window.LiquidBG = { init: function (o) { try { build(o || {}); } catch (e) {} } };
}());
