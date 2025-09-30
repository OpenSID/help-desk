<x-filament::page>
    <div class="!mt-[-15px] text-xs italic text-gray-500">
        {{__('This is an action to export data based on the data on the report page, and is equipped with problem solving for the report and then exported to Excel.')}}
    </div>
    {{ $this->form }}

    <div class="flex mt-4 gap-4">
        <x-filament::button wire:click="save">
            {{ __('Save') }}
        </x-filament::button>

        <div class="">
            <x-filament::button
                wire:click="export"
                color="success"
                :disabled="! $saved"
                class="{{ $saved ? '' : 'disabled opacity-50 cursor-not-allowed' }}"
            >
                {{__('Export to Excel')}}
            </x-filament::button>
        </div>
    </div>
</x-filament::page>
