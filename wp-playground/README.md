# WP Playground — Harmarium Preview

## Instant preview link

Once the theme and plugin ZIPs are published to the repository, open this URL in a browser:

```
https://playground.wordpress.net/#{"$schema":"https://playground.wordpress.net/blueprint-schema.json","phpVersion":"8.2","wordPressVersion":"latest","login":true,"features":{"networking":true},"steps":[{"step":"installPlugin","pluginData":{"resource":"wordpress.org/plugins","slug":"woocommerce"},"activate":true},{"step":"installPlugin","pluginData":{"resource":"wordpress.org/plugins","slug":"advanced-custom-fields"},"activate":true},{"step":"installTheme","themeData":{"resource":"url","url":"https://github.com/dexit/harmarium/raw/claude/harmarium-fse-theme-woo-euigA/wp-theme/harmarium.zip"},"options":{"activate":true}},{"step":"installPlugin","pluginData":{"resource":"url","url":"https://github.com/dexit/harmarium/raw/claude/harmarium-fse-theme-woo-euigA/wp-plugin/harmarium-extensions.zip"},"activate":true}]}
```

Or use the full `blueprint.json` in this directory which also seeds demo content:

```
https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/dexit/harmarium/claude/harmarium-fse-theme-woo-euigA/wp-playground/blueprint.json
```

## Building the ZIPs

Before the Playground links work, the theme and plugin directories must be
zipped and committed (or served from a publicly accessible URL).

```bash
# From repo root:
cd wp-theme  && zip -r harmarium.zip harmarium/  -x "*.DS_Store" && cd ..
cd wp-plugin && zip -r harmarium-extensions.zip harmarium-extensions/ -x "*.DS_Store" && cd ..
```

Then commit `wp-theme/harmarium.zip` and `wp-plugin/harmarium-extensions.zip`.

## What the Blueprint installs

| Step | What |
|------|------|
| WooCommerce (wordpress.org) | Gallery shop |
| Advanced Custom Fields (wordpress.org) | ACF bridge used by theme |
| Harmarium theme (ZIP from repo) | FSE block theme |
| Harmarium Extensions plugin (ZIP from repo) | Blocks, commissions, QR codes |
| Seed PHP | 6 portfolio artworks, front page, Virtual Gallery page, Commission page, 1 product |
| Permalinks | `/%postname%/` |
