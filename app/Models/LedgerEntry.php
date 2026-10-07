<?php
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
