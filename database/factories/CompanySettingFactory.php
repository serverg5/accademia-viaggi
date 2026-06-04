<?php

namespace Database\Factories;

use App\Models\CompanySetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanySetting>
 */
class CompanySettingFactory extends Factory
{
    protected $model = CompanySetting::class;

    public function definition(): array
    {
        return [
            'company_name' => 'Accademia Viaggi Demo',
            'logo_path' => null,
            'email' => 'amministrazione@example.test',
            'phone' => '+39 049 123 4567',
            'address' => 'Via Roma 24, 35122 Padova',
            'vat_number' => 'IT01234567890',
            'tax_code' => '01234567890',
            'bank_name' => 'Banca Demo',
            'iban' => 'IT60X0542811101000000123456',
            'bank_account_holder' => 'Accademia Viaggi Demo S.r.l.',
            'footer_notes' => 'Dati dimostrativi generati per test locali.',
        ];
    }
}
