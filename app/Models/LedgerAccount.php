<?php
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
