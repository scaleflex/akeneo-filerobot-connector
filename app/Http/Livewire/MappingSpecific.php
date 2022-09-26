<?php

namespace App\Http\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class MappingSpecific extends Component
{
    private $client;
    public $connectorUuid;
    public $mappings;
    public $family;

    public $behaviors = [
        ['code' => 'override', 'label' => 'Override'],
        ['code' => 'ask', 'label' => 'Ask for action'],
        ['code' => 'keep', 'label' => 'Keep old version'],
    ];

    public $types = [
        ['code' => 'variant', 'label' => 'Variant'],
        ['code' => 'tag', 'label' => 'Tag'],
        ['code' => 'global', 'label' => 'Global'],
    ];

    public $locales = [];

    public $scopes = [];

    public $attributes = [];

    public $allAttributes = [];

    public $showSaveButton = false;

    protected $rules = [
        'mappings.*.filerobot_position' => 'required',
        'mappings.*.name' => 'required',
        'mappings.*.akeneo_attribute' => 'required',
        'mappings.*.update_default_behavior' => 'required',
        'mappings.*.type' => 'required',
        'mappings.*.scope' => 'required',
        'mappings.*.locale' => 'required',
    ];

    public function mount($familyUUID)
    {
        $this->family = \App\Models\AkeneoFamily::find($familyUUID);
        $this->connectorUuid = $this->family->connector_uuid;
        $connector = \App\Models\Connector::find($this->family->connector_uuid);
        $clientBuilder = new \Akeneo\Pim\ApiClient\AkeneoPimClientBuilder($connector->akeneo_server_url);
        $this->client = $clientBuilder->buildAuthenticatedByPassword($connector->akeneo_client_id, $connector->akeneo_secret, $connector->akeneo_username, $connector->akeneo_password);

        $this->scopes = json_decode($connector->scopes, true);
        $this->allAttributes = unserialize($this->family->attributes);

        $this->mappings = new Collection([]);
        $this->reloadMapping();
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

    public function updateMapping()
    {
        $this->validate();

        foreach ($this->mappings as $mapping) {

            $activeAttribute = collect($this->allAttributes)->first(function ($item) use ($mapping) {
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
        $this->mappings = new Collection([]);
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

    public function updated($field, $value)
    {
        $index   = null;
        if (Str::contains($field, 'scope') ||
            Str::contains($field, 'locale')) {
            $explode = explode('.', $field);
            $index = $explode[1];
        }

        if (Str::contains($field, 'scope')) {
            if ($value === \App\Models\Connector::TYPE_NULL) {
                foreach ($this->scopes as $scope) {
                    foreach ($scope['locales'] as $locale) {
                        $this->locales[$index][] = $locale;
                    }
                }
                $this->locales[$index] = array_unique($this->locales[$index]);
            } else {
                foreach ($this->scopes as $scope) {
                    if ($scope['code'] === $value) {
                        $this->locales[$index] = $scope['locales'];
                    }
                }
            }
            unset($this->attributes[$index]);
        }


        if (Str::contains($field, 'locale') ||
            (Str::contains($field, 'scope') &&
                (array_key_exists('locale', $this->mappings[$index])
                    && $this->mappings[$index]['locale'] !== null))) {
            $explode = explode('.', $field);
            $index   = $explode[1];

            $scopable = false;
            $localizable = false;

            if ($this->mappings[$index]['scope'] !== \App\Models\Connector::TYPE_NULL) {
                $scopable = true;
            }

            if ($this->mappings[$index]['locale'] !== \App\Models\Connector::TYPE_NULL) {
                $localizable = true;
            }

            $attributes = $this->allAttributes;

            unset($this->attributes[$index]);
            foreach ($attributes as $attribute) {
                if ($attribute['scopable'] === $scopable && $attribute['localizable'] === $localizable) {
                    $this->attributes[$index][] = $attribute;
                }
            }
        }

        $this->showSaveButton = true;
    }

    private function reloadMapping()
    {
        $mappings = \App\Models\Mapping::where([
            'akeneo_family_uuid' => $this->family->uuid
        ])->whereNot('type', \App\Models\Connector::TYPE_GLOBAL)->orderBy('filerobot_position', 'asc')->get();

        $mappings->each(function ($mapping, $index) {

            foreach ($this->scopes as $scope) {
                if ($scope['code'] === $mapping->scope) {
                    $this->locales[$index] = $scope['locales'];
                }
            }
            $scopable = false;
            $localizable = false;


            if ($mapping->scope !== \App\Models\Connector::TYPE_NULL) {
                $scopable = true;
            }

            if ($mapping->locale !== \App\Models\Connector::TYPE_NULL) {
                $localizable = true;
            }

            $attributes = $this->allAttributes;

            unset($this->attributes[$index]);
            foreach ($attributes as $attribute) {
                if ($attribute['scopable'] === $scopable && $attribute['localizable'] === $localizable) {
                    $this->attributes[$index][] = $attribute;
                }
            }

            $this->mappings->push($mapping);
        });
    }
    public function render()
    {
        return view('livewire.mapping-specific');
    }
}
