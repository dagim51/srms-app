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
la_mig = glob.glob(os.path.join(mig_dir, "*_create_ledger_accounts_table.php"))[0]
le_mig = glob.glob(os.path.join(mig_dir, "*_create_ledger_entries_table.php"))[0]
lel_mig = glob.glob(os.path.join(mig_dir, "*_create_ledger_entry_lines_table.php"))[0]
inv_mig = glob.glob(os.path.join(mig_dir, "*_create_invoices_table.php"))[0]

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type'); // Asset, Liability, Equity, Revenue, Expense
            $table->timestamps();""", la_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->string('description');
            $table->uuid('reference_id')->nullable(); // Polymorphic if needed
            $table->date('entry_date');
            $table->timestamps();""", le_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('ledger_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('ledger_account_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->timestamps();""", lel_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete(); // bill to
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->string('status')->default('draft'); // draft, sent, paid, overdue
            $table->text('notes')->nullable();
            $table->timestamps();""", inv_mig)


# Models
models_dir = os.path.join(base_dir, "app", "Models")

write_file(os.path.join(models_dir, "LedgerAccount.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LedgerAccount extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'name',
        'type'
    ];
}
""")

write_file(os.path.join(models_dir, "LedgerEntry.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LedgerEntry extends Model
{
    use HasUuids;

    protected $fillable = [
        'description',
        'reference_id',
        'entry_date'
    ];

    public function lines()
    {
        return $this->hasMany(LedgerEntryLine::class);
    }
}
""")

write_file(os.path.join(models_dir, "LedgerEntryLine.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LedgerEntryLine extends Model
{
    use HasUuids;

    protected $fillable = [
        'ledger_entry_id',
        'ledger_account_id',
        'type',
        'amount'
    ];

    public function entry()
    {
        return $this->belongsTo(LedgerEntry::class, 'ledger_entry_id');
    }

    public function account()
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_account_id');
    }
}
""")

write_file(os.path.join(models_dir, "Invoice.php"), """<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Invoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'amount',
        'due_date',
        'status',
        'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
""")

print("Financial files updated successfully.")
