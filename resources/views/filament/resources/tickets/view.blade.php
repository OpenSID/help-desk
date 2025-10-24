{{--
    view.blade.php
    Halaman detail tiket pada modul Filament.
    - Menampilkan detail tiket, status, prioritas, tipe, owner, responsible, sprint/epic, kategori, estimasi, waktu log, subscriber, tanggal pembuatan & update, relasi tiket, komentar, aktivitas, time log, dan lampiran.
    - Terdapat navigasi kembali ke kanban board.
    - Menggunakan komponen-komponen custom dan Livewire.
--}}

@php($record = $this->record)
<x-filament::page>

    {{-- Tombol kembali ke kanban board --}}
    {{-- <a href="{{ route('filament.admin.pages.kanban', ['project' => $record->project->id]) }}"
       class="flex items-center gap-1 text-gray-500 hover:text-gray-700 font-medium text-xs">
        <x-heroicon-o-arrow-left class="w-4 h-4"/> {{ __('Back to kanban board') }}
    </a> --}}

    <div class="w-full flex md:flex-row flex-col gap-5">

        {{-- Kartu utama detail tiket --}}
        <div class="md:w-2/3 w-full h-full flex flex-col gap-5">
            <div class="h-full">
                <x-filament::card class="h-full">
                    <div class="w-full flex flex-col gap-0">
                        {{-- Header: kode, project, nama tiket --}}
                        <div class="flex items-center gap-2">
                            <span class="flex items-center gap-1 text-sm text-primary-500 font-medium">
                                <x-heroicon-o-ticket class="w-4 h-4"/>
                                {{ $record->code }}
                            </span>
                            <span class="text-sm text-gray-400 font-light">|</span>
                            <span class="flex items-center gap-1 text-sm text-gray-500">
                                {{ $record->project->name }}
                            </span>
                        </div>
                        <span class="text-xl text-gray-700">
                            {{ $record->name }}
                        </span>
                    </div>
                    <div class="w-full flex items-center gap-2">
                        @if($record->status)
                        <div class="px-2 py-1 rounded flex items-center justify-center text-center text-xs text-white"
                            style="background-color: {{ $record->status->color ?? '#6b7280' }};">
                            {{ $record->status->name ?? 'Unknown' }}
                        </div>
                        @endif
                        @if($record->priority)
                        <div class="px-2 py-1 rounded flex items-center justify-center text-center text-xs text-white"
                            style="background-color: {{ $record->priority->color ?? '#6b7280' }};">
                            {{ $record->priority->name ?? 'Unknown' }}
                        </div>
                        @endif
                        @if($record->type)
                        <div class="px-2 py-1 rounded flex items-center justify-center text-center text-xs text-white"
                            style="background-color: {{ $record->type->color ?? '#6b7280' }};">
                            <x-icon class="h-3 text-white" name="{{ $record->type->icon ?? 'heroicon-o-tag' }}"/>
                            <span class="ml-2">
                                {{ $record->type->name ?? 'Unknown' }}
                            </span>
                        </div>
                        @endif
                    </div>
                    <div class="w-full flex flex-col gap-0 pt-5">
                        {{-- Konten tiket --}}
                        <span class="text-gray-500 text-sm font-medium">
                            {{ __('Content') }}
                        </span>
                        <div class="w-full prose">
                            {!! $record->content !!}
                        </div>
                    </div>
                </x-filament::card>
            </div>
        </div>

        {{-- Kartu info samping: owner, responsible, sprint/epic, kategori, estimasi, waktu log, subscriber, tanggal, relasi --}}
       <div></div>
        <x-filament::card class="">
            {{-- Owner --}}
            <div class="w-full flex flex-col gap-1" wire:ignore>
                <span class="text-gray-500 text-sm font-medium">
                    {{ __('Owner') }}
                </span>
                <div class="w-full flex items-center gap-1 text-gray-500">
                    <div class="h-10 w-10 flex items-center">
                        <x-user-avatar :user="$record->owner"/>
                    </div>
                    {{ $record->owner->name }}
                </div>
            </div>

            {{-- Responsible --}}
            <div class="w-full flex flex-col gap-1 pt-3" wire:ignore>
                <span class="text-gray-500 text-sm font-medium">
                    {{ __('Responsible') }}
                </span>
                <div class="w-full flex items-center gap-1 text-gray-500">
                    @if($record->responsible)
                        <div class="h-10 w-10 flex items-center">
                            <x-user-avatar :user="$record->responsible"/>
                        </div>
                    @endif
                    {{ $record->responsible?->name ?? '-' }}
                </div>
            </div>

            {{-- Sprint/Epic --}}
            @if($record->project->type === 'scrum')
                <div class="w-full flex flex-col gap-1 pt-3">
                    <span class="text-gray-500 text-sm font-medium">
                        {{ __('Sprint') }}
                    </span>
                    <div class="w-full flex flex-col justify-center gap-1 text-gray-500">
                        @if($record->sprint)
                            {{ $record->sprint->name }}
                            <span class="text-xs text-gray-400">
                                {{ __('Starts at:') }} {{ $record->sprint->starts_at->format(__('Y-m-d')) }} -
                                {{ __('Ends at:') }} {{ $record->sprint->ends_at->format(__('Y-m-d')) }}
                            </span>
                        @else
                            -
                        @endif
                    </div>
                </div>
            @else
                <div class="w-full flex flex-col gap-1 pt-3">
                    <span class="text-gray-500 text-sm font-medium">
                        {{ __('Epic') }}
                    </span>
                    <div class="w-full flex items-center gap-1 text-gray-500">
                        @if($record->epic)
                            {{ $record->epic->name }}
                        @else
                            -
                        @endif
                    </div>
                </div>
            @endif

            <div class="space-y-1">
                <span class="text-gray-500 text-sm font-medium">
                    {{ __('Master Application') }}
                </span>
                @if($record->masterApplication)
                    <div class="flex flex-wrap gap-1">
                        <span
                            class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium"
                            style="background-color: {{ $record->masterApplication->color }}; color: white;"
                        >
                            {{ $record->masterApplication->name }}
                        </span>
                    </div>
                @else
                    <p class="text-sm text-gray-400">-</p>
                @endif
            </div>

            {{-- Kategori --}}
            <div class="space-y-1">
                <span class="text-gray-500 text-sm font-medium">
                    {{ __('Category') }}
                </span>
                @if ($record->categories->count())
                    <div class="flex flex-wrap gap-1">
                        @foreach ($record->categories as $category)
                            <span
                                class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium"
                                style="background-color: {{ $category->color }}; color: white;"
                            >
                                {{ $category->name }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400">-</p>
                @endif
            </div>

            <div class="space-y-1">
                <span class="text-gray-500 text-sm font-medium">
                    {{ __('Issue Source') }}
                </span>
                @if($record->issueSource)
                    <div class="flex flex-wrap gap-1">
                        <span
                            class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium"
                            style="background-color: {{ $record->issueSource->color }}; color: white;"
                        >
                            {{ $record->issueSource->name }}
                        </span>
                    </div>
                @else
                    <p class="text-sm text-gray-400">-</p>
                @endif
            </div>

            {{-- Estimasi waktu --}}
            <div class="w-full flex flex-col gap-1 pt-3">
                <span class="text-gray-500 text-sm font-medium">
                    {{ __('Estimation') }}
                </span>
                <div class="w-full flex items-center gap-1 text-gray-500">
                    @if($record->estimation)
                        {{ $record->estimationForHumans }}
                    @else
                        -
                    @endif
                </div>
            </div>

            {{-- Total waktu log --}}
            <div class="w-full flex flex-col gap-1 pt-3">
                <span class="text-gray-500 text-sm font-medium">
                    {{ __('Total time logged') }}
                </span>
                @if($record->hours()->count())
                    @if($record->estimation)
                        <div class="flex justify-between mb-1">
                            <span class="text-base font-medium
                                         text-{{ $record->estimationProgress > 100 ? 'danger' : 'primary' }}-700
                                         dark:text-white">
                                {{ $record->totalLoggedHours }}
                            </span>
                            <span class="text-sm font-medium
                                         text-{{ $record->estimationProgress > 100 ? 'danger' : 'primary' }}-700
                                         dark:text-white">
                            {{ round($record->estimationProgress) }}%
                        </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-{{ $record->estimationProgress > 100 ? 'danger' : 'primary' }}-600
                                        h-2.5 rounded-full"
                                 style="width: {{ $record->estimationProgress > 100 ?
                                                    100
                                                    : $record->estimationProgress }}%">
                            </div>
                        </div>
                    @else
                        <div class="w-full flex items-center gap-1 text-gray-500">
                            {{ $record->totalLoggedHours }}
                        </div>
                    @endif
                @else
                    -
                @endif
            </div>

            {{-- Subscriber --}}
            <div class="w-full flex flex-col gap-1 pt-3">
                <span class="text-gray-500 text-sm font-medium">
                    {{ __('Subscribers') }}
                </span>
                <div class="w-full flex items-center gap-1 text-gray-500">
                    @if($record->subscribers->count())
                        @foreach($record->subscribers as $subscriber)
                            <x-user-avatar :user="$subscriber"/>
                        @endforeach
                    @else
                        {{ '-' }}
                    @endif
                </div>
            </div>

            {{-- Milestone --}}
            <div class="space-y-1">
                <span class="text-gray-500 text-sm font-medium">
                    {{ __('Milestone') }}
                </span>
                @if ($record->milestone)
                    <div class="flex flex-wrap gap-1">
                        <span
                            class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium"
                            style="background-color: {{ e($record->milestone->color) }}; color: white;"
                        >
                            {{ $record->milestone->name }}
                        </span>
                    </div>
                @else
                    <p class="text-sm text-gray-400">-</p>
                @endif
            </div>

            {{-- Tanggal pembuatan --}}
            <div class="w-full flex flex-col gap-1 pt-3">
                <span class="text-gray-500 text-sm font-medium">
                    {{ __('Creation date') }}
                </span>
                <div class="w-full text-gray-500">
                    {{ $record->created_at->format(__('Y-m-d g:i A')) }}
                    <span class="text-xs text-gray-400">
                        ({{ $record->created_at->diffForHumans() }})
                    </span>
                </div>
            </div>

            {{-- Tanggal update --}}
            <div class="w-full flex flex-col gap-1 pt-3">
                <span class="text-gray-500 text-sm font-medium">
                    {{ __('Last update') }}
                </span>
                <div class="w-full text-gray-500">
                    {{ $record->updated_at->format(__('Y-m-d g:i A')) }}
                    <span class="text-xs text-gray-400">
                        ({{ $record->updated_at->diffForHumans() }})
                    </span>
                </div>
            </div>

            {{-- Relasi tiket --}}
            @if($record->relations->count())
                <div class="w-full flex flex-col gap-1 pt-3">
                    <span class="text-gray-500 text-sm font-medium">
                        {{ __('Ticket relations') }}
                    </span>
                    <div class="w-full text-gray-500">
                        @foreach($record->relations as $relation)
                            <div class="w-full flex items-center gap-1 text-xs">
                                <span class="rounded px-2 py-1 text-white
                                             bg-{{ config('system.tickets.relations.colors.' . $relation->type) }}-600">
                                    {{ __(config('system.tickets.relations.list.' . $relation->type)) }}
                                </span>
                                <a target="_blank" class="font-medium hover:underline"
                                   href="{{ route('filament.resources.tickets.share', $relation->relation->code) }}">
                                    {{ $relation->relation->code }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-filament::card>

    </div>

    <div class="w-full flex md:flex-row flex-col gap-5">

        {{-- Tab komentar, aktivitas, time log, lampiran --}}
        <x-filament::card class="md:w-2/3 w-full flex flex-col">
            <div class="w-full flex items-center gap-2">
                <button wire:click="selectTab('comments')"
                        class="md:text-xl text-sm p-3 border-b-2 border-transparent hover:border-primary-500 flex items-center
                        gap-1 @if($tab === 'comments') border-primary-500 text-primary-500 @else text-gray-700 @endif">
                    {{ __('Comments') }}
                </button>
                <button wire:click="selectTab('activities')"
                        class="md:text-xl text-sm p-3 border-b-2 border-transparent hover:border-primary-500
                        @if($tab === 'activities') border-primary-500 text-primary-500 @else text-gray-700 @endif">
                    {{ __('Activities') }}
                </button>
                <button wire:click="selectTab('time')"
                        class="md:text-xl text-sm p-3 border-b-2 border-transparent hover:border-primary-500
                        @if($tab === 'time') border-primary-500 text-primary-500 @else text-gray-700 @endif">
                    {{ __('Time logged') }}
                </button>
                <button wire:click="selectTab('attachments')"
                        class="md:text-xl text-sm p-3 border-b-2 border-transparent hover:border-primary-500
                        @if($tab === 'attachments') border-primary-500 text-primary-500 @else text-gray-700 @endif">
                    {{ __('Attachments') }}
                </button>
            </div>
            {{-- Tab Komentar --}}
            @if($tab === 'comments')
                <form wire:submit.prevent="submitComment" class="pb-5">
                    {{ $this->form }}
                    <button type="submit"
                            class="px-3 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded mt-3">
                        {{ __($selectedCommentId ? 'Edit comment' : 'Add comment') }}
                    </button>
                    @if($selectedCommentId)
                        <button type="button" wire:click="cancelEditComment"
                                class="px-3 py-2 bg-warning-500 hover:bg-warning-600 text-white rounded mt-3">
                            {{ __('Cancel') }}
                        </button>
                    @endif
                </form>
                @foreach($record->comments->sortByDesc('created_at') as $comment)
                    <div
                        class="w-full flex flex-col gap-2 @if(!$loop->last) pb-5 mb-5 border-b border-gray-200 @endif ticket-comment">
                        <div class="w-full flex justify-between">
                            <span class="flex items-center gap-1 text-gray-500 text-sm">
                                <span class="font-medium flex items-center gap-1">
                                    <x-user-avatar :user="$comment->user"/>
                                    {{ $comment->user->name }}
                                </span>
                                <span class="text-gray-400 px-2">|</span>
                                {{ $comment->created_at->format('Y-m-d g:i A') }}
                                ({{ $comment->created_at->diffForHumans() }})
                            </span>
                            @if($this->isAdministrator() || $comment->user_id === auth()->user()->id)
                                <div class="actions flex items-center gap-2">
                                    <button type="button" wire:click="editComment({{ $comment->id }})"
                                            class="text-primary-500 text-xs hover:text-primary-600 hover:underline">
                                        {{ __('Edit') }}
                                    </button>
                                    <span class="text-gray-300">|</span>
                                    <button type="button" wire:click="deleteComment({{ $comment->id }})"
                                            class="text-danger-500 text-xs hover:text-danger-600 hover:underline">
                                        {{ __('Delete') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="w-full prose">
                            {!! $comment->content !!}
                        </div>
                    </div>
                @endforeach
            @endif
            {{-- Tab Aktivitas --}}
            @if($tab === 'activities')
                <div class="w-full flex flex-col pt-5">
                    @if($record->activities->count())
                        @foreach($record->activities->sortByDesc('created_at') as $activity)
                            <div class="w-full flex flex-col gap-2
                                 @if(!$loop->last) pb-5 mb-5 border-b border-gray-200 @endif">
                                <span class="flex items-center gap-1 text-gray-500 text-sm">
                                    <span class="font-medium flex items-center gap-1">
                                        <x-user-avatar :user="$activity->user"/>
                                        {{ $activity->user->name }}
                                    </span>
                                    <span class="text-gray-400 px-2">|</span>
                                    {{ $activity->created_at->format('Y-m-d g:i A') }}
                                    ({{ $activity->created_at->diffForHumans() }})
                                </span>
                                <div class="w-full flex items-center gap-10">
                                    <span class="text-gray-400">{{ $activity->oldStatus->name ?? 'Unknown' }}</span>
                                    <x-heroicon-o-arrow-right class="w-6 h-6"/>
                                    <span style="color: {{ $activity->newStatus->color ?? '#6b7280' }}">
                                        {{ $activity->newStatus->name ?? 'Unknown' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <span class="text-gray-400 text-sm font-medium">
                            {{ __('No activities yet!') }}
                        </span>
                    @endif
                </div>
            @endif
            {{-- Tab Time Log --}}
            @if($tab === 'time')
                <livewire:timesheet.time-logged :ticket="$record" />
            @endif
            {{-- Tab Lampiran --}}
            @if($tab === 'attachments')
                <livewire:ticket.attachments :ticket="$record" />
            @endif
        </x-filament::card>

        <div class="md:w-1/3 w-full flex flex-col"></div>

    </div>

</x-filament::page>

{{-- Script untuk fitur share ticket --}}
@push('scripts')
    <script>
        window.addEventListener('shareTicket', (e) => {
            const text = e.detail.url;
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
            } catch (err) {
                console.error('Unable to copy to clipboard', err);
            }
            document.body.removeChild(textArea);
            new Notification()
                .success()
                .title('{{ __('Url copied to clipboard') }}')
                .duration(6000)
                .send()
        });
    </script>
@endpush
