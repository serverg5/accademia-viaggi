<x-filament-panels::page>
    @php($preview = $this->preview())

    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Modello</h2>

            <div class="mt-4 flex flex-wrap gap-3">
                <a
                    class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-500"
                    href="{{ route('travel-record-import-export.template', ['format' => 'xlsx']) }}"
                >
                    Scarica modello Excel
                </a>
                <a
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    href="{{ route('travel-record-import-export.template', ['format' => 'csv']) }}"
                >
                    Scarica modello CSV
                </a>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Esporta record</h2>

            <form class="mt-4 grid gap-3 sm:grid-cols-2" method="GET" action="{{ route('travel-record-import-export.export', ['format' => 'xlsx']) }}">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    Anno
                    <select name="year" class="mt-1 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                        <option value="">Tutti</option>
                        @foreach ($this->yearOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    Mese
                    <select name="month" class="mt-1 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                        <option value="">Tutti</option>
                        @foreach ($this->monthOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="flex flex-wrap gap-3 sm:col-span-2">
                    <button
                        class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-500"
                        type="submit"
                    >
                        Esporta Excel
                    </button>
                    <button
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        formaction="{{ route('travel-record-import-export.export', ['format' => 'csv']) }}"
                        type="submit"
                    >
                        Esporta CSV
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Importa record</h2>

        <form class="mt-4 flex flex-wrap items-end gap-3" method="POST" action="{{ route('travel-record-import-export.preview') }}" enctype="multipart/form-data">
            @csrf
            <label class="min-w-72 flex-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                File Excel o CSV
                <input
                    accept=".xlsx,.csv"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-950"
                    name="file"
                    required
                    type="file"
                />
            </label>
            <button
                class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-500"
                type="submit"
            >
                Analizza file
            </button>
        </form>
    </section>

    @if ($preview)
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Anteprima import</h2>

                <div class="flex flex-wrap gap-2 text-sm">
                    <span class="rounded-md bg-green-100 px-2 py-1 text-green-800">Da creare: {{ $preview['summary']['create'] }}</span>
                    <span class="rounded-md bg-blue-100 px-2 py-1 text-blue-800">Da aggiornare: {{ $preview['summary']['update'] }}</span>
                    <span class="rounded-md bg-red-100 px-2 py-1 text-red-800">Errori: {{ $preview['summary']['errors'] }}</span>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead>
                        <tr class="text-left text-gray-600 dark:text-gray-300">
                            <th class="px-3 py-2">Riga</th>
                            <th class="px-3 py-2">Azione</th>
                            <th class="px-3 py-2">Anno</th>
                            <th class="px-3 py-2">Mese</th>
                            <th class="px-3 py-2">Codice pratica</th>
                            <th class="px-3 py-2">Data</th>
                            <th class="px-3 py-2">Errori</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($preview['rows'] as $row)
                            <tr>
                                <td class="px-3 py-2">{{ $row['row_number'] }}</td>
                                <td class="px-3 py-2">{{ ucfirst($row['action']) }}</td>
                                <td class="px-3 py-2">{{ $row['year'] }}</td>
                                <td class="px-3 py-2">{{ $row['month'] }}</td>
                                <td class="px-3 py-2">{{ $row['practice_code'] }}</td>
                                <td class="px-3 py-2">{{ $row['record_date'] }}</td>
                                <td class="px-3 py-2 text-red-700">{{ implode(' ', $row['errors']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
                <form method="POST" action="{{ route('travel-record-import-export.confirm') }}">
                    @csrf
                    <button
                        class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-500 disabled:cursor-not-allowed disabled:opacity-50"
                        @disabled($preview['summary']['errors'] > 0)
                        type="submit"
                    >
                        Conferma import
                    </button>
                </form>

                <form method="POST" action="{{ route('travel-record-import-export.clear') }}">
                    @csrf
                    <button
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        type="submit"
                    >
                        Annulla
                    </button>
                </form>
            </div>
        </section>
    @endif
</x-filament-panels::page>
