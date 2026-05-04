'use client'

import * as THREE from 'three'

export function GalleryEnvironment() {
  return (
    <group>
      {/* Floor - polished concrete with subtle texture */}
      <mesh position={[0, -0.02, 0]} rotation={[-Math.PI / 2, 0, 0]} receiveShadow>
        <planeGeometry args={[30, 30]} />
        <meshStandardMaterial 
          color="#d8d5d0" 
          metalness={0.2} 
          roughness={0.6}
          side={THREE.DoubleSide}
        />
      </mesh>

      {/* Ceiling - soft white with slight glow */}
      <mesh position={[0, 4, 0]} rotation={[Math.PI / 2, 0, 0]} receiveShadow>
        <planeGeometry args={[30, 30]} />
        <meshStandardMaterial 
          color="#fafaf8" 
          metalness={0} 
          roughness={0.9}
          emissive="#f5f5f5"
          emissiveIntensity={0.1}
        />
      </mesh>

      {/* Back Wall - main display wall */}
      <mesh position={[0, 2, -12]} receiveShadow>
        <planeGeometry args={[30, 4]} />
        <meshStandardMaterial 
          color="#efefed" 
          metalness={0} 
          roughness={0.8}
        />
      </mesh>

      {/* Front Wall */}
      <mesh position={[0, 2, 12]} receiveShadow>
        <planeGeometry args={[30, 4]} />
        <meshStandardMaterial 
          color="#f2f0ed" 
          metalness={0} 
          roughness={0.8}
        />
      </mesh>

      {/* Left Wall */}
      <mesh position={[-15, 2, 0]} rotation={[0, Math.PI / 2, 0]} receiveShadow>
        <planeGeometry args={[30, 4]} />
        <meshStandardMaterial 
          color="#f5f3f1" 
          metalness={0} 
          roughness={0.8}
        />
      </mesh>

      {/* Right Wall */}
      <mesh position={[15, 2, 0]} rotation={[0, -Math.PI / 2, 0]} receiveShadow>
        <planeGeometry args={[30, 4]} />
        <meshStandardMaterial 
          color="#f5f3f1" 
          metalness={0} 
          roughness={0.8}
        />
      </mesh>

      {/* Base trim/molding - adds depth and realism */}
      <mesh position={[0, 0.15, -12]} receiveShadow>
        <boxGeometry args={[30.5, 0.3, 0.2]} />
        <meshStandardMaterial 
          color="#c0b8b0" 
          metalness={0.3} 
          roughness={0.6}
        />
      </mesh>

      <mesh position={[0, 0.15, 12]} receiveShadow>
        <boxGeometry args={[30.5, 0.3, 0.2]} />
        <meshStandardMaterial 
          color="#c0b8b0" 
          metalness={0.3} 
          roughness={0.6}
        />
      </mesh>

      {/* Professional Gallery Lighting System */}
      {/* Main back wall spotlights */}
      <spotLight
        position={[-10, 3.5, -9]}
        target-position={[-10, 1.5, -12]}
        angle={Math.PI / 4}
        penumbra={0.7}
        intensity={1.2}
        castShadow
        color="#fffef0"
        shadow-mapSize={[1024, 1024]}
      />
      
      <spotLight
        position={[0, 3.5, -9]}
        target-position={[0, 1.5, -12]}
        angle={Math.PI / 4}
        penumbra={0.7}
        intensity={1.2}
        castShadow
        color="#fffef0"
        shadow-mapSize={[1024, 1024]}
      />
      
      <spotLight
        position={[10, 3.5, -9]}
        target-position={[10, 1.5, -12]}
        angle={Math.PI / 4}
        penumbra={0.7}
        intensity={1.2}
        castShadow
        color="#fffef0"
        shadow-mapSize={[1024, 1024]}
      />

      {/* Side wall accent lights */}
      <spotLight
        position={[13, 3, 0]}
        angle={Math.PI / 3}
        penumbra={0.5}
        intensity={0.6}
        color="#fffef0"
      />

      <spotLight
        position={[-13, 3, 0]}
        angle={Math.PI / 3}
        penumbra={0.5}
        intensity={0.6}
        color="#fffef0"
      />

      {/* Subtle ambient light from floor reflections */}
      <spotLight
        position={[0, -0.5, 0]}
        angle={Math.PI / 2}
        penumbra={1}
        intensity={0.3}
        color="#e8e8e8"
      />

      {/* Corner shadows for realism */}
      <mesh position={[-14.9, 1, -11.9]} castShadow receiveShadow>
        <boxGeometry args={[0.2, 2, 0.2]} />
        <meshStandardMaterial color="#0a0a0a" metalness={0} roughness={1} />
      </mesh>

      <mesh position={[14.9, 1, -11.9]} castShadow receiveShadow>
        <boxGeometry args={[0.2, 2, 0.2]} />
        <meshStandardMaterial color="#0a0a0a" metalness={0} roughness={1} />
      </mesh>

      <mesh position={[-14.9, 1, 11.9]} castShadow receiveShadow>
        <boxGeometry args={[0.2, 2, 0.2]} />
        <meshStandardMaterial color="#0a0a0a" metalness={0} roughness={1} />
      </mesh>

      <mesh position={[14.9, 1, 11.9]} castShadow receiveShadow>
        <boxGeometry args={[0.2, 2, 0.2]} />
        <meshStandardMaterial color="#0a0a0a" metalness={0} roughness={1} />
      </mesh>
    </group>
  )
}

