'use client'

import * as THREE from 'three'
import { useState, useRef } from 'react'
import { useFrame } from '@react-three/fiber'
import { Image as Drei3DImage, Billboard } from '@react-three/drei'
import type { GalleryArtwork } from './ImmersiveGallery3D'

interface ArtworkFrameProps {
  artwork: GalleryArtwork
  isSelected: boolean
  onSelect: (artwork: GalleryArtwork) => void
}

export function ArtworkFrame({ artwork, isSelected, onSelect }: ArtworkFrameProps) {
  const [hovered, setHovered] = useState(false)
  const frameRef = useRef<THREE.Group>(null)
  const lightRef = useRef<THREE.SpotLight>(null)
  const scaleTarget = useRef(1)

  // Smooth scale transitions
  scaleTarget.current = isSelected ? 1.05 : hovered ? 1.01 : 1

  useFrame(() => {
    if (frameRef.current) {
      frameRef.current.scale.lerp(
        new THREE.Vector3(scaleTarget.current, scaleTarget.current, 1),
        0.08
      )
    }
    
    // Animate spotlight intensity
    if (lightRef.current) {
      const targetIntensity = isSelected ? 1.5 : hovered ? 0.8 : 0.5
      lightRef.current.intensity = THREE.MathUtils.lerp(lightRef.current.intensity, targetIntensity, 0.1)
    }
  })

  const frameWidth = 2.5
  const frameHeight = 3.5
  const frameDepth = 0.12
  const frameThickness = 0.15

  return (
    <group
      ref={frameRef}
      position={artwork.position}
      rotation={artwork.rotation}
      onClick={() => onSelect(artwork)}
      onPointerOver={() => setHovered(true)}
      onPointerOut={() => setHovered(false)}
    >
      {/* Spotlight above artwork */}
      <spotLight
        ref={lightRef}
        position={[0, frameHeight / 2 + 1, frameDepth + 0.5]}
        angle={Math.PI / 4}
        penumbra={0.8}
        intensity={0.5}
        color="#fffef0"
        castShadow
        shadow-mapSize-width={1024}
        shadow-mapSize-height={1024}
      />

      {/* Wooden Frame - Top */}
      <mesh 
        position={[0, frameHeight / 2 + frameThickness / 2, frameDepth / 2]} 
        castShadow 
        receiveShadow
      >
        <boxGeometry args={[frameWidth + frameThickness * 2, frameThickness, frameDepth]} />
        <meshStandardMaterial
          color={hovered || isSelected ? '#d4af37' : '#6b5344'}
          metalness={0.4}
          roughness={0.5}
        />
      </mesh>

      {/* Wooden Frame - Bottom */}
      <mesh 
        position={[0, -frameHeight / 2 - frameThickness / 2, frameDepth / 2]} 
        castShadow 
        receiveShadow
      >
        <boxGeometry args={[frameWidth + frameThickness * 2, frameThickness, frameDepth]} />
        <meshStandardMaterial
          color={hovered || isSelected ? '#d4af37' : '#6b5344'}
          metalness={0.4}
          roughness={0.5}
        />
      </mesh>

      {/* Wooden Frame - Left */}
      <mesh 
        position={[-frameWidth / 2 - frameThickness / 2, 0, frameDepth / 2]} 
        castShadow 
        receiveShadow
      >
        <boxGeometry args={[frameThickness, frameHeight + frameThickness * 2, frameDepth]} />
        <meshStandardMaterial
          color={hovered || isSelected ? '#d4af37' : '#6b5344'}
          metalness={0.4}
          roughness={0.5}
        />
      </mesh>

      {/* Wooden Frame - Right */}
      <mesh 
        position={[frameWidth / 2 + frameThickness / 2, 0, frameDepth / 2]} 
        castShadow 
        receiveShadow
      >
        <boxGeometry args={[frameThickness, frameHeight + frameThickness * 2, frameDepth]} />
        <meshStandardMaterial
          color={hovered || isSelected ? '#d4af37' : '#6b5344'}
          metalness={0.4}
          roughness={0.5}
        />
      </mesh>

      {/* Matte Frame Border */}
      <mesh position={[0, 0, frameDepth / 2 + 0.005]}>
        <planeGeometry args={[frameWidth + frameThickness * 2 + 0.05, frameHeight + frameThickness * 2 + 0.05]} />
        <meshStandardMaterial
          color="#2a2420"
          metalness={0.1}
          roughness={0.8}
        />
      </mesh>

      {/* Image */}
      <group position={[0, 0, frameDepth / 2 + 0.02]}>
        <Drei3DImage
          url={artwork.imagePath}
          scale={[frameWidth - frameThickness * 1.5, frameHeight - frameThickness * 1.5]}
          position={[0, 0, 0]}
          transparent={false}
        />
      </group>

      {/* Glass/Reflection Layer */}
      <mesh position={[0, 0, frameDepth / 2 + 0.03]}>
        <planeGeometry args={[frameWidth - frameThickness * 1.5, frameHeight - frameThickness * 1.5]} />
        <meshStandardMaterial
          color="#ffffff"
          metalness={0}
          roughness={0.1}
          transparent
          opacity={0.02}
        />
      </mesh>

      {/* Subtle glow when selected */}
      {isSelected && (
        <mesh position={[0, 0, frameDepth / 2 - 0.02]}>
          <planeGeometry args={[frameWidth + frameThickness * 2 + 0.2, frameHeight + frameThickness * 2 + 0.2]} />
          <meshBasicMaterial
            color="#d4af37"
            transparent
            opacity={0.1}
            side={THREE.DoubleSide}
          />
        </mesh>
      )}

      {/* Interactive Hint */}
      {(hovered || isSelected) && (
        <Billboard>
          <mesh position={[0, -frameHeight / 2 - 0.5, 0]}>
            <planeGeometry args={[1.2, 0.25]} />
            <meshBasicMaterial
              color="#d4af37"
              transparent
              opacity={0.9}
            />
          </mesh>
        </Billboard>
      )}
    </group>
  )
}
