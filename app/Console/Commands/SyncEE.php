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
                    dump($exception->getMessage());
                    $connector->akeneo_sync_status = Connector::FAILED;
                    $connector->akeneo_sync_last_message = 'Please check akeneo credentials';

                    $connector->activation = false;
                    $connector->lock_status = false;
                    $connector->save();
                }

                if ($client) {
                    try {
                        $assetCode = Str::replace('-', '_', $asset->uuid);
                        $client->getAssetManagerApi()->upsert($asset->asset_family, $assetCode, [
                            'code' => $assetCode,
                            'values' => [
                                $asset->asset_attribute => [
                                    ['locale' => $asset->locale, 'channel' => $asset->scope, 'data' => $asset->url_cdn],
                                ]
                            ]
                        ]);

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
}
