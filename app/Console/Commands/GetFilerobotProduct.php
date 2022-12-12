<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Connector;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GetFilerobotProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filerobot:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync filerobot products to connector';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filerobotHelper = app(\App\Helper\FilerobotHelper::class);
        Connector::where('activation', true)->chunk(100, function($connectors) use ($filerobotHelper) {
            $connectors->each(function($connector) use ($filerobotHelper) {
                $connector->filerobot_sync_status = Connector::PROCESSING;
                $connector->filerobot_sync_last_message = 'Syncing products to connector';
                $connector->lock_status = true;
                $connector->save();

                try {
                    $products = $filerobotHelper->getProductsFromFilerobot($connector->filerobot_token, $connector->filerobot_key);

                    $products->each(function($product, $productKey) use ($connector, $filerobotHelper) {
                        if (!Product::where([
                            'connector_uuid'        => $connector->uuid,
                            'filerobot_reference'   => $product->ref
                            ])->first()) {
                            Product::create([
                                'uuid'                  => Str::uuid(),
                                'connector_uuid'        => $connector->uuid,
                                'filerobot_reference'   => $product->ref,
                            ]);
                        }
                    });
                    $connector->filerobot_sync_status = Connector::SUCCESSFUL;
                    $connector->filerobot_sync_last_message = 'Sync products successful from Filerobot';
                    $connector->lock_status = false;
                    $connector->products_count = 0;
                    $connector->total_product = $products->count();
                    $connector->save();
                } catch (\Exception $exception) {
                    $connector->lock_status = false;
                    $connector->filerobot_sync_status = Connector::FAILED;
                    $connector->filerobot_sync_last_message = 'Please check Filerobot API key and token';
                    $connector->save();
                }
            });
        });
    }
}
