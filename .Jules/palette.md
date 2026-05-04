# Palette's Journal - Harmarium Portfolio

## 2025-05-04 - High Contrast Requirements for Dark Gallery
**Learning:** The 3D Atrium gallery uses a very dark background (#020203). Zinc-500 and Zinc-600 do not meet WCAG AA contrast ratios in this environment.
**Action:** Always use Zinc-400 or lighter for text elements within the gallery container to ensure accessibility.

## 2025-05-04 - Semantic Mirroring for 3D Content
**Learning:** 3D Canvas elements are invisible to screen readers.
**Action:** Implement a semantic mirror using an `sr-only` list (`ul/li`) that replicates the content of the 3D gallery, allowing assistive technologies to announce the items and their descriptions.

## 2025-05-04 - Asset Resilience
**Learning:** WordPress media library can contain broken or external links (e.g., harmarium.com) that fail to load in THREE.js due to CORS or availability.
**Action:** Implement a robust local fallback set and filter out problematic external URLs to prevent "Experience Temporarily Unavailable" states.

## 2025-05-04 - Custom Portfolio Endpoint Integration
**Learning:** The Harmarium WordPress setup uses a custom `portfolio` post type rather than the standard `posts` for artwork.
**Action:** Use the `/wp/v2/portfolio?_embed` endpoint to retrieve artwork, as it includes the necessary `wp:featuredmedia` in the embedded response.
