<?php

namespace App\Http\Controllers;

use App\Models\Connector;
use App\Models\Log;
use App\Models\Mapping;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncController extends Controller
{

    public function getSyncFormatData()
    {
        $connector = Connector::all()->first();

        $clientBuilder = new \Akeneo\Pim\ApiClient\AkeneoPimClientBuilder($connector->akeneo_server_url);
        $client = $clientBuilder->buildAuthenticatedByPassword($connector->akeneo_client_id, $connector->akeneo_secret, $connector->akeneo_username, $connector->akeneo_password);

        $items = $client->getLocaleApi()->listLocales();
        dump($items);
    }

    public function index()
    {
        $connector = Connector::all()->first();
        $mappings  = Mapping::where('connector_uuid', $connector->uuid)->get();
        $clientBuilder = new \Akeneo\Pim\ApiClient\AkeneoPimClientBuilder($connector->akeneo_server_url);
        $client = $clientBuilder->buildAuthenticatedByPassword($connector->akeneo_client_id, $connector->akeneo_secret, $connector->akeneo_username, $connector->akeneo_password);

//        $assets = $this->getAssetsVariants(
//            $connector->filerobot_token,
//            $connector->filerobot_key,
//            '032af501-b876-5112-92af-9578cfb50001'
//        );
//
//        $assets = $this->getAssetTags(
//            $connector->filerobot_token,
//            $connector->filerobot_key,
//            '032af501-b876-5112-92af-9578cfb50001'
//        );

//        $fileContent = file_get_contents("https://fyjnhqim.filerobot.com/akeneo-growth/emil123/photo-aaaa.jpeg?vh=23f6fb&vh=23f6fb&tl_px=410,380&br_px=655,606&ci_url_encoded=1", "r");
//        file_put_contents(public_path('abc.jpeg'), $fileContent);

//        dump($assets);
    }

    //== Preparation

    public function getProductsFromFilerobot($token, $apiKey)
    {
        $filerobotUrl = "https://api.filerobot.com/{$token}/v4/products" ;

        $response = Http::withHeaders([
            'X-Filerobot-Key' => $apiKey
        ])->get($filerobotUrl);

        if ($response->successful()) {
            return new Collection($response->object()->products);
        }
        return [];
    }

    public function getAssetsFromProduct($token, $apikey, $productCode)
    {
        $filerobotAssetsUrl = "https://api.filerobot.com/{$token}/v4/product/{$productCode}/images";

        $response = Http::withHeaders([
            'X-Filerobot-Key' => $apikey
        ])->get($filerobotAssetsUrl);

        if ($response->successful()) {
            return new Collection($response->object()->images);
        }
        return [];
    }

    public function getAssetTags($token, $apikey, $assetUUID)
    {
        $filerobotAssetsUrl = "https://api.filerobot.com/{$token}/v4/files/{$assetUUID}";

        $response = Http::withHeaders([
            'X-Filerobot-Key' => $apikey
        ])->get($filerobotAssetsUrl);

        if ($response->successful()) {
            $cleanTags = [];
            $tags = $response->object()->file->tags;

            foreach ($tags as $tag) {
                foreach ($tag as $item) {
                    $cleanTags[] = Str::replace('~ ', '', $item->label);
                }
            }

            $cleanTags = array_unique($cleanTags);

            return new Collection($cleanTags);
        }
        return new Collection([]);
    }

    public function getAssetsVariants($token, $apikey, $assetUUID)
    {
        $filerobotAssetsUrl = "https://api.filerobot.com/{$token}/v4/files/{$assetUUID}/variants";

        $response = Http::withHeaders([
            'X-Filerobot-Key' => $apikey
        ])->get($filerobotAssetsUrl);

        if ($response->successful()) {
            return new Collection($response->object()->variants);
        }
        return new Collection([]);
    }
}
