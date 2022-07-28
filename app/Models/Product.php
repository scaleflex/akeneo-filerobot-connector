<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    const TYPE_PRODUCT = 'product';
    const TYPE_PRODUCT_MODEL = 'product_model';

    protected $fillable = [
        'uuid',
        'connector_uuid',
        'filerobot_reference',
        'akeneo_attribute_family',
        'akeneo_product_exist',
        'product_type'
    ];

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    public function assets()
    {
        return $this->hasMany(Asset::class, 'uuid', 'product_uuid');
    }

    public function getFamily()
    {
        return AkeneoFamily::where([
            'connector_uuid' => $this->connector_uuid,
            'code'           => $this->akeneo_attribute_family
        ])->first();
    }
}
