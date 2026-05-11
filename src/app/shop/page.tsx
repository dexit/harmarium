import Image from 'next/image'
import Link from 'next/link'
import { getProducts, WPProduct } from '@/lib/wp'

export const metadata = {
  title: 'Shop | Harmarium',
  description: 'Original prints and limited editions by Harmarium.',
}

function ProductCard({ product }: { product: WPProduct }) {
  const image = product.images[0]
  const isAvailable = product.stock_status === 'instock'
  const isSale = product.sale_price && product.sale_price !== product.regular_price

  return (
    <article className="group relative flex flex-col">
      <div className="aspect-h-4 aspect-w-3 w-full overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
        {image ? (
          <Image
            src={image.src}
            alt={image.alt || product.name}
            width={400}
            height={533}
            className="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
          />
        ) : (
          <div className="h-full w-full bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-800 flex items-center justify-center">
            <span className="text-zinc-400 text-xs tracking-widest uppercase">No image</span>
          </div>
        )}
        {!isAvailable && (
          <div className="absolute inset-0 bg-black/40 flex items-center justify-center">
            <span className="bg-black/80 text-white text-xs font-semibold tracking-widest uppercase px-3 py-1.5">
              Sold Out
            </span>
          </div>
        )}
      </div>

      <div className="mt-4 flex flex-col gap-1">
        <h3 className="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
          <a
            href={product.permalink}
            target="_blank"
            rel="noopener noreferrer"
            className="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 rounded"
          >
            <span aria-hidden="true" className="absolute inset-0" />
            {product.name}
          </a>
        </h3>

        {product.categories.length > 0 && (
          <p className="text-xs text-zinc-500 dark:text-zinc-400">
            {product.categories.map(c => c.name).join(' · ')}
          </p>
        )}

        <div className="flex items-baseline gap-2 mt-1">
          {isSale ? (
            <>
              <span className="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                €{product.sale_price}
              </span>
              <span className="text-xs text-zinc-400 line-through">€{product.regular_price}</span>
            </>
          ) : (
            <span className="text-sm font-bold text-zinc-900 dark:text-zinc-100">
              {product.price ? `€${product.price}` : 'Price on request'}
            </span>
          )}
        </div>
      </div>
    </article>
  )
}

function EmptyState() {
  return (
    <div className="col-span-full py-24 flex flex-col items-center gap-4">
      <p className="text-zinc-500 dark:text-zinc-400 text-sm">
        No products found. Connect a WordPress + WooCommerce instance to display the shop.
      </p>
      <Link
        href="/contact"
        className="text-sm font-medium underline underline-offset-4 text-zinc-900 dark:text-zinc-100"
      >
        Commission a custom piece →
      </Link>
    </div>
  )
}

export default async function ShopPage() {
  let products: WPProduct[] = []

  try {
    products = await getProducts(24)
  } catch {
    // WP not available — render empty state
  }

  return (
    <div className="flex min-h-screen flex-col bg-white dark:bg-black">
      <header className="border-b border-zinc-100 bg-white/80 backdrop-blur-md dark:border-zinc-800 dark:bg-black/80">
        <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
          <div className="flex items-center gap-4">
            <Link
              href="/"
              className="focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 rounded-md transition-shadow text-sm font-medium text-zinc-900 dark:text-zinc-100"
            >
              ← Harmarium
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
                <Link href="/portfolio" className="text-sm font-medium hover:text-zinc-600 dark:hover:text-zinc-400">
                  Portfolio
                </Link>
              </li>
              <li>
                <Link href="/shop" className="text-sm font-medium text-black dark:text-white underline underline-offset-4 decoration-2">
                  Shop
                </Link>
              </li>
            </ul>
          </nav>
        </div>
      </header>

      <main id="main-content" className="flex-1 focus:outline-none">
        <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
          <header className="mb-10">
            <h1 className="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
              Shop
            </h1>
            <p className="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
              Limited edition prints, originals, and giclées. All works come with a certificate of authenticity.
            </p>
          </header>

          <div className="relative grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">
            {products.length > 0
              ? products.map(product => (
                  <ProductCard key={product.id} product={product} />
                ))
              : <EmptyState />
            }
          </div>

          <aside className="mt-20 rounded-2xl bg-zinc-50 dark:bg-zinc-900 px-8 py-10 ring-1 ring-zinc-100 dark:ring-zinc-800">
            <h2 className="text-lg font-semibold text-zinc-900 dark:text-zinc-50">
              Looking for something custom?
            </h2>
            <p className="mt-2 text-sm text-zinc-600 dark:text-zinc-400 max-w-xl">
              All portraits are painted to order. Commission a bespoke piece — acrylic, oil pastel, watercolour, or digital.
            </p>
            <Link
              href="/contact"
              className="mt-6 inline-flex items-center gap-2 rounded-md bg-black px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-zinc-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black dark:bg-white dark:text-black dark:hover:bg-zinc-200 transition-colors"
            >
              Commission a portrait
              <span aria-hidden="true">→</span>
            </Link>
          </aside>
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
