<div>
    <div class="flex justify-end text-xs block my-2 space-x-4">
        <div>
            <select
                class="block w-full border border-gray-300 rounded-md p-1"
                wire:model="actionStatus">
                <option value="">All items</option>
                <option value="pending">Pending action</option>
                <option value="override">Override</option>
                <option value="keep">Keep old version</option>
            </select>
        </div>
        <div>
            <select
                class="block w-full border border-gray-300 rounded-md p-1"
                wire:model="syncStatus">
                <option value="">All status</option>
                <option value="synced">Synced</option>
                <option value="not_sync">Not Sync</option>
                <option value="failed">Failed</option>
            </select>
        </div>
        <div>
            <select
                class="block w-full border border-gray-300 rounded-md p-1"
                wire:model="scopeFilter">
                <option value="">All scope</option>
                <option value="global">Global</option>
                <option value="tag">Tag</option>
                <option value="variant">Variant</option>
            </select>
        </div>
        <span wire:click="gotoMappingConfig()" class="cursor-pointer text-blue-500 mt-1.5">Config mappings</span>
    </div>
    <div class="grid grid-cols-3 gap-4">
        @foreach($assets as $asset)
        <div class="inline-flex space-x-2 border border-gray-200 flex p-2 my-2 hover:bg-gray-50 cursor-pointer">
            <div>
                <img class="img-thumbnail rounded w-20 h-20" src="{{ $asset->filerobot_url_cdn }}&width=200&height=200" alt="{{ $asset->product_code }}">
            </div>
            <div class="text-xs text-gray-500">
                <div><span>Position: </span><span>{{ $asset->filerobot_position }}</span></div>
                <div><span>Status: </span>
                    <span>
                        @if($asset->akeneo_sync_status === \App\Models\Asset::STATUS_SYNCED)
                        <span class="text-xs text-green-500">Synced</span>
                        @elseif ($asset->akeneo_sync_status === \App\Models\Asset::STATUS_NOT_SYNC)
                        <span class="text-xs text-gray-500">Not Sync</span>
                        @elseif ($asset->akeneo_sync_status === \App\Models\Asset::STATUS_FAILED)
                        <span class="text-xs text-red-500">Failed</span>
                        @endif
                    </span>
                </div>
                <div><span>Version: </span><span>{{ $asset->version }}</span></div>
                <div><span>Akeneo version: </span><span>{{ $asset->akeneo_latest_version !== null ? $asset->akeneo_latest_version : 'N/A' }}</span></div>
                <div><span>Mapping Config: </span>
                    <span>
                        {{ $asset->have_mapping ? 'Yes' : 'No' }}
                    </span>
                </div>
                @if($asset->new_version_action)
                <div>
                    <span>Need action: </span>
                    <span>
                    <select
                        x-on:change="$wire.emit('changeActionStatus', '{{ $asset->uuid }}', $event.target.value)"
                        class="block w-full border border-gray-300 rounded-md p-1" >
                        <option @if($asset->new_version_action === 'pending') selected @endif value="pending">Pending</option>
                        <option @if($asset->new_version_action === 'override') selected @endif value="override">Override</option>
                        <option @if($asset->new_version_action === 'keep') selected @endif value="keep">Keep</option>
                    </select>
                    </span>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    <div>
        {{ $assets->links() }}
    </div>
</div>
