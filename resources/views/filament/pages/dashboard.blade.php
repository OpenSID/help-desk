<x-filament::page>
    {{-- <x-filament::grid columns="1" gap="6"> --}}
        @foreach ($this->getWidgets() as $widget)
            @livewire($widget) {{-- render widget via Livewire --}}
        @endforeach
    {{-- </x-filament::grid> --}}
</x-filament::page>
