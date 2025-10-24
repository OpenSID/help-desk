@if($user)
    <div class="w-full flex justify-center disabled-pointer">
        @php($uniqid = uniqid())
        <img src="{{ $user->avatar_url }}"
             alt="{{ $user->name }}"
             data-popover-target="popover-user-{{ $user->id }}-{{ $uniqid }}"
             class="w-6 h-6 rounded-full bg-gray-200 bg-cover bg-center"/>

        <div data-popover id="popover-user-{{ $user->id }}-{{ $uniqid }}" role="tooltip"
             class="inline-block absolute invisible z-10 w-64 text-sm font-light text-gray-500
                                        bg-white rounded-lg border border-gray-200 shadow-sm opacity-0
                                        transition-opacity duration-300 dark:text-gray-400 dark:bg-gray-800
                                        dark:border-gray-600">
            <div class="p-3">
                <div class="flex justify-between items-center mb-2">
                    <img class="w-10 h-10 rounded-full"
                         src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                </div>
                <p class="text-base font-semibold leading-none text-gray-900 dark:text-white">
                    <a>{{ $user->name }}</a>
                </p>
                <p class="mb-3 text-sm font-normal">
                    <a href="mailto:{{ $user->email }}"
                       class="hover:underline">
                        {{ $user->email }}
                    </a>
                </p>
                <p class="mb-4 text-sm font-light">
                    {{ __('Member since') }}
                    <a class="text-blue-600 dark:text-blue-500">
                        {{ $user->created_at->format('Y-m-d') }}
                    </a>
                </p>
                <ul class="flex text-sm font-light">
                    <li class="mr-2">
                        <div>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            {{ collect(($user->ticketsOwned ?? collect())
                                    ->merge(($user->ticketsResponsible ?? collect())))->unique('id')->count() }}
                        </span>
                            <span>{{ __('Tickets') }}</span>
                        </div>
                    </li>
                    <li>
                        <div>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            {{ collect(($user->projectsOwning ?? collect())
                                ->merge(($user->projectsAffected ?? collect())))->unique('id')->count() }}
                        </span>
                            <span>{{ __('Projects') }}</span>
                        </div>
                    </li>
                </ul>
            </div>
            <div data-popper-arrow></div>
        </div>
    </div>

{{-- <button data-popover-target="popover-default" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Default popover</button>

<div data-popover id="popover-default" role="tooltip" class="absolute z-10 invisible inline-block w-64 text-sm text-gray-500 transition-opacity duration-300 bg-white border border-gray-200 rounded-lg shadow-xs opacity-0 dark:text-gray-400 dark:border-gray-600 dark:bg-gray-800">
    <div class="px-3 py-2 bg-gray-100 border-b border-gray-200 rounded-t-lg dark:border-gray-600 dark:bg-gray-700">
        <h3 class="font-semibold text-gray-900 dark:text-white">Popover title</h3>
    </div>
    <div class="px-3 py-2">
        <p>And here's some amazing content. It's very engaging. Right?</p>
    </div>
    <div data-popper-arrow></div>
</div> --}}

@endif
