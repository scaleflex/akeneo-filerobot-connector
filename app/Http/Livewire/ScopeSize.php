<?php

namespace App\Http\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ScopeSize extends Component
{

    public $connectorUUID;
    public $connector;

    public $locales = [];
    public $scopes = [];

    public $configs;

    public $showSaveButton = false;


    protected $rules = [
        'configs.*.scope' => 'required',
        'configs.*.locale' => 'required',
        'configs.*.size' => 'required',
    ];

    public function mount(string $connectorUUID)
    {
        $this->connectorUUID = $connectorUUID;
        $this->connector = \App\Models\Connector::find($connectorUUID);

        $this->scopes = json_decode($this->connector->scopes, true);

        foreach ($this->scopes as $scope) {
            foreach ($scope['locales'] as $locale) {
                $this->locales[] = $locale;
            }
        }
        $this->locales = array_unique($this->locales);

        $this->configs = new Collection([]);

        $this->reloadConfig();
    }


    public function addConfigItem()
    {
        $config = new \App\Models\Size([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'connector_uuid' => $this->connectorUUID
        ]);
        $this->configs->push($config);
        $this->showSaveButton = true;
    }

    public function updateConfig()
    {
        $this->validate();

        foreach ($this->configs as $config) {
            $isOldConfig = \App\Models\Size::find($config['uuid']);
            if ($isOldConfig) {
                $isOldConfig->update($config);
            } else {
                (new \App\Models\Size($config))->save();
            }
        }
        $this->configs = new Collection([]);
        $this->showSaveButton = false;
        $this->reloadConfig();
    }

    public function reloadConfig()
    {
        $configSizes = \App\Models\Size::where('connector_uuid', $this->connectorUUID)->get();

        foreach ($configSizes as $size) {
            $this->configs->push([
                'uuid' => $size->uuid,
                'connector_uuid' => $size->connector_uuid,
                'scope' => $size->scope,
                'locale' => $size->locale,
                'size' => $size->size
            ]);
        }
    }

    public function updated($property, $value)
    {
        $this->showSaveButton = true;
    }

    public function deleteItem(string $itemUUID)
    {
        $isInDb = \App\Models\Size::find($itemUUID);

        if ($isInDb) {
            $isInDb->delete();
        }

        $temp = new Collection([]);
        $this->configs->each(function($config) use ($temp, $itemUUID) {
            if ($config['uuid'] !== $itemUUID) {
                $temp->push($config);
            }
        });
        $this->configs = $temp;
    }


    public function render()
    {
        return view('livewire.scope-size');
    }
}
