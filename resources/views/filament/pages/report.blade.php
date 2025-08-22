<x-filament::page>
    <div class="!mt-[0px]">
        <!-- Tabs -->
        <div class="mb-4 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button wire:click="$set('activeTab', 1)" class="{{ $activeTab === 1 ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">{{ __('Trend Ticket') }}</button>
                <button wire:click="$set('activeTab', 2)" class="{{ $activeTab === 2 ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">{{ __('Ticket by owner') }}</button>
                <button wire:click="$set('activeTab', 3)" class="{{ $activeTab === 3 ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">{{ __('Ticket by responsible') }}</button>
                <button wire:click="$set('activeTab', 4)" class="{{ $activeTab === 4 ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">{{ __('Ticket by application') }}</button>
                <button wire:click="$set('activeTab', 5)" class="{{ $activeTab === 5 ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">{{ __('Ticket duplicates') }}</button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div>
            @if($activeTab === 1)
                @livewire(\App\Filament\Widgets\TicketTrendChart::class, [], key('ticket-trend'))
            @elseif($activeTab === 2)
                @livewire(\App\Filament\Widgets\TicketOwnerChart::class, [], key('ticket-owner'))
            @elseif($activeTab === 3)
                @livewire(\App\Filament\Widgets\TicketResponsibilityChart::class, [], key('ticket-responsibility'))
            @elseif($activeTab === 4)
                @livewire(\App\Filament\Widgets\TicketByApplicationChart::class, [], key('ticket-by-application'))
            @elseif($activeTab === 5)
                @livewire(\App\Filament\Widgets\TicketDuplicateChart::class, [], key('ticket-duplicate'))
            @endif
        </div>
    </div>
</x-filament::page>
