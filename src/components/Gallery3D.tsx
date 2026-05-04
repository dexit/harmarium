'use client'

import * as THREE from 'three'
import { useState, useEffect, Suspense, useMemo } from 'react'
import { Canvas, useFrame } from '@react-three/fiber'
import { Image, ScrollControls, useScroll, Float, Environment, useProgress, Html } from '@react-three/drei'
import { WPMedia } from '@/lib/wp'
import { ErrorBoundary } from './ErrorBoundary'

const LOCAL_IMAGES = [
  '/images/gallery/679552251_1407341641437944_5566833434944317801_n.jpg',
  '/images/gallery/680428219_1411180054387436_9040440674546774895_n.jpg',
  '/images/gallery/682617251_1411180031054105_7141077929646009908_n.jpg',
  '/images/gallery/682617251_1411180034387438_4588160502983284951_n.jpg',
  '/images/gallery/682665321_1410376504467791_1932792919615144817_n.jpg',
  '/images/gallery/684684163_1414084144097027_5240992258635284109_n.jpg',
  '/images/gallery/684692825_1411180057720769_668941511238367822_n.jpg',
]

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
          Initializing Space {Math.round(progress)}% <span className="sr-only">{Math.round(progress)} percent complete</span>
        </span>
      </div>
    </Html>
  )
}

interface FrameProps {
  url: string
  index: number
  total: number
}

function Frame({ url, index, total }: FrameProps) {
  const [hovered, hover] = useState(false)

  const angle = (index / total) * Math.PI * 2
  const radius = 8
  const x = Math.sin(angle) * radius
  const z = Math.cos(angle) * radius

  return (
    <group
      position={[x, 0, z]}
      rotation={[0, angle + Math.PI, 0]}
    >
      <Float speed={1.5} rotationIntensity={0.2} floatIntensity={0.5}>
        <Image
          url={url}
          scale={[3, 4]}
          onPointerOver={() => hover(true)}
          onPointerOut={() => hover(false)}
          transparent
          opacity={hovered ? 1 : 0.7}
          side={THREE.DoubleSide}
          // @ts-expect-error - 'alt' is missing from ImageProps but expected by a11y lint
          alt={`Gallery item ${index + 1}`}
        />
      </Float>
    </group>
  )
}

function Rig() {
  const scroll = useScroll()

  useFrame((state) => {
    const angle = scroll.offset * Math.PI * 2
    // Base position from scroll
    const targetX = Math.sin(angle) * 15
    const targetZ = Math.cos(angle) * 15

    // Subtle mouse parallax sway
    const parallaxX = state.pointer.x * 2
    const parallaxY = state.pointer.y * 2

    state.camera.position.x = THREE.MathUtils.lerp(state.camera.position.x, targetX + parallaxX, 0.05)
    state.camera.position.y = THREE.MathUtils.lerp(state.camera.position.y, parallaxY, 0.05)
    state.camera.position.z = THREE.MathUtils.lerp(state.camera.position.z, targetZ, 0.05)
    state.camera.lookAt(0, 0, 0)
  })

  return null
}

const FallbackUI = () => (
  <div className="h-full w-full bg-zinc-950 flex items-center justify-center flex-col gap-4">
    <span className="text-zinc-400 text-[10px] font-bold tracking-[0.4em] uppercase">
      Experience Temporarily Unavailable
    </span>
    <button
      onClick={() => window.location.reload()}
      className="text-white text-[8px] uppercase tracking-widest px-4 py-2 border border-white/20 hover:bg-white hover:text-black transition-colors"
    >
      Retry Connection
    </button>
  </div>
)

export default function Gallery3D({ images }: { images: WPMedia[] }) {
  const [mounted, setMounted] = useState(false)

  useEffect(() => {
    // eslint-disable-next-line react-hooks/set-state-in-effect
    setMounted(true)
  }, [])

  const displayImages = useMemo(() => {
    // Include all fetched images (up to 8) to prioritize the portfolio artwork
    const wpImages = (images || []).slice(0, 8)

    // Fallback to local set if WP is empty
    if (wpImages.length === 0) {
      return LOCAL_IMAGES.map((url, i) => ({
        id: i + 1000,
        source_url: url,
        title: { rendered: 'Atrium Piece' },
        alt_text: 'Atrium Piece',
        media_details: { width: 800, height: 1000 }
      } as WPMedia))
    }

    return wpImages
  }, [images])

  if (!mounted) {
    return (
      <div className="h-[70vh] w-full bg-zinc-950 rounded-2xl flex items-center justify-center">
        <span className="text-zinc-400 text-xs font-bold tracking-[0.4em] uppercase">
          Initializing Space
        </span>
      </div>
    )
  }

  return (
    <div
      className="h-[70vh] w-full bg-zinc-950 rounded-2xl overflow-hidden shadow-2xl ring-1 ring-white/10 relative group outline-none"
      role="region"
      aria-label="3D Image Gallery"
    >
      <ErrorBoundary fallback={<FallbackUI />}>
        <Canvas
          camera={{ position: [0, 0, 15], fov: 45 }}
          dpr={[1, 1.5]}
          gl={{ antialias: true, alpha: false, preserveDrawingBuffer: true }}
        >
          <color attach="background" args={['#020203']} />
          <fog attach="fog" args={['#020203', 10, 40]} />
          <ambientLight intensity={0.5} />
          <pointLight position={[10, 10, 10]} intensity={1.5} />

          <Suspense fallback={<Loader />}>
            <ScrollControls pages={4} damping={0.2} infinite>
              <Rig />
              {displayImages.map((img, i) => (
                <Frame
                  key={`frame-${img.id}-${i}`}
                  url={img.source_url}
                  index={i}
                  total={displayImages.length}
                />
              ))}
              <Environment preset="night" />
            </ScrollControls>
          </Suspense>
        </Canvas>
      </ErrorBoundary>

      <div className="absolute bottom-8 right-8 text-white pointer-events-none select-none text-right transition-all duration-1000 group-hover:opacity-100 opacity-20">
        <h2 className="text-lg font-black tracking-[0.2em] uppercase text-zinc-200">The Atrium</h2>
        <p className="text-[8px] uppercase tracking-[0.4em] text-zinc-400 mt-2">Scroll to Traverse</p>
      </div>

      <ul className="sr-only">
        {displayImages.map((img, i) => (
          <li key={img.id}>
            Gallery item {i + 1}: {img.alt_text || img.title.rendered}
          </li>
        ))}
      </ul>
    </div>
  )
}
