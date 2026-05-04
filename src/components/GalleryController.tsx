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
  [0, 1.6, 8],
  [-6, 1.6, 5],
  [-8, 1.6, -2],
  [-4, 1.6, -8],
  [0, 1.6, -8],
  [4, 1.6, -8],
  [8, 1.6, -4],
  [8, 1.6, 3],
  [0, 1.6, 10],
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

  // Handle keyboard input
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      const key = e.key.toLowerCase()
      if (['w', 'a', 's', 'd'].includes(key)) {
        keyPressed.current[key] = true
        e.preventDefault()
      }
    }

    const handleKeyUp = (e: KeyboardEvent) => {
      const key = e.key.toLowerCase()
      if (['w', 'a', 's', 'd'].includes(key)) {
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

      // Clamp pitch
      pitch.current = Math.max(-Math.PI / 2.5, Math.min(Math.PI / 2.5, pitch.current))
    }

    const handlePointerLock = () => {
      if (!isGuidedMode) {
        document.addEventListener('mousemove', handleMouseMove)
      }
    }

    window.addEventListener('keydown', handleKeyDown)
    window.addEventListener('keyup', handleKeyUp)
    window.addEventListener('wheel', handleScroll, { passive: false })
    document.addEventListener('pointerlockchange', handlePointerLock)

    return () => {
      window.removeEventListener('keydown', handleKeyDown)
      window.removeEventListener('keyup', handleKeyUp)
      window.removeEventListener('wheel', handleScroll)
      document.removeEventListener('pointerlockchange', handlePointerLock)
      document.removeEventListener('mousemove', handleMouseMove)
    }
  }, [isGuidedMode])

  useFrame(() => {
    if (isGuidedMode) {
      // Guided tour mode
      guidedProgress.current += guidedSpeed.current + scrollDelta.current
      scrollDelta.current *= 0.95 // Damping
      guidedProgress.current = Math.max(0, Math.min(1, guidedProgress.current))

      // Interpolate through waypoints
      const waypointIndex = guidedProgress.current * (WAYPOINTS.length - 1)
      const currentWaypoint = Math.floor(waypointIndex)
      const nextWaypoint = Math.min(currentWaypoint + 1, WAYPOINTS.length - 1)
      const t = waypointIndex - currentWaypoint

      const current = WAYPOINTS[currentWaypoint]
      const next = WAYPOINTS[nextWaypoint]

      camera.position.x = THREE.MathUtils.lerp(current[0], next[0], t)
      camera.position.y = THREE.MathUtils.lerp(current[1], next[1], t)
      camera.position.z = THREE.MathUtils.lerp(current[2], next[2], t)

      // Look toward center
      const centerX = 0
      const centerZ = -2
      const dirX = centerX - camera.position.x
      const dirZ = centerZ - camera.position.z

      yaw.current = Math.atan2(dirX, dirZ)
      pitch.current *= 0.95 // Slowly reset pitch to horizontal

      // Still allow mouse look
      eulerOrder.current.setFromQuaternion(camera.quaternion)
      eulerOrder.current.order = 'YXZ'
      eulerOrder.current.setFromVector3(new THREE.Vector3(pitch.current, yaw.current, 0))
      camera.quaternion.setFromEuler(eulerOrder.current)
    } else {
      // Free exploration mode
      moveDirection.current.set(0, 0, 0)

      if (keyPressed.current['w']) moveDirection.current.z -= 1
      if (keyPressed.current['s']) moveDirection.current.z += 1
      if (keyPressed.current['a']) moveDirection.current.x -= 1
      if (keyPressed.current['d']) moveDirection.current.x += 1

      if (moveDirection.current.length() > 0) {
        moveDirection.current.normalize()

        // Rotate movement by camera direction
        const forward = new THREE.Vector3(0, 0, -1)
        const right = new THREE.Vector3(1, 0, 0)

        forward.applyAxisAngle(new THREE.Vector3(0, 1, 0), yaw.current)
        right.applyAxisAngle(new THREE.Vector3(0, 1, 0), yaw.current)

        forward.multiplyScalar(moveDirection.current.z)
        right.multiplyScalar(moveDirection.current.x)

        const movement = forward.add(right).multiplyScalar(moveSpeed.current)
        camera.position.add(movement)

        // Clamp camera within gallery bounds
        camera.position.x = Math.max(-14, Math.min(14, camera.position.x))
        camera.position.z = Math.max(-11, Math.min(11, camera.position.z))
      }

      // Apply camera rotation
      eulerOrder.current.setFromQuaternion(camera.quaternion)
      eulerOrder.current.order = 'YXZ'
      eulerOrder.current.setFromVector3(new THREE.Vector3(pitch.current, yaw.current, 0))
      camera.quaternion.setFromEuler(eulerOrder.current)
    }
  })

  return null
}
