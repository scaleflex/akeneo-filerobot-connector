<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetManager extends Model
{
    use HasFactory;

    protected $primaryKey = 'uuid';
    public $incrementing  = false;
    public $timestamps = false;

    protected $table = 'asset_manager';

    protected $fillable = [
        'uuid',
        'connector_uuid',
        'filerobot_uuid',
        'url_cdn',
        'url_public',
        'asset_family',
        'asset_attribute',
        'scope',
        'locale',
        'status',
        'message'
    ];
}
