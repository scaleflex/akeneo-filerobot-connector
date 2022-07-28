<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessSyncAttribute;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class AkeneoFamily extends Component
{

    use WithPagination;

    public $connectorUUID;
    public $connector;
    public $search = '';

    public function mount($connectorUUID)
    {
        $this->connectorUUID = $connectorUUID;
        $this->connector     = \App\Models\Connector::find($connectorUUID);
    }

    public function syncAkeneoFamilies()
    {
        $connector = $this->connector;
        ProcessSyncAttribute::dispatch($connector->uuid)->delay(now()->addSecond(5));
        $this->connector     = \App\Models\Connector::find($this->connectorUUID);
    }

    public function render()
    {
        $families = \App\Models\AkeneoFamily::where('connector_uuid', $this->connector->uuid)
                                            ->where('code', 'ILIKE', "%{$this->search}%")
                                            ->paginate(10);

        return view('livewire.akeneo-family', [
            'families' => $families,
        ]);
    }
}
