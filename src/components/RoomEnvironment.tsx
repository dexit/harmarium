'use client'

import * as THREE from 'three'
import { useState, useRef } from 'react'
import { useThree, useFrame } from '@react-three/fiber'
import { GALLERY_ROOMS, ROOM_CONNECTIONS } from '@/lib/galleryData'

interface RoomEnvironmentProps {
  currentRoom: string
}

export function RoomEnvironment({ currentRoom }: RoomEnvironmentProps) {
  const { camera } = useThree()
  const [visibleRooms, setVisibleRooms] = useState<Set<string>>(new Set([currentRoom]))
  const prevRoomRef = useRef(currentRoom)

  // Update visible rooms based on camera position
  useFrame(() => {
    const currentRoomData = GALLERY_ROOMS.find(r => r.id === currentRoom)
    if (!currentRoomData) return

    const bounds = currentRoomData.bounds
    const isInRoom =
      camera.position.x >= bounds.min[0] &&
      camera.position.x <= bounds.max[0] &&
      camera.position.z >= bounds.min[2] &&
      camera.position.z <= bounds.max[2]

    if (!isInRoom && prevRoomRef.current !== currentRoom) {
      // Detect which room player is in
      const newRoom = GALLERY_ROOMS.find(room => {
        const b = room.bounds
        return (
          camera.position.x >= b.min[0] &&
          camera.position.x <= b.max[0] &&
          camera.position.z >= b.min[2] &&
          camera.position.z <= b.max[2]
        )
      })

      if (newRoom) {
        prevRoomRef.current = newRoom.id
        // Update visible rooms - current + adjacent
        const adjacent = ROOM_CONNECTIONS[newRoom.id] || []
        setVisibleRooms(new Set([newRoom.id, ...adjacent]))
      }
    }
  })

  return (
    <group>
      {GALLERY_ROOMS.map(room => (
        <RoomGeometry
          key={room.id}
          room={room}
          isVisible={visibleRooms.has(room.id)}
          isActive={room.id === currentRoom}
        />
      ))}
      {/* Hallway connectors */}
      <HallwayConnectors />
    </group>
  )
}

interface RoomGeometryProps {
  room: (typeof GALLERY_ROOMS)[0]
  isVisible: boolean
  isActive: boolean
}

function RoomGeometry({ room, isVisible, isActive }: RoomGeometryProps) {
  if (!isVisible) return null

  const bounds = room.bounds
  const width = bounds.max[0] - bounds.min[0]
  const height = bounds.max[1] - bounds.min[1]
  const depth = bounds.max[2] - bounds.min[2]
  const centerX = (bounds.min[0] + bounds.max[0]) / 2
  const centerY = (bounds.min[1] + bounds.max[1]) / 2
  const centerZ = (bounds.min[2] + bounds.max[2]) / 2

  const floorColor = isActive ? '#e8e6e0' : '#d8d5d0'
  const wallColor = isActive ? '#fafaf8' : '#f5f3f1'

  return (
    <group name={`room-${room.id}`}>
      {/* Floor */}
      <mesh
        position={[centerX, bounds.min[1], centerZ]}
        rotation={[-Math.PI / 2, 0, 0]}
        receiveShadow
      >
        <planeGeometry args={[width, depth]} />
        <meshStandardMaterial
          color={floorColor}
          metalness={0.15}
          roughness={0.6}
          side={THREE.DoubleSide}
        />
      </mesh>

      {/* Ceiling */}
      <mesh
        position={[centerX, bounds.max[1], centerZ]}
        rotation={[Math.PI / 2, 0, 0]}
        receiveShadow
      >
        <planeGeometry args={[width, depth]} />
        <meshStandardMaterial
          color={wallColor}
          metalness={0}
          roughness={0.9}
          emissive={isActive ? '#f5f5f5' : '#efefef'}
          emissiveIntensity={0.08}
        />
      </mesh>

      {/* Back Wall */}
      <mesh position={[centerX, centerY, bounds.min[2]]} receiveShadow>
        <planeGeometry args={[width, height]} />
        <meshStandardMaterial color={wallColor} metalness={0} roughness={0.8} />
      </mesh>

      {/* Front Wall */}
      <mesh position={[centerX, centerY, bounds.max[2]]} receiveShadow>
        <planeGeometry args={[width, height]} />
        <meshStandardMaterial color={wallColor} metalness={0} roughness={0.8} />
      </mesh>

      {/* Left Wall */}
      <mesh
        position={[bounds.min[0], centerY, centerZ]}
        rotation={[0, Math.PI / 2, 0]}
        receiveShadow
      >
        <planeGeometry args={[depth, height]} />
        <meshStandardMaterial color={wallColor} metalness={0} roughness={0.8} />
      </mesh>

      {/* Right Wall */}
      <mesh
        position={[bounds.max[0], centerY, centerZ]}
        rotation={[0, -Math.PI / 2, 0]}
        receiveShadow
      >
        <planeGeometry args={[depth, height]} />
        <meshStandardMaterial color={wallColor} metalness={0} roughness={0.8} />
      </mesh>

      {/* Room-specific lighting */}
      <RoomLighting room={room} />
    </group>
  )
}

