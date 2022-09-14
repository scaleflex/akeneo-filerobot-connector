<?php

namespace App\Helper;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FilerobotHelper
{

    public function getAssetsByCondition($token, $apiKey, $condition)
    {
        $filerobotUrl = "https://api.filerobot.com/{$token}/v4/files?recursive=1&q={$condition}" ;

        $response = Http::withHeaders([
            'X-Filerobot-Key' => $apiKey
        ])->get($filerobotUrl);
        if ($response->successful()) {
            return new Collection($response->object()->files);
        }
        throw new \Exception('Filerobot API error: ' . $response->body());
    }

    public function getAssetsByTag($token, $apiKey, $tag)
    {
        $filerobotUrl = "https://api.filerobot.com/{$token}/v4/files?recursive=1&q={$tag}" ;

        $response = Http::withHeaders([
            'X-Filerobot-Key' => $apiKey
        ])->get($filerobotUrl);
        if ($response->successful()) {
            return new Collection($response->object()->files);
        }
        throw new \Exception('Filerobot API error: ' . $response->body());
    }

    public function getProductsFromFilerobot($token, $apiKey)
    {
        $filerobotUrl = "https://api.filerobot.com/{$token}/v4/products" ;

        $response = Http::withHeaders([
            'X-Filerobot-Key' => $apiKey
        ])->get($filerobotUrl);
        if ($response->successful()) {
            return new Collection($response->object()->products);
        }
        throw new \Exception('Filerobot API error: ' . $response->body());
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
        throw new \Exception('Filerobot API error: ' . $response->body());
    }

    public function getAssetInformation($token, $apikey, $assetUUID)
    {
        $filerobotAssetsUrl = "https://api.filerobot.com/{$token}/v4/files/{$assetUUID}";

        $response = Http::withHeaders([
            'X-Filerobot-Key' => $apikey
        ])->get($filerobotAssetsUrl);

        if ($response->successful()) {
            return $response->object();
        }
        return null;
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

            return $cleanTags;
        }
        return null;
    }

    public function getAssetVariants($token, $apikey, $assetUUID)
    {
        $filerobotAssetsUrl = "https://api.filerobot.com/{$token}/v4/files/{$assetUUID}/variants";

        $response = Http::withHeaders([
            'X-Filerobot-Key' => $apikey
        ])->get($filerobotAssetsUrl);

        if ($response->successful()) {
            return $response->object()->variants;
        }
        return null;
    }
}
