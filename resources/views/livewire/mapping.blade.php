<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mapping') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end my-4">
                <a class="inline-flex space-x-1 hover:text-indigo-400" href="{{ route('connector.manage', ['id' => $connectorUuid]) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Back to previous</span>
                </a>
            </div>
            <div x-data="{ 'tab': 'specific' }" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex p-6 sm:px-20 bg-white border-b border-gray-200 space-x-8">
                    <button
                        x-bind:class=" tab === 'global' ? 'font-bold text-indigo-500' : ''"
                        class="space-x-1 flex"
                        @click="tab = 'global'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <span>Global</span>
                    </button>
                    <button
                        x-bind:class=" tab === 'specific' ? 'font-bold text-indigo-500' : ''"
                        class="space-x-1 flex"
                        @click="tab = 'specific'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                        </svg>
                        <span>Scope & Locale</span>
                    </button>
                </div>
                <div x-show="tab === 'global'" class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <div>
                        <div class="flex justify-end space-x-4 bg-gray-50 p-4 border-b border-indigo-200 shadow">
                            <button
                                class="relative inline-flex items-center px-4 py-2 rounded border border-gray-300 bg-white
                                      text-sm font-medium text-gray-700 hover:bg-gray-50"
                                wire:click="addMappingItem">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Add new</span>
                            </button>
                            @if($showSaveButton)
                            <button
                                class="relative inline-flex items-center px-4 py-2 rounded border border-gray-300 bg-white
                                      text-sm font-medium text-gray-700 hover:bg-gray-50"
                                wire:click="updateMapping">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2h2m3-4H9a2 2 0 00-2 2v7a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-1m-1 4l-3 3m0 0l-3-3m3 3V3" />
                                </svg>
                                <span>Save Mapping</span>
                            </button>
                            @endif
                        </div>
                        <div class="grid grid-cols-4 gap-4 my-10">
                            @foreach ($mappings as $index => $mapping)
                            <div wire:key="mapping-{{$index}}" class="p-4 border shadow hover:bg-gray-50">
                                <div class="my-2">
                                    <x-jet-label value="{{ __('Filerobot position') }}" />
                                    <x-jet-input
                                        wire:model="mappings.{{$index}}.filerobot_position"
                                        class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-4"
                                        type="number"
                                        min="0"
                                        step="1"/>
                                    @error('mappings.'.$index.'.filerobot_position')
                                        <span class="error">
                                            {{ __('This field is required') }}
                                        </span>
                                     @enderror
                                    </div>
                                <div class="my-2">
                                    <x-jet-label value="{{ __('Akeneo attribute') }}" />
                                    <select
                                        class="mt-1 block w-full border border-gray-300 rounded-md py-2.5 px-4"
                                        wire:model="mappings.{{$index}}.akeneo_attribute">
                                        <option>Choose attribute</option>
                                        @foreach ($availableAttributes as $attribute)
                                        <option value="{{ $attribute['code'] }}">{{ $attribute['label']['en_US'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('mappings.'.$index.'.akeneo_attribute')
                                    <span class="error">
                                        {{ __('This field is required') }}
                                    </span>
                                    @enderror
                                </div>
                                <div class="my-2">
                                    <x-jet-label value="{{ __('New version update behavior') }}" />
                                    <select
                                        class="mt-1 block w-full border border-gray-300 rounded-md py-2.5 px-4"
                                        wire:model="mappings.{{$index}}.update_default_behavior">
                                        <option>Choose behavior</option>
                                        @foreach ($behaviors as $behavior)
                                            <option value="{{ $behavior['code'] }}">{{ $behavior['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('mappings.'.$index.'.update_default_behavior')
                                    <span class="error">
                                        {{ __('This field is required') }}
                                    </span>
                                    @enderror
                                </div>
                                <div class="flex justify-end">
                                    <button wire:click="deleteItem('{{$mapping['uuid']}}')" class="p-2 bg-red-500 text-white rounded shadow text-xs my-2 hover:bg-red-700 inline-flex space-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span>{{ __('Remove') }}</span>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div x-show="tab === 'specific'" class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    @livewire('mapping-specific', ['familyUUID' => $family->uuid])
                </div>
            </div>
        </div>
    </div>
</div>
