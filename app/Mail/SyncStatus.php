<?php

namespace App\Mail;

use App\Models\Asset;
use App\Models\Connector;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SyncStatus extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $connector;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($connectorUUID)
    {
        $this->connector = Connector::find($connectorUUID);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $syncedCount  = Asset::where([
            'akeneo_sync_status' => Asset::STATUS_SYNCED,
            'connector_uuid' => $this->connector->uuid
        ])->count();
        $failedCount  = Asset::where([
            'akeneo_sync_status' => Asset::STATUS_FAILED,
            'connector_uuid'     => $this->connector->uuid
        ])->count();
        $pendingCount = Asset::where([
            'new_version_action' => Asset::ACTION_PENDING,
            'connector_uuid'     => $this->connector->uuid
        ])->count();

        return $this->markdown('email.sync')
                    ->with([
                        'connector'   => $this->connector,
                        'syncedCount' => $syncedCount,
                        'failedCount' => $failedCount,
                        'pendingCount' => $pendingCount
                    ]);
    }
}
