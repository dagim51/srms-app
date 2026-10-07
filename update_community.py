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
c_mig = glob.glob(os.path.join(mig_dir, "*_create_communities_table.php"))[0]
sf_mig = glob.glob(os.path.join(mig_dir, "*_create_shared_facilities_table.php"))[0]
fb_mig = glob.glob(os.path.join(mig_dir, "*_create_facility_bookings_table.php"))[0]

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('hoa_rules')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();""", c_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('community_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('capacity')->default(10);
            $table->text('booking_rules')->nullable();
            $table->timestamps();""", sf_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('shared_facility_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('status')->default('pending'); // pending, approved, rejected, cancelled
            $table->timestamps();""", fb_mig)

# Models
models_dir = os.path.join(base_dir, "app", "Models")

write_file(os.path.join(models_dir, "Community.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Community extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'hoa_rules',
        'status'
    ];

    public function facilities()
    {
        return $this->hasMany(SharedFacility::class);
    }
}
""")

write_file(os.path.join(models_dir, "SharedFacility.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SharedFacility extends Model
{
    use HasUuids;

    protected $fillable = [
        'community_id',
        'name',
        'description',
        'capacity',
        'booking_rules'
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function bookings()
    {
        return $this->hasMany(FacilityBooking::class);
    }
}
""")

write_file(os.path.join(models_dir, "FacilityBooking.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FacilityBooking extends Model
{
    use HasUuids;

    protected $fillable = [
        'shared_facility_id',
        'user_id',
        'start_time',
        'end_time',
        'status'
    ];

    public function facility()
    {
        return $this->belongsTo(SharedFacility::class, 'shared_facility_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
""")

print("Community files updated successfully.")
