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
rt_mig = glob.glob(os.path.join(mig_dir, "*_create_report_templates_table.php"))[0]
gr_mig = glob.glob(os.path.join(mig_dir, "*_create_generated_reports_table.php"))[0]

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type'); // financial, occupancy, maintenance
            $table->json('configuration')->nullable();
            $table->timestamps();""", rt_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('report_template_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete(); // requester
            $table->string('status')->default('processing'); // processing, completed, failed
            $table->string('file_path')->nullable();
            $table->string('format')->default('csv'); // csv, pdf
            $table->timestamps();""", gr_mig)


# Models
models_dir = os.path.join(base_dir, "app", "Models")

write_file(os.path.join(models_dir, "ReportTemplate.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'type',
        'configuration'
    ];

    protected $casts = [
        'configuration' => 'array'
    ];
}
""")

write_file(os.path.join(models_dir, "GeneratedReport.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GeneratedReport extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_template_id',
        'user_id',
        'status',
        'file_path',
        'format'
    ];

    public function template()
    {
        return $this->belongsTo(ReportTemplate::class, 'report_template_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
""")

print("Reporting files updated successfully.")
