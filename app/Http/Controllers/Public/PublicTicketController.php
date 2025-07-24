<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PublicTicketService;
use App\Services\CaptchaService;

class PublicTicketController extends Controller
{


    public function getCaptcha()
    {
        $captchaService = new CaptchaService();
        return response()->json($captchaService->generateCaptcha());
    }

    public function checkTicket(Request $request)
    {
        $request->validate([
            'ticket_code' => 'required|string',
            'captcha_input' => 'required|string',
            'captcha_code' => 'required|string',
        ]);

        $captchaService = new CaptchaService();
        if (!$captchaService->validateCaptcha($request->captcha_input, $request->captcha_code)) {
            return response()->json(['message' => 'Captcha salah!'], 401);
        }

        $ticketService = new PublicTicketService();
        $ticket = $ticketService->getTicketByCode($request->ticket_code);

        if (!$ticket) {
            return response()->json(['message' => 'Tiket tidak ditemukan!'], 404);
        }

        return response()->json($ticket);
    }
}
