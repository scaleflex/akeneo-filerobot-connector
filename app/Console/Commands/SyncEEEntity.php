<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\AssetEntity;
use App\Models\AssetManager;
use App\Models\Connector;
use App\Models\Mapping;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncEEEntity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'akeneo:sync:ee:entity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Entity to Akeneo';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filerobotHelper = app(\App\Helper\FilerobotHelper::class);

        AssetEntity::where([
            'status' => 'not_sync'
        ])->chunk(100, function($assets) {
            $assets->each(function($asset) {
                $connector = Connector::find($asset->connector_uuid);
                $connector->lock_status = true;
                $connector->akeneo_sync_status = Connector::PROCESSING;
                $connector->akeneo_sync_last_message = 'Start sync asset of entities to akeneo';
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

                    $connector->lock_status = false;
                    $connector->save();
                }

                $fallbackSize = $connector->fallback_size;
                $sizes = \App\Models\Size::where('connector_uuid', $connector->uuid)->get();

                if ($client) {
                    try {
                        $scopes = json_decode($asset->scope);
                        $locales = json_decode($asset->locale);

                        $labelData = json_decode($asset->entity_label, true);
                        $labels = [];
                        foreach ($labelData as $locale => $label) {
                            $labels[] = [
                                'channel' => null,
                                'locale' => $locale,
                                'data' => $label
                            ];
                        }

                        if (!empty($scopes)) {
                            foreach ($scopes as $scope) {
                                if (!empty($locales)) {
                                    foreach ($locales as $locale) {
                                        $url = $this->formatUrl($sizes, $asset->url_cdn, null, null, $fallbackSize);
                                        $mediaFile = $this->readImageContentFromUrlWithParams($asset->filename, $url);

                                        $client->getReferenceEntityRecordApi()->upsert($asset->entity, $asset->entity_code, [
                                            'code' => $asset->entity_code,
                                            'values' => [
                                                $asset->entity_attribute => [
                                                    // Change URL
                                                    [
                                                        'locale' => $locale,
                                                        'channel' => $scope,
                                                        'data' => $mediaFile
                                                    ],
                                                ],
                                                'label' => $labels
                                            ]
                                        ]);
                                    }
                                } else {
                                    $url = $this->formatUrl($sizes, $asset->url_cdn, null, null, $fallbackSize);
                                    $mediaFile = $this->readImageContentFromUrlWithParams($asset->filename, $url);

                                    $client->getReferenceEntityRecordApi()->upsert($asset->entity, $asset->entity_code, [
                                        'code' => $asset->entity_code,
                                        'values' => [
                                            $asset->entity_attribute => [
                                                // Change URL
                                                [
                                                    'locale' => null,
                                                    'channel' => $scope,
                                                    'data' => $mediaFile
                                                ],
                                            ],
                                            'label' => $labels
                                        ]
                                    ]);
                                }
                            }
                        } else {
                            if (!empty($locales)) {
                                foreach ($locales as $locale) {
                                    $url = $this->formatUrl($sizes, $asset->url_cdn, null, null, $fallbackSize);
                                    $mediaFile = $this->readImageContentFromUrlWithParams($asset->filename, $url);

                                    $client->getReferenceEntityRecordApi()->upsert($asset->entity, $asset->entity_code, [
                                        'code' => $asset->entity_code,
                                        'values' => [
                                            $asset->entity_attribute => [
                                                // Change URL
                                                [
                                                    'locale' => $locale,
                                                    'channel' => null,
                                                    'data' => $mediaFile
                                                ],
                                            ],
                                            'label' => $labels
                                        ]
                                    ]);
                                }
                            } else {

                                $url = $this->formatUrl($sizes, $asset->url_cdn, null, null, $fallbackSize);
                                $mediaFile = $this->readImageContentFromUrlWithParams($asset->filename, $url);

                                try {
                                    $mediaCode = $client->getReferenceEntityMediaFileApi()->create($mediaFile);
                                } catch (\Exception $exception) {
                                    throw new $exception;
                                }

                                $client->getReferenceEntityRecordApi()->upsert($asset->entity, $asset->entity_code, [
                                    'code' => $asset->entity_code,
                                    'values' => [
                                        $asset->entity_attribute => [
                                            // Change URL
                                            [
                                                'locale' => null,
                                                'channel' => null,
                                                'data' => $mediaCode,
                                            ],
                                        ],
                                        'label' => $labels
                                    ]
                                ]);
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

        return $url . '&width=' . $width . '&height=' . $height . '&gravity=auto';
    }


    private function readImageContentFromUrlWithParams($filename, $url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        $imageData = curl_exec($ch);
        curl_close($ch);

        $filePath   = null;
        $writePath   = null;
        if (!empty($filename)) {
            $filePath = public_path("/images/$filename");
            $writePath = public_path("/images/$filename");
            file_put_contents($writePath, $imageData);
        }
        return $filePath;
    }
}
