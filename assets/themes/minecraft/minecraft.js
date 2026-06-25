/* Livelatch — Minecraft theme
 * A scrolling voxel landscape rendered with an InstancedMesh of cubes, flown
 * over with a gently banking "elytra glide" camera. Requires THREE (CDN).
 */
(function () {
  'use strict';

  var MinecraftTheme = {
    init: function (opts) {
      if (!window.THREE) { return; }
      var T = window.THREE;
      var o = Object.assign({
        grassColor: '#5bbf3a', skyColor: '#86c5ff', stoneColor: '#8a8d92',
        viewDistance: 60, glideSpeed: 50
      }, opts || {});

      var canvas = document.getElementById('mc-canvas');
      if (!canvas) { return; }

      var GRID_W = 46;                               // columns across (x)
      var GRID_D = Math.max(30, Math.min(90, o.viewDistance | 0)); // depth (z)
      var WATER  = 1.6;
      var speed  = (Math.max(0, Math.min(100, o.glideSpeed | 0)) / 50) * 0.06 + 0.01;

      var grass = new T.Color(o.grassColor);
      var stone = new T.Color(o.stoneColor);
      var sky   = new T.Color(o.skyColor);
      var sand  = new T.Color('#dccb8a');
      var water = new T.Color('#2f6fd6');
      var snow  = new T.Color('#f3f7ff');

      var scene = new T.Scene();
      scene.background = sky;
      scene.fog = new T.Fog(sky.getHex(), GRID_D * 0.35, GRID_D * 0.95);

      var camera = new T.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 0.1, 500);

      var renderer = new T.WebGLRenderer({ canvas: canvas, antialias: false });
      renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
      renderer.setSize(window.innerWidth, window.innerHeight);

      var sun = new T.DirectionalLight(0xffffff, 1.0);
      sun.position.set(0.4, 1, 0.3);
      scene.add(sun);
      scene.add(new T.HemisphereLight(sky.getHex(), 0x4a3a2a, 0.7));

      var geo = new T.BoxGeometry(1, 1, 1);
      var mat = new T.MeshLambertMaterial({ vertexColors: true });
      var COUNT = GRID_W * GRID_D;
      var mesh = new T.InstancedMesh(geo, mat, COUNT);
      mesh.instanceMatrix.setUsage(T.DynamicDrawUsage);
      scene.add(mesh);

      var dummy = new T.Object3D();
      var tmpCol = new T.Color();

      // cheap smooth pseudo-noise from layered sines (no noise lib needed)
      function heightAt(x, z) {
        var h =
          5.5 +
          3.2 * Math.sin(x * 0.18) * Math.cos(z * 0.15) +
          1.8 * Math.sin((x + z) * 0.09) +
          1.2 * Math.cos(x * 0.33 + z * 0.21);
        return h;
      }

      function colorFor(h) {
        if (h < WATER + 0.3)      return water;
        if (h < WATER + 1.1)      return sand;
        if (h > 9.0)              return snow;
        if (h > 6.8)              return stone;
        return grass;
      }

      var worldZ = 0;

      function build() {
        var baseZ = Math.floor(worldZ);
        var frac  = worldZ - baseZ;
        var i = 0;
        for (var iz = 0; iz < GRID_D; iz++) {
          for (var ix = 0; ix < GRID_W; ix++) {
            var wx = ix - GRID_W / 2;
            var sampleZ = baseZ + iz;
            var h = heightAt(wx, sampleZ);
            var col = colorFor(h);
            var topY = Math.max(WATER, Math.round(h));
            // position: row 0 nearest camera, scrolls toward +z (toward viewer)
            var posZ = 3 - (iz - frac);
            dummy.position.set(wx, topY - 4.5, posZ);
            dummy.updateMatrix();
            mesh.setMatrixAt(i, dummy.matrix);
            tmpCol.copy(col);
            // subtle per-column shade so the grid reads as separate blocks
            var shade = 0.86 + ((ix * 7 + sampleZ * 13) % 5) * 0.035;
            tmpCol.multiplyScalar(shade);
            mesh.setColorAt(i, tmpCol);
            i++;
          }
        }
        mesh.instanceMatrix.needsUpdate = true;
        if (mesh.instanceColor) { mesh.instanceColor.needsUpdate = true; }
      }

      build();

      var t = 0;
      function animate() {
        t += 0.016;
        worldZ += speed;
        build();

        // Elytra glide: high vantage, looking down the corridor, banking roll.
        var roll = Math.sin(t * 0.6) * 0.13;
        var yaw  = Math.sin(t * 0.27) * 2.2;
        var bob  = Math.sin(t * 0.9) * 0.5;

        camera.position.set(0, 11 + bob, 9);
        camera.up.set(Math.sin(roll), Math.cos(roll), 0);
        camera.lookAt(yaw, 4.5 + Math.sin(t * 0.45) * 0.8, -GRID_D * 0.55);

        renderer.render(scene, camera);
        rafId = requestAnimationFrame(animate);
      }
      var rafId = requestAnimationFrame(animate);

      window.addEventListener('resize', function () {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
      });
    },
  };

  window.MinecraftTheme = MinecraftTheme;
}());
