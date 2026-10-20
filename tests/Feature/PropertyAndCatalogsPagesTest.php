<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\PropertyType;
use App\Models\Amenity;
use App\Models\PropertyFeature;

class PropertyAndCatalogsPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AuthorizationSeeder::class);
        $this->user = User::factory()->create();
        $this->user->assignRole('Administrator');
    }

    public function test_amenities_page_renders_cleanly(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard/amenities');
        $response->assertStatus(200);
    }

    public function test_features_page_renders_cleanly(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard/features');
        $response->assertStatus(200);
    }

    public function test_property_create_page_renders_cleanly(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard/properties/create');
        $response->assertStatus(200);
        $response->assertViewHas(['categories', 'types', 'amenities', 'features']);
    }

    public function test_amenity_crud_and_status_toggle(): void
    {
        // 1. Create with auto-generated code
        $createResponse = $this->actingAs($this->user)->post('/dashboard/amenities', [
            'name_en' => 'Olympic Swimming Pool',
            'category' => 'Wellness',
            'status' => 'active',
        ]);
        $createResponse->assertRedirect('/dashboard/amenities');
        $createResponse->assertSessionHas('success');

        $amenity = Amenity::where('name_en', 'Olympic Swimming Pool')->first();
        $this->assertNotNull($amenity);
        $this->assertEquals('olympic_swimming_pool', $amenity->code);
        $this->assertEquals('Wellness', $amenity->category);
        $this->assertEquals('active', $amenity->status);

        // 2. Duplicate name auto-generates unique code without 500 error
        $dupResponse = $this->actingAs($this->user)->post('/dashboard/amenities', [
            'name_en' => 'Olympic Swimming Pool',
            'category' => 'Wellness',
            'status' => 'active',
        ]);
        $dupResponse->assertRedirect('/dashboard/amenities');
        $dupAmenity = Amenity::where('code', 'olympic_swimming_pool_1')->first();
        $this->assertNotNull($dupAmenity);

        // 3. Update
        $updateResponse = $this->actingAs($this->user)->put("/dashboard/amenities/{$amenity->id}", [
            'name_en' => 'Heated Olympic Swimming Pool',
            'category' => 'Aquatics',
            'code' => $amenity->code,
            'status' => 'active',
        ]);
        $updateResponse->assertRedirect('/dashboard/amenities');
        $this->assertEquals('Heated Olympic Swimming Pool', $amenity->fresh()->name_en);

        // 4. Toggle status
        $toggleResponse = $this->actingAs($this->user)->patch("/dashboard/amenities/{$amenity->id}/toggle-status");
        $this->assertEquals('inactive', $amenity->fresh()->status);

        // 5. Delete
        $deleteResponse = $this->actingAs($this->user)->delete("/dashboard/amenities/{$amenity->id}");
        $deleteResponse->assertRedirect('/dashboard/amenities');
        $this->assertDatabaseMissing('amenities', ['id' => $amenity->id]);
    }

    public function test_feature_crud_and_status_toggle(): void
    {
        // 1. Create with auto-generated code
        $createResponse = $this->actingAs($this->user)->post('/dashboard/features', [
            'name_en' => 'Imported Granite Countertops',
            'status' => 'active',
        ]);
        $createResponse->assertRedirect('/dashboard/features');
        $createResponse->assertSessionHas('success');

        $feature = PropertyFeature::where('name_en', 'Imported Granite Countertops')->first();
        $this->assertNotNull($feature);
        $this->assertEquals('imported_granite_countertops', $feature->code);

        // 2. Duplicate auto-generates unique code safely
        $dupResponse = $this->actingAs($this->user)->post('/dashboard/features', [
            'name_en' => 'Imported Granite Countertops',
            'status' => 'active',
        ]);
        $dupResponse->assertRedirect('/dashboard/features');
        $dupFeature = PropertyFeature::where('code', 'imported_granite_countertops_1')->first();
        $this->assertNotNull($dupFeature);

        // 3. Update
        $updateResponse = $this->actingAs($this->user)->put("/dashboard/features/{$feature->id}", [
            'name_en' => 'Brazilian Granite Countertops',
            'code' => $feature->code,
            'status' => 'active',
        ]);
        $updateResponse->assertRedirect('/dashboard/features');
        $this->assertEquals('Brazilian Granite Countertops', $feature->fresh()->name_en);

        // 4. Toggle status
        $toggleResponse = $this->actingAs($this->user)->patch("/dashboard/features/{$feature->id}/toggle-status");
        $this->assertEquals('inactive', $feature->fresh()->status);

        // 5. Delete
        $deleteResponse = $this->actingAs($this->user)->delete("/dashboard/features/{$feature->id}");
        $deleteResponse->assertRedirect('/dashboard/features');
        $this->assertDatabaseMissing('property_features', ['id' => $feature->id]);
    }

    public function test_property_can_be_created_with_amenities_features_and_media(): void
    {
        Storage::fake('public');

        // Create Category and Type
        $category = PropertyCategory::create([
            'code' => 'residential',
            'name_en' => 'Residential',
            'status' => 'active',
        ]);

        $type = PropertyType::create([
            'code' => 'villa',
            'name_en' => 'Luxury Villa',
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        // Create Amenities & Features
        $amenity1 = Amenity::create([
            'code' => 'generator',
            'name_en' => 'Standby Generator',
            'category' => 'Utilities',
            'status' => 'active',
        ]);

        $amenity2 = Amenity::create([
            'code' => 'pool',
            'name_en' => 'Private Pool',
            'category' => 'Wellness',
            'status' => 'active',
        ]);

        $feature1 = PropertyFeature::create([
            'code' => 'marble_floors',
            'name_en' => 'Italian Marble Flooring',
            'status' => 'active',
        ]);

        // Mock photo file
        $file = UploadedFile::fake()->image('villa_exterior.jpg', 1200, 800);

        // Submit Property Creation Form
        $response = $this->actingAs($this->user)->post('/dashboard/properties', [
            'title' => 'Stunning Contemporary Villa in Old Airport',
            'description' => 'A prime embassy-grade diplomatic residence with full backup utilities.',
            'category_id' => $category->id,
            'type_id' => $type->id,
            'status' => 'available',
            'address' => 'Old Airport, Near ICS School',
            'bedrooms' => 5,
            'bathrooms' => 4.5,
            'area' => 450,
            'year_built' => 2024,
            'latitude' => 8.9952,
            'longitude' => 38.7421,
            'amenities' => [
                ['id' => $amenity1->id, 'value' => '100 KVA'],
                ['id' => $amenity2->id, 'value' => 'Heated'],
            ],
            'features' => [
                ['id' => $feature1->id, 'value' => 'Imported Carrara'],
            ],
            'photos' => [$file],
        ]);

        $response->assertRedirect('/dashboard/properties');
        $response->assertSessionHas('success');

        // Verify property was created in database
        $property = Property::where('title', 'Stunning Contemporary Villa in Old Airport')->first();
        $this->assertNotNull($property);
        $this->assertStringStartsWith('PROP-', $property->property_code);
        $this->assertEquals($type->id, $property->type_id);
        $this->assertEquals($category->id, $property->category_id);
        $this->assertEquals($this->user->id, $property->owner_user_id);
        $this->assertEquals('Old Airport, Near ICS School', $property->address);
        $this->assertEquals(5, $property->bedrooms);
        $this->assertEquals(4.5, $property->bathrooms);
        $this->assertEquals(450, $property->building_area);
        $this->assertEquals(2024, $property->construction_year);
        $this->assertEquals(8.9952, (float) $property->latitude);
        $this->assertEquals(38.7421, (float) $property->longitude);

        // Verify Amenities pivot sync
        $this->assertCount(2, $property->amenities);
        $this->assertEquals('100 KVA', $property->amenities()->where('amenities.id', $amenity1->id)->first()->pivot->value);

        // Verify Features pivot sync
        $this->assertCount(1, $property->features);
        $this->assertEquals('Imported Carrara', $property->features()->where('property_features.id', $feature1->id)->first()->pivot->value);

        // Verify Media was saved
        $this->assertCount(1, $property->media);
        $media = $property->media->first();
        $this->assertEquals('image', $media->kind);
        $this->assertEquals(1200, $media->width);
        $this->assertEquals(800, $media->height);
        Storage::disk('public')->assertExists($media->storage_key);
    }

    public function test_property_can_be_edited_updated_and_deleted(): void
    {
        $property = Property::create([
            'property_code' => 'PROP-2026-TEST01',
            'title' => 'Original Property Title',
            'status' => 'draft',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'building_area' => 120,
        ]);

        // Edit page loads
        $editResponse = $this->actingAs($this->user)->get("/dashboard/properties/{$property->id}/edit");
        $editResponse->assertStatus(200);

        // Update
        $updateResponse = $this->actingAs($this->user)->put("/dashboard/properties/{$property->id}", [
            'title' => 'Updated Property Title',
            'status' => 'available',
            'address' => 'Bole Subcity',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'building_area' => 180,
        ]);
        $updateResponse->assertRedirect('/dashboard/properties');

        $this->assertEquals('Updated Property Title', $property->fresh()->title);
        $this->assertEquals('Bole Subcity', $property->fresh()->address);
        $this->assertEquals(3, $property->fresh()->bedrooms);

        // Destroy
        $deleteResponse = $this->actingAs($this->user)->delete("/dashboard/properties/{$property->id}");
        $deleteResponse->assertRedirect('/dashboard/properties');
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }
}
