<?php

namespace App\Mail;

use App\Models\PriceAlert;
use App\Models\StorePrice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PriceDropAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly PriceAlert $alert,
        public readonly StorePrice $storePrice,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Giá sản phẩm đã vào vùng tối ưu',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.price-drop-alert',
        );
    }
}
