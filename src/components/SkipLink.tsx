import React from 'react'

const SkipLink = () => {
  return (
    <a
      href="#main-content"
      className="absolute left-4 top-4 z-50 -translate-y-20 rounded-md bg-black px-4 py-2 text-sm font-medium text-white transition-transform focus:translate-y-0 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2"
    >
      Skip to content
    </a>
  )
}

export default SkipLink
