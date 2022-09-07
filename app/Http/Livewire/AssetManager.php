<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Connector;
use App\Models\AssetManager as Model;
use Livewire\WithPagination;

class AssetManager extends Component
{
    use WithPagination;

    public $connectorUUID;
    public $connector;
    public $search = '';
    public $failedMessage;
    public $status = '';

    public function mount(string $connectorUUID)
    {
        $this->connectorUUID = $connectorUUID;
        $this->connector     = \App\Models\Connector::find($connectorUUID);
    }

    public function showFailedMessage($assetId)
    {
        $this->failedMessage = $this->failedMessage === $assetId ? false : $assetId;
    }

    public function render()
    {
        $assetQuery = Model::where('connector_uuid', $this->connectorUUID)
                                ->where(function($query) {
                                    $query->where('asset_family', 'like', '%' . $this->search . '%');
                                    $query->orWhere('asset_attribute', 'like', '%' . $this->search . '%');
                                });

        if (!empty($this->status)) {
            $assetQuery->where('status', $this->status);
        }

        $assets = $assetQuery->paginate(10);

        return view('livewire.asset-manager', [
            'assets' => $assets
        ]);
    }
}
