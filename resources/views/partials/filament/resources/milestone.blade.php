@if ($state)
    <div class="inline-flex items-center space-x-2 rtl:space-x-reverse px-4">
        <div class="w-5 h-5 rounded flex items-center justify-center text-center"
            style="background-color: {{ e($state->color) }};" title="{{ $state->name }}">
        </div>
        <span>{{ $state->name }}</span>
    </div>
@endif
