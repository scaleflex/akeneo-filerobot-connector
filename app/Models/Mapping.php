<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapping extends Model
{
    use HasFactory;

    const SYNC_TYPE_LINK   = 'pim_catalog_text';
    const SYNC_TYPE_BINARY = 'pim_catalog_image';

    const BEHAVIOR_KEEP = 'keep';
    const BEHAVIOR_OVERRIDE = 'override';
    const BEHAVIOR_ASK = 'ask';

    protected $fillable = [
        'uuid',
        'filerobot_position',
        'akeneo_attribute',
        'mapping_type',
        'akeneo_family_uuid',
        'akeneo_family',
        'connector_uuid',
        'update_default_behavior',

        //tag and variants
        'name',
        'type',
        'scope',
        'locale'
    ];

    protected $primaryKey = 'uuid';
    public $incrementing = false;
}
