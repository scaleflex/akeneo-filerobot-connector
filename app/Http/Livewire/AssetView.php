<?php

namespace App\Http\Livewire;

use App\Models\Asset;
use Livewire\Component;
use Livewire\WithPagination;

class AssetView extends Component
{

    use WithPagination;

    public $connectorUUID;
    public $syncStatus = '';
    public $actionStatus = '';
    public $scopeFilter = '';
    public $choosedAction = '';

    public $bulkActionStatus = '';

    public $failedMessage = false;

    public $search = '';


    public $selectedAll = false;
    public $selectedPage = false;
    public $selected    = [];

    protected $listeners = ['changeActionStatus' => 'changeActionStatus'];

    public function gotoMappingConfig($productUUID)
    {
        $product = \App\Models\Product::find($productUUID);
        $this->redirect(route('connector.mapping', [
            'familyUUID' => $product->getFamily()->uuid,
        ]));
    }

    public function reload()
    {
        $this->selectedAll = false;
        $this->selectedPage = false;
        $this->selected = [];
    }

    public function showFailedMessage($assetId)
    {
      $this->failedMessage = $this->failedMessage === $assetId ? false : $assetId;
    }

    public function updatedBulkActionStatus($status)
    {
        $this->bulkActionStatus = $status;
        Asset::whereIn('uuid', $this->selected)->update(['new_version_action' => $status]);
        $this->reload();
    }

    public function mount($connectorUUID)
    {
        $this->connectorUUID = $connectorUUID;
    }

    public function changeActionStatus($assetId, $action)
    {
        $asset = \App\Models\Asset::find($assetId);
        $asset->new_version_action = $action;
        $asset->save();
    }

    public function formatFailedMessage($message)
    {
        $message = collect(json_decode($message));
        return $message->map(function ($item) {
            return '- ' .  $item->message;
        })->implode('<br>');
    }

    public function updatedSelected()
    {
        $this->selectedAll = false;
        $this->selectedPage = false;
    }

    public function updatedSelectedPage($value)
    {
        $this->selected = $value ?
                            $this->assets->pluck('uuid') :
                            [];
    }

    public function updatedSelectedAll()
    {
            $this->selected = $this->assets->pluck('uuid');
    }

    public function getAssetsProperty()
    {
        $whereCondition = [
            'connector_uuid' => $this->connectorUUID,
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

        return \App\Models\Asset::where('product_code', 'ILIKE', "%{$this->search}%")
                                ->where($whereCondition)
                                ->paginate(10);
    }

    public function selectAll()
    {
        $this->selectedAll = true;
    }

    public function render()
    {

        if ($this->selectedAll) {
            $this->selected = $this->assets->pluck('uuid');
        }

        return view('livewire.asset-view', [
            'assets' => $this->assets
        ]);
    }
}
