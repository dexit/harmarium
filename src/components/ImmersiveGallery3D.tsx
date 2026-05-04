'use client'

import * as THREE from 'three'
import { useState, useEffect, Suspense, useMemo } from 'react'
import { Canvas, useFrame, useThree } from '@react-three/fiber'
import { Html, useProgress } from '@react-three/drei'
import { RoomEnvironment } from './RoomEnvironment'
import { ArtworkFrame } from './ArtworkFrame'
import { GalleryController } from './GalleryController'
import { GallerySidePanel } from './GallerySidePanel'
import { ErrorBoundary } from './ErrorBoundary'
import { ARTWORK_DATA, GALLERY_ROOMS } from '@/lib/galleryData'

export interface GalleryArtwork {
  id: string
  title: string
  description: string
  category: string
  imagePath: string
  position: [number, number, number]
  rotation: [number, number, number]
  scale: number
  room: string
}

// Fetch random placeholder image based on hash
function getPlaceholderImage(id: string): string {
  return `https://api.dicebear.com/7.x/avataaars/svg?seed=${id}`
}

// Create artwork with efficient memory usage
function createGalleryArtworks(): GalleryArtwork[] {
  return ARTWORK_DATA.map((art, index) => ({
    ...art,
    rotation: [0, 0, 0] as [number, number, number],
    scale: 1,
    imagePath: getPlaceholderImage(art.id),
  }))
}

interface SceneProps {
  artworks: GalleryArtwork[]
  selectedArtwork: GalleryArtwork | null
  onSelectArtwork: (artwork: GalleryArtwork) => void
  isGuidedMode: boolean
  currentRoom: string
}

function Scene({
  artworks,
  selectedArtwork,
  onSelectArtwork,
  isGuidedMode,
  currentRoom,
}: SceneProps) {
  return (
    <>
      <color attach="background" args={['#f5f5f5']} />
      <fog attach="fog" args={['#f5f5f5', 8, 80]} />

      <ambientLight intensity={0.4} />
      <directionalLight position={[10, 15, 10]} intensity={0.3} castShadow />

      {/* Multi-room environment */}
      <RoomEnvironment currentRoom={currentRoom} />

      {/* Render only artworks in nearby rooms for performance */}
      {artworks
        .filter(art => {
          const room = GALLERY_ROOMS.find(r => r.id === art.room)
          return room && art.room === currentRoom
        })
        .map(artwork => (
          <ArtworkFrame
            key={artwork.id}
            artwork={artwork}
            isSelected={selectedArtwork?.id === artwork.id}
            onSelect={onSelectArtwork}
          />
        ))}

      <GalleryController artworks={artworks} isGuidedMode={isGuidedMode} />
    </>
  )
}

function Loader() {
  const { progress } = useProgress()
  return (
    <Html center>
      <div className="text-white text-center font-mono">
        <p className="text-lg mb-4">[LOADING GALLERY]</p>
        <div className="w-64 h-2 bg-black/50 border border-yellow-400/50 rounded overflow-hidden">
          <div
            className="h-full bg-yellow-400 transition-all"
            style={{ width: `${progress}%` }}
          />
        </div>
        <p className="text-xs mt-4 text-yellow-400">{progress.toFixed(0)}%</p>
      </div>
    </Html>
  )
}

export default function ImmersiveGallery3D() {
  const [selectedArtwork, setSelectedArtwork] = useState<GalleryArtwork | null>(null)
  const [isGuidedMode, setIsGuidedMode] = useState(true)
  const [currentRoom, setCurrentRoom] = useState('entrance')
  const artworks = useMemo(() => createGalleryArtworks(), [])

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        setSelectedArtwork(null)
      }
    }

    window.addEventListener('keydown', handleKeyDown)
    return () => window.removeEventListener('keydown', handleKeyDown)
  }, [])

  return (
    <ErrorBoundary>
      <div className="w-full h-screen bg-black">
        <Canvas
          camera={{ position: [0, 1.6, -5], fov: 75 }}
          dpr={[1, 2]}
          gl={{
            antialias: true,
            alpha: false,
            powerPreference: 'high-performance',
          }}
          style={{ width: '100%', height: '100%' }}
          shadows="soft"
        >
          <Suspense fallback={<Loader />}>
            <Scene
              artworks={artworks}
              selectedArtwork={selectedArtwork}
              onSelectArtwork={setSelectedArtwork}
              isGuidedMode={isGuidedMode}
              currentRoom={currentRoom}
            />
          </Suspense>
        </Canvas>

        {/* UI Overlay */}
        {/* Mode Toggle Button - Game-like HUD */}
        <div className="absolute top-6 left-6 z-50 flex items-center gap-3">
          <button
            onClick={() => setIsGuidedMode(!isGuidedMode)}
            className="px-4 py-2 bg-black/40 backdrop-blur-xl border border-white/30 text-white text-sm rounded font-mono hover:bg-black/60 hover:border-white/50 transition-all duration-200 font-bold tracking-wider"
          >
            [{isGuidedMode ? 'TOUR' : 'FREE'}]
          </button>
          <div className="px-3 py-2 bg-black/40 backdrop-blur-xl border border-white/30 text-white text-xs rounded font-mono">
            HARMARIUM GALLERY
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

        {/* Instructions - Bottom Left Corner */}
        <div className="absolute bottom-6 left-6 z-50 text-white text-xs bg-black/40 backdrop-blur-xl border border-white/20 px-4 py-3 rounded font-mono space-y-1 max-w-xs">
          <p className="font-bold text-white/80 mb-2 tracking-wider">CONTROLS</p>
          {isGuidedMode ? (
            <>
              <p>
                <span className="text-yellow-400">SCROLL</span> - Navigate tour
              </p>
              <p>
                <span className="text-yellow-400">MOUSE</span> - Look around
              </p>
              <p>
                <span className="text-yellow-400">CLICK</span> - Inspect artwork
              </p>
            </>
          ) : (
            <>
              <p>
                <span className="text-yellow-400">W/A/S/D</span> - Move
              </p>
              <p>
                <span className="text-yellow-400">MOUSE</span> - Look around
              </p>
              <p>
                <span className="text-yellow-400">CLICK</span> - Lock/Unlock mouse
              </p>
            </>
          )}
        </div>

        {/* Room Info - Bottom Center */}
        <div className="absolute bottom-6 left-1/2 transform -translate-x-1/2 z-50 text-white text-xs bg-black/40 backdrop-blur-xl border border-white/20 px-4 py-2 rounded font-mono">
          <p className="text-yellow-400 text-center text-xs uppercase tracking-widest">
            [{GALLERY_ROOMS.find(r => r.id === currentRoom)?.name || 'Unknown'}]
          </p>
        </div>

        {/* Artwork Counter - Bottom Right */}
        <div className="absolute bottom-6 right-6 z-50 text-white text-xs bg-black/40 backdrop-blur-xl border border-white/20 px-4 py-3 rounded font-mono">
          <p className="text-white/60">ARTWORKS LOADED</p>
          <p className="text-yellow-400 text-lg font-bold">{artworks.length}</p>
        </div>

        {/* Side Panel */}
        <GallerySidePanel
          artwork={selectedArtwork}
          isOpen={!!selectedArtwork}
          onClose={() => setSelectedArtwork(null)}
        />
      </div>
    </ErrorBoundary>
  )
}
