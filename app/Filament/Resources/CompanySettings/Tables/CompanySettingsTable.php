<?php

namespace App\Filament\Resources\CompanySettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompanySettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')
                    ->label('Ragione sociale')
                    ->searchable(),
                TextColumn::make('logo_path')
                    ->label('Logo')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Telefono')
                    ->searchable(),
                TextColumn::make('vat_number')
                    ->label('Partita IVA')
                    ->searchable(),
                TextColumn::make('tax_code')
                    ->label('Codice fiscale')
                    ->searchable(),
                TextColumn::make('bank_name')
                    ->label('Banca')
                    ->searchable(),
                TextColumn::make('iban')
                    ->label('IBAN')
                    ->searchable(),
                TextColumn::make('bank_account_holder')
                    ->label('Intestatario conto')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Aggiornato il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifica'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Elimina selezionati'),
                ]),
            ]);
    }
}
