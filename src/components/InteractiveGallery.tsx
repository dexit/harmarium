'use client'

import { useState, useEffect } from 'react'
import Image from 'next/image'

interface GalleryItem {
  id: string
  title: string
  image: string
  category: string
  description: string
}

const galleryItems: GalleryItem[] = [
  {
    id: '1',
    title: 'Ethereal Dimensions',
    image: '/images/gallery/679552251_1407341641437944_5566833434944317801_n.jpg',
    category: 'Digital Art',
    description: 'An exploration of space and light'
  },
  {
    id: '2',
    title: 'Chromatic Harmony',
    image: '/images/gallery/680428219_1411180054387436_9040440674546774895_n.jpg',
    category: 'Mixed Media',
    description: 'Colors dancing in perfect balance'
  },
  {
    id: '3',
    title: 'Geometric Essence',
    image: '/images/gallery/682617251_1411180031054105_7141077929646009908_n.jpg',
    category: 'Abstract',
    description: 'Shapes and patterns in harmony'
  },
  {
    id: '4',
    title: 'Digital Landscape',
    image: '/images/gallery/682617251_1411180034387438_4588160502983284951_n.jpg',
    category: 'Digital Art',
    description: 'Worlds beyond imagination'
  },
  {
    id: '5',
    title: 'Radiant Moments',
    image: '/images/gallery/682665321_1410376504467791_1932792919615144817_n.jpg',
    category: 'Photography',
    description: 'Capturing light in time'
  },
  {
    id: '6',
    title: 'Sublime Forms',
    image: '/images/gallery/684684163_1414084144097027_5240992258635284109_n.jpg',
    category: 'Sculpture',
    description: 'Three-dimensional poetry'
  },
  {
    id: '7',
    title: 'Neural Networks',
    image: '/images/gallery/684692825_1411180057720769_668941511238367822_n.jpg',
    category: 'Digital Art',
    description: 'Connections and complexity'
  },
]

const categories = ['All', 'Digital Art', 'Mixed Media', 'Abstract', 'Photography', 'Sculpture']

export default function InteractiveGallery() {
  const [selectedCategory, setSelectedCategory] = useState('All')
  const [selectedItem, setSelectedItem] = useState<GalleryItem | null>(null)
  const [mounted, setMounted] = useState(false)
  const [hoveredId, setHoveredId] = useState<string | null>(null)

  useEffect(() => {
    setMounted(true)
  }, [])

  const filteredItems = selectedCategory === 'All' 
    ? galleryItems 
    : galleryItems.filter(item => item.category === selectedCategory)

  if (!mounted) {
    return null
  }

  return (
    <div className="min-h-screen bg-background">
      {/* Hero Section */}
      <section className="relative overflow-hidden border-b border-muted/30 bg-primary/5">
        <div className="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
          <div className="animate-slide-up">
            <h1 className="text-5xl font-bold tracking-tight text-foreground sm:text-6xl">
              Art <span className="text-accent">Gallery</span>
            </h1>
            <p className="mt-6 max-w-2xl text-lg leading-8 text-foreground/70">
              Experience a curated collection of contemporary artworks exploring the intersection of creativity, technology, and human expression.
            </p>
          </div>
        </div>
      </section>

      {/* Main Gallery Section */}
      <main className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        {/* Category Filter */}
        <div className="mb-16 flex flex-wrap gap-3">
          {categories.map((category) => (
            <button
              key={category}
              onClick={() => setSelectedCategory(category)}
              className={`px-6 py-2.5 rounded-full text-sm font-medium transition-all duration-300 ${
                selectedCategory === category
                  ? 'bg-accent text-foreground shadow-lg'
                  : 'bg-muted text-foreground hover:bg-muted/80 border border-muted/40'
              }`}
              aria-pressed={selectedCategory === category}
            >
              {category}
            </button>
          ))}
        </div>

        {/* Gallery Grid */}
        <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
          {filteredItems.map((item, index) => (
            <div
              key={item.id}
              className="group cursor-pointer animate-fade-in"
              style={{ animationDelay: `${index * 0.1}s` }}
              onClick={() => setSelectedItem(item)}
              onMouseEnter={() => setHoveredId(item.id)}
              onMouseLeave={() => setHoveredId(null)}
              role="button"
              tabIndex={0}
              onKeyDown={(e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                  setSelectedItem(item)
                }
              }}
            >
              <div className="relative overflow-hidden rounded-lg bg-muted/50">
                <div className="aspect-square relative overflow-hidden">
                  <Image
                    src={item.image}
                    alt={item.title}
                    fill
                    className="object-cover transition-transform duration-500 group-hover:scale-110"
                  />
                  {/* Overlay */}
                  <div className={`absolute inset-0 bg-black/40 transition-opacity duration-300 ${
                    hoveredId === item.id ? 'opacity-100' : 'opacity-0'
                  }`} />
                </div>

                {/* Info Section */}
                <div className="p-4">
                  <p className="text-xs font-semibold text-accent uppercase tracking-wider">
                    {item.category}
                  </p>
                  <h3 className="mt-2 text-lg font-bold text-foreground group-hover:text-accent transition-colors duration-300">
                    {item.title}
                  </h3>
                  <p className="mt-2 text-sm text-foreground/60">
                    {item.description}
                  </p>
                </div>
              </div>
            </div>
          ))}
        </div>
      </main>

      {/* Modal */}
      {selectedItem && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
          onClick={() => setSelectedItem(null)}
          role="dialog"
          aria-modal="true"
          aria-label={`${selectedItem.title} details`}
        >
          <button
            className="absolute top-6 right-6 text-white hover:text-accent transition-colors z-50"
            onClick={() => setSelectedItem(null)}
            aria-label="Close modal"
          >
            <span className="text-3xl">×</span>
          </button>

          <div
            className="relative w-full max-w-2xl bg-background rounded-lg overflow-hidden shadow-2xl"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="aspect-square relative overflow-hidden">
              <Image
                src={selectedItem.image}
                alt={selectedItem.title}
                fill
                className="object-cover"
              />
            </div>

            <div className="p-8">
              <p className="text-xs font-semibold text-accent uppercase tracking-wider">
                {selectedItem.category}
              </p>
              <h2 className="mt-3 text-3xl font-bold text-foreground">
                {selectedItem.title}
              </h2>
              <p className="mt-4 text-foreground/70 leading-relaxed">
                {selectedItem.description}
              </p>

              <div className="mt-8 flex gap-4">
                <button
                  onClick={() => setSelectedItem(null)}
                  className="flex-1 px-6 py-3 bg-accent text-foreground font-semibold rounded-lg hover:bg-accent/90 transition-colors duration-300"
                >
                  Close
                </button>
                <button
                  className="flex-1 px-6 py-3 border-2 border-accent text-accent font-semibold rounded-lg hover:bg-accent/10 transition-colors duration-300"
                >
                  Learn More
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
