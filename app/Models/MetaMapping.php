<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaMapping extends Model
{
    use HasFactory;

    protected $table = 'meta_mapping';

    protected $fillable = [
        'uuid',
        'connector_uuid',
        'metadata',
        'akeneo_family',
        'akeneo_attribute',
        'is_locale',
        'scope'
    ];

    protected $primaryKey = 'uuid';
    public $incrementing = false;
}
