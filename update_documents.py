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
dt_mig = glob.glob(os.path.join(mig_dir, "*_create_document_templates_table.php"))[0]
c_mig = glob.glob(os.path.join(mig_dir, "*_create_contracts_table.php"))[0]
cs_mig = glob.glob(os.path.join(mig_dir, "*_create_contract_signatories_table.php"))[0]

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->string('name');
            $table->longText('body_content');
            $table->string('status')->default('active');
            $table->timestamps();""", dt_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('document_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->longText('body_content');
            $table->uuid('contractable_id')->nullable();
            $table->string('contractable_type')->nullable();
            $table->string('status')->default('draft'); // draft, sent, partial_signed, fully_signed, expired
            $table->timestamps();""", c_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('Signer');
            $table->timestamp('signed_at')->nullable();
            $table->string('signature_ip')->nullable();
            $table->timestamps();""", cs_mig)

# Models
models_dir = os.path.join(base_dir, "app", "Models")

write_file(os.path.join(models_dir, "DocumentTemplate.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DocumentTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'body_content',
        'status'
    ];
}
""")

write_file(os.path.join(models_dir, "Contract.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Contract extends Model
{
    use HasUuids;

    protected $fillable = [
        'document_template_id',
        'title',
        'body_content',
        'contractable_id',
        'contractable_type',
        'status'
    ];

    public function contractable()
    {
        return $this->morphTo();
    }

    public function signatories()
    {
        return $this->hasMany(ContractSignatory::class);
    }
}
""")

write_file(os.path.join(models_dir, "ContractSignatory.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ContractSignatory extends Model
{
    use HasUuids;

    protected $fillable = [
        'contract_id',
        'user_id',
        'role',
        'signed_at',
        'signature_ip'
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
""")

print("Document files updated successfully.")
