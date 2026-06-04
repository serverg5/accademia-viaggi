<div
    class="space-y-4"
    x-data="{ copied: false, text: @js($summary['body']) }"
>
    @if ($summary['is_too_long'])
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Il riepilogo supera il limite pratico dei link email. Copia il testo e incollalo nel tuo client email.
        </div>
    @else
        <div class="flex flex-wrap gap-2">
            <a
                class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500"
                href="{{ $summary['mailto_url'] }}"
            >
                Apri email
            </a>
        </div>
    @endif

    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            Oggetto
        </label>
        <input
            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900"
            readonly
            type="text"
            value="{{ $summary['subject'] }}"
        />
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            Riepilogo email
        </label>
        <textarea
            class="block min-h-80 w-full rounded-lg border-gray-300 font-mono text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900"
            readonly
            x-text="text"
        ></textarea>
    </div>

    <button
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800"
        type="button"
        x-on:click="navigator.clipboard.writeText(text).then(() => { copied = true; setTimeout(() => copied = false, 1800) })"
    >
        <span x-show="! copied">Copia riepilogo email</span>
        <span x-cloak x-show="copied">Copiato</span>
    </button>
</div>
