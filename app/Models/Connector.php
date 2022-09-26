<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Connector extends Model
{
    use HasFactory;

    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const SUCCESSFUL = 'successful';
    const FAILED = 'failed';

    //Locale and Scope Mapping Type
    const TYPE_NULL = 'null';
    const TYPE_TAG = 'tag';
    const TYPE_VARIANT = 'variant';
    const TYPE_GLOBAL = 'global';

    protected $fillable = [
        'uuid',
        'name',
        'image',
        'filerobot_token',
        'filerobot_key',
        'akeneo_version',
        'akeneo_server_url',
        'akeneo_client_id',
        'akeneo_secret',
        'akeneo_username',
        'akeneo_password',
        'user_id',

        'akeneo_sync_status',
        'akeneo_sync_last_message',

        'filerobot_sync_status',
        'filerobot_sync_last_message',

        'setup_status',
        'setup_message',

        'lock_status',

        'email',

        'products_count',
        'total_product',

        'scopes',
        'locales',

        // For enterprise version
        'families',

        'fallback_size'
    ];

    protected $primaryKey = 'uuid';
    public $incrementing = false;
}
