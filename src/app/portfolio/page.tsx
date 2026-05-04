import Link from 'next/link'
import Image from 'next/image'
import Gallery3D from '@/components/Gallery3D'
import { getPortfolio, WPPortfolioItem, WPMedia } from '@/lib/wp'

export default async function PortfolioPage() {
  let images: WPMedia[] = []

  try {
    const portfolioItems: WPPortfolioItem[] = await getPortfolio()

    // Map portfolio items to WPMedia format for compatibility with Gallery3D
    images = portfolioItems.map(item => {
      const featuredMedia = item._embedded?.['wp:featuredmedia']?.[0]
      if (!featuredMedia) return null

      return {
        id: item.id,
        source_url: featuredMedia.source_url,
        title: item.title,
        alt_text: featuredMedia.alt_text || item.title.rendered,
        media_details: featuredMedia.media_details
      } as WPMedia
    }).filter(Boolean) as WPMedia[]

  } catch (error) {
    console.error('Failed to fetch WordPress portfolio:', error)
  }

  return (
    <div className="flex min-h-screen flex-col bg-white dark:bg-black">
      <header className="border-b border-zinc-100 bg-white/80 backdrop-blur-md dark:border-zinc-800 dark:bg-black/80">
        <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
          <div className="flex items-center gap-4">
            <Link href="/" className="focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 rounded-md transition-shadow">
              <Image
                className="dark:invert"
                src="/next.svg"
                alt="Harmarium Logo"
                width={100}
                height={20}
                priority
              />
            </Link>
          </div>
          <nav aria-label="Main Navigation">
            <ul className="flex gap-6">
              <li>
                <Link href="/" className="text-sm font-medium hover:text-zinc-600 dark:hover:text-zinc-400">
                  Home
                </Link>
              </li>
              <li>
                <Link href="/portfolio" className="text-sm font-medium text-black dark:text-white underline underline-offset-4 decoration-2">
                  Portfolio
                </Link>
              </li>
              <li>
                <Link href="/shop" className="text-sm font-medium hover:text-zinc-600 dark:hover:text-zinc-400">
                  Shop
                </Link>
              </li>
            </ul>
          </nav>
        </div>
      </header>

      <main id="main-content" className="flex-1 focus:outline-none">
        <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
          <div className="flex flex-col gap-8">
            <header>
              <h1 className="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                Portfolio
              </h1>
              <p className="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                An immersive view of my latest works.
              </p>
            </header>

            <Gallery3D images={images} />

            <section className="mt-12">
              <h2 className="text-xl font-semibold mb-6">Traditional View</h2>
              <div className="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">
                {images.length > 0 ? images.map((image) => (
                  <div key={image.id} className="group">
                    <div className="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-lg bg-zinc-200 xl:aspect-h-8 xl:aspect-w-7">
                      <Image
                        src={image.source_url}
                        alt={image.alt_text || image.title.rendered}
                        width={400}
                        height={500}
                        className="h-full w-full object-cover object-center group-hover:opacity-75 transition-opacity"
                      />
                    </div>
                    <h3 className="mt-4 text-sm text-zinc-700 dark:text-zinc-300" dangerouslySetInnerHTML={{ __html: image.title.rendered }} />
                  </div>
                )) : (
                  <p className="text-sm text-zinc-500 italic col-span-full">No artwork found in portfolio. Showing fallback items in the 3D gallery.</p>
                )}
              </div>
            </section>
          </div>
        </div>
      </main>

      <footer className="border-t border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-black">
        <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
          <p className="text-center text-xs leading-5 text-zinc-500 dark:text-zinc-400">
            &copy; {new Date().getFullYear()} Harmarium. All rights reserved.
          </p>
        </div>
      </footer>
    </div>
  )
}
