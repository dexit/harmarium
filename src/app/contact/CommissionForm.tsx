'use client'

import { useState, useTransition } from 'react'
import { submitCommission } from '@/lib/wp'

type Field = {
  name: string
  email: string
  subject: string
  message: string
  budget: string
  timeline: string
}

const EMPTY: Field = {
  name: '',
  email: '',
  subject: '',
  message: '',
  budget: '',
  timeline: '',
}

const BUDGET_OPTIONS = [
  { value: '', label: 'Select a range (optional)' },
  { value: 'under-500', label: 'Under €500' },
  { value: '500-1000', label: '€500 – €1,000' },
  { value: '1000-2000', label: '€1,000 – €2,000' },
  { value: '2000-plus', label: '€2,000+' },
  { value: 'discuss', label: 'Happy to discuss' },
]

const TIMELINE_OPTIONS = [
  { value: '', label: 'Select a timeline (optional)' },
  { value: '2-4-weeks', label: '2 – 4 weeks' },
  { value: '1-2-months', label: '1 – 2 months' },
  { value: '3-plus-months', label: '3+ months' },
  { value: 'flexible', label: 'Flexible' },
]

export default function CommissionForm() {
  const [fields, setFields] = useState<Field>(EMPTY)
  const [errors, setErrors] = useState<Partial<Field>>({})
  const [submitted, setSubmitted] = useState(false)
  const [serverError, setServerError] = useState('')
  const [isPending, startTransition] = useTransition()

  const update = (key: keyof Field) => (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>
  ) => {
    setFields(prev => ({ ...prev, [key]: e.target.value }))
    if (errors[key]) setErrors(prev => ({ ...prev, [key]: undefined }))
  }

  const validate = (): boolean => {
    const next: Partial<Field> = {}
    if (!fields.name.trim()) next.name = 'Your name is required.'
    if (!fields.email.trim()) {
      next.email = 'An email address is required.'
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(fields.email)) {
      next.email = 'Please enter a valid email address.'
    }
    if (!fields.subject.trim()) next.subject = 'A subject is required.'
    if (!fields.message.trim()) next.message = 'Please tell me a little about the commission.'
    setErrors(next)
    return Object.keys(next).length === 0
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    if (!validate()) return
    setServerError('')

    startTransition(async () => {
      try {
        await submitCommission({
          ...fields,
          nonce: (window as unknown as { harmarium?: { nonce?: string } }).harmarium?.nonce ?? '',
        })
        setSubmitted(true)
        setFields(EMPTY)
      } catch (err) {
        setServerError(err instanceof Error ? err.message : 'Something went wrong. Please try again.')
      }
    })
  }

  if (submitted) {
    return (
      <div
        role="status"
        aria-live="polite"
        className="rounded-2xl bg-zinc-50 dark:bg-zinc-900 ring-1 ring-zinc-200 dark:ring-zinc-700 px-8 py-12 text-center"
      >
        <p className="text-2xl font-bold text-zinc-900 dark:text-zinc-50 mb-3">
          Thank you — request received.
        </p>
        <p className="text-sm text-zinc-600 dark:text-zinc-400">
          I&apos;ll review your brief and get back to you within 2 business days.
        </p>
        <button
          onClick={() => setSubmitted(false)}
          className="mt-8 text-sm underline underline-offset-4 text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors"
        >
          Submit another request
        </button>
      </div>
    )
  }

  return (
    <form
      onSubmit={handleSubmit}
      noValidate
      aria-label="Commission request form"
      className="space-y-6"
    >
      {serverError && (
        <div
          role="alert"
          className="rounded-lg bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300"
        >
          {serverError}
        </div>
      )}

      <div className="grid gap-6 sm:grid-cols-2">
        {/* Name */}
        <div>
          <label htmlFor="hm-name" className="block text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-1.5">
            Your name <span aria-hidden="true" className="text-red-500">*</span>
          </label>
          <input
            id="hm-name"
            type="text"
            autoComplete="name"
            required
            aria-required="true"
            aria-describedby={errors.name ? 'hm-name-error' : undefined}
            aria-invalid={!!errors.name}
            value={fields.name}
            onChange={update('name')}
            className="block w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition"
            placeholder="Jane Smith"
          />
          {errors.name && (
            <p id="hm-name-error" role="alert" className="mt-1.5 text-xs text-red-600 dark:text-red-400">
              {errors.name}
            </p>
          )}
        </div>

        {/* Email */}
        <div>
          <label htmlFor="hm-email" className="block text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-1.5">
            Email address <span aria-hidden="true" className="text-red-500">*</span>
          </label>
          <input
            id="hm-email"
            type="email"
            autoComplete="email"
            required
            aria-required="true"
            aria-describedby={errors.email ? 'hm-email-error' : undefined}
            aria-invalid={!!errors.email}
            value={fields.email}
            onChange={update('email')}
            className="block w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition"
            placeholder="jane@example.com"
          />
          {errors.email && (
            <p id="hm-email-error" role="alert" className="mt-1.5 text-xs text-red-600 dark:text-red-400">
              {errors.email}
            </p>
          )}
        </div>
      </div>

      {/* Subject */}
      <div>
        <label htmlFor="hm-subject" className="block text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-1.5">
          Subject <span aria-hidden="true" className="text-red-500">*</span>
        </label>
        <input
          id="hm-subject"
          type="text"
          required
          aria-required="true"
          aria-describedby={errors.subject ? 'hm-subject-error' : undefined}
          aria-invalid={!!errors.subject}
          value={fields.subject}
          onChange={update('subject')}
          className="block w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition"
          placeholder="Portrait of my grandmother, acrylic on canvas"
        />
        {errors.subject && (
          <p id="hm-subject-error" role="alert" className="mt-1.5 text-xs text-red-600 dark:text-red-400">
            {errors.subject}
          </p>
        )}
      </div>

      {/* Message */}
      <div>
        <label htmlFor="hm-message" className="block text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-1.5">
          Tell me about the piece <span aria-hidden="true" className="text-red-500">*</span>
        </label>
        <textarea
          id="hm-message"
          rows={5}
          required
          aria-required="true"
          aria-describedby={errors.message ? 'hm-message-error' : undefined}
          aria-invalid={!!errors.message}
          value={fields.message}
          onChange={update('message')}
          className="block w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition resize-y"
          placeholder="Size, medium, subject, reference photos available, any specific style notes…"
        />
        {errors.message && (
          <p id="hm-message-error" role="alert" className="mt-1.5 text-xs text-red-600 dark:text-red-400">
            {errors.message}
          </p>
        )}
      </div>

      <div className="grid gap-6 sm:grid-cols-2">
        {/* Budget */}
        <div>
          <label htmlFor="hm-budget" className="block text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-1.5">
            Budget range
          </label>
          <select
            id="hm-budget"
            value={fields.budget}
            onChange={update('budget')}
            className="block w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition"
          >
            {BUDGET_OPTIONS.map(o => (
              <option key={o.value} value={o.value}>{o.label}</option>
            ))}
          </select>
        </div>

        {/* Timeline */}
        <div>
          <label htmlFor="hm-timeline" className="block text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-1.5">
            Ideal timeline
          </label>
          <select
            id="hm-timeline"
            value={fields.timeline}
            onChange={update('timeline')}
            className="block w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition"
          >
            {TIMELINE_OPTIONS.map(o => (
              <option key={o.value} value={o.value}>{o.label}</option>
            ))}
          </select>
        </div>
      </div>

      <div className="flex items-center gap-4 pt-2">
        <button
          type="submit"
          disabled={isPending}
          className="rounded-md bg-black px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-zinc-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black dark:bg-white dark:text-black dark:hover:bg-zinc-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          aria-busy={isPending}
        >
          {isPending ? 'Sending…' : 'Send request'}
        </button>
        <p className="text-xs text-zinc-400">
          <span aria-hidden="true" className="text-red-500">* </span>Required fields
        </p>
      </div>
    </form>
  )
}
