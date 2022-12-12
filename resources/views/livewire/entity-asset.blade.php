<div>
    <div>
        <div class="flex justify-between my-5">
            <div class="inline-flex justify-end text-xs">
                <input wire:model="search"
                       placeholder="Search entity..."
                       class="mt-1 block w-full border border-gray-300 rounded-md px-2 py-1"
                />
            </div>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Entity
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Attribute
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Code
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                </th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($assets as $asset)
                <tr class="hover:bg-gray-50 cursor-pointer" wire:key="{{$asset->uuid}}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <img class="img-thumbnail rounded w-10 h-10"
                             src="{{ $asset->url_cdn }}&width=20&height=20"
                             alt="{{ $asset->entity_code }}">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $asset->entity }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $asset->entity_attribute }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $asset->entity_code }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                         <span>
                             @if($asset->status === \App\Models\Asset::STATUS_SYNCED)
                                 <span class="text-xs text-green-500">Synced</span>
                             @elseif ($asset->status === \App\Models\Asset::STATUS_NOT_SYNC)
                                 <span class="text-xs text-gray-500">Not Sync</span>
                             @elseif ($asset->status === \App\Models\Asset::STATUS_FAILED)
                                 <span class="text-xs text-red-500">Failed</span>
                                 <span>{{ $asset->message }}</span>
                             @endif
                        </span>
                    </td>
                </tr>
        @endforeach
        </tbody>
        </table>
        <div>
            {{ $assets->links() }}
        </div>
</div>
</div>
