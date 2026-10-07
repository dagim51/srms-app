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
mc_mig = glob.glob(os.path.join(mig_dir, "*_create_marketing_campaigns_table.php"))[0]
l_mig = glob.glob(os.path.join(mig_dir, "*_create_leads_table.php"))[0]

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('channel'); // email, social, web
            $table->decimal('budget', 15, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();""", mc_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('marketing_campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('status')->default('new'); // new, contacted, qualified, converted, lost
            $table->text('notes')->nullable();
            $table->timestamps();""", l_mig)


# Models
models_dir = os.path.join(base_dir, "app", "Models")

write_file(os.path.join(models_dir, "MarketingCampaign.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MarketingCampaign extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'channel',
        'budget',
        'start_date',
        'end_date',
        'status'
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
""")

write_file(os.path.join(models_dir, "Lead.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Lead extends Model
{
    use HasUuids;

    protected $fillable = [
        'marketing_campaign_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'notes'
    ];

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }
}
""")

print("Marketing files updated successfully.")
