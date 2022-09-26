<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\AssetManager;
use App\Models\Connector;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncEE extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'akeneo:sync:ee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Assets to Akeneo';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filerobotHelper = app(\App\Helper\FilerobotHelper::class);

        AssetManager::where([
            'status' => 'not_sync'
        ])->chunk(100, function($assets) {
            $assets->each(function($asset) {
                $connector = Connector::find($asset->connector_uuid);
                $connector->lock_status = true;
                $connector->akeneo_sync_status = Connector::PROCESSING;
                $connector->akeneo_sync_last_message = 'Start sync assets to akeneo';
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

                $fallbackSize = $connector->fallback_size;
                $sizes = \App\Models\Size::where('connector_uuid', $connector->uuid)->get();

                if ($client) {
                    try {
                        $assetCode = Str::replace('-', '_', $asset->uuid);

                        $scopes = json_decode($asset->scope);
                        $locales = json_decode($asset->locale);

                        if (!empty($scopes)) {
                            foreach ($scopes as $scope) {
                                if (!empty($locales)) {
                                    foreach ($locales as $locale) {
                                        $url = $this->formatUrl($sizes, $asset->url_cdn, $scope, $locale, $fallbackSize);

                                        $client->getAssetManagerApi()->upsert($asset->asset_family, $assetCode, [
                                            'code' => $assetCode,
                                            'values' => [
                                                $asset->asset_attribute => [
                                                    // Change URL
                                                    ['locale' => $locale, 'channel' => $scope, 'data' => $url],
                                                ]
                                            ]
                                        ]);
                                    }
                                } else {
                                    $url = $this->formatUrl($sizes, $asset->url_cdn, $scope, null, $fallbackSize);
                                    $client->getAssetManagerApi()->upsert($asset->asset_family, $assetCode, [
                                        'code' => $assetCode,
                                        'values' => [
                                            $asset->asset_attribute => [
                                                // Change URL
                                                ['locale' => null, 'channel' => $scope, 'data' => $url],
                                            ]
                                        ]
                                    ]);
                                }
                            }
                        } else {
                            if (!empty($locales)) {
                                foreach ($locales as $locale) {
                                    $url = $this->formatUrl($sizes, $asset->url_cdn,null, $locale, $fallbackSize);
                                    $client->getAssetManagerApi()->upsert($asset->asset_family, $assetCode, [
                                        'code' => $assetCode,
                                        'values' => [
                                            $asset->asset_attribute => [
                                                // Change URL
                                                ['locale' => $locale, 'channel' => null, 'data' => $url],
                                            ]
                                        ]
                                    ]);
                                }
                            } else {
                                $url = $this->formatUrl($sizes, $asset->url_cdn, null, null, $fallbackSize);
                                $client->getAssetManagerApi()->upsert($asset->asset_family, $assetCode, [
                                    'code' => $assetCode,
                                    'values' => [
                                        $asset->asset_attribute => [
                                            // Change URL
                                            ['locale' => null, 'channel' => null, 'data' => $url],
                                        ]
                                    ]
                                ]);
                            }
                        }

                        $metaList = $this->getMetaFormat($connector, $asset);

                        foreach ($metaList as $meta) {
                            try {
                                $data =  [
                                    'code' => $assetCode,
                                    'values' => [
                                        $meta['attribute'] => [
                                           [
                                               "locale"    => $meta['locale'],
                                               "channel"   => $meta['channel'],
                                               "data"      => $meta['data']
                                           ]
                                        ]
                                    ]
                                ];
                                $client->getAssetManagerApi()->upsert($asset->asset_family, $assetCode, $data);
                            } catch (\Exception $exception) {
                                throw new \Exception('Meta data Err: ' . json_encode($data));
                            }
                        }


                        $asset->status = 'synced';
                        $asset->save();
                    } catch (\Exception $exception) {
                        $asset->status = 'failed';
                        $asset->message = $exception->getMessage();
                        $asset->save();
                    }
                }
            });
        });
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

    private function getMetaFormat($connector, $asset)
    {
        $scopes = json_decode($connector->scopes, true);
        $locales = [];

        // Get all mappings
        $metaMappings = \App\Models\MetaMapping::where([
            'connector_uuid' => $connector->uuid,
            'akeneo_family' => $asset->asset_family
        ])->get();


        // Get list of locale in Akeneo

        foreach ($scopes as $scope) {
            foreach ($scope['locales'] as $locale) {
                $locales[] = $locale;
            }
        }
        $locales = array_unique($locales);

        // Compare locale in meta
        $result = [];

        $metaObject = unserialize($asset->metadata);

        $metaMappings->each(function($item, $index) use ($metaObject, $locales, &$result) {

            if (property_exists($metaObject, $item->metadata)) {

                $propertyName = $item->metadata;
                $subObject = $metaObject->$propertyName;

                if ($item->scope !== 'null') {
                    if ($item->is_locale) {
                        foreach($locales as $locale) {
                            if (property_exists($subObject, $locale)) {
                                $resultItem = [
                                    'attribute' => $item->akeneo_attribute,
                                    'locale' => $locale,
                                    'channel' => $item->scope,
                                    'data' => $subObject->$locale
                                ];
                                $result[] = $resultItem;
                            }
                        }
                    } else {
                        $resultItem = [
                            'attribute' => $item->akeneo_attribute,
                            'locale' => null,
                            'channel' => $item->scope,
                            'data' => 'Check This'
                        ];
                        $result[] = $resultItem;
                    }
                } else {
                    if ($item->is_locale) {

                        foreach($locales as $locale) {
                            if (property_exists($subObject, $locale)) {
                                $resultItem = [
                                    'attribute' => $item->akeneo_attribute,
                                    'locale' => $locale,
                                    'channel' => null,
                                    'data' => $subObject->$locale
                                ];
                                $result[] = $resultItem;
                            }
                        }
                    } else {
                        $resultItem = [
                            'attribute' => $item->akeneo_attribute,
                            'locale' => null,
                            'channel' => null,
                            'data' => 'Check this'
                        ];
                        $result[] = $resultItem;
                    }
                }
            }
        });
        //return array of meta
        return $result;
    }
}
