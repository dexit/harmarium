'use client'

import * as THREE from 'three'
import { useState, useRef } from 'react'
import { useFrame } from '@react-three/fiber'
import { Image as Drei3DImage } from '@react-three/drei'
import type { GalleryArtwork } from './ImmersiveGallery3D'

interface ArtworkFrameProps {
  artwork: GalleryArtwork
  isSelected: boolean
  onSelect: (artwork: GalleryArtwork) => void
}

export function ArtworkFrame({ artwork, isSelected, onSelect }: ArtworkFrameProps) {
  const [hovered, setHovered] = useState(false)
  const frameRef = useRef<THREE.Group>(null)
  const scaleTarget = useRef(1)

  // Update target scale based on hover/selected state
  scaleTarget.current = isSelected ? 1.05 : hovered ? 1.02 : 1

  useFrame(() => {
    if (frameRef.current) {
      frameRef.current.scale.lerp(
        new THREE.Vector3(scaleTarget.current, scaleTarget.current, 1),
        0.1
      )
    }
  })

  const frameWidth = 2.5
  const frameHeight = 3.5
  const frameDepth = 0.1
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
      {/* Wooden Frame */}
      {/* Top */}
      <mesh position={[0, frameHeight / 2 + frameThickness / 2, frameDepth / 2]} castShadow receiveShadow>
        <boxGeometry args={[frameWidth + frameThickness * 2, frameThickness, frameDepth]} />
        <meshStandardMaterial
          color={hovered || isSelected ? '#d4af37' : '#8b7355'}
          metalness={0.3}
          roughness={0.4}
        />
      </mesh>

      {/* Bottom */}
      <mesh position={[0, -frameHeight / 2 - frameThickness / 2, frameDepth / 2]} castShadow receiveShadow>
        <boxGeometry args={[frameWidth + frameThickness * 2, frameThickness, frameDepth]} />
        <meshStandardMaterial
          color={hovered || isSelected ? '#d4af37' : '#8b7355'}
          metalness={0.3}
          roughness={0.4}
        />
      </mesh>

      {/* Left */}
      <mesh position={[-frameWidth / 2 - frameThickness / 2, 0, frameDepth / 2]} castShadow receiveShadow>
        <boxGeometry args={[frameThickness, frameHeight + frameThickness * 2, frameDepth]} />
        <meshStandardMaterial
          color={hovered || isSelected ? '#d4af37' : '#8b7355'}
          metalness={0.3}
          roughness={0.4}
        />
      </mesh>

      {/* Right */}
      <mesh position={[frameWidth / 2 + frameThickness / 2, 0, frameDepth / 2]} castShadow receiveShadow>
        <boxGeometry args={[frameThickness, frameHeight + frameThickness * 2, frameDepth]} />
        <meshStandardMaterial
          color={hovered || isSelected ? '#d4af37' : '#8b7355'}
          metalness={0.3}
          roughness={0.4}
        />
      </mesh>

      {/* Image Canvas */}
      <group position={[0, 0, frameDepth / 2 + 0.01]}>
        <Drei3DImage
          url={artwork.imagePath}
          scale={[frameWidth, frameHeight]}
          position={[0, 0, 0]}
          transparent={false}
        />
      </group>

      {/* Glow Effect when selected */}
      {isSelected && (
        <mesh position={[0, 0, frameDepth / 2 - 0.02]}>
          <planeGeometry args={[frameWidth + frameThickness * 2 + 0.1, frameHeight + frameThickness * 2 + 0.1]} />
          <meshBasicMaterial
            color="#d4af37"
            transparent
            opacity={0.15}
            side={THREE.DoubleSide}
          />
        </mesh>
      )}

      {/* Cursor Hint */}
      {(hovered || isSelected) && (
        <mesh position={[0, -frameHeight / 2 - frameThickness - 0.3, frameDepth / 2]}>
          <planeGeometry args={[1, 0.2]} />
          <meshBasicMaterial
            color="#d4af37"
            transparent
            opacity={0.7}
          />
        </mesh>
      )}
    </group>
  )
}
