'use client'

import * as THREE from 'three'
import { useState, useEffect, Suspense, useMemo } from 'react'
import { Canvas, useFrame } from '@react-three/fiber'
import { Image, ScrollControls, useScroll, Float, Environment } from '@react-three/drei'
import { WPMedia } from '@/lib/wp'

function Frame({ url, index, total }: { url: string; index: number; total: number }) {
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
    const targetX = Math.sin(angle) * 15
    const targetZ = Math.cos(angle) * 15

    state.camera.position.x = THREE.MathUtils.lerp(state.camera.position.x, targetX, 0.05)
    state.camera.position.z = THREE.MathUtils.lerp(state.camera.position.z, targetZ, 0.05)
    state.camera.lookAt(0, 0, 0)
  })

  return null
}

export default function Gallery3D({ images }: { images: WPMedia[] }) {
  const [mounted, setMounted] = useState(false)

  useEffect(() => {
    // eslint-disable-next-line react-hooks/set-state-in-effect
    setMounted(true)
  }, [])

  const displayImages = useMemo(() => {
    if (images && images.length > 0) return images

    // Only use picsum images to avoid WordPress CORS/undefined issues in this environment
    return Array.from({ length: 12 }).map((_, i) => ({
      id: i,
      source_url: `https://picsum.photos/id/${i + 70}/800/1000`,
      title: { rendered: 'Gallery Piece' },
      alt_text: 'Gallery Piece',
      media_details: { width: 800, height: 1000 }
    } as WPMedia))
  }, [images])

  if (!mounted) {
    return (
      <div className="h-[70vh] w-full bg-zinc-950 rounded-2xl flex items-center justify-center">
        <span className="text-zinc-800 text-xs font-bold tracking-[0.4em] uppercase">
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
      <Canvas
        camera={{ position: [0, 0, 15], fov: 45 }}
        dpr={[1, 1.5]}
        gl={{ antialias: true, alpha: false }}
      >
        <color attach="background" args={['#020203']} />
        <fog attach="fog" args={['#020203', 10, 40]} />
        <ambientLight intensity={0.5} />
        <pointLight position={[10, 10, 10]} intensity={1.5} />

        <Suspense fallback={null}>
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

      <div className="absolute bottom-8 right-8 text-white pointer-events-none select-none text-right transition-all duration-1000 group-hover:opacity-100 opacity-20">
        <h2 className="text-lg font-black tracking-[0.2em] uppercase text-zinc-200">The Atrium</h2>
        <p className="text-[8px] uppercase tracking-[0.4em] text-zinc-500 mt-2">Scroll to Traverse</p>
      </div>

      <div className="sr-only">
        {displayImages.map((img, i) => (
          <div key={img.id}>
            Gallery item {i + 1}: {img.alt_text || img.title.rendered}
          </div>
        ))}
      </div>
    </div>
  )
}
