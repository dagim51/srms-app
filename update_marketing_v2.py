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
alter_mig = glob.glob(os.path.join(mig_dir, "*_add_marketing_campaign_id_to_leads_table.php"))[0]

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('channel'); // email, social, web
            $table->decimal('budget', 15, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();""", mc_mig)

replace_in_file("            //\n        });",
"""            $table->foreignUuid('marketing_campaign_id')->nullable()->constrained()->nullOnDelete();
        });""", alter_mig)

replace_in_file("public function down(): void\n    {\n        Schema::table('leads', function (Blueprint $table) {\n            //\n        });",
"""public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['marketing_campaign_id']);
            $table->dropColumn('marketing_campaign_id');
        });""", alter_mig)


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

# Edit existing Lead model to add the relation and fillable field
lead_model_path = os.path.join(models_dir, "Lead.php")
with open(lead_model_path, 'r', encoding='utf-8') as f:
    lead_content = f.read()

if 'marketing_campaign_id' not in lead_content:
    lead_content = lead_content.replace("'status',", "'status',\n        'marketing_campaign_id',")
    lead_content = lead_content.replace("}", "    public function campaign()\n    {\n        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');\n    }\n}")
    with open(lead_model_path, 'w', encoding='utf-8') as f:
        f.write(lead_content)

print("Marketing files updated successfully.")
