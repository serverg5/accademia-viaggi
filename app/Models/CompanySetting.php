<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'logo_path',
        'email',
        'phone',
        'address',
        'vat_number',
        'tax_code',
        'bank_name',
        'iban',
        'bank_account_holder',
        'footer_notes',
    ];
}
