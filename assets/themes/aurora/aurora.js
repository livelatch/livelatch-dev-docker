/* Livelatch — Aurora theme: GLSL aurora curtains over a starfield (Three.js
 * fullscreen fragment shader). Pointer bends the light. */
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
    '  float t = uTime * 0.18 * (0.3 + uSpeed);',
    '  vec3 col = uBg;',
    '  for (int i=0;i<3;i++){',
    '    float fi = float(i);',
    '    float wob = fbm(vec2(uv.x*2.4 + t*0.6 + fi*1.7, t*0.25 + fi*3.1));',
    '    float pos = 0.34 + 0.16*fi + 0.10*sin(t*0.7 + fi);',
    '    float yy = uv.y - (pos + (wob-0.5)*0.28);',
    '    float band = exp(-yy*yy*55.0);',
    '    vec3 c = mix(uColorA, uColorB, fi*0.5);',
    '    col += c * band * (0.6 + uIntensity);',
    '  }',
    '  col += uColorB * (0.25*uIntensity) * exp(-distance(uv,uMouse)*4.0);',
    '  float star = step(0.997, hash(floor(gl_FragCoord.xy/2.0)));',
    '  col += vec3(star) * 0.5 * smoothstep(0.35, 1.0, uv.y);',
    '  col *= mix(0.7, 1.0, smoothstep(0.0, 0.5, uv.y));',
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
    if (reduce) { u.uTime.value = 3.0; renderer.render(scene, camera); return; }

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

  window.AuroraBG = { init: function (o) { try { build(o || {}); } catch (e) {} } };
}());
