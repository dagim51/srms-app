import os
import glob

def replace_in_file(pattern, replacement, path):
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    content = content.replace(pattern, replacement)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

# Migrations
migrations_dir = 'c:\\Users\\dell\\Desktop\\SRMS\\srms-app\\database\\migrations\\'

proj_mig = glob.glob(migrations_dir + '*create_development_projects_table.php')[0]
bldg_mig = glob.glob(migrations_dir + '*create_development_buildings_table.php')[0]
plan_mig = glob.glob(migrations_dir + '*create_payment_plans_table.php')[0]
inst_mig = glob.glob(migrations_dir + '*create_payment_plan_installments_table.php')[0]
unit_mig = glob.glob(migrations_dir + '*create_development_units_table.php')[0]

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('planning'); // planning, construction, completed
            $table->date('handover_date')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->timestamps();
            $table->softDeletes();""", proj_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('development_project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('total_floors')->default(1);
            $table->string('status')->default('planning');
            $table->integer('progress_percentage')->default(0);
            $table->timestamps();""", bldg_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('development_building_id')->constrained()->cascadeOnDelete();
            $table->string('floor_name')->nullable();
            $table->string('unit_number');
            $table->string('status')->default('available'); // available, reserved, sold
            $table->decimal('price', 15, 2)->nullable();
            
            // Optional tie to the generic Property core table if needed
            $table->foreignUuid('property_id')->nullable()->constrained()->nullOnDelete();
            
            $table->timestamps();""", unit_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('development_project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();""", plan_mig)

replace_in_file("            $table->id();\n            $table->timestamps();",
"""            $table->uuid('id')->primary();
            $table->foreignUuid('payment_plan_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Downpayment", "Handover"
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->integer('due_days_after_reservation')->nullable();
            $table->timestamps();""", inst_mig)

print("Migrations updated successfully.")
