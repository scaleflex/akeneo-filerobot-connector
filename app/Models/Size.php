<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'connector_uuid',
        'scope',
        'locale',
        'size'
    ];

    protected $primaryKey = 'uuid';
    public $incrementing = false;
}
