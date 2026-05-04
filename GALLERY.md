# Immersive 3D Art Gallery

## Overview

The Harmarium gallery is now an immersive first-person 3D experience that allows visitors to walk through a professional art gallery space and interact with artworks in real-time.

## Features

### Dual Movement Modes

#### Guided Tour Mode (Default)
- **Auto-Navigate**: Scroll through the gallery automatically following a predefined path
- **Mouse Look**: Move your mouse to look around while the gallery guides your movement
- **Scroll Control**: Use scroll wheel to adjust speed or pause the tour
- Perfect for visitors who want a guided, hands-free experience

#### Free Exploration Mode
- **Full Control**: Use WASD keys to move freely around the gallery
- **Mouse Look**: Move mouse to look in any direction
- **Bounded Space**: Movement is confined to the gallery area to prevent walking through walls
- Perfect for visitors who want to explore at their own pace

### Mode Toggling
- Click the **"🎬 Guided Tour" / "🎮 Free Mode"** button in the top-left corner to switch between modes
- Smooth camera transitions when switching modes

### Artwork Interaction
- **Click** any artwork to open the side panel
- The side panel displays:
  - Full-resolution artwork preview
  - Title and category
  - Detailed description
  - 3D position coordinates
  - Share button
- **Click "Back to Gallery"** or the close button to return to exploring

### Professional Gallery Environment
- White-walled gallery space with natural lighting
- Strategic spotlight placement highlighting each artwork
- Polished flooring with reflective properties
- 7 professionally presented artworks from Harmarium collection
- Smooth frame animations when hovering/selecting

## Controls

### Guided Tour Mode
| Control | Action |
|---------|--------|
| Scroll ⬇️ | Speed up tour |
| Scroll ⬆️ | Slow down / pause |
| 🖱️ Mouse | Look around |
| 🖱️ Click | Inspect artwork |

### Free Exploration Mode
| Control | Action |
|---------|--------|
| W | Move forward |
| A | Move left |
| S | Move backward |
| D | Move right |
| 🖱️ Mouse | Look around |
| 🖱️ Click | Inspect artwork |

### General
| Control | Action |
|---------|--------|
| Top-Left Button | Toggle between modes |
| ESC or Click Overlay | Close side panel |

## Technical Implementation

### Components

- **ImmersiveGallery3D** - Main component orchestrating the 3D scene and state management
- **GalleryEnvironment** - Creates the gallery architecture (walls, floor, ceiling, lighting)
- **ArtworkFrame** - Individual artwork components with interaction handling
- **GalleryController** - Manages camera movement logic and user input
- **GallerySidePanel** - Slides in from the right with artwork details

### Architecture

Built with:
- **React Three Fiber** - React renderer for Three.js
- **Three.js** - 3D graphics library
- **Next.js** - Full-stack React framework
- **Tailwind CSS** - Utility-first styling

### Key Technologies

- **First-person camera** with smooth interpolation
- **Dynamic artwork loading** from local image files
- **Input handling** for keyboard, mouse, and scroll events
- **State management** for selected artwork and mode toggling
- **Responsive canvas** that adapts to window size

## Gallery Layout

Artworks are strategically positioned throughout the gallery:

```
                Back Wall
        [-8,-2]  [0,-8]  [8,-2]
              ↓    ↓     ↓
             
  [6,3]← Side          →[-6,3]
         Wall          Wall
             ↑    ↑     ↑
        [-8,-2] [0,-2] [8,-2]

                Front
              (Entrance)
```

## Browser Compatibility

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Requires WebGL support

## Performance Notes

- Optimized for 60 FPS on most modern devices
- Automatic LOD (level of detail) adjustments for lower-end hardware
- Efficient texture loading with mipmap support
- Smooth animations using requestAnimationFrame

## Future Enhancements

- Audio guide narration for artworks
- Virtual gallery tours with timeline scrubbing
- Multiplayer presence (see other visitors)
- AR mode for viewing artworks in real-world spaces
- VR headset support
- Customizable gallery layouts and artwork positions
- Save/share favorite artworks
