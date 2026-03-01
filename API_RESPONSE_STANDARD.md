# Standar Response API

Proyek ini menggunakan format JSON standar untuk semua endpoint API. Kami telah menyediakan helper **Trait** untuk memudahkan implementasi agar konsisten di semua Controller.

## Format Response JSON

Setiap response API akan memiliki struktur berikut:

```json
{
  "success": boolean,
  "message": string,
  "data": mixed
}
```

- **success**: `true` jika request berhasil, `false` jika gagal/error.
- **message**: Pesan deskriptif (misal: "Data berhasil diambil" atau "Ticket not found").
- **data**: Payload utama (Object, Array, atau null).

---

## Cara Penggunaan (Implementasi)

Kami telah membuat Trait `App\Traits\ApiResponse` yang sudah di-load secara otomatis di **Base Controller**.

### 1. Response Sukses (`successResponse`)

Gunakan method `$this->successResponse($data, $message, $code)` di dalam controller.

**Contoh (Index/List):**
```php
public function index()
{
    $projects = Project::all();
    
    // Otomatis return JSON dengan success: true
    return $this->successResponse(ProjectApiResource::collection($projects), 'List Data Project');
}
```

**Contoh (Detail/Show):**
```php
public function show($id)
{
    $project = Project::find($id);
    
    return $this->successResponse(new ProjectApiResource($project), 'Detail Project');
}
```

---

### 2. Response Error (`errorResponse`)

Gunakan method `$this->errorResponse($message, $code, $data)` untuk mengembalikan error secara manual (bukan Exception).

**Kapan digunakan?**
- Jika data by ID tidak ditemukan (404).
- Jika validasi logika bisnis gagal.
- Jika access denied (403).

**Contoh (Data Not Found):**
```php
public function show($id)
{
    $ticket = Ticket::find($id);

    if (! $ticket) {
        // Return success: false, status: 404
        return $this->errorResponse('Ticket not found', 404);
    }

    return $this->successResponse($ticket, 'Detail Ticket');
}
```

**Catatan Penting:**
Untuk endpoint **List/Index**, jika data kosong (0 record), **JANGAN** gunakan `errorResponse`. Tetap gunakan `successResponse` dengan data array kosong `[]`.

---

## Lokasi File
- Trait: `app/Traits/ApiResponse.php`
- Base Controller: `app/Http/Controllers/Controller.php`
