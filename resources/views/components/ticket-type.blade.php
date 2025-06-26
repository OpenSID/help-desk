<div class="w-6 h-6 rounded flex items-center justify-center text-center"
     style="background-color: {{ $type->color ?? '#6b7280' }};"
     title="{{ $type->name ?? 'Unknown' }}">
    <x-icon class="h-3 text-white" name="{{ $type->icon ?? 'heroicon-o-tag' }}" />
</div>
