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
mt_mig = glob.glob(os.path.join(mig_dir, "*_create_maintenance_tickets_table.php"))[0]
ct_mig = glob.glob(os.path.join(mig_dir, "*_create_cleaning_tasks_table.php"))[0]

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('open'); // open, assigned, in_progress, waiting, completed, closed
            $table->timestamps();
            $table->softDeletes();""", mt_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('accommodation_booking_id')->nullable()->constrained('accommodation_bookings')->nullOnDelete();
            $table->foreignUuid('assigned_cleaner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending'); // pending, assigned, in_progress, inspection, ready
            $table->text('notes')->nullable();
            $table->timestamps();""", ct_mig)

# Models
models_dir = os.path.join(base_dir, "app", "Models")

write_file(os.path.join(models_dir, "MaintenanceTicket.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceTicket extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'property_id',
        'reported_by',
        'assigned_to',
        'title',
        'description',
        'priority',
        'status'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
""")

write_file(os.path.join(models_dir, "CleaningTask.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CleaningTask extends Model
{
    use HasUuids;

    protected $fillable = [
        'property_id',
        'accommodation_booking_id',
        'assigned_cleaner_id',
        'inspector_id',
        'status',
        'notes'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function booking()
    {
        return $this->belongsTo(AccommodationBooking::class, 'accommodation_booking_id');
    }

    public function cleaner()
    {
        return $this->belongsTo(User::class, 'assigned_cleaner_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
""")

print("PM files updated successfully.")
