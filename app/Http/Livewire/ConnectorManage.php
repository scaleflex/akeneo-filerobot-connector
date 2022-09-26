<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessSyncAttribute;
use Livewire\Component;

class ConnectorManage extends Component
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

    public string $fallback_size = '';

    public string $email = '';

    public \App\Models\Connector $connector;

    public function mount($id)
    {
        $this->connector = \App\Models\Connector::find($id);

        if (!$this->connector) {
            session()->flash('message', __('Connector does not exist'));
            $this->redirect(route('connector'));
        }

        $this->name = $this->connector->name;
        $this->filerobot_token = $this->connector->filerobot_token;
        $this->filerobot_key = $this->connector->filerobot_key;
        $this->akeneo_version = $this->connector->akeneo_version;
        $this->akeneo_server_url = $this->connector->akeneo_server_url;
        $this->akeneo_client_id = $this->connector->akeneo_client_id;
        $this->akeneo_secret = $this->connector->akeneo_secret;
        $this->email        = $this->connector->email;
        $this->akeneo_username = $this->connector->akeneo_username;
        $this->akeneo_password = $this->connector->akeneo_password;
        $this->fallback_size = $this->connector->fallback_size;

    }

    public function changeEmail()
    {
        $this->connector->email = $this->email;
        $this->connector->save();
    }

    public function changeStatus($status)
    {
        $this->connector->activation = $status;
        $this->connector->save();
        $this->connector->refresh();
    }

    public function save()
    {
        $verifyUpdateStatus = $this->connector->update([
            'name' => $this->name,
            'filerobot_token' => $this->filerobot_token,
            'filerobot_key' => $this->filerobot_key,
            'akeneo_server_url' => $this->akeneo_server_url,
            'akeneo_version' => $this->akeneo_version,
            'akeneo_client_id' => $this->akeneo_client_id,
            'akeneo_secret' => $this->akeneo_secret,
            'akeneo_username' => $this->akeneo_username,
            'akeneo_password' => $this->akeneo_password,
            'setup_status' => \App\Models\Connector::PENDING,
            'setup_message' => 'Configuration updated',
            'fallback_size' => $this->fallback_size
        ]);

        if ($verifyUpdateStatus) {
            ProcessSyncAttribute::dispatch($this->connector->uuid)->delay(now()->addSecond(10));
            $this->redirect(route('connector'));
        }
    }


    public function render()
    {
        return view('livewire.connector-manage');
    }
}
