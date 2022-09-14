<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\AssetManager;
use App\Models\Connector;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PrepareSyncDataEE extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'akeneo:prepare:ee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Akeneo EE Assets to connector';

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
                $connector->filerobot_sync_last_message = 'Syncing products to connector';
                $connector->lock_status = true;
                $connector->save();

                try {
                    $assets = $filerobotHelper->getAssetsByCondition($connector->filerobot_token, $connector->filerobot_key, 'akeneo_enable:true');

                    foreach ($assets as $asset) {

                        $isExistInDB = AssetManager::where('filerobot_uuid', $asset->uuid)->where('connector_uuid', $connector->uuid)->first();

                        $assetFamily    = $asset->meta->akeneo_family;
                        $assetAttribute = $asset->meta->akeneo_attribute;
                        $assetScope     = $asset->meta->akeneo_scope;
                        $assetLocale    = $asset->meta->akeneo_locale;

                        $data = [
                            'url_cdn' => $asset->url->cdn,
                            'url_public' => $asset->url->public,
                            'asset_family' => $assetFamily,
                            'asset_attribute' => $assetAttribute,
                            'scope' => $assetScope,
                            'locale' => $assetLocale
                        ];

                        if (!$isExistInDB) {
                            $data['uuid'] = Str::uuid();
                            $data['filerobot_uuid'] = $asset->uuid;
                            $data['connector_uuid'] = $connector->uuid;
                            $data['message'] = '';
                            $data['status'] = 'not_sync';
                            AssetManager::create($data);
                        } else {
                            if ($isExistInDB->status !== 'synced') {
                                $isExistInDB->update($data);
                            }
                        }
                    }

                    $connector->filerobot_sync_status = Connector::SUCCESSFUL;
                    $connector->filerobot_sync_last_message = 'Sync assets successful from Filerobot';
                    $connector->lock_status = false;
                    $connector->save();
                } catch (\Exception $exception) {
                    $connector->activation = false;
                    $connector->lock_status = false;
                    $connector->filerobot_sync_status = Connector::FAILED;
                    $connector->filerobot_sync_last_message = 'Please check Filerobot API key and token';
                    $connector->save();
                }
            });

        });
    }
}
