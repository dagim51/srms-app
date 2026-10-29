<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (! Schema::hasColumn('roles', 'description')) {
                    $table->string('description', 500)->nullable()->after('name');
                }
                if (! Schema::hasColumn('roles', 'is_system')) {
                    $table->boolean('is_system')->default(false)->after('status');
                }
            });
        }

        if (Schema::hasTable('documents')) {
            Schema::table('documents', function (Blueprint $table) {
                if (! Schema::hasColumn('documents', 'access_level')) {
                    $table->string('access_level', 40)->default('private')->after('status');
                    $table->index(['access_level']);
                }
                if (! Schema::hasColumn('documents', 'documentable_type')) {
                    $table->string('documentable_type', 120)->nullable()->after('description');
                    $table->uuid('documentable_id')->nullable()->after('documentable_type');
                    $table->index(['documentable_type', 'documentable_id']);
                }
            });
        }

        if (Schema::hasTable('user_roles')) {
            Schema::table('user_roles', function (Blueprint $table) {
                if (! Schema::hasColumn('user_roles', 'branch_id')) {
                    $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                }
                if (! Schema::hasColumn('user_roles', 'team_id')) {
                    $table->foreignUuid('team_id')->nullable()->constrained('teams')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('user_permissions')) {
            Schema::table('user_permissions', function (Blueprint $table) {
                if (! Schema::hasColumn('user_permissions', 'branch_id')) {
                    $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                }
                if (! Schema::hasColumn('user_permissions', 'team_id')) {
                    $table->foreignUuid('team_id')->nullable()->constrained('teams')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (Schema::hasColumn('roles', 'is_system')) {
                    $table->dropColumn('is_system');
                }
                if (Schema::hasColumn('roles', 'description')) {
                    $table->dropColumn('description');
                }
            });
        }

        if (Schema::hasTable('user_permissions')) {
            Schema::table('user_permissions', function (Blueprint $table) {
                if (Schema::hasColumn('user_permissions', 'team_id')) {
                    $table->dropForeign(['team_id']);
                    $table->dropColumn('team_id');
                }
                if (Schema::hasColumn('user_permissions', 'branch_id')) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                }
            });
        }

        if (Schema::hasTable('user_roles')) {
            Schema::table('user_roles', function (Blueprint $table) {
                if (Schema::hasColumn('user_roles', 'team_id')) {
                    $table->dropForeign(['team_id']);
                    $table->dropColumn('team_id');
                }
                if (Schema::hasColumn('user_roles', 'branch_id')) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                }
            });
        }

        if (Schema::hasTable('documents')) {
            Schema::table('documents', function (Blueprint $table) {
                if (Schema::hasColumn('documents', 'documentable_type')) {
                    $table->dropIndex(['documentable_type', 'documentable_id']);
                    $table->dropColumn(['documentable_type', 'documentable_id']);
                }
                if (Schema::hasColumn('documents', 'access_level')) {
                    $table->dropColumn('access_level');
                }
            });
        }
    }
};
