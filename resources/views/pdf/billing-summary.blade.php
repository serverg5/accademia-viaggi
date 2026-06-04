<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 32px 70px; }
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.35; }
        .header { border-bottom: 1px solid #d1d5db; margin-bottom: 18px; padding-bottom: 12px; }
        .logo { max-height: 64px; max-width: 180px; }
        .company { font-size: 19px; font-weight: 700; margin: 0 0 4px; }
        .muted { color: #4b5563; }
        h1 { font-size: 18px; margin: 0 0 14px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #f3f4f6; font-weight: 700; text-align: left; }
        th, td { border: 1px solid #d1d5db; padding: 6px 7px; vertical-align: top; }
        .totals { margin-top: 14px; width: 42%; }
        .totals td:first-child { font-weight: 700; }
        .footer { border-top: 1px solid #d1d5db; bottom: -46px; color: #4b5563; font-size: 10px; left: 0; padding-top: 8px; position: fixed; right: 0; }
    </style>
</head>
<body>
    <div class="header">
        @if ($logoDataUri)
            <div style="margin-bottom: 10px;">
                <img class="logo" src="{{ $logoDataUri }}" alt="Logo">
            </div>
        @endif

        @if ($company)
            <p class="company">{{ $company->company_name }}</p>
            <div class="muted">
                @if ($company->address)
                    <div>{{ $company->address }}</div>
                @endif
                @if ($company->email || $company->phone)
                    <div>
                        @if ($company->email) Email: {{ $company->email }} @endif
                        @if ($company->email && $company->phone) - @endif
                        @if ($company->phone) Tel: {{ $company->phone }} @endif
                    </div>
                @endif
                @if ($company->vat_number || $company->tax_code)
                    <div>
                        @if ($company->vat_number) P. IVA: {{ $company->vat_number }} @endif
                        @if ($company->vat_number && $company->tax_code) - @endif
                        @if ($company->tax_code) Codice fiscale: {{ $company->tax_code }} @endif
                    </div>
                @endif
            </div>
        @else
            <p class="company">Riepilogo fatturazione</p>
        @endif
    </div>

    <h1>Riepilogo Fatturazione - {{ $monthName }} {{ $year }}</h1>

    <table>
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th>{{ $column->label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($columns as $column)
                        <td>{{ $row['values'][$column->key] ?? '' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max($columns->count(), 1) }}">Nessun record presente per il periodo selezionato.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tbody>
            <tr>
                <td>Totale {{ $totalCostsLabel }}</td>
                <td>{{ $totalCosts }}</td>
            </tr>
            <tr>
                <td>Totale {{ $totalSalesLabel }}</td>
                <td>{{ $totalSales }}</td>
            </tr>
        </tbody>
    </table>

    @if ($company)
        <div class="footer">
            @if ($company->bank_name || $company->iban || $company->bank_account_holder)
                <div>
                    @if ($company->bank_name) Banca: {{ $company->bank_name }} @endif
                    @if ($company->bank_name && $company->iban) - @endif
                    @if ($company->iban) IBAN: {{ $company->iban }} @endif
                    @if (($company->bank_name || $company->iban) && $company->bank_account_holder) - @endif
                    @if ($company->bank_account_holder) Intestatario: {{ $company->bank_account_holder }} @endif
                </div>
            @endif
            @if ($company->footer_notes)
                <div>{{ $company->footer_notes }}</div>
            @endif
        </div>
    @endif
</body>
</html>
