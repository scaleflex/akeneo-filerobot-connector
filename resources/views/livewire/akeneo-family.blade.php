<div class="my-10" wire:poll.750ms>
    <div class="flex justify-between my-5">
        <div class="inline-flex justify-end">
            <input wire:model="search"
                   placeholder="Search families..."
                   class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-4"
            />
        </div>
        @if ($connector->setup_status === \App\Models\Connector::PROCESSING)
        <button
            class="relative inline-flex space-x-2
                   items-center px-4 py-2 rounded border
                   hover:bg-gray-100
                   border-gray-300 bg-white text-sm font-medium text-gray-700"
            disabled="disabled">
            <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin h-5 w-5 block mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Updating Attributes</span>
        </button>
        @else
        <button
            class="relative inline-flex space-x-2
                   items-center px-4 py-2 rounded border
                   hover:bg-gray-100
                   border-gray-300 bg-white text-sm font-medium text-gray-700"
            wire:click="syncAkeneoFamilies">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Update Attributes</span>
        </button>
        @endif
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
        <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Code
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Label
            </th>
            <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Edit</span>
            </th>
        </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($families as $family)
                <tr class="hover:bg-gray-50 cursor-pointer">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{$family->code}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{array_values(unserialize($family->label))['0']}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <a class="inline-flex hover:text-indigo-400" href="{{ route('connector.mapping', ['familyUUID' => $family->uuid]) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Mapping configuration</span>
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $families->links() }}
</div>
