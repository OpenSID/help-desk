{{--
    ticket-category.blade.php
    Komponen partial untuk menampilkan daftar kategori tiket dalam bentuk badge warna.
    - Menerima variabel $state (array kategori)
    - Setiap kategori ditampilkan sebagai badge dengan warna background sesuai field color
--}}
<div class="flex flex-wrap gap-1">
    {{-- Loop setiap kategori dan tampilkan badge warna --}}
    @foreach ($state as $category)
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-white"
              style="background-color: {{ $category->color ?? '#999' }}">
            {{ $category->name }}
        </span>
    @endforeach
</div>
