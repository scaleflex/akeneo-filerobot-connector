<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Connector extends Component
{
    public function render()
    {
        $connectors = \App\Models\Connector::where('user_id', Auth::id())->get();
        return view('livewire.connector', [
            'connectors' => $connectors
        ]);
    }
}
