<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\PublicTicketService;
use App\Services\CaptchaService;

class PublicTicketCheck extends Component
{
    public $ticket_code;
    public $captcha_input;
    public $captcha_image;
    public $captcha_code;
    public $generated_captcha;
    public $ticket = null;
    public $message = null;

    public function mount()
    {
        $this->refreshCaptcha();
    }

    public function refreshCaptcha()
    {
        $this->captcha_image = captcha_src('flat');
        $this->captcha_input = '';
    }

    public function generateCaptcha()
    {
        $captchaService = new CaptchaService();
        $captcha = $captchaService->generateCaptcha();
        $this->generated_captcha = $captcha['captcha_code'];
        $this->captcha_code = $captcha['captcha_code'];
    }

    public function checkTicket()
    {

        $this->validate([
            'ticket_code' => 'required|string',
            'captcha_input' => 'required|captcha',
        ], [
            'captcha_input.captcha' => 'Kode captcha salah!',
            'ticket_code.required' => 'Tiket tidak boleh kosong!',
        ]);

        $ticketService = new PublicTicketService();
        $ticket = $ticketService->getTicketByCode($this->ticket_code);
        $this->ticket = $ticket;

        if (empty($this->ticket)) {
            $this->message = 'Tiket tidak ditemukan!';
            $this->ticket = null;
        } else {
            $this->message = null;
        }
    }

    public function render()
    {
        return view('livewire.public-ticket-check');
    }
}
