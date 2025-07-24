<div>
    <form wire:submit.prevent="checkTicket" class="space-y-6">
        <div>
            <label class="block text-gray-700 font-medium mb-1">Kode Tiket</label>
            <input type="text" wire:model="ticket_code"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                placeholder="Masukkan kode tiket"
                wire:loading.attr="disabled"
                wire:target="checkTicket,refreshCaptcha">
            @error('ticket_code')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block text-gray-700 font-medium mb-1">Captcha</label>
            <div class="flex items-center space-x-3 mb-2">
                <div class="relative">
                    <img src="{{ $captcha_image }}" alt="captcha" class="rounded shadow border" style="height:40px;">
                    <div wire:loading wire:target="refreshCaptcha"
                        class="absolute inset-0 flex items-center justify-center bg-white/70 rounded">
                        <i class="fas fa-spinner fa-spin text-blue-600 text-xl"></i>
                    </div>
                </div>
                <button type="button" wire:click="refreshCaptcha"
                    class="text-blue-600 hover:underline text-sm flex items-center"
                    wire:loading.attr="disabled"
                    wire:target="refreshCaptcha">
                    <span wire:loading.remove wire:target="refreshCaptcha">
                        <i class="fas fa-rotate-right mr-1"></i> Refresh
                    </span>
                    <i wire:loading wire:target="refreshCaptcha" class="fas fa-spinner fa-spin ml-1 text-blue-600"></i>
                </button>
            </div>
            <input type="text" wire:model="captcha_input"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                placeholder="Masukkan kode di atas"
                wire:loading.attr="disabled"
                wire:target="checkTicket,refreshCaptcha">
            @error('captcha_input')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>
        <button type="submit"
            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded transition shadow-2xl border border-green-700 mt-4 flex items-center justify-center"
            wire:loading.attr="disabled"
            wire:target="checkTicket">
            <span wire:loading.remove wire:target="checkTicket">
                <i class="fas fa-ticket-alt mr-2"></i> Cek Tiket
            </span>
            <i wire:loading wire:target="checkTicket" class="fas fa-spinner fa-spin ml-2"></i>
        </button>

        @if (isset($message) && $message)
            <div class="mt-4 text-center text-red-600 font-semibold">{{ $message }}</div>
        @endif

        @if ($ticket)
            <div class="mt-6 bg-green-50 border border-green-400 rounded p-4 text-green-800">
                <div><span class="font-bold">Status:</span> {{ $ticket->status->name }}</div>
                <div><span class="font-bold">Judul:</span> {{ $ticket->name }}</div>
                <div><span class="font-bold">Isi:</span> {!! $ticket->content !!}</div>
                <div><span class="font-bold">Dibuat pada:</span> {{ $ticket->created_at }}</div>
            </div>
        @endif
    </form>
</div>
