import os
import glob

def write_file(path, content):
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

def replace_in_file(pattern, replacement, path):
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    content = content.replace(pattern, replacement)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

base_dir = r"c:\Users\dell\Desktop\SRMS\srms-app"
mig_dir = os.path.join(base_dir, "database", "migrations")

# Migrations
tp_mig = glob.glob(os.path.join(mig_dir, "*_create_tenant_profiles_table.php"))[0]
ra_mig = glob.glob(os.path.join(mig_dir, "*_create_rental_applications_table.php"))[0]
l_mig = glob.glob(os.path.join(mig_dir, "*_create_leases_table.php"))[0]
rs_mig = glob.glob(os.path.join(mig_dir, "*_create_rent_schedules_table.php"))[0]

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('employment_status')->nullable();
            $table->decimal('annual_income', 15, 2)->nullable();
            $table->string('background_check_status')->default('pending');
            $table->timestamps();""", tp_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('tenant_profile_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, reviewing, approved, rejected
            $table->decimal('proposed_rent', 15, 2)->nullable();
            $table->text('message')->nullable();
            $table->timestamps();""", ra_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('tenant_profile_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('rent_amount', 15, 2);
            $table->decimal('deposit_amount', 15, 2)->nullable();
            $table->string('status')->default('active'); // active, terminated, completed
            $table->timestamps();""", l_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('lease_id')->constrained()->cascadeOnDelete();
            $table->date('due_date');
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('unpaid'); // unpaid, paid, late
            $table->uuid('payment_id')->nullable();
            $table->timestamps();""", rs_mig)

# Models
models_dir = os.path.join(base_dir, "app", "Models")

write_file(os.path.join(models_dir, "TenantProfile.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TenantProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'employment_status',
        'annual_income',
        'background_check_status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
""")

write_file(os.path.join(models_dir, "RentalApplication.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RentalApplication extends Model
{
    use HasUuids;

    protected $fillable = [
        'property_id',
        'tenant_profile_id',
        'status',
        'proposed_rent',
        'message'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tenantProfile()
    {
        return $this->belongsTo(TenantProfile::class);
    }
}
""")

write_file(os.path.join(models_dir, "Lease.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Lease extends Model
{
    use HasUuids;

    protected $fillable = [
        'property_id',
        'tenant_profile_id',
        'start_date',
        'end_date',
        'rent_amount',
        'deposit_amount',
        'status'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tenantProfile()
    {
        return $this->belongsTo(TenantProfile::class);
    }

    public function schedules()
    {
        return $this->hasMany(RentSchedule::class);
    }
}
""")

write_file(os.path.join(models_dir, "RentSchedule.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RentSchedule extends Model
{
    use HasUuids;

    protected $fillable = [
        'lease_id',
        'due_date',
        'amount',
        'status',
        'payment_id'
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }
}
""")

print("Rental files updated successfully.")
