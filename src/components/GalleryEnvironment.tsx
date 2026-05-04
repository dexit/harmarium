'use client'

import * as THREE from 'three'

export function GalleryEnvironment() {
  return (
    <group>
      {/* Floor */}
      <mesh position={[0, 0, 0]} rotation={[-Math.PI / 2, 0, 0]} receiveShadow>
        <planeGeometry args={[30, 30]} />
        <meshStandardMaterial color="#f0ede8" metalness={0.1} roughness={0.8} />
      </mesh>

      {/* Ceiling */}
      <mesh position={[0, 4, 0]} rotation={[Math.PI / 2, 0, 0]} receiveShadow>
        <planeGeometry args={[30, 30]} />
        <meshStandardMaterial color="#fafaf8" metalness={0} roughness={0.9} />
      </mesh>

      {/* Back Wall */}
      <mesh position={[0, 2, -12]} receiveShadow>
        <planeGeometry args={[30, 4]} />
        <meshStandardMaterial color="#f5f3f0" metalness={0} roughness={0.85} />
      </mesh>

      {/* Front Wall */}
      <mesh position={[0, 2, 12]} receiveShadow>
        <planeGeometry args={[30, 4]} />
        <meshStandardMaterial color="#f5f3f0" metalness={0} roughness={0.85} />
      </mesh>

      {/* Left Wall */}
      <mesh position={[-15, 2, 0]} rotation={[0, Math.PI / 2, 0]} receiveShadow>
        <planeGeometry args={[30, 4]} />
        <meshStandardMaterial color="#faf8f6" metalness={0} roughness={0.85} />
      </mesh>

      {/* Right Wall */}
      <mesh position={[15, 2, 0]} rotation={[0, Math.PI / 2, 0]} receiveShadow>
        <planeGeometry args={[30, 4]} />
        <meshStandardMaterial color="#faf8f6" metalness={0} roughness={0.85} />
      </mesh>

      {/* Gallery Lights - Spotlights for artwork */}
      <spotLight
        position={[-8, 3.5, -5]}
        angle={Math.PI / 6}
        penumbra={0.5}
        intensity={1.2}
        castShadow
        color="#ffffff"
      />
      <spotLight
        position={[-4, 3.5, -8]}
        angle={Math.PI / 6}
        penumbra={0.5}
        intensity={1.2}
        castShadow
        color="#ffffff"
      />
      <spotLight
        position={[0, 3.5, -8]}
        angle={Math.PI / 6}
        penumbra={0.5}
        intensity={1.2}
        castShadow
        color="#ffffff"
      />
      <spotLight
        position={[4, 3.5, -8]}
        angle={Math.PI / 6}
        penumbra={0.5}
        intensity={1.2}
        castShadow
        color="#ffffff"
      />
      <spotLight
        position={[8, 3.5, -5]}
        angle={Math.PI / 6}
        penumbra={0.5}
        intensity={1.2}
        castShadow
        color="#ffffff"
      />
      <spotLight
        position={[6, 3.5, 3]}
        angle={Math.PI / 6}
        penumbra={0.5}
        intensity={1.2}
        castShadow
        color="#ffffff"
      />
      <spotLight
        position={[-6, 3.5, 3]}
        angle={Math.PI / 6}
        penumbra={0.5}
        intensity={1.2}
        castShadow
        color="#ffffff"
      />
    </group>
  )
}
