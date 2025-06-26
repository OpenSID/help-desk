<div class="inline-flex items-center space-x-2 rtl:space-x-reverse px-4">
    <div class="w-5 h-5 rounded flex items-center justify-center text-center"
         style="background-color: {{ $state->color ?? '#6b7280' }};" title="{{ $state->name ?? 'Unknown' }}">
        <x-icon class="h-3 text-white" name="{{ $state->icon ?? 'heroicon-o-tag' }}" />
    </div>
    <span>{{ $state->name ?? 'Unknown' }}</span>
</div>
