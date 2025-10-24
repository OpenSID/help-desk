<x-base-layout>

    @isset($title)
        <x-slot:title>{{$title}}</x-slot:title>
    @endisset

    <div class="flex w-full h-screen overflow-hidden">
        <div
            class="w-full bg-primary-700 bg-contain bg-no-repeat bg-bottom"
            style="background-image: url('{{ asset('images/help-desk.png') }}'); ">
        </div>
        <div
            class="w-full flex flex-col justify-center items-center bg-white"
        >
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="mt-10 w-56" />

            <div class="w-full overflow-y-auto">
                {{$slot}}
            </div>

            {{-- Tombol Login Admin di tengah --}}
            <div class="mb-10 flex justify-center">
                <a href="{{ url('/login') }}"
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
