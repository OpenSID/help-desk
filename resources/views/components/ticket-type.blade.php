@php
    // Handle invalid icons (like o-ban which doesn't exist in newer Heroicons)
    $iconName = $type->icon ?? 'heroicon-o-tag';
    $invalidIcons = ['heroicon-o-ban', 'o-ban', 'ban'];
    if (in_array($iconName, $invalidIcons)) {
        $iconName = 'heroicon-o-x-circle';
    }
@endphp
<div class="w-6 h-6 rounded flex items-center justify-center text-center"
     style="background-color: {{ $type->color ?? '#6b7280' }};"
     title="{{ $type->name ?? 'Unknown' }}">
    <x-icon class="h-3 text-white" name="{{ $iconName }}" />
</div>
