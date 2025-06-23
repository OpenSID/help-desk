
<div class="flex flex-wrap gap-1">
    @foreach ($state as $category)
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-white"
              style="background-color: {{ $category->color ?? '#999' }}">
            {{ $category->name }}
        </span>
    @endforeach
</div>
