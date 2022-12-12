<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Connector;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PrepareSyncData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'akeneo:prepare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Akeneo product to connector';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filerobotHelper = app(\App\Helper\FilerobotHelper::class);

        Product::chunk(100, function ($products) use ($filerobotHelper) {

            $products->each(function($product, $index) use ($filerobotHelper) {
                $connector = Connector::find($product->connector_uuid);

                if ($connector->activation) {

                    $connector->akeneo_sync_status = Connector::PROCESSING;
                    $connector->akeneo_sync_last_message = 'Get product family from akeneo for ' . $product->filerobot_reference;

                    $connector->lock_status = true;
                    $connector->save();

                    $client = null;

                    try {
                        $clientBuilder = new \Akeneo\Pim\ApiClient\AkeneoPimClientBuilder($connector->akeneo_server_url);

                        $client = $clientBuilder->buildAuthenticatedByPassword($connector->akeneo_client_id,
                            $connector->akeneo_secret,
                            $connector->akeneo_username,
                            $connector->akeneo_password);
                    } catch (\Exception $exception) {
                        $connector->akeneo_sync_status = Connector::FAILED;
                        $connector->akeneo_sync_last_message = 'Please check akeneo credentials';

                        $connector->activation = false;
                        $connector->lock_status = false;

                        $connector->save();
                    }

                    $akeneoProduct = false;

                    try {
                        $akeneoProduct = $client->getProductApi()->get($product->filerobot_reference);
                        $product->product_type = Product::TYPE_PRODUCT;
                        $product->save();
                    } catch (\Exception $exception) {
                        try {
                            $akeneoProduct = $client->getProductModelApi()->get($product->filerobot_reference);
                            $product->product_type = Product::TYPE_PRODUCT_MODEL;
                            $product->save();
                        } catch (\Exception $exception) {
                            $product->akeneo_product_exist = false;
                            $product->save();

                            $connector->lock_status = false;
                            $connector->save();
                        }
                    }

                    if ($akeneoProduct) {

                        $product->akeneo_product_exist    = true;
                        $product->akeneo_attribute_family = $akeneoProduct['family'];
                        $product->save();

                        $connector->akeneo_sync_status = Connector::SUCCESSFUL;
                        $connector->akeneo_sync_last_message = 'Successful get akeneo product family for product ' . $product->filerobot_reference;

                        $connector->filerobot_sync_status = Connector::PROCESSING;
                        $connector->filerobot_sync_last_message = 'Sync assets to connector for product ' . $product->filerobot_reference;
                        $connector->save();

                        $assets = null;

                        try {
                            $assets    = $filerobotHelper->getAssetsFromProduct($connector->filerobot_token,
                                                                                $connector->filerobot_key,
                                                                                $product->filerobot_reference);
                        } catch (\Exception $exception) {
                            $connector->filerobot_sync_status = Connector::FAILED;
                            $connector->filerobot_sync_last_message = 'Failed to get  assets for product ' . $product->filerobot_reference;

                            $connector->lock_status = false;

                            $connector->save();
                        }

                        if ($assets) {
                            $localAssets = Asset::where([
                                'connector_uuid' => $connector->uuid,
                                'product_uuid'   => $product->uuid
                            ])->get();

                            $assetPositions = [];

                            $assets->map(function($asset, $assetKey) use ($product, $connector, $localAssets, &$assetPositions, $filerobotHelper) {
                                try {
                                    if(@getimagesize($asset->url->public)) {

                                        $newAsset = false;

                                        $connectorAsset  = $localAssets->first(function($localAsset) use ($asset) {
                                                return $localAsset->filerobot_position == $asset->product->position && $localAsset->asset_type === Connector::TYPE_GLOBAL;
                                        });

                                        if (!$connectorAsset) {
                                            $newAsset  = true;
                                            $connectorAsset = new Asset([
                                                'uuid'                  => Str::uuid(),
                                                'connector_uuid'        => $product->connector_uuid,
                                                'product_uuid'          => $product->uuid,
                                                'product_code'          => $product->filerobot_reference,
                                                'version'               => 0,
                                                'akeneo_sync_status'    => Asset::STATUS_NOT_SYNC,
                                                'filerobot_position'    => $asset->product->position,
                                                'asset_type'            => Connector::TYPE_GLOBAL
                                            ]);
                                        }

                                        $newVersion = false;

                                        if ($connectorAsset->filerobot_url_cdn !== $asset->url->cdn) {
                                            $connectorAsset->filerobot_url_cdn_old      = $connectorAsset->filerobot_url_cdn;
                                            $connectorAsset->filerobot_url_public_old   = $connectorAsset->filerobot_url_public;
                                            $connectorAsset->filerobot_url_cdn          = $asset->url->cdn;
                                            $connectorAsset->filerobot_url_public       = $asset->url->public;
                                            $newVersion = true;
                                        }

                                        if ($newVersion && $connectorAsset->created_at) {
                                            $connectorAsset->version = $connectorAsset->version + 1;
                                        }

                                        $connectorAsset->save();
                                        $assetPositions[] = $asset->product->position;

                                        //Tags and Variants
                                        $connectorScopes = json_decode($connector->scopes);

                                        $values = $filerobotHelper->getAssetTags(
                                            $connector->filerobot_token,
                                            $connector->filerobot_key,
                                            $asset->uuid
                                        );

                                        if (!empty($values)) {
                                            foreach ($values as $item) {
                                                $tagAsset  = $localAssets->first(function($localAsset) use ($asset, $item) {
                                                    return $localAsset->filerobot_position == $asset->product->position
                                                        && $localAsset->asset_type === Connector::TYPE_TAG &&
                                                        $localAsset->asset_name = $item;
                                                });
                                                // Find Asset Exits
                                                if (!$tagAsset) {
                                                    $tagAsset = new Asset([
                                                        'uuid'                  => Str::uuid(),
                                                        'connector_uuid'        => $product->connector_uuid,
                                                        'product_uuid'          => $product->uuid,
                                                        'product_code'          => $product->filerobot_reference,
                                                        'version'               => 0,
                                                        'akeneo_sync_status'    => Asset::STATUS_NOT_SYNC,
                                                        'filerobot_position'    => $asset->product->position,
                                                        'asset_name'            => $item,
                                                        'asset_type'            => Connector::TYPE_TAG,
                                                    ]);
                                                }
                                                $newVersion = false;

                                                if ($tagAsset->filerobot_url_cdn !== $asset->url->cdn) {
                                                    $tagAsset->filerobot_url_cdn_old      = $tagAsset->filerobot_url_cdn;
                                                    $tagAsset->filerobot_url_public_old   = $tagAsset->filerobot_url_public;
                                                    $tagAsset->filerobot_url_cdn          = $asset->url->cdn;
                                                    $tagAsset->filerobot_url_public       = $asset->url->public;
                                                    $newVersion = true;
                                                }

                                                if ($newVersion && $tagAsset->created_at) {
                                                    $tagAsset->version = $tagAsset->version + 1;
                                                }

                                                $tagAsset->save();
                                            }
                                        }


                                        $values = $filerobotHelper->getAssetVariants(
                                            $connector->filerobot_token,
                                            $connector->filerobot_key,
                                            $asset->uuid
                                        );

                                        if (!empty($values)) {
                                            foreach ($values as $key => $variant) {
                                                $variantAsset  = $localAssets->first(function($localAsset) use ($asset, $variant) {
                                                    return $localAsset->filerobot_position == $asset->product->position
                                                        && $localAsset->asset_type === Connector::TYPE_VARIANT &&
                                                        $localAsset->asset_name = $variant->name;
                                                });
                                                // Find Asset Exits
                                                if (!$variantAsset) {
                                                    $variantAsset = new Asset([
                                                        'uuid'                  => Str::uuid(),
                                                        'connector_uuid'        => $product->connector_uuid,
                                                        'product_uuid'          => $product->uuid,
                                                        'product_code'          => $product->filerobot_reference,
                                                        'version'               => 0,
                                                        'akeneo_sync_status'    => Asset::STATUS_NOT_SYNC,
                                                        'filerobot_position'    => $asset->product->position,
                                                        'asset_name'            => $variant->name,
                                                        'asset_type'            => Connector::TYPE_VARIANT,
                                                    ]);
                                                }

                                                if ($variantAsset->filerobot_url_cdn !== $variant->url) {
                                                    $variantAsset->filerobot_url_cdn_old      = $variantAsset->filerobot_url_cdn;
                                                    $variantAsset->filerobot_url_public_old   = $variantAsset->filerobot_url_public;
                                                    $variantAsset->filerobot_url_cdn          = $variant->url;
                                                    $variantAsset->filerobot_url_public       = $variant->url;
                                                    $newVersion = true;
                                                }

                                                if ($newVersion && $variantAsset->created_at) {
                                                    $variantAsset->version = $variantAsset->version + 1;
                                                }

                                                $variantAsset->save();
                                            }
                                        }
                                        // End tags and variants
                                    }
                                } catch (\Exception $exception) {
                                   //Do something else
                                }
                            });

                            // Delete assets that are not in the list anymore
                            $localAssets->each(function($localAsset) use ($assetPositions) {
                                if (!in_array($localAsset->filerobot_position, $assetPositions)) {
                                    $localAsset->delete();
                                }
                            });

                            $connector->filerobot_sync_status = Connector::SUCCESSFUL;
                            $connector->filerobot_sync_last_message = 'Successful sync assets for product ' . $product->filerobot_reference;
                            $connector->lock_status = false;
                            $connector->save();
                        }

                        $connector->filerobot_sync_status = Connector::SUCCESSFUL;
                        $connector->filerobot_sync_last_message = 'Synced assets for product ' . $product->filerobot_reference;
                        $connector->lock_status = false;
                        $connector->save();
                    }
                }
            });
        });
    }
}
