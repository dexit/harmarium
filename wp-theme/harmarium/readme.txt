=== Harmarium ===
Contributors: harmarium
Tested up to: 6.7
Stable tag: 1.0.0
Requires at least: 6.5
Requires PHP: 7.4
License: GPLv2 or later

A modern, interactive Full Site Editing block theme for Harmarium Portrait Art,
with a virtual exhibition wall, a portfolio CPT, an "exhibition" CPT that holds
curated portfolio sets, and a full WooCommerce gallery shop including a
configurable real-life mockup preview that places the artwork on a real-room wall.

== Features ==

* FSE / block theme with theme.json (colors, fluid type, shadows, duotones).
* Custom post types
  - portfolio  (artworks; auto-extends the existing harmarium.com REST endpoint)
  - exhibition (curated set of portfolio items + wall colour, lighting, floor)
* Taxonomies: artwork_type, portfolio_category, portfolio_tag.
* Templates for: front-page, home, page, single, search, 404, archive, archive-portfolio,
  single-portfolio, archive-exhibition, single-exhibition, archive-product,
  single-product, page-cart, page-checkout, plus three custom page templates.
* WooCommerce
  - Full block-checkout / block-cart / shop templates.
  - Block product gallery, zoom, lightbox, slider.
  - HPOS + cart/checkout-blocks compatibility declared.
  - Per-product "Harmarium Mockup" tab: linked artwork, scene, frame, mat,
    real cm dimensions, X/Y/scale offsets — all configurable. Defaults via
    Customizer (Customize → Harmarium · Product mockup).
  - Front-end interactive preview (drag to reposition, switch room, switch frame,
    resize). Plain CSS frames so any uploaded scene works.
* Virtual exhibition wall: pan + zoom, items pulled live via REST.
* LD+JSON: Organization, WebSite + SearchAction, BreadcrumbList, VisualArtwork
  (portfolio singles), Product/Offer (Woo singles), ExhibitionEvent (exhibition
  singles), CollectionPage + ItemList (archives & shop), SearchResultsPage.
  Auto-defers Organization/WebSite when Yoast or Rank Math is active.
* Editor compatibility
  - Gutenberg: block styles, pattern categories, editor stylesheet, full FSE.
  - Elementor: theme location registration, canvas + full-width templates,
    shared content stylesheet, body class for Elementor-built pages.
* Accessibility: prefers-reduced-motion respected, focus-visible on buttons,
  ARIA on lightbox + mockup controls.

== Asset placeholders ==

Scenes referenced from /assets/images/scenes/{living-room,minimal-loft,studio-wall,
concrete-loft,dark-gallery,sunlit-room}.jpg can be replaced site-wide; per
product, Customize → Harmarium · Product mockup → "Custom scene image" overrides.

== Changelog ==

= 1.0.0 =
Initial release.
