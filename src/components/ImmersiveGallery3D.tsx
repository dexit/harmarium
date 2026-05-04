'use client'

import * as THREE from 'three'
import { useState, useEffect, Suspense, useMemo } from 'react'
import { Canvas, useFrame, useThree } from '@react-three/fiber'
import { Html, useProgress } from '@react-three/drei'
import { GalleryEnvironment } from './GalleryEnvironment'
import { ArtworkFrame } from './ArtworkFrame'
import { GalleryController } from './GalleryController'
import { GallerySidePanel } from './GallerySidePanel'
import { ErrorBoundary } from './ErrorBoundary'

const GALLERY_IMAGES = [
  '/images/gallery/679552251_1407341641437944_5566833434944317801_n.jpg',
  '/images/gallery/680428219_1411180054387436_9040440674546774895_n.jpg',
  '/images/gallery/682617251_1411180031054105_7141077929646009908_n.jpg',
  '/images/gallery/682617251_1411180034387438_4588160502983284951_n.jpg',
  '/images/gallery/682665321_1410376504467791_1932792919615144817_n.jpg',
  '/images/gallery/684684163_1414084144097027_5240992258635284109_n.jpg',
  '/images/gallery/684692825_1411180057720769_668941511238367822_n.jpg',
]

export interface GalleryArtwork {
  id: string
  title: string
  description: string
  category: string
  imagePath: string
  position: [number, number, number]
  rotation: [number, number, number]
  scale: number
}

// Create artwork data from images
function createGalleryData(): GalleryArtwork[] {
  const artworks: GalleryArtwork[] = []
  const positions: [number, number, number][] = [
    [-8, 1.5, -5],
    [-4, 1.5, -8],
    [0, 1.5, -8],
    [4, 1.5, -8],
    [8, 1.5, -5],
    [6, 1.5, 3],
    [-6, 1.5, 3],
  ]

  const categories = ['Abstract', 'Digital Art', 'Mixed Media', 'Photography', 'Sculpture', 'Installation', 'Contemporary']

  GALLERY_IMAGES.forEach((imagePath, index) => {
    const position = positions[index] || [0, 1.5, 0]
    artworks.push({
      id: `artwork-${index}`,
      title: `Artwork ${index + 1}`,
      description: `An exquisite piece from the Harmarium collection showcasing contemporary artistry and creative expression.`,
      category: categories[index % categories.length],
      imagePath,
      position,
      rotation: [0, 0, 0],
      scale: 1,
    })
  })

  return artworks
}

function Loader() {
  const { progress } = useProgress()
  return (
    <Html center>
      <div className="flex flex-col items-center gap-4 bg-black/60 backdrop-blur-sm px-8 py-6 rounded-2xl border border-white/10">
        <div className="w-48 h-1 bg-zinc-800 rounded-full overflow-hidden">
          <div
            className="h-full bg-white transition-all duration-300 ease-out"
            style={{ width: `${progress}%` }}
            role="progressbar"
            aria-valuenow={Math.round(progress)}
            aria-valuemin={0}
            aria-valuemax={100}
          />
        </div>
        <span className="text-zinc-400 text-[10px] font-bold tracking-[0.4em] uppercase" aria-live="polite">
          Gallery Loading {Math.round(progress)}%
        </span>
      </div>
    </Html>
  )
}

interface SceneProps {
  artworks: GalleryArtwork[]
  selectedArtwork: GalleryArtwork | null
  onSelectArtwork: (artwork: GalleryArtwork | null) => void
  isGuidedMode: boolean
}

function Scene({ artworks, selectedArtwork, onSelectArtwork, isGuidedMode }: SceneProps) {
  return (
    <>
      <color attach="background" args={['#f5f5f5']} />
      <fog attach="fog" args={['#f5f5f5', 2, 40]} />

      <ambientLight intensity={0.6} />
      <directionalLight position={[10, 15, 10]} intensity={0.8} castShadow />
      <pointLight position={[-10, 8, -10]} intensity={0.4} color="#e0d5c7" />

      <GalleryEnvironment />

      {artworks.map((artwork) => (
        <ArtworkFrame
          key={artwork.id}
          artwork={artwork}
          isSelected={selectedArtwork?.id === artwork.id}
          onSelect={onSelectArtwork}
        />
      ))}

      <GalleryController
        artworks={artworks}
        isGuidedMode={isGuidedMode}
      />
    </>
  )
}

const FallbackUI = () => (
  <div className="h-full w-full bg-zinc-950 flex items-center justify-center flex-col gap-4">
    <span className="text-zinc-400 text-[10px] font-bold tracking-[0.4em] uppercase">
      Gallery Experience Unavailable
    </span>
    <button
      onClick={() => window.location.reload()}
      className="text-white text-[8px] uppercase tracking-widest px-4 py-2 border border-white/20 hover:bg-white hover:text-black transition-colors"
    >
      Retry
    </button>
  </div>
)

