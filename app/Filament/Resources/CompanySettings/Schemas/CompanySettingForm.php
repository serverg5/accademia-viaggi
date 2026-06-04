<?php

namespace App\Filament\Resources\CompanySettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanySettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dati aziendali')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Ragione sociale')
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->directory('company/logos'),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telefono')
                            ->tel()
                            ->maxLength(255),
                        Textarea::make('address')
                            ->label('Indirizzo')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('vat_number')
                            ->label('Partita IVA')
                            ->maxLength(255),
                        TextInput::make('tax_code')
                            ->label('Codice fiscale')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Dati bancari e note')
                    ->schema([
                        TextInput::make('bank_name')
                            ->label('Banca')
                            ->maxLength(255),
                        TextInput::make('iban')
                            ->label('IBAN')
                            ->maxLength(255),
                        TextInput::make('bank_account_holder')
                            ->label('Intestatario conto')
                            ->maxLength(255),
                        Textarea::make('footer_notes')
                            ->label('Note pie di pagina')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
