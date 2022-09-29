<div>
    <div class="flex justify-between">
        <div class="flex justify-end text-xs block my-2 space-x-4">
            <div>
                <input wire:model.debounce.500ms="search"
                       placeholder="family code, attribute code..."
                       class="block w-full border border-gray-300 rounded-md px-2 py-1"
                />
            </div>
            <div>
                <select
                    class="block w-full border border-gray-300 rounded-md p-1"
                    wire:model="status">
                    <option value="">All items</option>
                    <option value="synced">Sync</option>
                    <option value="failed">Failed</option>
                    <option value="not_sync">Not sync</option>
                </select>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Family
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Attribute
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Scope
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Locale
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Sync Status
                </th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($assets as $asset)
                <tr class="hover:bg-gray-50 cursor-pointer" wire:key="{{$asset->uuid}}">
                    <td class="px-6 py-4 block w-32 rounded">
                        <img class="img-thumbnail"
                             src="{{ $asset->url_cdn }}&width=100&height=100"/>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <span>{{ $asset->asset_family }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <span>{{ $asset->asset_attribute }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <span>{{ $asset->scope }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <span>{{ $asset->locale }}</span>
                    </td>
                    <td>
                    <span class="block text-center">
                        @if($asset->status === 'synced')
                            <span class="text-xs text-green-500">Synced</span>
                        @elseif ($asset->status === 'not_sync')
                            <span class="text-xs text-gray-500">Not Sync</span>
                        @elseif ($asset->status === 'failed')
                            <div class="inline-flex space-x-2">
                              <span class="text-xs text-red-500">Failed</span>
                              <span wire:click="showFailedMessage('{{$asset->uuid}}')">
                                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                       viewBox="0 0 24 24"
                                       stroke="currentColor" stroke-width="2">
                                       <path stroke-linecap="round" stroke-linejoin="round"
                                             d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                  </svg>
                              </span>
                            </div>
                        @endif
                    </span>
                    </td>
                </tr>
                @if($failedMessage === $asset->uuid)
                    <tr wire:key="failedmessage-{{$asset->uuid}}">
                        <td colspan="6" class="max-w-full py-5 text-xs">
                            {{ $asset->message }}
                        </td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    </div>
    <div>
        {{ $assets->links() }}
    </div>
</div>
