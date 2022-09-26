<?php

namespace App\Http\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class MetaMapping extends Component
{
    public $connectorUUID;
    public $connector;

    public $scopes = [];
    public $families = [];

    public $attributes = [];

    public $enableScopes = [];

    public $mappings;

    public $showSaveButton = false;

    protected $rules = [
        'mappings.*.metadata' => 'required',
        'mappings.*.akeneo_family' => 'required',
        'mappings.*.akeneo_attribute' => 'required',
        'mappings.*.scope' => 'required',
    ];

    public function mount(string $connectorUUID)
    {
        $this->connectorUUID = $connectorUUID;
        $this->connector = \App\Models\Connector::find($connectorUUID);


        $this->scopes = json_decode($this->connector->scopes, true);
        $this->families = unserialize($this->connector->families);
        dump($this->families);
        $this->mappings = new Collection([]);

        $this->reloadMapping();
    }

    public function updateMapping()
    {
        $this->validate();

        foreach ($this->mappings as $mapping) {
            $isOldMapping = \App\Models\MetaMapping::find($mapping['uuid']);
            if ($isOldMapping) {
                $isOldMapping->update($mapping);
            } else {
                (new \App\Models\MetaMapping($mapping))->save();
            }
        }
        $this->mappings = new Collection([]);
        $this->showSaveButton = false;
        $this->reloadMapping();
    }

    public function deleteItem(string $itemUUID)
    {
        $isMappingInDB = \App\Models\MetaMapping::find($itemUUID);

        if ($isMappingInDB) {
            $isMappingInDB->delete();
        }

        $temp = new Collection([]);
        $this->mappings->each(function($config) use ($temp, $itemUUID) {
            if ($config['uuid'] !== $itemUUID) {
                $temp->push($config);
            }
        });
        $this->mappings = $temp;
    }

    public function updated($property, $value)
    {
        $this->showSaveButton = true;

        if (!empty($value)) {
            if (Str::contains($property, 'akeneo_family')) {
                $family = (new Collection($this->families))->first(function($item, $index) use ($value) {
                    return $item['code'] === $value;
                });

                if ($family) {
                    $explode = explode('.', $property);
                    $index = $explode[1];
                    $this->attributes[$index] = $family['attributes'];

                    $newMappings = $this->mappings->map(function($item, $position) use ($index) {
                        if ($position === (int)$index) {
                            $item['akeneo_attribute'] = '';
                            $item['scope'] = 'null';
                        }
                        return $item;
                    });
                    $this->mappings = $newMappings;
                }
            }


            if (Str::contains($property, 'akeneo_attribute')) {
                $explode = explode('.', $property);
                $index = $explode[1];
                $attribute = $this->attributes[$index][$value];
                $locale = false;
                $scope = false;
                if ($attribute['value_per_locale']){
                    $locale = true;
                }

                if ($attribute['value_per_channel']){
                    $scope = true;
                    if (!in_array((int)$index, $this->enableScopes)) {
                        $this->enableScopes[] = (int)$index;
                    }
                } else {
                    if (($key = array_search((int)$index, $this->enableScopes)) !== false) {
                        unset($this->enableScopes[$key]);
                    }
                }

                $newMappings = $this->mappings->map(function($item, $position) use ($index, $locale, $scope) {
                    if ($position === (int)$index) {
                        $item['is_locale'] = $locale;
                        $item['scope'] = $scope ? '' : 'null';
                    }
                    return $item;
                });

                $this->mappings = $newMappings;
            }
        }
    }

    public function addMappingItem()
    {
        $mapping = new \App\Models\MetaMapping([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'connector_uuid' => $this->connectorUUID,
            'scope' => 'null'
        ]);
        $this->mappings->push($mapping);
        $this->showSaveButton = true;
    }

    public function reloadMapping()
    {
        $mappings = \App\Models\MetaMapping::where('connector_uuid', $this->connectorUUID)->get();

        foreach ($mappings as $index => $mapping) {
            $family = (new Collection($this->families))->first(function($item, $index) use ($mapping) {
                return $item['code'] ===  $mapping->akeneo_family;
            });

            $this->attributes[$index] = $family['attributes'];

            if ($family['attributes'][$mapping->akeneo_attribute]['value_per_channel']){
                if (!in_array((int)$index, $this->enableScopes)) {
                    $this->enableScopes[] = (int)$index;
                }
            }

            $this->mappings->push([
                'connector_uuid' => $mapping->connector_uuid,
                'uuid' => $mapping->uuid,
                'metadata' => $mapping->metadata,
                'akeneo_family' => $mapping->akeneo_family,
                'akeneo_attribute' => $mapping->akeneo_attribute,
                'is_locale' => $mapping->is_locale,
                'scope' => $mapping->scope,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.meta-mapping');
    }
}
