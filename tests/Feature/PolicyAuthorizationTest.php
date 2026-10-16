<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_authenticated_user_passes_property_view_any_and_create_gates(): void
    {
        $user = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Property::class));
        $this->assertTrue(Gate::forUser($user)->allows('create', Property::class));
    }

    public function test_only_the_owner_can_update_or_delete_a_property(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $property = Property::create([
            'title' => 'Policy Test Property',
            'property_code' => 'POL-001',
            'owner_user_id' => $owner->id,
            'status' => 'verified',
        ]);

        $this->assertTrue(Gate::forUser($owner)->allows('update', $property));
        $this->assertTrue(Gate::forUser($owner)->allows('delete', $property));
        $this->assertFalse(Gate::forUser($other)->allows('update', $property));
        $this->assertFalse(Gate::forUser($other)->allows('delete', $property));
    }
}
