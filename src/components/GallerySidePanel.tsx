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

      {/* Side Panel - Game-style data terminal */}
      <div
        className={`fixed right-0 top-0 h-full w-full md:w-[450px] bg-black border-l-2 border-yellow-400/50 shadow-2xl transform transition-transform duration-300 z-50 overflow-y-auto ${
          isOpen ? 'translate-x-0' : 'translate-x-full'
        }`}
        style={{ fontFamily: 'monospace' }}
      >
        {artwork && (
          <div className="flex flex-col h-full bg-gradient-to-br from-black via-gray-900 to-black">
            {/* Scan Lines Effect */}
            <div className="absolute inset-0 pointer-events-none opacity-5">
              <div className="h-full bg-repeat" style={{
                backgroundImage: 'repeating-linear-gradient(0deg, rgba(255,255,255,0.03) 0px, rgba(255,255,255,0.03) 1px, transparent 1px, transparent 2px)'
              }} />
            </div>

            {/* Header - Terminal style */}
            <div className="sticky top-0 bg-black/80 border-b-2 border-yellow-400/30 px-6 py-4 flex items-center justify-between backdrop-blur">
              <div className="flex-1">
                <p className="text-yellow-400 text-xs font-bold tracking-[0.3em] uppercase opacity-60">> ARTWORK DATA</p>
                <h2 className="text-xl font-bold text-yellow-300 mt-1">{artwork.title}</h2>
              </div>
              <button
                onClick={onClose}
                className="text-yellow-400/60 hover:text-yellow-400 transition-colors p-2 text-lg font-bold hover:bg-yellow-400/10 rounded"
                aria-label="Close panel"
              >
                ✕
              </button>
            </div>

            {/* Content */}
            <div className="flex-1 overflow-y-auto px-6 py-6 space-y-6 relative z-10">
              {/* Image Preview with frame */}
              <div className="relative bg-gray-950 rounded border-2 border-yellow-400/40 overflow-hidden aspect-[2.5/3.5] p-1">
                <div className="w-full h-full bg-gray-900 border border-yellow-400/20 overflow-hidden">
                  <img
                    src={artwork.imagePath}
                    alt={artwork.title}
                    className="w-full h-full object-cover"
                  />
                </div>
              </div>

              {/* Category */}
              <div className="border-l-2 border-yellow-400/40 pl-4 py-2">
                <p className="text-yellow-400/60 text-xs font-bold tracking-widest uppercase mb-1">[CATEGORY]</p>
                <p className="text-yellow-300 font-mono">&gt; {artwork.category}</p>
              </div>

              {/* Description */}
              <div className="border-l-2 border-yellow-400/40 pl-4 py-2">
                <p className="text-yellow-400/60 text-xs font-bold tracking-widest uppercase mb-2">[ANALYSIS]</p>
                <p className="text-yellow-200/80 leading-relaxed text-sm font-mono">&gt; {artwork.description}</p>
              </div>

              {/* Position Info - Coordinates style */}
              <div className="border-l-2 border-yellow-400/40 pl-4 py-2 space-y-1">
                <p className="text-yellow-400/60 text-xs font-bold tracking-widest uppercase mb-2">[SPATIAL COORDINATES]</p>
                <p className="text-yellow-300/90 font-mono text-xs">&gt; X: <span className="text-yellow-400">{artwork.position[0].toFixed(2)}</span></p>
                <p className="text-yellow-300/90 font-mono text-xs">&gt; Y: <span className="text-yellow-400">{artwork.position[1].toFixed(2)}</span></p>
                <p className="text-yellow-300/90 font-mono text-xs">&gt; Z: <span className="text-yellow-400">{artwork.position[2].toFixed(2)}</span></p>
              </div>

              {/* Action Buttons - Terminal style */}
              <div className="flex gap-2 pt-4 border-t border-yellow-400/20">
                <button
                  onClick={onClose}
                  className="flex-1 px-4 py-2 bg-yellow-400/10 hover:bg-yellow-400/20 border border-yellow-400/40 hover:border-yellow-400/60 text-yellow-300 rounded font-mono text-xs font-bold uppercase tracking-wider transition-all"
                >
                  [CLOSE]
                </button>
                <button
                  className="flex-1 px-4 py-2 bg-yellow-400/10 hover:bg-yellow-400/20 border border-yellow-400/40 hover:border-yellow-400/60 text-yellow-300 rounded font-mono text-xs font-bold uppercase tracking-wider transition-all"
                  title="Save artwork data"
                >
                  [SAVE]
                </button>
              </div>

              {/* Footer */}
              <div className="text-center border-t border-yellow-400/20 pt-4 mt-4">
                <p className="text-yellow-400/40 text-xs font-mono tracking-wider">___ END OF DATA ___</p>
              </div>
            </div>
          </div>
        )}
      </div>
    </>
  )
}
