<?php

namespace Tests\Feature;

use App\Jobs\ProcessPropertyMedia;
use App\Models\Property;
use App\Models\PropertyMedia;
use App\Models\User;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class Module3PropertyMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthorizationSeeder::class);
    }

    public function test_property_creation_and_price_update_writes_price_history(): void
    {
        $owner = User::factory()->create();

        $property = Property::create([
            'id' => (string) Str::uuid(),
            'property_code' => 'PROP-'.Str::random(6),
            'title' => 'Bole Residence',
            'owner_user_id' => $owner->id,
            'price' => 5000000.00,
            'currency' => 'ETB',
            'status' => 'draft',
        ]);

        // Creation history
        $this->assertDatabaseHas('property_price_history', [
            'property_id' => $property->id,
            'price' => 5000000.00,
            'currency' => 'ETB',
        ]);

        // Acting as owner to update price
        $this->actingAs($owner);
        $property->update([
            'price' => 5500000.00,
        ]);

        // Updated price history
        $this->assertDatabaseHas('property_price_history', [
            'property_id' => $property->id,
            'price' => 5500000.00,
            'changed_by' => $owner->id,
        ]);

        $this->assertCount(2, $property->priceHistory);
        $this->assertEquals(5500000.00, (float) $property->priceHistory->first()->price);
    }

    public function test_property_status_transition_writes_status_history(): void
    {
        $admin = User::factory()->create();

        $property = Property::create([
            'id' => (string) Str::uuid(),
            'property_code' => 'PROP-'.Str::random(6),
            'title' => 'Kazanchis Commercial Space',
            'owner_user_id' => $admin->id,
            'price' => 12000000.00,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('property_status_history', [
            'property_id' => $property->id,
            'from_status' => null,
            'to_status' => 'draft',
        ]);

        // Update status to active
        $this->actingAs($admin);
        $property->update([
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('property_status_history', [
            'property_id' => $property->id,
            'from_status' => 'draft',
            'to_status' => 'active',
            'changed_by' => $admin->id,
        ]);

        $this->assertCount(2, $property->statusHistory);
        $this->assertEquals('active', $property->statusHistory->first()->to_status);
        $this->assertEquals('draft', $property->statusHistory->first()->from_status);
    }

    public function test_process_property_media_job_processes_and_watermarks_image(): void
    {
        Storage::fake('public');

        $property = Property::create([
            'id' => (string) Str::uuid(),
            'property_code' => 'PROP-'.Str::random(6),
            'title' => 'Watermark Test Property',
            'price' => 1000000.00,
            'status' => 'draft',
        ]);

        // Create a 200x200 sample test jpeg image
        $imageResource = imagecreatetruecolor(200, 200);
        $bg = imagecolorallocate($imageResource, 100, 150, 200);
        imagefilledrectangle($imageResource, 0, 0, 200, 200, $bg);

        ob_start();
        imagejpeg($imageResource);
        $imageContent = ob_get_clean();
        imagedestroy($imageResource);

        $storageKey = 'properties/test-image.jpg';
        Storage::disk('public')->put($storageKey, $imageContent);

        $media = PropertyMedia::create([
            'id' => (string) Str::uuid(),
            'property_id' => $property->id,
            'kind' => 'image',
            'storage_key' => $storageKey,
            'mime_type' => 'image/jpeg',
            'status' => 'pending',
        ]);

        // Dispatch job synchronously
        (new ProcessPropertyMedia($media))->handle();

        $media->refresh();
        $this->assertEquals('processed', $media->status);
        $this->assertEquals(200, $media->width);
        $this->assertEquals(200, $media->height);

        // Verify watermarked file exists on disk
        $this->assertTrue(Storage::disk('public')->exists($storageKey));
    }
}
