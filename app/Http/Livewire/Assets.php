<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class Assets extends Component
{

    use WithPagination;

    public $productUUID;
    public $syncStatus = '';
    public $actionStatus = '';
    public $choosedAction = '';
    public $scopeFilter  = '';

    protected $listeners = ['changeActionStatus' => 'changeActionStatus'];

    public function gotoMappingConfig()
    {
        $product = \App\Models\Product::find($this->productUUID);
        $this->redirect(route('connector.mapping', [
            'familyUUID' => $product->getFamily()->uuid,
        ]));
    }

    public function mount($productUUID)
    {
        $this->productUUID = $productUUID;
    }

    public function changeActionStatus($assetId, $action)
    {
        $asset = \App\Models\Asset::find($assetId);
        $asset->new_version_action = $action;
        $asset->save();
    }

    public function render()
    {
        $whereCondition = [
            'product_uuid' => $this->productUUID,
        ];

        if (!empty($this->syncStatus)) {
            $whereCondition['akeneo_sync_status'] = $this->syncStatus;
        }

        if (!empty($this->actionStatus)) {
            $whereCondition['new_version_action'] = $this->actionStatus;
        }


        if (!empty($this->scopeFilter)) {
            $whereCondition['asset_type'] = $this->scopeFilter;
        }


        $assets = \App\Models\Asset::where($whereCondition)->paginate(6);

        return view('livewire.assets', [
            'assets' => $assets
        ]);
    }
}
