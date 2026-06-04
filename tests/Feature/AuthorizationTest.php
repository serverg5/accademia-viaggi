<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\TravelColumn;
use App\Models\TravelYear;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedTravelDomain();
    }

    public function test_admin_has_expected_permissions(): void
    {
        $admin = $this->adminUser();

        foreach (Permissions::ALL as $permission) {
            $this->assertTrue($admin->can($permission), "Admin should have permission [{$permission}].");
        }
    }

    public function test_operatore_has_only_expected_permissions(): void
    {
        $operator = $this->operatorUser();

        foreach (Permissions::OPERATORE as $permission) {
            $this->assertTrue($operator->can($permission), "Operatore should have permission [{$permission}].");
        }

        $this->assertFalse($operator->can(Permissions::MANAGE_USERS));
        $this->assertFalse($operator->can(Permissions::MANAGE_ROLES));
        $this->assertFalse($operator->can(Permissions::MANAGE_COMPANY_SETTINGS));
        $this->assertFalse($operator->can(Permissions::MANAGE_TRAVEL_COLUMNS));
        $this->assertFalse($operator->can(Permissions::DELETE_TRAVEL_RECORDS));
        $this->assertFalse($operator->can(Permissions::VIEW_BILLING));
        $this->assertFalse($operator->can(Permissions::EXPORT_BILLING));
        $this->assertFalse($operator->can(Permissions::MANAGE_YEARS));
        $this->assertFalse($operator->can(Permissions::UNLOCK_YEARS));
    }

    public function test_operatore_cannot_manage_columns_company_settings_years_or_unlock_years(): void
    {
        $operator = $this->operatorUser();
        $column = TravelColumn::query()->where('key', 'practice_code')->firstOrFail();
        $companySetting = CompanySetting::query()->create(['company_name' => 'Test Azienda']);
        $travelYear = TravelYear::query()->firstOrCreate(['year' => 2030]);

        $this->assertFalse(Gate::forUser($operator)->allows('update', $column));
        $this->assertFalse(Gate::forUser($operator)->allows('update', $companySetting));
        $this->assertFalse(Gate::forUser($operator)->allows('update', $travelYear));
        $this->assertFalse(Gate::forUser($operator)->allows('unlock', $travelYear));
    }

    public function test_admin_can_manage_configuration_subject_to_system_column_safety_rules(): void
    {
        $admin = $this->adminUser();
        $customColumn = TravelColumn::query()->create([
            'key' => 'custom_note',
            'label' => 'Nota',
            'type' => TravelColumn::TYPE_TEXT,
            'is_deletable' => true,
        ]);
        $systemColumn = TravelColumn::query()->where('key', 'practice_code')->firstOrFail();

        $this->assertTrue(Gate::forUser($admin)->allows('create', TravelColumn::class));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $customColumn));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $customColumn));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $systemColumn));
    }

    public function test_admin_can_manage_users_and_operator_cannot(): void
    {
        $admin = $this->adminUser();
        $operator = $this->operatorUser();

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', User::class));
        $this->assertTrue(Gate::forUser($admin)->allows('create', User::class));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $admin));
        $this->assertFalse(Gate::forUser($operator)->allows('viewAny', User::class));

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk();

        $this->actingAs($operator)
            ->get('/admin/users')
            ->assertForbidden();
    }
}
