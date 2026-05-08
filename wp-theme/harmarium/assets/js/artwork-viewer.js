/* =====================================================================
   Harmarium · Artwork Viewer
   Manages:
     1. Gallery — main image + thumbnails + zoom lightbox
     2. In-room mockup — scene switcher, drag-to-reposition, scale
     3. Frame selector — visual swatches update CSS frame on artwork
     4. Canvas type — updates data-canvas attribute driving CSS
     5. Packaging — informational selector, updates cart hidden field
   No jQuery. No dependencies.
   ===================================================================== */

(function () {
  'use strict';

  const $  = (s, c) => (c || document).querySelector(s);
  const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));

  /* ── Frame definitions ────────────────────────────────────────── */
  const FRAME_LABELS = {
    'none'        : 'Unframed',
    'thin-black'  : 'Thin Black',
    'thick-black' : 'Thick Black',
    'thin-white'  : 'Thin White',
    'oak'         : 'Natural Oak',
    'walnut'      : 'Walnut',
    'gold'        : 'Gold Leaf',
  };

  const CANVAS_LABELS = {
    'stretched'    : 'Stretched Canvas',
    'gallery-wrap' : 'Gallery Wrap',
    'framed-print' : 'Framed Print',
    'museum-frame' : 'Museum Frame',
  };

  /* ── Init all viewers on page ─────────────────────────────────── */
  function initAll() {
    $$('.hm-av').forEach(initViewer);
    initLightbox();
  }

  /* ── Single viewer init ───────────────────────────────────────── */
  function initViewer(root) {
    // Read config injected by PHP
    const cfgEl = $('.hm-av__config-json', root);
    if (!cfgEl) return;
    let cfg;
    try { cfg = JSON.parse(cfgEl.textContent); } catch (e) { return; }

    // State
    const state = {
      frame    : cfg.frame  || 'none',
      canvas   : cfg.canvas || 'stretched',
      packaging: cfg.packaging || 'ready-hang',
      scene    : cfg.sceneKey || 'living-room',
      scale    : cfg.scale  || 0.42,
      x        : cfg.x      || 0.50,
      y        : cfg.y      || 0.44,
      activeImg: 0,
    };

    // DOM refs
    const mainStage    = $('.hm-av__main', root);
    const imgWrap      = $('.hm-av__img-wrap', root);
    const artworkWrap  = $('.hm-av__artwork-wrap', root);
    const artworkImg   = $('.hm-av__artwork-img', root);
    const thumbs       = $$('.hm-av__thumb', root);
    const modeBtns     = $$('.hm-av__mode-btn', root);
    const mockupStage  = $('.hm-av__mockup-stage', root);
    const mockupScene  = $('.hm-av__mockup-scene', root);
    const mockupArt    = $('.hm-av__mockup-art', root);
    const mockupImg    = mockupArt ? $('img', mockupArt) : null;
    const sceneSelect  = $('.hm-av__scene-select', root);
    const scaleInput   = $('.hm-av__scale-input', root);
    const resetBtn     = $('.hm-av__mockup-reset', root);
    const frameSwatch  = $$('.hm-av__frame-swatch', root);
    const canvasOpts   = $$('.hm-av__canvas-option', root);
    const pkgOpts      = $$('.hm-av__pkg-option', root);
    const frameLabel   = $('.hm-av__section-value[data-label="frame"]', root);
    const canvasLabel  = $('.hm-av__section-value[data-label="canvas"]', root);
    const pkgLabel     = $('.hm-av__section-value[data-label="packaging"]', root);
    const zoomBtn      = $('.hm-av__zoom-btn', root);

    // Hidden inputs for WC
    const cartFrameInput    = $('[name="hm_frame"]', root.closest('form, .product') || document);
    const cartCanvasInput   = $('[name="hm_canvas_type"]', root.closest('form, .product') || document);
    const cartPkgInput      = $('[name="hm_packaging"]', root.closest('form, .product') || document);

    /* ── Apply state to DOM ───────────────────────────────────── */
    function applyFrame() {
      if (artworkWrap) artworkWrap.dataset.frame = state.frame;
      if (mockupArt)   mockupArt.dataset.frame   = state.frame;
      if (frameLabel)  frameLabel.textContent     = FRAME_LABELS[state.frame] || state.frame;
      frameSwatch.forEach((s) => s.classList.toggle('is-active', s.dataset.frameSwatch === state.frame));
      if (cartFrameInput) cartFrameInput.value = state.frame;
    }

    function applyCanvas() {
      if (artworkWrap) artworkWrap.dataset.canvas = state.canvas;
      if (canvasLabel) canvasLabel.textContent     = CANVAS_LABELS[state.canvas] || state.canvas;
      canvasOpts.forEach((o) => o.classList.toggle('is-active', o.dataset.canvas === state.canvas));
      if (cartCanvasInput) cartCanvasInput.value = state.canvas;
    }

    function applyPackaging() {
      pkgOpts.forEach((o) => o.classList.toggle('is-active', o.dataset.pkg === state.packaging));
      if (pkgLabel) pkgLabel.textContent = cfg.packagingLabels?.[state.packaging] || state.packaging;
      if (cartPkgInput) cartPkgInput.value = state.packaging;
    }

    function placeMockupArt() {
      if (!mockupArt || !mockupStage) return;
      const sw = mockupStage.offsetWidth  || 800;
      const sh = mockupStage.offsetHeight || 600;
      const aw = sw * state.scale;
      mockupArt.style.left  = (state.x * 100) + '%';
      mockupArt.style.top   = (state.y * 100) + '%';
      mockupArt.style.width = (state.scale * 100) + '%';
    }

    function switchScene(key) {
      if (!mockupScene) return;
      state.scene = key;
      const base = (window.HarmariumData && window.HarmariumData.scenesBase) || '';
      mockupScene.src = base + key + '.svg';
      // Reset position to scene defaults if available
      const sd = cfg.scenes?.[key];
      if (sd?.defaultX !== undefined) { state.x = sd.defaultX; state.y = sd.defaultY; state.scale = sd.defaultScale || state.scale; }
      placeMockupArt();
    }

    function switchImage(idx) {
      const images = cfg.images || [];
      if (!images[idx]) return;
      state.activeImg = idx;
      if (artworkImg)   artworkImg.src   = images[idx].full;
      if (artworkImg)   artworkImg.alt   = images[idx].alt || '';
      if (mockupImg)    mockupImg.src    = images[idx].full;
      thumbs.forEach((t, i) => t.classList.toggle('is-active', i === idx));
    }

    /* ── Mode toggle (artwork view / room view) ────────────────── */
    function setMode(mode) {
      if (!mainStage) return;
      const isMockup = mode === 'mockup';
      mainStage.classList.toggle('hm-av__main--mockup', isMockup);
      modeBtns.forEach((b) => b.classList.toggle('is-active', b.dataset.mode === mode));
      if (isMockup) placeMockupArt();
    }

    /* ── Event: mode buttons ───────────────────────────────────── */
    modeBtns.forEach((btn) => {
      btn.addEventListener('click', () => setMode(btn.dataset.mode));
    });

    /* ── Event: thumbnails ─────────────────────────────────────── */
    thumbs.forEach((thumb, i) => {
      thumb.addEventListener('click', () => switchImage(i));
      thumb.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); switchImage(i); } });
      thumb.setAttribute('tabindex', '0');
      thumb.setAttribute('role', 'button');
    });

    /* ── Event: frame swatches ─────────────────────────────────── */
    frameSwatch.forEach((swatch) => {
      swatch.addEventListener('click', () => { state.frame = swatch.dataset.frameSwatch; applyFrame(); });
      swatch.setAttribute('tabindex', '0');
      swatch.setAttribute('role', 'button');
      swatch.setAttribute('aria-label', FRAME_LABELS[swatch.dataset.frameSwatch] || swatch.dataset.frameSwatch);
      swatch.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); state.frame = swatch.dataset.frameSwatch; applyFrame(); } });
    });

    /* ── Event: canvas options ─────────────────────────────────── */
    canvasOpts.forEach((opt) => {
      opt.addEventListener('click', () => { state.canvas = opt.dataset.canvas; applyCanvas(); });
    });

    /* ── Event: packaging options ──────────────────────────────── */
    pkgOpts.forEach((opt) => {
      opt.addEventListener('click', () => { state.packaging = opt.dataset.pkg; applyPackaging(); });
    });

    /* ── Event: scene select ───────────────────────────────────── */
    if (sceneSelect) {
      sceneSelect.addEventListener('change', () => switchScene(sceneSelect.value));
    }

    /* ── Event: scale slider ───────────────────────────────────── */
    if (scaleInput) {
      scaleInput.value = state.scale;
      scaleInput.addEventListener('input', () => { state.scale = parseFloat(scaleInput.value); placeMockupArt(); });
    }

    /* ── Event: reset ──────────────────────────────────────────── */
    if (resetBtn) {
      resetBtn.addEventListener('click', () => {
        state.scale = cfg.scale || 0.42;
        state.x     = cfg.x    || 0.50;
        state.y     = cfg.y    || 0.44;
        if (scaleInput) scaleInput.value = state.scale;
        placeMockupArt();
      });
    }

    /* ── Event: drag artwork in mockup ─────────────────────────── */
    if (mockupArt && mockupStage) {
      mockupArt.addEventListener('pointerdown', (e) => {
        e.preventDefault();
        mockupArt.setPointerCapture(e.pointerId);
        mockupArt.style.transition = 'none';
        mockupArt.style.cursor = 'grabbing';
      });
      mockupArt.addEventListener('pointerup', (e) => {
        mockupArt.releasePointerCapture(e.pointerId);
        mockupArt.style.transition = '';
        mockupArt.style.cursor = '';
      });
      mockupArt.addEventListener('pointermove', (e) => {
        if (!mockupArt.hasPointerCapture(e.pointerId)) return;
        const r = mockupStage.getBoundingClientRect();
        state.x = Math.min(0.92, Math.max(0.08, (e.clientX - r.left) / r.width));
        state.y = Math.min(0.90, Math.max(0.08, (e.clientY - r.top) / r.height));
        mockupArt.style.left = (state.x * 100) + '%';
        mockupArt.style.top  = (state.y * 100) + '%';
      });
    }

    /* ── Event: zoom button ─────────────────────────────────────── */
    if (zoomBtn && artworkImg) {
      zoomBtn.addEventListener('click', () => {
        window.hmOpenLightbox?.(artworkImg.src, artworkImg.alt);
      });
    }

    /* ── Scene SVG: auto-read artwork zone coords ──────────────── */
    function applySceneZone() {
      if (!mockupScene || !mockupArt) return;
      // Wait for SVG to load then read the artwork-zone rect
      mockupScene.addEventListener('load', readZone, { once: true });
    }

    function readZone() {
      // Try to fetch the SVG and parse the artwork zone rect
      const src = mockupScene.src;
      if (!src || src.endsWith('.jpg') || src.endsWith('.png')) return;
      fetch(src)
        .then((r) => r.text())
        .then((svgText) => {
          const parser = new DOMParser();
          const doc    = parser.parseFromString(svgText, 'image/svg+xml');
          const zone   = doc.querySelector('#hm-artwork-zone, [data-artwork-zone="true"]');
          if (!zone) return;
          const vb    = doc.documentElement.viewBox.baseVal;
          const vbW   = vb.width  || 1600;
          const vbH   = vb.height || 900;
          const zx    = parseFloat(zone.getAttribute('x') || '0');
          const zy    = parseFloat(zone.getAttribute('y') || '0');
          const zw    = parseFloat(zone.getAttribute('width') || '400');
          const zh    = parseFloat(zone.getAttribute('height') || '400');
          // Set position to centre of zone as fraction of scene
          state.x     = (zx + zw / 2) / vbW;
          state.y     = (zy + zh / 2) / vbH;
          state.scale = (zw / vbW) * 0.92; // slightly smaller than zone width
          if (scaleInput) scaleInput.value = state.scale;
          placeMockupArt();
        })
        .catch(() => {/* fail silently — use default coords */});
    }

    /* ── Init ──────────────────────────────────────────────────── */
    applyFrame();
    applyCanvas();
    applyPackaging();
    placeMockupArt();
    applySceneZone();

    // Recalculate on resize
    const ro = new ResizeObserver(() => placeMockupArt());
    if (mockupStage) ro.observe(mockupStage);
  }

  /* ── Shared lightbox ──────────────────────────────────────────── */
  function initLightbox() {
    let lb = document.getElementById('hm-av-lightbox');
    if (!lb) {
      lb = document.createElement('div');
      lb.id = 'hm-av-lightbox';
      lb.className = 'hm-av-lightbox';
      lb.setAttribute('role', 'dialog');
      lb.setAttribute('aria-modal', 'true');
      lb.setAttribute('aria-label', 'Full size artwork view');
      lb.innerHTML = `
        <button class="hm-av-lightbox__close" aria-label="Close full size view">×</button>
        <img class="hm-av-lightbox__img" alt="">`;
      document.body.appendChild(lb);
    }

    const lbImg   = $('img', lb);
    const lbClose = $('button', lb);

    window.hmOpenLightbox = function (src, alt) {
      lbImg.src = src;
      lbImg.alt = alt || '';
      lb.classList.add('is-open');
      document.documentElement.style.overflow = 'hidden';
      lbClose.focus();
    };

    function closeLb() {
      lb.classList.remove('is-open');
      document.documentElement.style.overflow = '';
    }

    lbClose.addEventListener('click', closeLb);
    lb.addEventListener('click', (e) => { if (e.target === lb) closeLb(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && lb.classList.contains('is-open')) closeLb(); });
  }

  /* ── Boot ─────────────────────────────────────────────────────── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
})();
