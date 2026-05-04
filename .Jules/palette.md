# Palette's Journal - Harmarium

## 2024-05-24 - Headless 3D Foundation
**Learning:** Initializing a headless WordPress frontend with a 3D gallery requires robust fallback mechanisms. WordPress REST API media endpoints can be slow or blocked by CORS in certain environments, so a stable "empty state" or "placeholder state" is essential for the 3D canvas to prevent hydration errors or visual breakage.
**Action:** Always implement a `mounted` state for Three.js canvases in Next.js to avoid SSR/hydration mismatches, and provide high-quality fallback imagery (like picsum.photos) when the WP API is unreachable.

## 2024-05-24 - Accessibility in 3D Spaces
**Learning:** 3D galleries (Three.js) are often "black boxes" for screen readers and keyboard users.
**Action:** Use a hidden semantic list (`sr-only`) that mirrors the gallery content and wrap the canvas in a region with clear ARIA labels. Implement high-contrast `focus-visible` rings globally to ensure the interactive elements surrounding the canvas are easily navigable.

## 2024-05-24 - 3D Loading UX & Asset Reliability
**Learning:** A static "Initializing" message for 3D galleries is insufficient for good UX. Users need feedback during heavy asset loading (textures, geometries). Generic external placeholders (picsum) can lead to broken layouts if the service is throttled or down.
**Action:** Implement a dynamic loading progress indicator using `@react-three/drei`'s `useProgress` with appropriate ARIA `role="progressbar"`. Ship local optimized assets as a "hero" fallback to guarantee a high-fidelity experience regardless of API availability.