function RoomLighting({ room }: { room: (typeof GALLERY_ROOMS)[0] }) {
  const bounds = room.bounds
  const width = bounds.max[0] - bounds.min[0]
  const centerX = (bounds.min[0] + bounds.max[0]) / 2
  const centerZ = (bounds.min[2] + bounds.max[2]) / 2

  // Adaptive lighting based on room
  const isHallway = room.id.includes('hallway')
  const lightPositions = isHallway
    ? [[centerX, 3.5, centerZ]] // Single light for hallways
    : [
        [bounds.min[0] + width * 0.25, 3.5, centerZ],
        [bounds.max[0] - width * 0.25, 3.5, centerZ],
      ]

  return (
    <group name={`lighting-${room.id}`}>
      {lightPositions.map((pos, idx) => (
        <spotLight
          key={`light-${idx}`}
          position={pos as [number, number, number]}
          angle={Math.PI / 3}
          penumbra={0.6}
          intensity={isHallway ? 0.8 : 1}
          castShadow
          color="#fffef0"
          shadow-mapSize={512}
        />
      ))}
      {/* Ambient light from back wall */}
      <spotLight
        position={[centerX, 3, bounds.min[2] - 1]}
        angle={Math.PI / 2}
        penumbra={1}
        intensity={0.4}
        color="#e8e8e8"
      />
    </group>
  )
}

function HallwayConnectors() {
  // Visual connectors between rooms using instancing for efficiency
  const connectorPositions = [
    // Vertical hallways
    { from: 'main-portraits', to: 'east-hallway', pos: [21, 2, -14] as [number, number, number] },
    { from: 'main-portraits', to: 'west-hallway', pos: [-21, 2, -14] as [number, number, number] },
    // Horizontal connectors
    { from: 'exhibition-room', to: 'back-hall', pos: [-23.5, 2, 12.5] as [number, number, number] },
    { from: 'sketch-room', to: 'back-hall', pos: [22.5, 2, 12.5] as [number, number, number] },
  ]

  return (
    <group name="hallway-connectors">
      {connectorPositions.map((connector, idx) => (
        <HallwayDoor key={idx} position={connector.pos} />
      ))}
    </group>
  )
}

interface HallwayDoorProps {
  position: [number, number, number]
}

function HallwayDoor({ position }: HallwayDoorProps) {
  return (
    <group position={position}>
      {/* Door frame - minimal geometry for performance */}
      <mesh position={[0, 0, 0]}>
        <boxGeometry args={[0.2, 2, 0.1]} />
        <meshStandardMaterial color="#888888" metalness={0.3} roughness={0.6} />
      </mesh>
      {/* Subtle glow indicator */}
      <mesh position={[0, 1.8, 0.1]}>
        <sphereGeometry args={[0.08, 8, 8]} />
        <meshStandardMaterial
          emissive="#d4af37"
          emissiveIntensity={0.5}
          color="#d4af37"
        />
      </mesh>
    </group>
  )
}
