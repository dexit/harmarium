// Multi-room gallery data structure
import { fetchPortfolioItems, fetchMediaUrl, getCategoryName, stripHtml } from './portfolioApi'

export const GALLERY_ROOMS = [
  {
    id: 'entrance',
    name: 'Entrance Hall',
    bounds: { min: [-8, 0, -3], max: [8, 4, 3] },
    description: 'Welcome to Harmarium Gallery',
  },
  {
    id: 'main-portraits',
    name: 'Main Portrait Gallery',
    bounds: { min: [-12, 0, -20], max: [12, 4, -8] },
    description: 'Featured Portrait Collection',
  },
  {
    id: 'digital-sketches',
    name: 'Digital Sketches Room',
    bounds: { min: [10, 0, -20], max: [30, 4, -8] },
    description: 'Modern Digital Artwork',
  },
  {
    id: 'early-days',
    name: 'Early Days - Paintings',
    bounds: { min: [-30, 0, -20], max: [-12, 4, -8] },
    description: 'Classic Painting Collection',
  },
  {
    id: 'east-hallway',
    name: 'East Hallway',
    bounds: { min: [30, 0, -25], max: [35, 4, 5] },
    description: 'Connecting Hallway',
  },
  {
    id: 'west-hallway',
    name: 'West Hallway',
    bounds: { min: [-35, 0, -25], max: [-30, 4, 5] },
    description: 'Connecting Hallway',
  },
  {
    id: 'exhibition-room',
    name: 'Exhibition: The Other U',
    bounds: { min: [-35, 0, 5], max: [-12, 4, 20] },
    description: 'Special Exhibition',
  },
  {
    id: 'sketch-room',
    name: 'Sketchbook Archives',
    bounds: { min: [10, 0, 5], max: [35, 4, 20] },
    description: 'Archival Sketches',
  },
  {
    id: 'back-hall',
    name: 'Back Hall',
    bounds: { min: [-12, 0, 20], max: [12, 4, 25] },
    description: 'Rear Corridor',
  },
]

export const ROOM_CONNECTIONS: Record<string, string[]> = {
  'entrance': ['main-portraits', 'digital-sketches', 'early-days'],
  'main-portraits': ['entrance', 'east-hallway', 'west-hallway'],
  'digital-sketches': ['entrance', 'east-hallway', 'sketch-room'],
  'early-days': ['entrance', 'west-hallway', 'exhibition-room'],
  'east-hallway': ['main-portraits', 'digital-sketches', 'sketch-room'],
  'west-hallway': ['main-portraits', 'early-days', 'exhibition-room'],
  'exhibition-room': ['early-days', 'west-hallway', 'back-hall'],
  'sketch-room': ['digital-sketches', 'east-hallway', 'back-hall'],
  'back-hall': ['exhibition-room', 'sketch-room'],
}

export interface ArtworkData {
  id: string
  title: string
  description: string
  category: string
  room: string
  position: [number, number, number]
}

const ROOM_POSITIONS: Record<string, [number, number, number][]> = {
  'main-portraits': [
    [-8, 1.5, -10],
    [-2, 1.5, -10],
    [4, 1.5, -10],
    [-8, 1.5, -14],
    [-2, 1.5, -14],
    [4, 1.5, -14],
  ],
  'digital-sketches': [
    [15, 1.5, -10],
    [21, 1.5, -10],
    [27, 1.5, -10],
    [15, 1.5, -14],
    [21, 1.5, -14],
    [27, 1.5, -14],
  ],
  'early-days': [
    [-26, 1.5, -10],
    [-20, 1.5, -10],
    [-14, 1.5, -10],
    [-26, 1.5, -14],
    [-20, 1.5, -14],
    [-14, 1.5, -14],
  ],
  'exhibition-room': [
    [-28, 1.5, 8],
    [-22, 1.5, 8],
    [-16, 1.5, 8],
    [-28, 1.5, 14],
    [-22, 1.5, 14],
    [-16, 1.5, 14],
  ],
  'sketch-room': [
    [15, 1.5, 8],
    [21, 1.5, 8],
    [27, 1.5, 8],
    [15, 1.5, 14],
    [21, 1.5, 14],
    [27, 1.5, 14],
  ],
}

const DEFAULT_ARTWORK_DATA: ArtworkData[] = [
  {
    id: 'fallback-1',
    title: 'Gallery Loading...',
    description: 'Loading artwork from Harmarium portfolio...',
    category: 'Notice',
    room: 'main-portraits',
    position: [0, 1.5, -12],
  },
]

export async function fetchArtworkData(): Promise<ArtworkData[]> {
  try {
    const portfolioItems = await fetchPortfolioItems()

    if (!portfolioItems || portfolioItems.length === 0) {
      return DEFAULT_ARTWORK_DATA
    }

    const artwork: ArtworkData[] = []
    const rooms = ['main-portraits', 'digital-sketches', 'early-days', 'exhibition-room', 'sketch-room']
    let roomIndex = 0
    let positionIndex = 0

    for (let i = 0; i < portfolioItems.length && artwork.length < 30; i++) {
      const item = portfolioItems[i]
      const currentRoom = rooms[roomIndex % rooms.length]
      const positions = ROOM_POSITIONS[currentRoom]

      if (!positions || positionIndex >= positions.length) {
        roomIndex++
        positionIndex = 0
        continue
      }

      const category = item.portfolio_category?.[0]
        ? await getCategoryName(item.portfolio_category[0])
        : 'Portfolio'

      const title = item.title?.rendered || `Artwork ${i + 1}`
      const description = stripHtml(item.excerpt?.rendered || 'A beautiful artwork from the Harmarium collection.')

      artwork.push({
        id: `portfolio-${item.id}`,
        title,
        description,
        category,
        room: currentRoom,
        position: positions[positionIndex],
      })

      positionIndex++
      if (positionIndex >= positions.length) {
        roomIndex++
        positionIndex = 0
      }
    }

    return artwork.length > 0 ? artwork : DEFAULT_ARTWORK_DATA
  } catch (error) {
    console.error('[Gallery] Error fetching artwork:', error)
    return DEFAULT_ARTWORK_DATA
  }
}

export const ARTWORK_DATA: ArtworkData[] = DEFAULT_ARTWORK_DATA
