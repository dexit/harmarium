/**
 * Gallery Hallway — immersive first-person scroll-driven walkthrough.
 *
 * Renders a CSS 3D perspective hallway onto a <canvas> using the 2D context
 * for maximum compatibility (no WebGL required). Artworks are fetched from
 * the REST API and composited as scaled images on the left and right walls.
 *
 * Scroll position controls the camera's Z position (depth into the hallway).
 */
(function () {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  document.querySelectorAll('[data-hm-hallway]').forEach((root) => {
    let cfg;
    try { cfg = JSON.parse(root.dataset.hmHallwayConfig || '{}'); } catch { cfg = {}; }

    const canvas   = root.querySelector('.hm-gallery-hallway__canvas');
    const overlay  = root.querySelector('.hm-gallery-hallway__overlay');
    const loading  = root.querySelector('.hm-gallery-hallway__loading');
    const tooltip  = root.querySelector('.hm-gallery-hallway__tooltip');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const DPR = Math.min(window.devicePixelRatio || 1, 2);

    /* ── Sizing ── */
    function resize() {
      const w = root.offsetWidth;
      const h = Math.round(w * 0.5625); // 16:9
      canvas.style.width  = w + 'px';
      canvas.style.height = h + 'px';
      canvas.width  = w * DPR;
      canvas.height = h * DPR;
      ctx.scale(DPR, DPR);
      W = w; H = h;
    }
    let W = 0, H = 0;
    resize();
    window.addEventListener('resize', resize, { passive: true });

    /* ── State ── */
    let artworks  = [];   // { img, title, link, loaded }
    let camZ      = 0;    // 0 = entrance, increases as you walk in
    let targetZ   = 0;
    let raf       = null;
    let hovered   = null; // index of hovered artwork

    /* ── Scroll → camera depth ── */
    const DEPTH_PER_PX = 0.004; // tune: how fast you walk

    function onScroll() {
      const rect   = root.getBoundingClientRect();
      const scrolled = Math.max(0, -rect.top);
      targetZ = scrolled * DEPTH_PER_PX;
    }
    window.addEventListener('scroll', onScroll, { passive: true });

    /* ── Fetch artworks ── */
    async function loadArtworks() {
      const url = cfg.sourceUrl || '/wp-json/wp/v2/portfolio?per_page=20&_fields=id,title,link,harmarium';
      try {
        const res  = await fetch(url);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        artworks = data.map((item) => {
          const thumb = item?.harmarium?.thumb_medium || item?.harmarium?.thumb || '';
          const obj = {
            title:  item.title?.rendered || '',
            link:   item.link || '#',
            thumb,
            img:    null,
            loaded: false,
          };
          if (thumb) {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => { obj.img = img; obj.loaded = true; };
            img.onerror = () => { obj.loaded = true; };
            img.src = thumb;
          } else {
            obj.loaded = true;
          }
          return obj;
        });
      } catch (err) {
        console.error('Harmarium hallway: failed to load artworks:', err);
      } finally {
        loading.hidden = true;
      }
    }

    /* ── Render one frame ── */
    const WALL_COLOR  = cfg.wallColor  || '#f0ebe3';
    const FLOOR_COLOR = cfg.floorColor || '#d4c9b8';
    const LIGHT_COLOR = cfg.lightColor || '#fff8f0';

    // Perspective helpers: map a world (x, y, z) to canvas (sx, sy, scale)
    const VP_Z   = 3;    // viewer eye Z offset beyond near plane
    const FOV_H  = 0.55; // half the hallway width in "world units"
    const FOV_V  = 0.31; // half the hallway height

    function project(wx, wy, wz, camZp) {
      const z = wz - camZp + VP_Z;
      if (z <= 0.01) return null;
      const sx = W / 2 + (wx / z) * (W / 2);
      const sy = H / 2 - (wy / z) * (H / 2);
      const sc = 1 / z;
      return { sx, sy, sc };
    }

    // Artworks are spaced evenly along Z, alternating left/right walls
    const SPACING    = 2.2;   // world units between artworks
    const WALL_X     = 1.15;  // distance from centreline to wall
    const ART_HEIGHT = 0.45;  // half-height of artwork in world units
    const ART_DEPTH  = 0.01;  // slight depth offset from wall

    function artWorldPos(i) {
      const side = i % 2 === 0 ? -WALL_X : WALL_X;
      const z    = 2 + i * SPACING;
      return { wx: side, wy: 0, wz: z };
    }

    function drawHallway() {
      ctx.clearRect(0, 0, W, H);

      // Sky / ceiling gradient
      const sky = ctx.createLinearGradient(0, 0, 0, H * 0.5);
      sky.addColorStop(0, LIGHT_COLOR);
      sky.addColorStop(1, WALL_COLOR);
      ctx.fillStyle = sky;
      ctx.fillRect(0, 0, W, H * 0.5);

      // Floor gradient
      const floor = ctx.createLinearGradient(0, H * 0.5, 0, H);
      floor.addColorStop(0, WALL_COLOR);
      floor.addColorStop(1, FLOOR_COLOR);
      ctx.fillStyle = floor;
      ctx.fillRect(0, H * 0.5, W, H * 0.5);

      // Perspective converging lines (walls/ceiling/floor rails)
      const vp = { x: W / 2, y: H / 2 }; // vanishing point
      const lines = [
        // left wall top, left wall bottom, right wall top, right wall bottom
        [0, 0], [0, H], [W, 0], [W, H],
        [W * 0.1, H * 0.3], [W * 0.1, H * 0.7],
        [W * 0.9, H * 0.3], [W * 0.9, H * 0.7],
      ];
      ctx.strokeStyle = 'rgba(0,0,0,0.07)';
      ctx.lineWidth   = 1;
      lines.forEach(([x, y]) => {
        ctx.beginPath();
        ctx.moveTo(x, y);
        ctx.lineTo(vp.x, vp.y);
        ctx.stroke();
      });

      // Spot-light tunnel overlay
      const spot = ctx.createRadialGradient(W / 2, H * 0.45, 0, W / 2, H * 0.45, W * 0.55);
      spot.addColorStop(0, 'rgba(255,248,240,0.18)');
      spot.addColorStop(1, 'rgba(0,0,0,0)');
      ctx.fillStyle = spot;
      ctx.fillRect(0, 0, W, H);

      // Draw artworks back-to-front (painter's algorithm)
      const sorted = artworks
        .map((art, i) => ({ art, i, ...artWorldPos(i) }))
        .sort((a, b) => b.wz - a.wz); // farthest first

      hovered = null;
      const mx = _mouseX, my = _mouseY;

      sorted.forEach(({ art, i, wx, wy, wz }) => {
        // Four corners of the artwork plane
        const topL = project(wx - ART_HEIGHT * 0.75, wy + ART_HEIGHT, wz - ART_DEPTH, camZ);
        const topR = project(wx + ART_HEIGHT * 0.75, wy + ART_HEIGHT, wz - ART_DEPTH, camZ);
        const botL = project(wx - ART_HEIGHT * 0.75, wy - ART_HEIGHT, wz - ART_DEPTH, camZ);
        const botR = project(wx + ART_HEIGHT * 0.75, wy - ART_HEIGHT, wz - ART_DEPTH, camZ);
        if (!topL || !topR || !botL || !botR) return;
        if (topL.sc < 0.01) return; // behind camera

        const pw = topR.sx - topL.sx;
        const ph = botL.sy - topL.sy;
        if (pw < 2 || ph < 2) return;

        // Hover detection
        const inX = mx >= topL.sx && mx <= topR.sx;
        const inY = my >= topL.sy && my <= botL.sy;
        if (inX && inY) hovered = i;

        // Frame shadow
        ctx.shadowColor   = 'rgba(0,0,0,0.35)';
        ctx.shadowBlur    = 14 * topL.sc * W;
        ctx.shadowOffsetX = 3 * topL.sc * W;
        ctx.shadowOffsetY = 6 * topL.sc * W;

        // Frame rect
        ctx.fillStyle = hovered === i ? '#e8ddd0' : '#f5f0ea';
        const pad = Math.max(2, pw * 0.05);
        ctx.fillRect(topL.sx - pad, topL.sy - pad, pw + pad * 2, ph + pad * 2);

        ctx.shadowColor = 'transparent';
        ctx.shadowBlur  = 0;

        // Artwork image or placeholder
        if (art.img && art.loaded) {
          try {
            ctx.drawImage(art.img, topL.sx, topL.sy, pw, ph);
          } catch { /* tainted canvas from cross-origin */ }
        } else {
          // Placeholder gradient
          const grad = ctx.createLinearGradient(topL.sx, topL.sy, topR.sx, botL.sy);
          grad.addColorStop(0, '#e8e0d5');
          grad.addColorStop(1, '#d4ccc0');
          ctx.fillStyle = grad;
          ctx.fillRect(topL.sx, topL.sy, pw, ph);
        }

        // Spot-light shimmer on artwork
        const shimmer = ctx.createRadialGradient(
          topL.sx + pw * 0.35, topL.sy + ph * 0.25, 0,
          topL.sx + pw * 0.5,  topL.sy + ph * 0.5,  pw * 0.7
        );
        shimmer.addColorStop(0, 'rgba(255,250,240,0.22)');
        shimmer.addColorStop(1, 'rgba(0,0,0,0)');
        ctx.fillStyle = shimmer;
        ctx.fillRect(topL.sx, topL.sy, pw, ph);

        // Hover: title label
        if (hovered === i && art.title) {
          tooltip.textContent = art.title;
          tooltip.style.left  = Math.round(topL.sx + pw / 2) + 'px';
          tooltip.style.top   = Math.round(botL.sy + 8) + 'px';
          tooltip.hidden = false;
        }
      });

      if (hovered === null) tooltip.hidden = true;
      canvas.style.cursor = hovered !== null ? 'pointer' : 'default';
    }

    /* ── Animation loop ── */
    let _mouseX = -9999, _mouseY = -9999;

    canvas.addEventListener('mousemove', (e) => {
      const r = canvas.getBoundingClientRect();
      _mouseX = (e.clientX - r.left) * (W / r.width);
      _mouseY = (e.clientY - r.top)  * (H / r.height);
    });
    canvas.addEventListener('mouseleave', () => { _mouseX = _mouseY = -9999; });

    canvas.addEventListener('click', () => {
      if (hovered !== null && artworks[hovered]) {
        window.location.href = artworks[hovered].link;
      }
    });

    function tick() {
      raf = requestAnimationFrame(tick);
      // Smooth camera
      const ease = reduceMotion ? 1 : 0.08;
      camZ += (targetZ - camZ) * ease;
      drawHallway();
    }

    /* ── Intersection observer: only run RAF when visible ── */
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          if (!raf) tick();
        } else {
          if (raf) { cancelAnimationFrame(raf); raf = null; }
        }
      });
    }, { threshold: 0.1 });
    io.observe(root);

    // Make the container tall so scroll drives depth
    root.style.setProperty('--hm-hallway-height', (artworks.length || 20) * 80 + 'px');

    loadArtworks().then(() => {
      root.style.setProperty('--hm-hallway-height', Math.max(600, artworks.length * 80) + 'px');
    });
  });
})();
