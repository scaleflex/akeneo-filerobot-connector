<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkeneoFamily extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'code',
        'label',
        'connector_uuid',
        'attributes',
        'attribute_as_label',
        'attribute_as_image',
        'attribute_requirements'
    ];

    protected $primaryKey = 'uuid';
    public $incrementing = false;
}
