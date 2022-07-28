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
    <div class="space-y-4 my-10">
        @foreach ($mappings as $index => $mapping)
            <div  class="p-4 border shadow hover:bg-gray-50 flex justify-between text-xs">
                <div>
                    <label for="mappings.{{$index}}.filerobot_position">{{ __('Position') }}</label>
                    <input
                        class="mt-1 block w-20 border border-gray-300 rounded-md py-2 px-4"
                        type="number"
                        min="0"
                        id="mappings.{{$index}}.filerobot_position"
                        wire:model="mappings.{{$index}}.filerobot_position"
                        step="1" />
                    @error('mappings.'.$index.'.filerobot_position')
                    <span class="error">
                         {{ __('This field is required') }}
                    </span>
                    @enderror
                </div>
                <div>
                    <label for="mappings.{{$index}}.name">{{ __('Name') }}</label>
                    <input
                        wire:model="mappings.{{$index}}.name"
                        class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-4"
                        type="text"
                        id="mappings.{{$index}}.name"
                        min="0"
                        step="1"/>
                    @error('mappings.'.$index.'.name')
                    <span class="error">
                        {{ __('This field is required') }}
                    </span>
                    @enderror
                </div>
                <div>
                    <label for="mappings.{{$index}}.type">{{ __('Type') }}</label>
                    <select
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                        wire:model="mappings.{{$index}}.type">
                        <option>Mapping type</option>
                        @foreach ($types as $type)
                            <option value="{{ $type['code'] }}">{{ $type['label'] }}</option>
                        @endforeach
                    </select>
                    @error('mappings.'.$index.'.scope')
                    <span class="error">
                        {{ __('This field is required') }}
                    </span>
                    @enderror
                </div>
                <div>
                    <label for="mappings.{{$index}}.scope">{{ __('Scope') }}</label>
                    <select
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                        wire:model="mappings.{{$index}}.scope">
                        <option>Scope</option>
                        <option value="null">Null</option>
                        @foreach ($scopes as $scope)
                            <option value="{{ $scope['code'] }}">{{ array_values($scope['labels'])[0] }}</option>
                        @endforeach
                    </select>
                    @error('mappings.'.$index.'.scope')
                    <span class="error">
                           {{ __('This field is required') }}
                    </span>
                    @enderror
                </div>
                @if (array_key_exists($index, $locales))
                    <div>
                        <label for="mappings.{{$index}}.locale">{{ __('Locale') }}</label>
                        <select
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                            wire:model="mappings.{{$index}}.locale">
                            <option>Type</option>
                            <option value="null">Null</option>
                            @foreach ($locales[$index] as $locale)
                                <option value="{{ $locale }}">{{ $locale }}</option>
                            @endforeach
                        </select>
                        @error('mappings.'.$index.'.locale')
                        <span class="error">
                                {{ __('This field is required') }}
                        </span>
                        @enderror
                    </div>
                @endif
                @if (array_key_exists($index, $attributes))
                    <div>
                        <label for="mappings.{{$index}}.akeneo_attribute">{{ __('Attribute') }}</label>
                        <select
                            id="mappings.{{$index}}.akeneo_attribute"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                            wire:model="mappings.{{$index}}.akeneo_attribute">
                            <option>Type</option>
                            @foreach ($attributes[$index] as $attribute)
                                <option value="{{ $attribute['code'] }}">{{ array_values($attribute['label'])[0] }}</option>
                            @endforeach
                        </select>
                        @error('mappings.'.$index.'.akeneo_attribute')
                        <span class="error">
                                {{ __('This field is required') }}
                        </span>
                        @enderror
                    </div>
                @endif
                <div>
                    <label for="mappings.{{$index}}.update_default_behavior">{{ __('Behavior') }}</label>
                    <select
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                        wire:model="mappings.{{$index}}.update_default_behavior">
                        <option>Behavior</option>
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
                <div class="flex justify-end pt-4">
                    <button wire:click="deleteItem('{{$mapping['uuid']}}')" class="p-2 h-8 w-8 text-xs my-2 text-red-800 hover:text-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 block mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>
