<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketStatusApiResource;
use App\Models\TicketStatus;
use Illuminate\Http\Request;

class TicketStatusController extends Controller
{
    /**
     * Get Master Ticket Statuses
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = TicketStatus::query();

        // Parameter pencarian berdasarkan nama status
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Urutkan berdasarkan order (urutan prioritas status)
        $statuses = $query->orderBy('order', 'asc')->get();

        return $this->successResponse(TicketStatusApiResource::collection($statuses), 'List Master Ticket Status');
    }
}
