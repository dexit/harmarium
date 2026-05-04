// Harmarium Portfolio API Service
// Fetches real artwork from harmarium.com/wp-json/wp/v2/portfolio

const API_BASE = 'https://harmarium.com/wp-json/wp/v2'

interface PortfolioItem {
  id: number
  title: { rendered: string }
  excerpt: { rendered: string }
  featured_media: number
  portfolio_category: number[]
  link: string
}

interface MediaItem {
  id: number
  source_url: string
  media_details?: {
    width: number
    height: number
  }
}

// Cache for API responses to minimize requests
const portfolioCache = new Map<string, any>()

/**
 * Fetch portfolio items from Harmarium API with caching
 */
export async function fetchPortfolioItems(): Promise<PortfolioItem[]> {
  try {
    const cacheKey = 'portfolio_items'
    if (portfolioCache.has(cacheKey)) {
      return portfolioCache.get(cacheKey)
    }

    const response = await fetch(`${API_BASE}/portfolio?per_page=50`)
    if (!response.ok) throw new Error('Failed to fetch portfolio')

    const items: PortfolioItem[] = await response.json()
    portfolioCache.set(cacheKey, items)
    return items
  } catch (error) {
    console.error('[Harmarium API] Error fetching portfolio:', error)
    return []
  }
}

/**
 * Fetch media item details (image URL)
 */
export async function fetchMediaUrl(mediaId: number): Promise<string | null> {
  try {
    const cacheKey = `media_${mediaId}`
    if (portfolioCache.has(cacheKey)) {
      return portfolioCache.get(cacheKey)
    }

    const response = await fetch(`${API_BASE}/media/${mediaId}`)
    if (!response.ok) return null

    const media: MediaItem = await response.json()
    const url = media.source_url
    portfolioCache.set(cacheKey, url)
    return url
  } catch (error) {
    console.error('[Harmarium API] Error fetching media:', error)
    return null
  }
}

/**
 * Get category name from category ID
 */
export async function getCategoryName(categoryId: number): Promise<string> {
  try {
    const cacheKey = `category_${categoryId}`
    if (portfolioCache.has(cacheKey)) {
      return portfolioCache.get(cacheKey)
    }

    const response = await fetch(`${API_BASE}/portfolio_category/${categoryId}`)
    if (!response.ok) return 'Portfolio'

    const category = await response.json()
    const name = category.name || 'Portfolio'
    portfolioCache.set(cacheKey, name)
    return name
  } catch (error) {
    console.error('[Harmarium API] Error fetching category:', error)
    return 'Portfolio'
  }
}

/**
 * Strip HTML tags from text
 */
export function stripHtml(html: string): string {
  return html
    .replace(/<[^>]*>/g, '')
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#039;/g, "'")
    .trim()
}
