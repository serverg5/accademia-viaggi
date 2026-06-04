<?php

namespace Tests\Feature;

use App\Filament\Resources\TravelSelectOptions\TravelSelectOptionResource;
use App\Models\TravelColumn;
use App\Models\TravelSelectOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TravelConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedTravelDomain();
    }

    public function test_labels_can_be_renamed_and_billing_visibility_is_authorized(): void
    {
        $admin = $this->adminUser();
        $operator = $this->operatorUser();
        $column = TravelColumn::query()->where('key', 'practice_code')->firstOrFail();

        $column->update([
            'label' => 'Codice dossier',
            'is_visible_in_billing' => false,
        ]);

        $this->assertSame('Codice dossier', $column->refresh()->label);
        $this->assertFalse($column->is_visible_in_billing);
        $this->assertTrue(Gate::forUser($admin)->allows('changeBillingVisibility', $column));
        $this->assertFalse(Gate::forUser($operator)->allows('changeBillingVisibility', $column));
    }

    public function test_system_columns_cannot_be_destructively_deleted(): void
    {
        $admin = $this->adminUser();
        $systemColumn = TravelColumn::query()->where('key', 'costs')->firstOrFail();

        $this->assertTrue($systemColumn->is_system);
        $this->assertFalse($systemColumn->is_deletable);
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $systemColumn));
    }

    public function test_select_options_can_be_managed_by_admin_and_operatore_with_delete_restricted_to_admin(): void
    {
        $admin = $this->adminUser();
        $operator = $this->operatorUser();
        $selectColumn = TravelColumn::query()->where('type', TravelColumn::TYPE_SELECT)->firstOrFail();
        $option = TravelSelectOption::query()->create([
            'travel_column_id' => $selectColumn->getKey(),
            'label' => 'Auto',
            'value' => 'auto',
            'is_active' => true,
        ]);

        $this->assertTrue(Gate::forUser($admin)->allows('create', TravelSelectOption::class));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $option));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $option));
        $this->assertTrue(Gate::forUser($operator)->allows('create', TravelSelectOption::class));
        $this->assertTrue(Gate::forUser($operator)->allows('update', $option));
        $this->assertFalse(Gate::forUser($operator)->allows('delete', $option));
        $this->assertFalse(Gate::forUser($operator)->allows('update', $selectColumn));
    }

    public function test_select_option_resource_excludes_options_for_non_select_columns(): void
    {
        $this->actingAs($this->operatorUser());

        $selectColumn = TravelColumn::query()->where('type', TravelColumn::TYPE_SELECT)->firstOrFail();
        $textColumn = TravelColumn::query()->where('type', TravelColumn::TYPE_TEXT)->firstOrFail();

        $visibleOption = TravelSelectOption::query()->create([
            'travel_column_id' => $selectColumn->getKey(),
            'label' => 'Treno',
            'value' => 'treno',
        ]);
        $hiddenOption = TravelSelectOption::query()->create([
            'travel_column_id' => $textColumn->getKey(),
            'label' => 'Non valida',
            'value' => 'non-valida',
        ]);

        $ids = TravelSelectOptionResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($visibleOption->getKey(), $ids);
        $this->assertNotContains($hiddenOption->getKey(), $ids);
    }
}
