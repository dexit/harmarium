import Image from "next/image";
import Link from "next/link";

export default function Home() {
  return (
    <div className="flex min-h-screen flex-col">
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
                <Link href="/portfolio" className="text-sm font-medium hover:text-zinc-600 dark:hover:text-zinc-400">
                  Portfolio
                </Link>
              </li>
              <li>
                <Link href="/shop" className="text-sm font-medium hover:text-zinc-600 dark:hover:text-zinc-400">
                  Shop
                </Link>
              </li>
              <li>
                <Link href="/contact" className="text-sm font-medium hover:text-zinc-600 dark:hover:text-zinc-400">
                  Commission
                </Link>
              </li>
            </ul>
          </nav>
        </div>
      </header>

      <main id="main-content" className="flex-1 focus:outline-none">
        <section className="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
          <div className="max-w-2xl">
            <h1 className="text-4xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 sm:text-6xl">
              Harmarium
            </h1>
            <p className="mt-6 text-lg leading-8 text-zinc-600 dark:text-zinc-400">
              The portfolio and creative workspace of a multi-disciplinary artist. Exploring the intersection of digital and traditional mediums.
            </p>
            <div className="mt-10 flex items-center gap-x-6">
              <Link
                href="/portfolio"
                className="rounded-md bg-black px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-zinc-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black dark:bg-white dark:text-black dark:hover:bg-zinc-200"
              >
                View Portfolio
              </Link>
              <Link href="/contact" className="text-sm font-semibold leading-6 text-zinc-900 dark:text-zinc-50">
                Commission a portrait <span aria-hidden="true">→</span>
              </Link>
            </div>
          </div>
        </section>
      </main>

      <footer className="border-t border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-black">
        <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
          <p className="text-center text-xs leading-5 text-zinc-500 dark:text-zinc-400">
            &copy; {new Date().getFullYear()} Harmarium. All rights reserved.
          </p>
        </div>
      </footer>
    </div>
  );
}
