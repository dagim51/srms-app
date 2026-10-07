<?php
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