export default function ImmersiveGallery3D() {
  const [mounted, setMounted] = useState(false)
  const [selectedArtwork, setSelectedArtwork] = useState<GalleryArtwork | null>(null)
  const [isGuidedMode, setIsGuidedMode] = useState(true)
  const artworks = useMemo(() => createGalleryData(), [])

  useEffect(() => {
    setMounted(true)
  }, [])

  if (!mounted) {
    return (
      <div className="h-screen w-full bg-zinc-950 rounded-2xl flex items-center justify-center">
        <span className="text-zinc-400 text-xs font-bold tracking-[0.4em] uppercase">
          Initializing Gallery
        </span>
      </div>
    )
  }

  return (
    <div className="h-screen w-full relative bg-white overflow-hidden" role="region" aria-label="Immersive 3D Art Gallery">
      <ErrorBoundary fallback={<FallbackUI />}>
        <Canvas
          camera={{ position: [0, 1.6, 8], fov: 75 }}
          dpr={[1, 2]}
          gl={{ antialias: true, alpha: false, powerPreference: 'high-performance' }}
          style={{ width: '100%', height: '100%' }}
          shadows="soft"
        >
          <Suspense fallback={<Loader />}>
            <Scene
              artworks={artworks}
              selectedArtwork={selectedArtwork}
              onSelectArtwork={setSelectedArtwork}
              isGuidedMode={isGuidedMode}
            />
          </Suspense>
        </Canvas>

        {/* Mode Toggle Button - Game-like HUD */}
        <div className="absolute top-6 left-6 z-50 flex items-center gap-3">
          <button
            onClick={() => setIsGuidedMode(!isGuidedMode)}
            className="px-4 py-2 bg-black/40 backdrop-blur-xl border border-white/30 text-white text-sm rounded font-mono hover:bg-black/60 hover:border-white/50 transition-all duration-200 font-bold tracking-wider"
          >
            [{isGuidedMode ? 'TOUR' : 'FREE'}]
          </button>
          <div className="px-3 py-2 bg-black/40 backdrop-blur-xl border border-white/30 text-white text-xs rounded font-mono">
            FPS GALLERY
          </div>
        </div>

        {/* Crosshair - Game HUD element */}
        {!isGuidedMode && (
          <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-40 pointer-events-none">
            <div className="w-8 h-8 border-2 border-white/30 rounded-full"></div>
            <div className="absolute top-1/2 left-1/2 w-4 h-0.5 bg-white/30 transform -translate-x-1/2 -translate-y-1/2"></div>
            <div className="absolute top-1/2 left-1/2 h-4 w-0.5 bg-white/30 transform -translate-x-1/2 -translate-y-1/2"></div>
          </div>
        )}

        {/* Instructions - Bottom Left Corner (Game Style) */}
        <div className="absolute bottom-6 left-6 z-50 text-white text-xs bg-black/40 backdrop-blur-xl border border-white/20 px-4 py-3 rounded font-mono space-y-1 max-w-xs">
          <p className="font-bold text-white/80 mb-2 tracking-wider">CONTROLS</p>
          {isGuidedMode ? (
            <>
              <p><span className="text-yellow-400">SCROLL</span> - Navigate tour</p>
              <p><span className="text-yellow-400">MOUSE</span> - Look around</p>
              <p><span className="text-yellow-400">CLICK</span> - Inspect artwork</p>
            </>
          ) : (
            <>
              <p><span className="text-yellow-400">W/A/S/D</span> - Move</p>
              <p><span className="text-yellow-400">MOUSE</span> - Look around</p>
              <p><span className="text-yellow-400">CLICK</span> - Lock/Unlock mouse</p>
            </>
          )}
        </div>

        {/* Artwork Counter - Bottom Right */}
        <div className="absolute bottom-6 right-6 z-50 text-white text-xs bg-black/40 backdrop-blur-xl border border-white/20 px-4 py-3 rounded font-mono">
          <p className="text-white/60">ARTWORKS LOADED</p>
          <p className="text-yellow-400 text-lg font-bold">{artworks.length}</p>
        </div>

        {/* Crosshair Label - Center (when hovering artworks in free mode) */}
        {!isGuidedMode && selectedArtwork && (
          <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 translate-y-8 z-40 pointer-events-none">
            <div className="px-3 py-1 bg-yellow-400/80 text-black text-xs font-bold rounded font-mono">
              [{selectedArtwork.title}]
            </div>
          </div>
        )}

        {/* Side Panel */}
        <GallerySidePanel
          artwork={selectedArtwork}
          onClose={() => setSelectedArtwork(null)}
        />
      </ErrorBoundary>
    </div>
  )
}
