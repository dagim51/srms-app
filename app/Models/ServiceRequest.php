<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['service_listing_id', 'customer_id', 'property_id', 'status', 'requirements', 'requested_date', 'agreed_price'];
    protected $casts = ['requested_date' => 'datetime', 'agreed_price' => 'decimal:2'];

    public function listing() { return $this->belongsTo(ServiceListing::class, 'service_listing_id'); }
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function property() { return $this->belongsTo(Property::class); }
}
