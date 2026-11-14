<?php

namespace App\Mail;

use App\Models\PriceAlert;
use App\Models\StorePrice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContinueTrackingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly PriceAlert $alert,
        public readonly StorePrice $storePrice,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bạn có muốn tiếp tục theo dõi sản phẩm này không?',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.continue-tracking',
        );
    }
}
