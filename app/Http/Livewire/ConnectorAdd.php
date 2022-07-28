<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessSyncAttribute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class ConnectorAdd extends Component
{
    public bool $activation;
    public string $name = '';
    public string $image = '';
    public string $filerobot_token = '';
    public string $filerobot_key = '';
    public string $akeneo_version = '';
    public string $akeneo_server_url = '';
    public string $akeneo_client_id = '';
    public string $akeneo_secret = '';
    public string $akeneo_username = '';
    public string $akeneo_password = '';
    public string $email = '';


    public function mount($version = null)
    {
        if (!in_array($version, ['ee', 'ce'])) {
            session()->flash('message', __('Akeneo version must be "ee" or "ce"'));
            $this->redirect(route('connector'));
        }
        $this->akeneo_version = $version;
    }

    public function save()
    {
        $connector = new \App\Models\Connector([
            'uuid'              => Str::uuid(),
            'name'              => $this->name,
            'filerobot_token'   => $this->filerobot_token,
            'filerobot_key'     => $this->filerobot_key,
            'akeneo_server_url' => $this->akeneo_server_url,
            'akeneo_version'    => $this->akeneo_version,
            'akeneo_client_id'  => $this->akeneo_client_id,
            'akeneo_secret'     => $this->akeneo_secret,
            'akeneo_username'   => $this->akeneo_username,
            'akeneo_password'   => $this->akeneo_password,
            'email'             => $this->email,
            'user_id'           => Auth::user()->getAuthIdentifier(),
            'setup_status'      => \App\Models\Connector::PENDING,
            'setup_message'     => 'Setup in Queue'
        ]);


       if ($connector->save()) {
            ProcessSyncAttribute::dispatch($connector->uuid)->delay(now()->addSecond(5));
            return $this->redirect(route('connector'));
       }
    }

    public function render()
    {
        return view('livewire.connector-add');
    }
}
