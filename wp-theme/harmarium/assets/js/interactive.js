/* Harmarium · interactive front-end
 *
 * Vanilla JS, no dependencies. Modules:
 *  - lightbox      — click any data-harmarium-zoomable image to open
 *  - filter        — click filter pills to filter the current gallery
 *  - mockup        — render the configurable real-life mockup preview
 *  - wall          — pan/zoom the virtual exhibition wall
 *  - reveal        — fade-in on scroll
 *
 * All modules are guarded — only run when their target nodes exist.
 */
(function () {
  'use strict';

  const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));
  const $  = (sel, ctx) => (ctx || document).querySelector(sel);
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ──────────── Lightbox ──────────── */
  function initLightbox() {
    const targets = $$('[data-harmarium-zoomable] img, .wp-block-post-featured-image[data-harmarium-zoomable] img, .harmarium-gallery img');
    if (!targets.length) return;

    let overlay = $('.hm-lightbox');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.className = 'hm-lightbox';
      overlay.setAttribute('role', 'dialog');
      overlay.setAttribute('aria-modal', 'true');
      overlay.innerHTML = `
        <button type="button" class="hm-lightbox__close" aria-label="Close">×</button>
        <button type="button" class="hm-lightbox__nav prev" aria-label="Previous">‹</button>
        <button type="button" class="hm-lightbox__nav next" aria-label="Next">›</button>
        <img class="hm-lightbox__img" alt="" />`;
      document.body.appendChild(overlay);
    }
    const img = $('.hm-lightbox__img', overlay);
    let group = []; let idx = 0;

    function open(list, i) {
      group = list; idx = i;
      img.src = group[idx].currentSrc || group[idx].src;
      img.alt = group[idx].alt || '';
      overlay.classList.add('is-open');
      document.documentElement.style.overflow = 'hidden';
    }
    function close() { overlay.classList.remove('is-open'); document.documentElement.style.overflow = ''; }
    function step(d) { idx = (idx + d + group.length) % group.length; img.src = group[idx].currentSrc || group[idx].src; img.alt = group[idx].alt || ''; }

    targets.forEach((el) => {
      el.style.cursor = 'zoom-in';
      el.addEventListener('click', (e) => {
        if (e.metaKey || e.ctrlKey || e.shiftKey) return;
        e.preventDefault();
        const list = $$('img', el.closest('[data-harmarium-gallery], .wp-block-query, main') || document);
        const filtered = list.filter((n) => n.closest('a, [data-harmarium-zoomable]'));
        open(filtered.length ? filtered : [el], Math.max(0, filtered.indexOf(el)));
      });
    });

    overlay.addEventListener('click', (e) => {
      if (e.target === overlay || e.target.classList.contains('hm-lightbox__close')) close();
      else if (e.target.classList.contains('next')) step(1);
      else if (e.target.classList.contains('prev')) step(-1);
    });
    document.addEventListener('keydown', (e) => {
      if (!overlay.classList.contains('is-open')) return;
      if (e.key === 'Escape') close();
      else if (e.key === 'ArrowRight') step(1);
      else if (e.key === 'ArrowLeft') step(-1);
    });
  }

  /* ──────────── Filter ──────────── */
  function initFilter() {
    $$('[data-harmarium-filter]').forEach((bar) => {
      const gallery = bar.parentElement.querySelector('[data-harmarium-gallery], .wp-block-query');
      if (!gallery) return;
      bar.addEventListener('click', (e) => {
        const btn = e.target.closest('.harmarium-filter__pill');
        if (!btn) return;
        const slug = btn.dataset.filter;
        $$('.harmarium-filter__pill', bar).forEach((p) => p.classList.toggle('is-active', p === btn));
        $$('.wp-block-post', gallery).forEach((card) => {
          const matches = slug === '*' || Array.from(card.classList).some((c) => c === 'artwork_type-' + slug || c === 'portfolio_category-' + slug || c === 'portfolio_tag-' + slug || c.endsWith('-' + slug));
          card.style.transition = 'opacity .35s ease, transform .35s ease';
          card.style.opacity = matches ? '1' : '0';
          card.style.transform = matches ? '' : 'scale(.97)';
          card.style.pointerEvents = matches ? '' : 'none';
          if (!matches) card.setAttribute('hidden', ''); else card.removeAttribute('hidden');
        });
      });
    });
  }

  /* ──────────── Real-life mockup ──────────── */
  function initMockup() {
    $$('[data-harmarium-mockup]').forEach((root) => {
      const cfgEl = $('.harmarium-mockup__config', root);
      if (!cfgEl) return;
      let cfg;
      try { cfg = JSON.parse(cfgEl.textContent); } catch (e) { return; }
      // Store a deep copy of the initial config so reset can restore it.
      const initialCfg = JSON.parse(JSON.stringify(cfg));
      const stage  = $('.harmarium-mockup__stage', root);
      const scene  = $('.harmarium-mockup__scene', root);
      const art    = $('[data-harmarium-art]', root);
      if (!stage || !art) return;

      function place() {
        art.style.left  = (cfg.x * 100) + '%';
        art.style.top   = (cfg.y * 100) + '%';
        art.style.width = (cfg.scale * 100) + '%';
        art.dataset.frame = cfg.frame;
      }
      place();

      function resetMockup() {
        Object.assign(cfg, initialCfg);
        scene.src = initialCfg.scene;
        $$('[data-harmarium-control]', root).forEach((ctrl) => {
          const kind = ctrl.dataset.harmariumControl;
          if (kind === 'scene' && ctrl.tagName === 'SELECT') ctrl.value = initialCfg.sceneKey;
          if (kind === 'frame' && ctrl.tagName === 'SELECT') ctrl.value = initialCfg.frame;
          if (kind === 'scale') ctrl.value = initialCfg.scale;
        });
        place();
      }

      $$('[data-harmarium-control]', root).forEach((ctrl) => {
        const kind = ctrl.dataset.harmariumControl;
        const evName = ctrl.tagName === 'SELECT' ? 'change' : (kind === 'reset' ? 'click' : 'input');
        ctrl.addEventListener(evName, () => {
          if (kind === 'scene') {
            cfg.sceneKey = ctrl.value;
            const base = (window.HarmariumData && window.HarmariumData.scenesBase) || '';
            scene.src = base ? base + ctrl.value + '.jpg' : scene.src.replace(/[^/]+\.jpg$/, ctrl.value + '.jpg');
          } else if (kind === 'frame') {
            cfg.frame = ctrl.value; art.dataset.frame = cfg.frame;
          } else if (kind === 'scale') {
            cfg.scale = parseFloat(ctrl.value);
            art.style.width = (cfg.scale * 100) + '%';
          } else if (kind === 'reset') {
            resetMockup();
          }
        });
      });

      // Drag to reposition — all listeners scoped to art element via pointer capture.
      art.addEventListener('pointerdown', (e) => {
        art.setPointerCapture(e.pointerId);
        art.style.transition = 'none';
      });
      art.addEventListener('pointerup', (e) => {
        art.releasePointerCapture(e.pointerId);
        art.style.transition = '';
      });
      art.addEventListener('pointermove', (e) => {
        if (!art.hasPointerCapture(e.pointerId)) return;
        const r = stage.getBoundingClientRect();
        cfg.x = Math.min(0.95, Math.max(0.05, (e.clientX - r.left) / r.width));
        cfg.y = Math.min(0.95, Math.max(0.05, (e.clientY - r.top) / r.height));
        art.style.left = (cfg.x * 100) + '%';
        art.style.top  = (cfg.y * 100) + '%';
      });
    });
  }

  /* ──────────── Virtual exhibition wall ──────────── */
  function initWall() {
    $$('[data-harmarium-wall]').forEach(async (wall) => {
      const plane = $('[data-harmarium-wall-plane]', wall);
      if (!plane) return;
      // Use PHP-provided post ID for reliability rather than body class parsing.
      const exhibitionId = window.HarmariumData && window.HarmariumData.postId ? parseInt(window.HarmariumData.postId, 10) : 0;
      if (!exhibitionId || !window.HarmariumData) return;
      try {
        const res = await fetch(window.HarmariumData.restUrl + 'exhibition/' + exhibitionId);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        const items = data?.harmarium?.items || [];
        items.forEach((it, i) => {
          const a = document.createElement('a');
          a.className = 'harmarium-wall__work';
          a.href = it.permalink;
          a.style.left = (10 + (i % 5) * 18 + Math.random() * 4) + '%';
          a.style.top  = (20 + Math.floor(i / 5) * 28 + Math.random() * 4) + '%';
          a.innerHTML = `<img loading="lazy" src="${it.thumb}" alt=""><figcaption>${it.title}</figcaption>`;
          plane.appendChild(a);
        });
      } catch (e) {
        console.error('Harmarium: failed to load exhibition items:', e);
      }

      // Pan + zoom — window listeners removed on pointerup to prevent leaks.
      let scale = 1, tx = 0, ty = 0, sx = 0, sy = 0;
      function apply() { plane.style.transform = `translate(${tx}px, ${ty}px) scale(${scale})`; }

      function onMove(e) { tx = e.clientX - sx; ty = e.clientY - sy; apply(); }
      function onUp() {
        wall.style.cursor = '';
        window.removeEventListener('pointermove', onMove);
        window.removeEventListener('pointerup', onUp);
      }

      wall.addEventListener('wheel', (e) => { e.preventDefault(); scale = Math.min(2.4, Math.max(.5, scale - e.deltaY * 0.0015)); apply(); }, { passive: false });
      wall.addEventListener('pointerdown', (e) => {
        sx = e.clientX - tx; sy = e.clientY - ty;
        wall.style.cursor = 'grabbing';
        window.addEventListener('pointermove', onMove);
        window.addEventListener('pointerup', onUp);
      });
      wall.addEventListener('click', (e) => {
        const ctrl = e.target.closest('[data-harmarium-wall-control]');
        if (!ctrl) return;
        const k = ctrl.dataset.harmariumWallControl;
        if (k === 'zoom-in')  { scale = Math.min(2.4, scale + .15); }
        if (k === 'zoom-out') { scale = Math.max(.5,  scale - .15); }
        if (k === 'reset')    { scale = 1; tx = 0; ty = 0; }
        apply();
      });
    });
  }

  /* ──────────── Reveal on scroll ──────────── */
  function initReveal() {
    if (reduceMotion || !('IntersectionObserver' in window)) return;
    const targets = $$('.is-style-harmarium-card, .wp-block-cover, .harmarium-gallery__item');
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = '';
          io.unobserve(entry.target);
        }
      });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 });
    targets.forEach((t) => {
      t.style.opacity = '0'; t.style.transform = 'translateY(12px)';
      t.style.transition = 'opacity .8s var(--hm-ease, ease), transform .8s var(--hm-ease, ease)';
      io.observe(t);
    });
  }

  function ready(fn) { document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', fn) : fn(); }
  ready(() => { initLightbox(); initFilter(); initMockup(); initWall(); initReveal(); });
})();
