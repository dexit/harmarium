'use client'

import { useEffect, useState } from 'react'
import type { GalleryArtwork } from './ImmersiveGallery3D'

interface GallerySidePanelProps {
  artwork: GalleryArtwork | null
  onClose: () => void
}

export function GallerySidePanel({ artwork, onClose }: GallerySidePanelProps) {
  const [isOpen, setIsOpen] = useState(false)

  useEffect(() => {
    if (artwork) {
      setIsOpen(true)
    } else {
      setIsOpen(false)
    }
  }, [artwork])

  return (
    <>
      {/* Overlay */}
      {isOpen && (
        <div
          className="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 transition-opacity"
          onClick={onClose}
          role="button"
          tabIndex={0}
          onKeyDown={(e) => e.key === 'Escape' && onClose()}
        />
      )}

      {/* Side Panel */}
      <div
        className={`fixed right-0 top-0 h-full w-96 bg-white shadow-2xl transform transition-transform duration-300 z-50 overflow-y-auto ${
          isOpen ? 'translate-x-0' : 'translate-x-full'
        }`}
      >
        {artwork && (
          <div className="flex flex-col h-full">
            {/* Header */}
            <div className="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
              <h2 className="text-2xl font-bold text-gray-900">{artwork.title}</h2>
              <button
                onClick={onClose}
                className="text-gray-400 hover:text-gray-600 transition-colors p-1"
                aria-label="Close panel"
              >
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            {/* Content */}
            <div className="flex-1 overflow-y-auto px-6 py-6 space-y-6">
              {/* Image Preview */}
              <div className="relative bg-gray-100 rounded-lg overflow-hidden aspect-[2.5/3.5]">
                <img
                  src={artwork.imagePath}
                  alt={artwork.title}
                  className="w-full h-full object-cover"
                />
              </div>

              {/* Category */}
              <div>
                <label className="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">
                  Category
                </label>
                <p className="text-gray-900 font-medium">{artwork.category}</p>
              </div>

              {/* Description */}
              <div>
                <label className="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">
                  Description
                </label>
                <p className="text-gray-700 leading-relaxed text-sm">{artwork.description}</p>
              </div>

              {/* Position Info */}
              <div className="grid grid-cols-3 gap-4 pt-4 border-t border-gray-200">
                <div>
                  <label className="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                    Position X
                  </label>
                  <p className="text-gray-900 font-mono text-sm">{artwork.position[0].toFixed(1)}</p>
                </div>
                <div>
                  <label className="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                    Position Y
                  </label>
                  <p className="text-gray-900 font-mono text-sm">{artwork.position[1].toFixed(1)}</p>
                </div>
                <div>
                  <label className="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                    Position Z
                  </label>
                  <p className="text-gray-900 font-mono text-sm">{artwork.position[2].toFixed(1)}</p>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="flex gap-3 pt-4 border-t border-gray-200">
                <button
                  onClick={onClose}
                  className="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-lg font-medium transition-colors"
                >
                  Back to Gallery
                </button>
                <button
                  className="flex-1 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium transition-colors"
                  title="Share artwork"
                >
                  Share
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </>
  )
}
