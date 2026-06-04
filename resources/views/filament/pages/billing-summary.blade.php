<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="text-sm text-gray-500 dark:text-gray-400">Periodo</div>
            <div class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">
                {{ $this->monthName() }} {{ $this->selectedYear() }}
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="text-sm text-gray-500 dark:text-gray-400">Totale {{ $this->costLabel() }}</div>
            <div class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">
                {{ $this->formattedTotalCosts() }}
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="text-sm text-gray-500 dark:text-gray-400">Totale {{ $this->saleLabel() }}</div>
            <div class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">
                {{ $this->formattedTotalSales() }}
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
