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
          camera={{ position: [0, 1.6, 0], fov: 70 }}
          dpr={[1, 1.5]}
          gl={{ antialias: true, alpha: false }}
          style={{ width: '100%', height: '100%' }}
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

        {/* Mode Toggle Button */}
        <div className="absolute top-6 left-6 z-50">
          <button
            onClick={() => setIsGuidedMode(!isGuidedMode)}
            className="px-4 py-2 bg-white/10 backdrop-blur-md border border-white/20 text-white text-sm rounded-lg hover:bg-white/20 transition-colors font-medium"
          >
            {isGuidedMode ? '🎬 Guided Tour' : '🎮 Free Mode'}
          </button>
        </div>

        {/* Instructions */}
        <div className="absolute top-6 right-6 z-50 text-right text-white text-xs bg-white/5 backdrop-blur-md border border-white/10 px-4 py-3 rounded-lg max-w-xs">
          <p className="font-semibold mb-2">Controls</p>
          {isGuidedMode ? (
            <>
              <p>🖱️ Scroll: Move through gallery</p>
              <p>🔄 Mouse: Look around</p>
              <p>🖱️ Click: Inspect artwork</p>
            </>
          ) : (
            <>
              <p>⌨️ WASD: Move around</p>
              <p>🖱️ Mouse: Look around</p>
              <p>🖱️ Click: Inspect artwork</p>
            </>
          )}
        </div>

        {/* Side Panel */}
        <GallerySidePanel
          artwork={selectedArtwork}
          onClose={() => setSelectedArtwork(null)}
        />
      </ErrorBoundary>
    </div>
  )
}
