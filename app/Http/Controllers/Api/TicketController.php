<?php

namespace App\Http\Controllers\Api;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TicketController extends Controller
{

    /**
     * Cek tiket berdasarkan ID numerik
     */
    public function show($id, Request $request)
    {
        $ticket = Ticket::with(['owner', 'status', 'project'])->find($id);

        if (! $ticket) {
            return response()->json([
                'message' => 'Ticket not found',
            ], 404);
        }

        return $this->formatTicketResponse($ticket);
    }

    /**
     * Format response JSON untuk ticket
     */
    protected function formatTicketResponse(Ticket $ticket)
    {
        $content = $ticket->content;

        // Ganti <br> dan </p> jadi newline sebelum strip_tags
        $content = preg_replace('/<br\s*\/?>/i', "\n", $content);
        $content = preg_replace('/<\/p>/i', "\n", $content);

        // Hapus semua tag HTML lain
        $plainText = trim(strip_tags($content));

        return response()->json([
            'message' => 'Success',
            'data' => [
                'tiket_id'         => $ticket->id,
                'tiket_code'       => $ticket->code,
                'tiket_status'     => $ticket->status?->name,
                'tiket_nama'       => $ticket->name,
                'tiket_layanan'    => $ticket->project?->name,
                'tiket_deskripsi'  => $plainText,
                'tiket_created_at' => $ticket->created_at?->format('Y-m-d H:i:s'),
                'tiket_updated_at' => $ticket->updated_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
