<?php

namespace App\Http\Livewire;

use App\Models\AssetEntity;
use Livewire\Component;
use Livewire\WithPagination;

class EntityAsset extends Component
{
    use WithPagination;

    public $connector;

    public $search = '';

    public function mount(string $connectorUUID)
    {
        $this->connector = \App\Models\Connector::find($connectorUUID);
    }

    public function render()
    {
        $assets = AssetEntity::where(function($query) {
            $query->where('entity', 'like', '%' . $this->search . '%');
            $query->orWhere('entity_attribute', 'like', '%' . $this->search . '%');
            $query->orWhere('entity_code', 'like', '%' . $this->search . '%');
        })->where('connector_uuid', $this->connector->uuid)->paginate(10);

        return view('livewire.entity-asset', [
            'assets' => $assets
        ]);
    }
}
