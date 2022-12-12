<?php

namespace App\Console\Commands;

use App\Models\AssetEntity;
use App\Models\Connector;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PrepareFilerobotEntity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'akeneo:filerobot:entity-prepare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get asset for entity from filerobot';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filerobotHelper = app(\App\Helper\FilerobotHelper::class);

        Connector::where('activation', true)->where('akeneo_version', 'ee')->chunk(100, function($connectors) use ($filerobotHelper) {
            $connectors->each(function ($connector) use ($filerobotHelper) {
                $connector->filerobot_sync_status = Connector::PROCESSING;
                $connector->filerobot_sync_last_message = 'Syncing entities to connector';
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

                    $connector->lock_status = false;

                    $connector->save();
                }



                $locales = [];
                $scopes = json_decode($connector->scopes);
                foreach ($scopes as $scope) {
                    foreach ($scope->locales as $locale){
                        if (!array_key_exists($locale, $locales)) {
                            $locales[] = $locale;
                        }
                    }
                }
                $locales = array_unique($locales);

                $metaList = $filerobotHelper->getMetaList($connector->filerobot_token);

                if ($client) {
                    try {
                        $assets = $filerobotHelper->getAssetsByCondition($connector->filerobot_token, $connector->filerobot_key, 'akeneo_entity_enable:true');

                        foreach ($assets as $asset) {
                            $akeneoRefEnAttr = null;

                            $isExistInDB = AssetEntity::where('filerobot_uuid', $asset->uuid)->where('connector_uuid', $connector->uuid)->first();

                            if (property_exists($asset->meta, 'akeneo_entity')){
                                $assetEntity    = $asset->meta->akeneo_entity;
                            } else {
                                throw new \Exception('Filerobot Meta akeneo_entity does not exist');
                            }

                            if (property_exists($asset->meta, 'akeneo_entity_code')){
                                $assetEntityItem = $asset->meta->akeneo_entity_code;
                            } else {
                                throw new \Exception('Filerobot Meta akeneo_entity_code does not exist');
                            }

                            if (property_exists($asset->meta, 'akeneo_entity_attribute')){
                                $assetEntityAttribute = $asset->meta->akeneo_entity_attribute;

                                try {
//                                    $akeneoRefEnAttr = $client->getReferenceEntityAttributeApi()->get($assetEntity, $assetEntityAttribute);
                                    $akeneoRefEnAttr = $client->getReferenceEntityAttributeApi()->get('aaaa', '1233a');
                                } catch (\Exception $exception) {
                                    throw new \Exception($exception->getMessage());
                                }
                            } else {
                                throw new \Exception('Filerobot Meta akeneo_entity_attribute does not exist');
                            }

                            $assetLabels = null;
                            if (property_exists($asset->meta, 'akeneo_entity_label')){
                                $rawLabelObject    = $asset->meta->akeneo_entity_label;


                                foreach ($locales as $locale) {
                                    if (property_exists($rawLabelObject, $locale)) {
                                        $assetLabels[$locale] = $rawLabelObject->$locale;
                                    }
                                }
                            } else {
                                throw new \Exception('Filerobot Meta akeneo_entity_attribute does not exist');
                            }

                            $assetScope = null;
                            if ($akeneoRefEnAttr['value_per_channel']) {
                                if (property_exists($asset->meta, 'akeneo_entity_attribute_scope')){
                                    $rawScopes     = $asset->meta->akeneo_entity_attribute_scope;


                                    if (is_array($rawScopes)) {
                                        foreach ($rawScopes as $item) {
                                            $metaList->each(function($meta) use ($item, &$assetScope) {
                                                if ($meta->key === 'akeneo_entity_attribute_scope') {
                                                    foreach ($meta->possible_values as $value) {
                                                        if ($item === $value->internal_unique_value) {
                                                            $assetScope[] = $value->api_value;
                                                        }
                                                    }
                                                }
                                            });
                                        }
                                    }

                                } else {
                                    throw new \Exception('Filerobot Meta akeneo_entity_attribute_scope does not exist');
                                }
                            }

                            $assetLocale = null;
                            if ($akeneoRefEnAttr['value_per_locale']) {
                                if (property_exists($asset->meta, 'akeneo_entity_attribute_locale')){
                                    $rawLocale    = $asset->meta->akeneo_entity_attribute_locale;


                                    if(is_array($rawLocale)) {
                                        foreach ($rawLocale as $item) {
                                            $metaList->each(function($meta) use ($item, &$assetLocale) {
                                                if ($meta->key === 'akeneo_entity_attribute_locale') {
                                                    foreach ($meta->possible_values as $value) {
                                                        if ($item === $value->internal_unique_value) {
                                                            $assetLocale[] = $value->api_value;
                                                        }
                                                    }
                                                }
                                            });
                                        }
                                    }
                                } else {
                                    throw new \Exception('Filerobot Meta akeneo_entity_attribute_locale does not exist');
                                }
                            }


                            $name = $asset->name;
                            $extension = $this->get_extension($asset->type);
                            $filename = $name.$extension;

                            $data = [
                                'url_cdn' => $asset->url->cdn,
                                'url_public' => $asset->url->public,
                                'entity' => $assetEntity,
                                'entity_code' => $assetEntityItem,
                                'entity_attribute' => $assetEntityAttribute,
                                'entity_label' => json_encode($assetLabels),
                                'scope' => json_encode($assetScope),
                                'locale' => json_encode($assetLocale),
                                'filename' => $filename
                            ];

                            if (!$isExistInDB) {
                                $data['uuid'] = Str::uuid()->toString();
                                $data['filerobot_uuid'] = $asset->uuid;
                                $data['connector_uuid'] = $connector->uuid;
                                $data['message'] = '';
                                AssetEntity::create($data);
                            } else {
                                if ($isExistInDB->status !== 'synced') {
                                    $data['status'] = 'not_sync';
                                    $isExistInDB->update($data);
                                }
                            }
                        }

                        $connector->filerobot_sync_status = Connector::SUCCESSFUL;
                        $connector->filerobot_sync_last_message = 'Sync entity successful from Filerobot';
                        $connector->lock_status = false;
                        $connector->save();
                    } catch (\Exception $exception) {
                        $connector->lock_status = false;
                        $connector->filerobot_sync_status = Connector::FAILED;
                        $connector->filerobot_sync_last_message = $exception->getMessage();
                        $connector->save();
                    }
                }
            });

        });

    }


    function get_extension($imagetype)
    {
        if(empty($imagetype)) return false;

        switch($imagetype)
        {
            case 'image/bmp': return '.bmp';
            case 'image/cis-cod': return '.cod';
            case 'image/gif': return '.gif';
            case 'image/ief': return '.ief';
            case 'image/jpeg': return '.jpg';
            case 'image/pipeg': return '.jfif';
            case 'image/tiff': return '.tif';
            case 'image/x-cmu-raster': return '.ras';
            case 'image/x-cmx': return '.cmx';
            case 'image/x-icon': return '.ico';
            case 'image/x-portable-anymap': return '.pnm';
            case 'image/x-portable-bitmap': return '.pbm';
            case 'image/x-portable-graymap': return '.pgm';
            case 'image/x-portable-pixmap': return '.ppm';
            case 'image/x-rgb': return '.rgb';
            case 'image/x-xbitmap': return '.xbm';
            case 'image/x-xpixmap': return '.xpm';
            case 'image/x-xwindowdump': return '.xwd';
            case 'image/png': return '.png';
            case 'image/x-jps': return '.jps';
            case 'image/x-freehand': return '.fh';
            default: return false;
        }
    }
}
