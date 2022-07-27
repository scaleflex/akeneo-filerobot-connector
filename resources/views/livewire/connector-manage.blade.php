<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $connector->name }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end my-4">
                <a class="inline-flex space-x-1 hover:text-indigo-400" href="{{ route('connector') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Back to connectors</span>
                </a>
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <div x-data="{current: 'configuration'}">
                        <div class="flex justify-start space-x-8 bg-gray-50 p-4 border-b border-indigo-200 shadow">
                            <span class="flex justify-start space-x-1"
                                  :class="current==='configuration' ? 'text-bold cursor-pointer  text-indigo-400' : ' cursor-pointer'" @click="current='configuration'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Configuration</span>
                            </span>
                            @if ($connector->setup_status === \App\Models\Connector::SUCCESSFUL && $connector->activation)
                            <span class="flex justify-start space-x-1"
                                  :class="current==='mapping' ? 'text-bold cursor-pointer  text-indigo-400' : ' cursor-pointer'" @click="current='mapping'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                <span>Mapping</span>
                            </span>
                            <span class="flex justify-start space-x-1"
                                  :class="current==='product' ? 'text-bold cursor-pointer  text-indigo-400' : ' cursor-pointer'" @click="current='product'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Products</span>
                            </span>
                            @endif
                        </div>
                        <div class="py-6"  wire:poll.750ms">
                            <div x-show="current==='configuration'">
                                <div class="text-gray-800 block p-5 bg-gray-50 border border-gray-300 rounded">
                                    <h1 class="text-md font-bold">System current status</h1>
                                    <hr>
                                    <div class="text-xs my-2">
                                        <div>
                                            <span class="font-bold">Setup status: </span> <span>{{ ucfirst($connector->setup_status) }}</span>
                                        </div>
                                        <div>
                                            <span class="font-bold">Setup message: </span> <span>{{ ucfirst($connector->setup_message) }}</span>
                                        </div>
                                    </div>
                                    <div class="text-xs my-2">
                                        <div>
                                            <span class="font-bold">Filerobot status: </span> <span>{{ ucfirst($connector->filerobot_sync_status) }}</span>
                                        </div>
                                        <div>
                                            <span class="font-bold">Filerobot message: </span> <span>{{ ucfirst($connector->filerobot_sync_last_message) }}</span>
                                        </div>
                                    </div>
                                    <div class="text-xs my-2">
                                        <div>
                                            <span class="font-bold">Akeneo status: </span> <span>{{ ucfirst($connector->akeneo_sync_status) }}</span>
                                        </div>
                                        <div>
                                            <span class="font-bold">Akeneo message: </span> <span>{{ ucfirst($connector->akeneo_sync_last_message) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="block">
                                    <form wire:submit.prevent="save">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="my-4">
                                                <x-jet-label for="name" value="{{ __('Name') }}" />
                                                <x-jet-input  class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-4"
                                                              type="text"
                                                              wire:model="name"
                                                              id="name" />
                                            </div>
                                            @if($connector->lock_status === false && $connector->setup_status === \App\Models\Connector::SUCCESSFUL)
                                            <div class="cursor-pointer my-4">
                                                <x-jet-label for="name" value="{{ __('Status') }}" />
                                                @if ($connector->activation)
                                                <label wire:click="changeStatus(false)" for="disabled-checked-toggle" class="inline-flex relative items-center cursor-pointer">
                                                    <input type="checkbox" value="" id="disabled-checked-toggle" class="sr-only peer" checked disabled>
                                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                                    <span class="ml-3 text-sm font-medium text-gray-400 dark:text-gray-500">Enabled</span>
                                                </label>
                                                @else
                                                <label wire:click="changeStatus(true)" for="disabled-toggle" class="inline-flex relative items-center mb-5 cursor-pointer">
                                                    <input type="checkbox" value="" id="disabled-toggle" class="sr-only peer" disabled>
                                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                                    <span class="ml-3 text-sm font-medium text-gray-400 dark:text-gray-500">Disabled</span>
                                                </label>

                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-xl font-bold">Filerobot</p>
                                                <div class="mt-4">
                                                    <x-jet-label for="filerobot_token" value="{{ __('Token') }}" />
                                                    <x-jet-input  class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-4"
                                                                  type="text"
                                                                  wire:model="filerobot_token"
                                                                  id="filerobot_token"/>
                                                </div>
                                                <div class="mt-4">
                                                    <x-jet-label for="filerobot_key" value="{{ __('API Key') }}" />
                                                    <x-jet-input  class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-4" type="text" wire:model="filerobot_key" id="filerobot_key"/>
                                                </div>
                                                <p class="text-xl font-bold mt-4">Notification</p>
                                                <div class="mt-4">
                                                    <x-jet-label for="email" value="{{ __('Your Email') }}" />
                                                    <x-jet-input  class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-4" type="text" wire:model="email" id="email"/>
                                                </div>
                                                <x-jet-button wire:click="changeEmail()" class="mt-4" type="button">
                                                    {{ __('Update email') }}
                                                </x-jet-button>
                                            </div>
                                            <div>
                                                <p class="text-xl font-bold">Akeneo</p>
                                                <div class="mt-4">
                                                    <x-jet-label for="akeneo_server_url" value="{{ __('URL') }}" />
                                                    <x-jet-input  class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-4" type="text" wire:model="akeneo_server_url" id="akeneo_server_url"/>
                                                </div>
                                                <div class="mt-4">
                                                    <x-jet-label for="akeneo_client_id" value="{{ __('Client ID') }}" />
                                                    <x-jet-input  class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-4" type="text" wire:model="akeneo_client_id" id="akeneo_client_id"/>
                                                </div>
                                                <div class="mt-4">
                                                    <x-jet-label for="akeneo_secret" value="{{ __('Secret') }}" />
                                                    <x-jet-input  class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-4" type="text" wire:model="akeneo_secret" id="akeneo_secret"/>
                                                </div>
                                                <div class="mt-4">
                                                    <x-jet-label for="akeneo_username" value="{{ __('Username') }}" />
                                                    <x-jet-input  class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-4" type="text" wire:model="akeneo_username" id="akeneo_username"/>
                                                </div>
                                                <div class="mt-4">
                                                    <x-jet-label for="akeneo_password" value="{{ __('Password') }}" />
                                                    <x-jet-input  class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-4" type="text" wire:model="akeneo_password" id="akeneo_password"/>
                                                </div>
                                                @if(!$connector->lock_status)
                                                <x-jet-button class="mt-4">
                                                    {{ __('Submit') }}
                                                </x-jet-button>
                                                @else
                                                <div>
                                                    <x-jet-button class="mt-4 bg-gray-500 hover:gray-500" disabled="" type="button">
                                                        {{ __('Locked') }}
                                                    </x-jet-button>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @if ($connector->setup_status === \App\Models\Connector::SUCCESSFUL)
                            <div x-show="current==='mapping'">
                                @livewire('akeneo-family', ['connectorUUID' => $connector->uuid])
                            </div>
                            <div x-show="current==='product'">
                                @livewire('product', ['connectorUUID' => $connector->uuid])
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
