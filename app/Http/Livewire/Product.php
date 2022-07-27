<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class Product extends Component
{
    use WithPagination;

    public $connectorUUID;

    public $activeProduct = null;

    public $search = '';

    public $akeneoStatus = '';

    public function mount($connectorUUID)
    {
        $this->connectorUUID = $connectorUUID;
    }

    public function showProductTab($productUUID)
    {
        if ($this->activeProduct == $productUUID) {
            $this->activeProduct = null;
        } else {
            $this->activeProduct = $productUUID;
        }
    }

    public function render()
    {


        $query = \App\Models\Product::where('connector_uuid', $this->connectorUUID)
                                        ->where('filerobot_reference', 'ILIKE', "%{$this->search}%");
        if (!empty($this->akeneoStatus)) {
            $query->where('akeneo_product_exist', $this->akeneoStatus === '1' ? true : false);
        }

        return view('livewire.product', [
            'products' => $query->paginate(10)
        ]);
    }
}
