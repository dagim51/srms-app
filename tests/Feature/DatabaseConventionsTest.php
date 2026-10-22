<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DatabaseConventionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unique_constraints_prevent_duplicates()
    {
        $orgId1 = Str::uuid()->toString();

        DB::table('organizations')->insert([
            'id' => $orgId1,
            'name' => 'Org 1',
            'slug' => 'unique-slug',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        // This should fail due to unique constraint on 'slug'
        DB::table('organizations')->insert([
            'id' => Str::uuid()->toString(),
            'name' => 'Org 2',
            'slug' => 'unique-slug',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_foreign_key_constraints_prevent_invalid_references()
    {
        $this->expectException(QueryException::class);

        // This should fail because the organization_id and user_id don't exist
        DB::table('organization_members')->insert([
            'id' => Str::uuid()->toString(),
            'organization_id' => Str::uuid()->toString(),
            'user_id' => Str::uuid()->toString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_composite_unique_constraints_work()
    {
        $orgId = Str::uuid()->toString();
        $userId = Str::uuid()->toString();

        DB::table('organizations')->insert([
            'id' => $orgId,
            'name' => 'Org',
            'slug' => 'org-slug',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => $userId,
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => 'secret',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('organization_members')->insert([
            'id' => Str::uuid()->toString(),
            'organization_id' => $orgId,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        // This should fail due to unique constraint on ['organization_id', 'user_id']
        DB::table('organization_members')->insert([
            'id' => Str::uuid()->toString(),
            'organization_id' => $orgId,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
