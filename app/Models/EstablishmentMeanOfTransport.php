<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstablishmentMeanOfTransport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'establishment_id',
        'mean_of_transport_id',
    ];

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function meanOfTransport()
    {
        return $this->belongsTo(MeanOfTransport::class);
    }
}
