<?php

namespace App\Jobs;

use App\Models\Connector;
use App\Models\Mapping;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProcessSyncAttribute implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $connector;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $connectionUUID)
    {
        $this->connector = Connector::find($connectionUUID);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $connector = $this->connector;

        $currentStatus = $connector->activation;

        $connector->lock_status = true;

        $connector->setup_status = Connector::PROCESSING;
        $connector->setup_message = 'Setup in process';

        $connector->filerobot_sync_status = Connector::PROCESSING;
        $connector->filerobot_sync_last_message = 'Check Filerobot credentials';

        $connector->activation = false;

        $connector->save();


        try {
            $filerobotHelper = app(\App\Helper\FilerobotHelper::class);
            $filerobotHelper->getProductsFromFilerobot($connector->filerobot_token, $connector->filerobot_key);

            $connector->filerobot_sync_status = Connector::SUCCESSFUL;
            $connector->filerobot_sync_last_message = 'Filerobot credentials are valid';
            $connector->save();
        }catch (\Exception $exception) {
            $connector->filerobot_sync_status = Connector::FAILED;
            $connector->filerobot_sync_last_message = 'Check Filerobot credentials';
            $connector->save();
        }

        $connector->akeneo_sync_status = Connector::PROCESSING;
        $connector->akeneo_sync_last_message = 'Start get attribute families from akeneo';
        $connector->save();

        $clientBuilder = new \Akeneo\Pim\ApiClient\AkeneoPimClientBuilder($connector->akeneo_server_url);
        try {
            $client = $clientBuilder->buildAuthenticatedByPassword($connector->akeneo_client_id,
                $connector->akeneo_secret,
                $connector->akeneo_username,
                $connector->akeneo_password);

            //Get Scope and Scope locales
            $scopes = $client->getChannelApi()->all();

            $scopesArrays = [];
            foreach ($scopes as $scope) {
                $scopesArrays[] = [
                    'code'      => $scope['code'],
                    'labels'     => $scope['labels'],
                    'locales'   => $scope['locales'],
                ];
            }

            $connector->scopes = json_encode($scopesArrays);
            $connector->save();


            $families = $client->getFamilyApi()->all();
            foreach ($families as $family) {
                $existFamily = \App\Models\AkeneoFamily::where('connector_uuid', $this->connector->uuid)
                    ->where('code', $family['code'])->first();
                $availableAttributes = [];

                $attributes = $family['attributes'] ?? [];
                foreach ($attributes as $attribute) {
                    $akeneoAttribute = $client->getAttributeApi()->get($attribute);

                    if ($akeneoAttribute['type'] == 'pim_catalog_image' ||
                        $akeneoAttribute['type'] == 'pim_catalog_text') {
                        $availableAttributes[] = [
                            'code'          => $akeneoAttribute['code'],
                            'label'         => $akeneoAttribute['labels'],
                            'type'          => $akeneoAttribute['type'],
                            'scopable'      => $akeneoAttribute['scopable'],
                            'localizable'   => $akeneoAttribute['localizable']
                        ];
                    }
                }

                $familyData = [
                    'label' => serialize($family['labels']),
                    'code'  => $family['code'],
                    'attributes' => serialize($availableAttributes),
                    'attribute_as_label' => $family['attribute_as_label'],
                    'attribute_as_image' => $family['attribute_as_image'],
                    'attribute_requirements' => serialize($family['attribute_requirements']),
                ];

                if ($existFamily) {
                    $existFamily->update($familyData);
                } else {
                    $familyData['uuid']             = Str::uuid();
                    $familyData['connector_uuid']   = $this->connector->uuid;
                    \App\Models\AkeneoFamily::create($familyData);

                    if ($familyData['attribute_as_image']) {
                        Mapping::create(
                            [
                                'uuid'                      => Str::uuid(),
                                'filerobot_position'        => 0,
                                'akeneo_attribute'          => $familyData['attribute_as_image'],
                                'mapping_type'              => 'pim_catalog_image',
                                'akeneo_family_uuid'        => $familyData['uuid'],
                                'akeneo_family'             => $familyData['code'],
                                'connector_uuid'            => $connector->uuid,
                                'update_default_behavior'   => 'keep'
                            ]
                        );
                    }
                }
            }
            $connector->setup_status = Connector::SUCCESSFUL;
            $connector->setup_message = 'Get attribute families from akeneo successful';

            $connector->akeneo_sync_status = Connector::SUCCESSFUL;
            $connector->akeneo_sync_last_message = 'Sync attribute families finished';

            $connector->activation = $currentStatus;

            $connector->save();
        } catch (\Exception $exception) {
            $connector->setup_status = Connector::FAILED;
            $connector->setup_message = 'Get akeneo attribute families failed';

            $connector->akeneo_sync_status = Connector::FAILED;
            $connector->akeneo_sync_last_message = 'Please check akeneo credentials';

            $connector->save();
        }
        $connector->lock_status = false;
        $connector->save();
    }
}
