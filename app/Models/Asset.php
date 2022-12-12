<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    const STATUS_NOT_SYNC = 'not_sync';
    const STATUS_SYNCED   = 'synced';
    const STATUS_FAILED   = 'failed';

    const ACTION_OVERRIDE      = 'override';
    const ACTION_KEEP          = 'keep';
    const ACTION_PENDING       = 'pending';

    protected $primaryKey = 'uuid';

    protected $fillable = [
        'uuid',
        'product_uuid',
        'connector_uuid',
        'product_code',

        'filerobot_position',

        'filerobot_url_cdn',
        'filerobot_url_cdn_old',

        'filerobot_url_public',
        'filerobot_url_public_old',

        'filename',

        'version',
        'akeneo_latest_version',
        'akeneo_latest_attribute',
        'akeneo_sync_status',

        'have_mapping',
        'new_version_action',

        'last_sync_error',

        'asset_name',
        'asset_type', // tag, variant, global
    ];

    public $incrementing = false;
}
