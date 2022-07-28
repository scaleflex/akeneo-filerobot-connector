<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Connector') }}
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
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-2xl font-bold">Filerobot</p>
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
                                    <p class="text-xl font-bold">Notification</p>
                                    <div class="mt-4">
                                        <x-jet-label for="email" value="{{ __('Your Email') }}" />
                                        <x-jet-input  class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-4" type="text" wire:model="email" id="email"/>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold">Akeneo</p>
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
                                    <x-jet-button class="mt-4">
                                        {{ __('Submit') }}
                                    </x-jet-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
