<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Simulation extends Model
{
    use HasFactory;

    protected $casts = [
        'simulationsOffer' => 'array',
        'simulationsCredit' => 'array',
    ];

    protected $fillable = ['client', 'simulationsCredit', 'simulationsOffer'];
}
