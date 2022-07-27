<div>
    <div>
        <div class="flex justify-between">
            <div class="text-xs block my-2 space-x-4">
                @if($actionStatus !== '' && !empty($selected))
                    <select
                        wire:model="bulkActionStatus"
                        class="block w-full border border-gray-300 rounded-md p-1">
                        <option value="">Change Action</option>
                        <option value="pending">Pending</option>
                        <option value="override">Override</option>
                        <option value="keep">Keep old version</option>
                    </select>
                @endif
            </div>
            <div class="flex justify-end text-xs block my-2 space-x-4">
                <div>
                    <input wire:model.debounce.500ms="search"
                           placeholder="Product code..."
                           class="block w-full border border-gray-300 rounded-md px-2 py-1"
                    />
                </div>
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
            </div>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                @if ($actionStatus !== '')
                <th scope="col" class="relative px-6 py-3">
                    <input type="checkbox"
                           wire:model="selectedPage"
                           class="form-checkbox border-cool-gray-300 block transition duration-150 ease-in-out sm:text-sm sm:leading-5"
                    />
                </th>
                @endif
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Version
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Mapping Config
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Sync Status
                </th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Edit</span>
                </th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @if ($selectedPage && $actionStatus !== '')
            <tr class="hover:bg-gray-50 cursor-pointer h-20" wire:key="row-message">
                <td colspan="6">
                    @unless ($selectedAll && $assets->count() !== $assets->total())
                    <div>
                        <span>You have selected <strong>{{ $assets->count() }}</strong> assets, do you want to select all <strong>{{ $assets->total() }}</strong>?</span>
                        <x-button.link wire:click="selectAll" class="ml-1 text-blue-600">Select All</x-button.link>
                    </div>
                    @else
                    <span>You are currently selecting all <strong>{{ $assets->total() }}</strong> assets.</span>
                    @endif
                </td>
            </tr>
            @endif
            @foreach($assets as $asset)
            <tr class="hover:bg-gray-50 cursor-pointer" wire:key="{{$asset->uuid}}">
                @if($actionStatus !== '')
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    <div class="flex rounded-md shadow-sm">
                        <input type="checkbox"
                               wire:model="selected" value="{{ $asset->uuid }}"
                               class="form-checkbox border-cool-gray-300 block transition duration-150 ease-in-out sm:text-sm sm:leading-5"
                        />
                    </div>
                </td>
                @endif
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    <img class="img-thumbnail rounded w-20 h-20" src="{{ $asset->filerobot_url_cdn }}&width=100&height=100" alt="{{ $asset->product_code }}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div><span>Version: </span><span>{{ $asset->version }}</span></div>
                    <span>Akeneo version: </span><span>{{ $asset->akeneo_latest_version !== null ? $asset->akeneo_latest_version : 'N/A' }}</span></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div><span>Mapping status: </span> {{ $asset->have_mapping ? 'Yes' : 'No' }}</div>
                    <span wire:click="gotoMappingConfig('{{$asset->product_uuid}}')"
                          class="cursor-pointer text-blue-500 mt-1.5">Config mappings</span>
                </td>
                <td>
                    <span>
                        @if($asset->akeneo_sync_status === \App\Models\Asset::STATUS_SYNCED)
                        <span class="text-xs text-green-500">Synced</span>
                        @elseif ($asset->akeneo_sync_status === \App\Models\Asset::STATUS_NOT_SYNC)
                        <span class="text-xs text-gray-500">Not Sync</span>
                        @elseif ($asset->akeneo_sync_status === \App\Models\Asset::STATUS_FAILED)
                        <div class="inline-flex space-x-2">
                            <span class="text-xs text-red-500">Failed</span>
                            <span wire:click="showFailedMessage('{{$asset->uuid}}')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        @endif
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    @if($asset->new_version_action)
                    <span>
                     <select
                         x-on:change="$wire.emit('changeActionStatus', '{{ $asset->uuid }}', $event.target.value)"
                         class="block w-full border border-gray-300 rounded-md p-1" >
                                <option @if($asset->new_version_action === 'pending') selected @endif value="pending">Pending</option>
                                <option @if($asset->new_version_action === 'override') selected @endif value="override">Override</option>
                                <option @if($asset->new_version_action === 'keep') selected @endif value="keep">Keep</option>
                     </select>
                    </span>
                    @else
                        <span>No action needed</span>
                    @endif
                </td>
            </tr>
            @if($failedMessage === $asset->uuid)
            <tr wire:key="failedmessage-{{$asset->uuid}}">
                <td colspan="6" class="max-w-full py-5 text-xs">
                    {{ $this->formatFailedMessage($asset->last_sync_error) }}
                </td>
            </tr>
            @endif
            @endforeach
            </tbody>
        </table>
        <div>
            {{ $assets->links() }}
        </div>
    </div>
</div>
