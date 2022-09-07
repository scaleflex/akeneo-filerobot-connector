<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Connector') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <div class="flex justify-end bg-gray-50 p-4 border-b border-indigo-200 shadow">
                       <div x-data="{ open: false }">
                          <div class="relative cursor-pointer">
                             <div class="flex justify-start">
                                  <span
                                      class="relative inline-flex items-center px-4 py-2 rounded border border-gray-300 bg-white
                                      text-sm font-medium text-gray-700 hover:bg-gray-50"
                                      @click="open=!open">Add Connector</span>
                             </div>
                              <div x-show="open" class="absolute bg-white border border-gray-300 rounded w-48 space-y-2">
                                  <a href="{{ route('connector.add', 'ce') }}" class="block px-4 py-2 w-full hover:bg-gray-50 text-sm text-gray-500">Growth/CE Version</a>
                                  <a class="block px-4 py-2 w-full hover:bg-gray-50 text-sm text-gray-500" href="{{ route('connector.add', 'ee') }}">Enterprise Version</a>
                              </div>
                          </div>
                       </div>
                    </div>
                    <div class="mt-24" >
                        <h1 class="block my-4 text-bold text-2xl">Connectors</h1>
                        <div class="grid grid-cols-3 gap-4" wire:poll.750ms>
                            @foreach($connectors as $connector)
                            <div class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:bg-gray-50">
                                <div class="flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full"
                                         src="/small_icon.jpeg" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <a href="{{route('connector.manage', $connector->uuid)}}" class="focus:outline-none">
                                        <span class="absolute inset-0" aria-hidden="true"></span>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{$connector->name}}
                                        </p>
                                        <p class="text-sm text-gray-500 truncate">
                                            Status:
                                            @if($connector->activation)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Activated
                                            </span>
                                            @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                In Active
                                            </span>
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500 truncate">
                                            Setup status:
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($connector->setup_status === \App\Models\Connector::PENDING)
                                                    bg-gray-100 text-gray-800
                                                    @elseif ($connector->setup_status === \App\Models\Connector::FAILED)
                                                    bg-red-100 text-red-800
                                                    @elseif ($connector->setup_status === \App\Models\Connector::PROCESSING)
                                                    bg-blue-100 text-blue-800
                                                    @elseif ($connector->setup_status === \App\Models\Connector::SUCCESSFUL)
                                                    bg-green-100 text-green-800
                                                    @endif
                                                ">
                                                @if ($connector->setup_status === \App\Models\Connector::PROCESSING)
                                                <svg class="animate-spin block h-3 w-3 text-indigo-500 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                 </svg>
                                                @endif
                                               {{ ucfirst($connector->setup_status) }}
                                            </span>
                                        </p>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
