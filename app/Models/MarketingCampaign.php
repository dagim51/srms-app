<?php
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
