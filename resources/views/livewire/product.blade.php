<div class="my-10" x-data="{tab: 'product'}" wire:poll.750ms>
   <div class="flex justify-end">
        <span :class="tab == 'product' ? 'bg-gray-200' : ''" class="p-1 border border-gray-200 rounded-l hover:bg-gray-50">
            <a href="#" @click="tab = 'product'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </a>
        </span>
       <span :class="tab == 'asset' ? 'bg-gray-200' : ''"  class="p-1 border border-gray-200 border-l-0 rounded-r hover:bg-gray-50">
           <a href="#" @click="tab = 'asset'">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
           </a>
       </span>
   </div>
   <div x-show="tab === 'product'">
       <div class="flex justify-between my-5">
           <div class="inline-flex justify-end text-xs">
               <input wire:model="search"
                      placeholder="Search products..."
                      class="mt-1 block w-full border border-gray-300 rounded-md px-2 py-1"
               />
           </div>
           <div>
               <select
                   class="block w-full border border-gray-300 rounded-md p-1"
                   wire:model="akeneoStatus">
                   <option value="">All items</option>
                   <option value="1">Exist on Akeneo</option>
                   <option value="2">Not Exist on Akeneo</option>
               </select>
           </div>
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
           @foreach($products as $product)
           <tr wire:key="product-{{$product->uuid}}" class="hover:bg-gray-50 cursor-pointer">
               <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                   {{ $product->filerobot_reference }}
               </td>
               <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
               </td>
               <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span wire:click="showProductTab('{{ $product->uuid }}')" class="inline-flex hover:text-indigo-400">
                    <span>View Assets</span>
                </span>
               </td>
           </tr>
           @if ($activeProduct === $product->uuid)
           <tr>
               <td colspan="3">
                   @if ($product->akeneo_product_exist)
                   @livewire('assets', ['productUUID' => $product->uuid])
                   @else
                   <span class="text-center text-gray-500 block h-18 py-10">This product does not exist on Akeneo</span>
                   @endif
               </td>
           </tr>
           @endif
           @endforeach
           </tbody>
       </table>
       {{ $products->links() }}
   </div>
   <div x-show="tab === 'asset'">
       @livewire('asset-view', ['connectorUUID' => $connectorUUID])
   </div>
</div>
