<?php

namespace App\Http\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;

class Mapping extends Component
{
    private $client;
    public $connectorUuid;
    public $mappings;
    public $family;
    public $availableAttributes = [];
    public $behaviors = [
        ['code' => 'override', 'label' => 'Override'],
        ['code' => 'ask', 'label' => 'Ask for action'],
        ['code' => 'keep', 'label' => 'Keep old version'],
    ];
    public $showSaveButton = false;

    protected $rules = [
        'mappings.*.filerobot_position' => 'required',
        'mappings.*.akeneo_attribute' => 'required',
        'mappings.*.update_default_behavior' => 'required',
    ];

    public function mount($familyUUID)
    {
        $this->family = \App\Models\AkeneoFamily::find($familyUUID);
        $this->connectorUuid = $this->family->connector_uuid;
        $connector = \App\Models\Connector::find($this->family->connector_uuid);
        $clientBuilder = new \Akeneo\Pim\ApiClient\AkeneoPimClientBuilder($connector->akeneo_server_url);
        $this->client = $clientBuilder->buildAuthenticatedByPassword($connector->akeneo_client_id, $connector->akeneo_secret, $connector->akeneo_username, $connector->akeneo_password);

        $this->reloadMapping();
        $this->getAvailableAttributes();
    }

    public function addMappingItem()
    {
        $mapping = new \App\Models\Mapping([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'akeneo_family_uuid' => $this->family->uuid,
            'connector_uuid' => $this->connectorUuid,
            'akeneo_family' => $this->family->code,
        ]);
        $this->mappings->push($mapping);
        $this->showSaveButton = true;
    }

    public function getAvailableAttributes()
    {
        $this->availableAttributes = unserialize($this->family->attributes);
    }

    public function updateMapping()
    {
        $this->validate();

        foreach ($this->mappings as $mapping) {

            $mapping['channel'] = \App\Models\Connector::TYPE_GLOBAL;

            $activeAttribute = collect($this->availableAttributes)->first(function ($item) use ($mapping) {
                return $item['code'] == $mapping['akeneo_attribute'];
            });
            $mapping['mapping_type'] = $activeAttribute['type'];

            $isOldMapping = \App\Models\Mapping::find($mapping['uuid']);
            if ($isOldMapping) {
                $isOldMapping->update($mapping);
            } else {
                (new \App\Models\Mapping($mapping))->save();
            }
        }
        $this->reloadMapping();
        $this->showSaveButton = false;
    }

    public function deleteItem(string $itemUUID)
    {
        $isMappingInDB = \App\Models\Mapping::find($itemUUID);

        if ($isMappingInDB) {
            $isMappingInDB->delete();
        }

        $tempMappings = new Collection([]);
        $this->mappings->each(function($mapping) use ($tempMappings, $itemUUID) {
            if ($mapping['uuid'] !== $itemUUID) {
                $tempMappings->push($mapping);
            }
        });
        $this->mappings = $tempMappings;
    }

    public function render()
    {
        return view('livewire.mapping');
    }

    public function updated($field)
    {
        $this->showSaveButton = true;
    }

    private function reloadMapping()
    {
        $mappings = \App\Models\Mapping::where([
            'type' => \App\Models\Connector::TYPE_GLOBAL,
            'akeneo_family_uuid' => $this->family->uuid
        ])->orderBy('filerobot_position', 'asc')->get();

        $this->mappings = new Collection();

        $mappings->each(function ($mapping, $index) {
            $this->mappings->push($mapping);
        });
    }
}
