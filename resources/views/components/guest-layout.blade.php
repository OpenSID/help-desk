<x-base-layout>

    @isset($title)
        <x-slot:title>{{$title}}</x-slot:title>
    @endisset

    <div class="absolute w-full h-full top-0 left-0 right-0 bottom-0 overflow-hidden">
        <div
            class="absolute lg:w-1/2 md:w-1/3 lg:flex md:flex hidden flex-col justify-start
            items-start top-0 bottom-0 left-0 bg-primary-700 bg-cover bg-no-repeat
            bg-left-bottom bg-opacity-90"
            style="background-image: url('{{ asset('images/help-desk.png') }}'); background-size: 80%">
        </div>
        <div
            class="absolute lg:w-1/2 md:w-2/3 xl:p-44 lg:p-32 md:p-24 p-20 flex flex-col justify-center
            items-center top-0 bottom-0 right-0 bg-white overflow-y-auto"
        >
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="mb-5 w-56" />
            {{$slot}}

            {{-- Tombol Login Admin di tengah --}}
            <div class="mt-10 flex justify-center">
                <a href="{{ url('/admin/login') }}"
                   class="flex items-center gap-2 px-6 py-3 bg-blue-700 text-white rounded-full shadow-lg hover:bg-blue-800 transition font-semibold text-lg"
                   title="Login Admin">
                    <!-- Heroicon: Lock Closed -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H8m4-6a4 4 0 10-8 0v4a2 2 0 002 2h8a2 2 0 002-2v-4a4 4 0 00-8 0" />
                    </svg>
                    <span>Login Admin</span>
                </a>
            </div>
        </div>
    </div>

</x-base-layout>
