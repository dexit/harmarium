// Multi-room gallery data structure
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

// Room connection map for efficient loading
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

// Artwork data mapped to rooms
export const ARTWORK_DATA = [
  // Main Portrait Room
  {
    id: 'portrait-1',
    title: 'Orange 2',
    description: 'A vibrant portrait study exploring color harmony and form.',
    category: 'Portrait',
    room: 'main-portraits',
    position: [-8, 1.5, -10] as [number, number, number],
  },
  {
    id: 'portrait-2',
    title: 'Portrait 1184',
    description: 'Contemporary portrait composition with dynamic lighting.',
    category: 'Portrait',
    room: 'main-portraits',
    position: [-2, 1.5, -10] as [number, number, number],
  },
  {
    id: 'portrait-3',
    title: 'Hel',
    description: 'Expressive character study with rich emotional depth.',
    category: 'Portrait',
    room: 'main-portraits',
    position: [4, 1.5, -10] as [number, number, number],
  },
  {
    id: 'portrait-4',
    title: 'Spring',
    description: 'Seasonal portrait celebrating new beginnings.',
    category: 'Portrait',
    room: 'main-portraits',
    position: [-8, 1.5, -14] as [number, number, number],
  },
  
  // Digital Sketches Room
  {
    id: 'digital-1',
    title: 'Digital Sketch 1034',
    description: 'Digital line work and form exploration.',
    category: 'Digital Sketch',
    room: 'digital-sketches',
    position: [15, 1.5, -10] as [number, number, number],
  },
  {
    id: 'digital-2',
    title: 'Digital Sketch 1033',
    description: 'Digital medium study with bold strokes.',
    category: 'Digital Sketch',
    room: 'digital-sketches',
    position: [21, 1.5, -10] as [number, number, number],
  },
  {
    id: 'digital-3',
    title: 'Digital Sketch 1032',
    description: 'Contemporary digital artwork.',
    category: 'Digital Sketch',
    room: 'digital-sketches',
    position: [27, 1.5, -10] as [number, number, number],
  },
  
  // Early Days - Paintings
  {
    id: 'early-1',
    title: 'Welsh Rose',
    description: 'Classical painting from early collection.',
    category: 'Painting',
    room: 'early-days',
    position: [-26, 1.5, -10] as [number, number, number],
  },
  {
    id: 'early-2',
    title: 'Water Lilly',
    description: 'Nature-inspired painting with watercolor techniques.',
    category: 'Painting',
    room: 'early-days',
    position: [-20, 1.5, -10] as [number, number, number],
  },
  {
    id: 'early-3',
    title: 'Silver Portrait',
    description: 'Monochromatic study in silver tones.',
    category: 'Painting',
    room: 'early-days',
    position: [-14, 1.5, -10] as [number, number, number],
  },
  
  // Exhibition Room
  {
    id: 'exhibit-1',
    title: 'Young Richy',
    description: 'Portrait from The Other U exhibition.',
    category: 'Exhibition',
    room: 'exhibition-room',
    position: [-28, 1.5, 10] as [number, number, number],
  },
  {
    id: 'exhibit-2',
    title: 'Mute',
    description: 'Expressive piece from special collection.',
    category: 'Exhibition',
    room: 'exhibition-room',
    position: [-22, 1.5, 10] as [number, number, number],
  },
  {
    id: 'exhibit-3',
    title: 'cockatoo',
    description: 'Animal portrait study.',
    category: 'Exhibition',
    room: 'exhibition-room',
    position: [-16, 1.5, 10] as [number, number, number],
  },
  
  // Sketchbook Archives
  {
    id: 'sketch-1',
    title: 'Portrait Sketch 8893',
    description: 'Archival sketch from personal collection.',
    category: 'Sketch',
    room: 'sketch-room',
    position: [15, 1.5, 10] as [number, number, number],
  },
  {
    id: 'sketch-2',
    title: 'Portrait Sketch 8892',
    description: 'Foundational drawing technique study.',
    category: 'Sketch',
    room: 'sketch-room',
    position: [21, 1.5, 10] as [number, number, number],
  },
  {
    id: 'sketch-3',
    title: 'Portrait Sketch 8891',
    description: 'Character design exploration.',
    category: 'Sketch',
    room: 'sketch-room',
    position: [27, 1.5, 10] as [number, number, number],
  },
]
