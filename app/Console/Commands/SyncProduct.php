<?php

namespace App\Console\Commands;

use App\Mail\SyncStatus;
use App\Models\Asset;
use App\Models\Connector;
use App\Models\Mapping;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SyncProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'akeneo:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Asset to Akeneo';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        Product::chunk(100, function ($products) {
            $products->each(function ($product) {
                $connector = Connector::find($product->connector_uuid);
                $connector->products_count = $connector->products_count + 1;
                $connector->lock_status = true;

                $connector->akeneo_sync_status = Connector::PROCESSING;
                $connector->akeneo_sync_last_message = 'Start sync assets to akeneo';

                $connector->save();

                $client = null;

                $mappings = Mapping::where([
                    'connector_uuid' => $connector->uuid,
                    'akeneo_family' => $product->akeneo_attribute_family
                ])->get();

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

                if ($client) {

                    $assets = Asset::where([
                        'product_uuid' => $product->uuid
                    ])->get();

                    if (!empty($assets)) {
                        $assets->each(function ($asset, $index) use ($mappings, $client, $product, $connector) {

                            //Get Assets Tags and Variants
                            $mapping = $mappings->first(function ($mp) use ($asset, $connector) {
                                return $mp->type == $asset->asset_type &&
                                       $mp->filerobot_position == $asset->filerobot_position &&
                                       $mp->name == $asset->asset_name;
                            });


                            if ($mapping) {
                                $asset->have_mapping = true;
                                $asset->save();

                                /**
                                 * 3 Cases
                                 * - Not Synced -> Sync
                                 * - New version -> Sync if update_default_behavior is Mapping::BEHAVIOR_OVERRIDE or new version action is Asset::ACTION_OVERRIDE
                                 * - New attribute -> Sync
                                 */
                                if ($asset->akeneo_sync_status === Asset::STATUS_NOT_SYNC ||
                                    $mapping->akeneo_sync_status === Asset::STATUS_FAILED ||
                                    (
                                        $asset->version !== $asset->akeneo_version_synced &&
                                        (
                                            $mapping->update_default_behavior === Mapping::BEHAVIOR_OVERRIDE ||
                                            $asset->new_version_action === Asset::ACTION_OVERRIDE
                                        )
                                    ) ||
                                    $mapping->akeneo_attribute !== $asset->akeneo_latest_attribute
                                ) {
                                    if ($mapping->mapping_type === Mapping::SYNC_TYPE_LINK) {
                                        $this->syncLink($client, $connector, $mapping, $product, $asset);
                                    } else if ($mapping->mapping_type === Mapping::SYNC_TYPE_BINARY) {
                                        $this->syncBinary($client, $connector, $mapping, $product, $asset);
                                    }
                                } elseif ($mapping->update_default_behavior === Mapping::BEHAVIOR_ASK) {
                                    $asset->have_mapping = true;
                                    $asset->new_version_action = Asset::ACTION_PENDING;
                                    $asset->save();
                                }
                            } else {
                                $asset->have_mapping = false;
                                $asset->save();
                            }
                        });
                    }


                    $connector->lock_status = false;
                    $connector->akeneo_sync_status = Connector::SUCCESSFUL;
                    $connector->akeneo_sync_last_message = 'Synced asset for product ' . $product->filerobot_reference;
                    $connector->save();

                    if ($connector->email && $connector->products_count === $connector->total_product) {
                        $connector->products_count = 0;
                        $connector->save();
                        Mail::to($connector->email)->queue(new SyncStatus($connector->uuid));
                    }
                }
            });
        });
    }

    private function readImageContentFromUrlWithParams($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    private function syncBinary($client, Connector $connector, Mapping $mapping, Product $product, Asset $asset)
    {
        $fileRead   = null;
        $urlToRead  = null;
        $imageUrl = parse_url($asset->filerobot_url_public);

        if (!empty($imageUrl)) {
            $pathInfo = pathinfo($imageUrl['path']);

            if ($pathInfo) {
                $imageData = $this->readImageContentFromUrlWithParams($asset->filerobot_url_public);
                $urlToRead = public_path("/images/{$pathInfo['basename']}");
                file_put_contents($urlToRead, $imageData);
                $fileRead = fopen($urlToRead, 'r');
            }
        }

        if ($fileRead) {
            try {
                if ($product->product_type === Product::TYPE_PRODUCT) {
                    $client->getProductMediaFileApi()->create($fileRead, [
                        'identifier'    => $asset->product_code,
                        'attribute'     => $mapping->akeneo_attribute,
                        'scope'         => $mapping->scope === \App\Models\Connector::TYPE_NULL ? null : $mapping->scope,
                        'locale'        => $mapping->locale === \App\Models\Connector::TYPE_NULL ? null : $mapping->locale,
                    ]);
                } elseif ($product->product_type === Product::TYPE_PRODUCT_MODEL) {
                    $client->getProductMediaFileApi()->create($fileRead, [
                        'identifier'    => $asset->product_code,
                        'attribute'     => $mapping->akeneo_attribute,
                        'scope'         => $mapping->scope === \App\Models\Connector::TYPE_NULL ? null : $mapping->scope,
                        'locale'        => $mapping->locale === \App\Models\Connector::TYPE_NULL ? null : $mapping->locale,
                        'type'          => 'product_model'
                    ]);
                }

                $asset->akeneo_sync_status = Asset::STATUS_SYNCED;
                $asset->akeneo_latest_attribute = $mapping->akeneo_attribute;
                $asset->akeneo_latest_version = $asset->version;
                $asset->new_version_action = null;
                $asset->save();
            } catch (\Exception $exception) {
                $connector->lock_status = false;
                $connector->save();
                $asset->new_version_action = null;
                $asset->last_sync_error = json_encode($exception->getResponseErrors());
                $asset->akeneo_sync_status = Asset::STATUS_FAILED;
                $asset->save();
            }
            unlink($urlToRead); // Delete temporary images
        } else {
            $asset->new_version_action = null;
            $asset->last_sync_error = json_encode([
                "property" => "value",
                "message"  => "Can not read file from Filerobot"
            ]);
            $asset->akeneo_sync_status = Asset::STATUS_FAILED;
            $asset->save();
        }
        $connector->lock_status = false;
        $connector->save();
    }

    private function syncLink($client, Connector $connector, Mapping $mapping, Product $product, Asset $asset)
    {
        try {
            $sizes = \App\Models\Size::where('connector_uuid', $connector->uuid)->get();
            $scope = $mapping->scope === \App\Models\Connector::TYPE_NULL ? null : $mapping->scope;
            $locale = $mapping->locale === \App\Models\Connector::TYPE_NULL ? null : $mapping->locale;
            $url = $this->formatUrl($sizes, $asset->filerobot_url_cdn,$scope, $locale, $connector->fallback_size);

            if ($product->product_type === Product::TYPE_PRODUCT) {
                $client->getProductApi()->upsert($asset->product_code, [
                    'values' => [
                        $mapping->akeneo_attribute => [
                            [
                                'data'      => $url,
                                'locale'    => $scope,
                                'scope'     => $locale
                            ]
                        ]
                    ]
                ]);
            } elseif ($product->product_type === Product::TYPE_PRODUCT_MODEL) {
                $scope = $mapping->scope === \App\Models\Connector::TYPE_NULL ? null : $mapping->scope;
                $locale = $mapping->locale === \App\Models\Connector::TYPE_NULL ? null : $mapping->locale;
                $url = $this->formatUrl($sizes, $asset->filerobot_url_cdn,$scope, $locale, $connector->fallback_size);

                $client->getProductModelApi()->upsert($asset->product_code, [
                    'values' => [
                        $mapping->akeneo_attribute => [
                            [
                                'data'      => $url,
                                'locale'    => $scope,
                                'scope'     => $locale
                            ]
                        ]
                    ]
                ]);
            }

            $asset->akeneo_sync_status      = Asset::STATUS_SYNCED;
            $asset->akeneo_latest_attribute = $mapping->akeneo_attribute;
            $asset->akeneo_latest_version   = $asset->version;
            $asset->new_version_action      = null;
            $asset->save();
        } catch (\Exception $exception) {
            $connector->lock_status = false;
            $connector->save();
            $asset->new_version_action = null;
            $asset->last_sync_error = json_encode($exception->getResponseErrors());
            $asset->akeneo_sync_status = Asset::STATUS_FAILED;
            $asset->save();
        }
    }

    private function formatUrl($sizes, $url, $scope, $locale, $defaultSize = '800x800')
    {
        $size = $sizes->first(function ($item, $index) use ($scope, $locale) {
            $scope = $scope === null ? 'null' : $scope;
            $locale = $locale === null ? 'null' : $locale;
            return strtolower($item->scope) === strtolower($scope) && strtolower($item->locale) === strtolower($locale);
        });

        $sizeImage = $defaultSize;

        if ($size) {
            $sizeImage = $size->size;
        }

        $width = 800;
        $height = 800;

        $explode = explode('x', $sizeImage);
        if (array_key_exists(0, $explode) && array_key_exists(1, $explode)) {
            $width = $explode[0];
            $height = $explode[1];
        }

        return $url . '&width=' . $width . '&height=' . $height;
    }
}
