<?php

namespace Tests\Feature;

use App\Models\ImportLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BulkImportFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_bulk_import_hub(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $response = $this->actingAs($staff)->get(route('admin.bulk-import.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_bulk_import_hub(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->get(route('admin.bulk-import.index'));

        $response->assertStatus(200);
        $response->assertSee('Bulk Data Import Hub');
    }

    public function test_admin_can_download_sample_csv_templates(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $types = ['villas', 'guests', 'bookings', 'expenses'];

        foreach ($types as $type) {
            $response = $this->actingAs($admin)->get(route('admin.bulk-import.template', $type));

            $response->assertStatus(200);
            $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
            $this->assertStringContainsString("villa_{$type}_template.csv", (string) $response->headers->get('Content-Disposition'));
        }
    }

    public function test_admin_can_bulk_import_villas_via_csv(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $csvContent = "code,name,category,bedrooms,bathrooms,max_guests,area_sqm,price_per_night,weekend_price_per_night,cleaning_fee,security_deposit,status,location,address,description,amenities,is_featured\n".
            "VIL-881,Coral Haven Pavilion,Beachfront,4,5,8,450,1500.00,1800.00,120.00,500.00,available,North Coast,101 Coral Bay,Luxury oceanfront retreat,\"Private Pool, Ocean View\",true\n".
            "VIL-882,Emerald Peak Sanctuary,Mountain View,3,3,6,320,950.00,1100.00,90.00,300.00,available,Summit Ridge,44 Peak Road,Mountain sanctuary,\"Heated Pool, Wi-Fi 6\",false\n";

        $file = UploadedFile::fake()->createWithContent('villas_import.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('admin.bulk-import.store'), [
            'type' => 'villas',
            'file' => $file,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('villas', ['code' => 'VIL-881', 'name' => 'Coral Haven Pavilion']);
        $this->assertDatabaseHas('villas', ['code' => 'VIL-882', 'name' => 'Emerald Peak Sanctuary']);

        $this->assertDatabaseHas('import_logs', [
            'user_id' => $admin->id,
            'import_type' => 'villas',
            'imported_rows' => 2,
            'status' => ImportLog::STATUS_COMPLETED,
        ]);
    }
}
