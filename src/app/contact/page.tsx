import Link from 'next/link'
import CommissionForm from './CommissionForm'

export const metadata = {
  title: 'Commission a Portrait | Harmarium',
  description: 'Commission a bespoke original portrait — acrylic, oil pastel, watercolour, or digital.',
}

const PROCESS_STEPS = [
  {
    step: '01',
    title: 'Brief',
    description: 'Fill in the form below with your vision, subject, preferred medium, and any reference images you have in mind.',
  },
  {
    step: '02',
    title: 'Quote',
    description: "I'll review your brief and reply within 2 business days with a quote, timeline, and any clarifying questions.",
  },
  {
    step: '03',
    title: 'Creation',
    description: 'Once we agree on the details, I begin the work. Progress photos are shared at key stages.',
  },
  {
    step: '04',
    title: 'Delivery',
    description: 'The finished piece ships worldwide with a certificate of authenticity and full insurance.',
  },
]

export default function ContactPage() {
  return (
    <div className="flex min-h-screen flex-col bg-white dark:bg-black">
      <header className="border-b border-zinc-100 bg-white/80 backdrop-blur-md dark:border-zinc-800 dark:bg-black/80">
        <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
          <Link
            href="/"
            className="focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 rounded-md transition-shadow text-sm font-medium text-zinc-900 dark:text-zinc-100"
          >
            ← Harmarium
          </Link>
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
            </ul>
          </nav>
        </div>
      </header>

      <main id="main-content" className="flex-1 focus:outline-none">
        <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

          {/* Hero */}
          <div className="max-w-2xl mb-16">
            <h1 className="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 sm:text-4xl">
              Commission a Portrait
            </h1>
            <p className="mt-4 text-base leading-7 text-zinc-600 dark:text-zinc-400">
              Every portrait I paint is made to order. Whether it&apos;s a family member, a beloved pet, or an abstract personal vision —
              I work closely with each client to create something unique, enduring, and entirely theirs.
            </p>
            <p className="mt-3 text-base leading-7 text-zinc-600 dark:text-zinc-400">
              Available in acrylic, oil pastel, watercolour, and digital. Sizes from A4 to 100&nbsp;×&nbsp;120&nbsp;cm.
              All originals include a signed certificate of authenticity.
            </p>
          </div>

          <div className="grid gap-16 lg:grid-cols-[1fr_2fr]">
            {/* Process sidebar */}
            <aside>
              <h2 className="text-lg font-semibold text-zinc-900 dark:text-zinc-50 mb-6">
                The process
              </h2>
              <ol className="space-y-8">
                {PROCESS_STEPS.map(({ step, title, description }) => (
                  <li key={step} className="flex gap-4">
                    <span
                      className="flex-none w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 text-xs font-bold text-zinc-500 dark:text-zinc-400 flex items-center justify-center"
                      aria-hidden="true"
                    >
                      {step}
                    </span>
                    <div>
                      <h3 className="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{title}</h3>
                      <p className="mt-1 text-sm text-zinc-600 dark:text-zinc-400 leading-6">{description}</p>
                    </div>
                  </li>
                ))}
              </ol>

              <div className="mt-10 rounded-xl bg-zinc-50 dark:bg-zinc-900 ring-1 ring-zinc-100 dark:ring-zinc-800 px-5 py-6">
                <h3 className="text-sm font-semibold text-zinc-900 dark:text-zinc-50 mb-2">
                  Prefer to browse first?
                </h3>
                <p className="text-xs text-zinc-500 dark:text-zinc-400 mb-4">
                  Browse the portfolio or pick up a print from the shop.
                </p>
                <div className="flex flex-col gap-2">
                  <Link
                    href="/portfolio"
                    className="text-xs font-medium underline underline-offset-4 text-zinc-900 dark:text-zinc-100 hover:text-zinc-600 dark:hover:text-zinc-400 transition-colors"
                  >
                    View portfolio →
                  </Link>
                  <Link
                    href="/shop"
                    className="text-xs font-medium underline underline-offset-4 text-zinc-900 dark:text-zinc-100 hover:text-zinc-600 dark:hover:text-zinc-400 transition-colors"
                  >
                    Browse the shop →
                  </Link>
                </div>
              </div>
            </aside>

            {/* Form */}
            <section aria-labelledby="form-heading">
              <h2 id="form-heading" className="text-lg font-semibold text-zinc-900 dark:text-zinc-50 mb-6">
                Send a brief
              </h2>
              <CommissionForm />
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
