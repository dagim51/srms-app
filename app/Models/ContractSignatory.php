<?php
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
