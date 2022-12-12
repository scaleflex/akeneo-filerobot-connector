<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetEntity extends Model
{
    use HasFactory;

    protected $primaryKey = 'uuid';
    public $incrementing = false;

    protected $table = 'asset_entity';

    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'connector_uuid',
        'filerobot_uuid',
        'url_cdn',
        'url_public',
        'filename',
        'entity',
        'entity_code',
        'entity_label',
        'entity_attribute',
        'scope',
        'locale',
        'status',
        'message'
    ];
}
