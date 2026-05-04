'use client'

import * as THREE from 'three'
import { useFrame, useThree } from '@react-three/fiber'
import { useEffect, useRef } from 'react'
import type { GalleryArtwork } from './ImmersiveGallery3D'

interface GalleryControllerProps {
  artworks: GalleryArtwork[]
  isGuidedMode: boolean
}

const WAYPOINTS: [number, number, number][] = [
  // Entrance hall
  [0, 1.6, 0],
  // Main portrait room
  [-8, 1.6, -12],
  [-2, 1.6, -12],
  [4, 1.6, -12],
  // Through west hallway to exhibition
  [-23, 1.6, -5],
  [-28, 1.6, 12],
  [-22, 1.6, 12],
  // Back to center
  [-23, 1.6, 5],
  // East hallway to digital sketches
  [23, 1.6, -5],
  [20, 1.6, -12],
  [26, 1.6, -12],
  // East hallway to sketch room
  [23, 1.6, 5],
  [20, 1.6, 12],
  [26, 1.6, 12],
  // Back hall
  [0, 1.6, 20],
  // Return to entrance
  [0, 1.6, 0],
]

export const GalleryController = ({
  artworks,
  isGuidedMode,
}: GalleryControllerProps) => {
  const { camera } = useThree()
  const keyPressed = useRef<Record<string, boolean>>({})
  const scrollDelta = useRef(0)
  const guidedProgress = useRef(0)
  const guidedSpeed = useRef(0.0003)
  const eulerOrder = useRef(new THREE.Euler(0, 0, 0, 'YXZ'))
  const yaw = useRef(0)
  const pitch = useRef(0)
  const moveDirection = useRef(new THREE.Vector3())
  const moveSpeed = useRef(0.15)
  const velocity = useRef(new THREE.Vector3())
  const isGrounded = useRef(true)
  const canvasRef = useRef<HTMLCanvasElement | null>(null)

  // Handle keyboard input
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      const key = e.key.toLowerCase()
      if (['w', 'a', 's', 'd', ' '].includes(key)) {
        keyPressed.current[key] = true
        if (key === ' ') e.preventDefault()
      }
    }

    const handleKeyUp = (e: KeyboardEvent) => {
      const key = e.key.toLowerCase()
      if (['w', 'a', 's', 'd', ' '].includes(key)) {
        keyPressed.current[key] = false
      }
    }

    const handleScroll = (e: WheelEvent) => {
      scrollDelta.current += e.deltaY * 0.00001
      if (isGuidedMode) {
        e.preventDefault()
      }
    }

    const handleMouseMove = (e: MouseEvent) => {
      const movementX = e.movementX || 0
      const movementY = e.movementY || 0

      const mouseSpeed = 0.005
      yaw.current -= movementX * mouseSpeed
      pitch.current -= movementY * mouseSpeed

      // Clamp pitch for realistic FPS view
      pitch.current = Math.max(-Math.PI / 2.5, Math.min(Math.PI / 2.5, pitch.current))
    }

    const handleClick = () => {
      if (!isGuidedMode && document.pointerLockElement !== canvasRef.current) {
        canvasRef.current?.requestPointerLock()
      }
    }

    // Get canvas reference
    const canvas = document.querySelector('canvas')
    if (canvas) {
      canvasRef.current = canvas
    }

    window.addEventListener('keydown', handleKeyDown)
    window.addEventListener('keyup', handleKeyUp)
    window.addEventListener('wheel', handleScroll, { passive: false })
    window.addEventListener('mousemove', handleMouseMove)
    document.addEventListener('click', handleClick)

    return () => {
      window.removeEventListener('keydown', handleKeyDown)
      window.removeEventListener('keyup', handleKeyUp)
      window.removeEventListener('wheel', handleScroll)
      window.removeEventListener('mousemove', handleMouseMove)
      document.removeEventListener('click', handleClick)
    }
  }, [isGuidedMode])

  useFrame(() => {
    if (isGuidedMode) {
      // Guided tour mode - smooth scripted movement
      guidedProgress.current += guidedSpeed.current + scrollDelta.current
      scrollDelta.current *= 0.95
      guidedProgress.current = Math.max(0, Math.min(1, guidedProgress.current))

      const waypointIndex = guidedProgress.current * (WAYPOINTS.length - 1)
      const currentWaypoint = Math.floor(waypointIndex)
      const nextWaypoint = Math.min(currentWaypoint + 1, WAYPOINTS.length - 1)
      const t = waypointIndex - currentWaypoint

      const current = WAYPOINTS[currentWaypoint]
      const next = WAYPOINTS[nextWaypoint]

      camera.position.x = THREE.MathUtils.lerp(current[0], next[0], t)
      camera.position.y = THREE.MathUtils.lerp(current[1], next[1], t)
      camera.position.z = THREE.MathUtils.lerp(current[2], next[2], t)

      // Look toward center naturally
      const centerX = 0
      const centerZ = -2
      const dirX = centerX - camera.position.x
      const dirZ = centerZ - camera.position.z

      yaw.current = Math.atan2(dirX, dirZ)
      pitch.current *= 0.95

      eulerOrder.current.setFromQuaternion(camera.quaternion)
      eulerOrder.current.order = 'YXZ'
      eulerOrder.current.setFromVector3(new THREE.Vector3(pitch.current, yaw.current, 0))
      camera.quaternion.setFromEuler(eulerOrder.current)
    } else {
      // Free exploration - full FPS-style movement with momentum
      moveDirection.current.set(0, 0, 0)

      if (keyPressed.current['w']) moveDirection.current.z -= 1
      if (keyPressed.current['s']) moveDirection.current.z += 1
      if (keyPressed.current['a']) moveDirection.current.x -= 1
      if (keyPressed.current['d']) moveDirection.current.x += 1

      // Normalize movement
      if (moveDirection.current.length() > 0) {
        moveDirection.current.normalize()

        // Calculate world-space movement based on camera direction
        const forward = new THREE.Vector3(0, 0, -1)
        const right = new THREE.Vector3(1, 0, 0)

        forward.applyAxisAngle(new THREE.Vector3(0, 1, 0), yaw.current)
        right.applyAxisAngle(new THREE.Vector3(0, 1, 0), yaw.current)

        forward.multiplyScalar(moveDirection.current.z)
        right.multiplyScalar(moveDirection.current.x)

        const movement = forward.add(right).multiplyScalar(moveSpeed.current)
        camera.position.add(movement)

        // Collision detection - keep within gallery bounds with buffer
        const margin = 0.5
        camera.position.x = Math.max(-35 + margin, Math.min(35 - margin, camera.position.x))
        camera.position.z = Math.max(-25 + margin, Math.min(25 - margin, camera.position.z))
        camera.position.y = Math.max(0.3, Math.min(3.8, camera.position.y))
      }

      // Apply camera rotation (mouse-based first-person view)
      eulerOrder.current.setFromQuaternion(camera.quaternion)
      eulerOrder.current.order = 'YXZ'
      eulerOrder.current.setFromVector3(new THREE.Vector3(pitch.current, yaw.current, 0))
      camera.quaternion.setFromEuler(eulerOrder.current)
    }
  })

  return null
}

