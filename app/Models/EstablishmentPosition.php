<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstablishmentPosition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'establishment_id',
        'position_id',
    ];

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
