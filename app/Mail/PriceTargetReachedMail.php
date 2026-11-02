<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PriceTargetReachedMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Sản phẩm bạn theo dõi đã đạt giá mong muốn')
            ->view('emails.price-target-reached')
            ->with([
                'data' => $this->data,
                'buy_url' => $this->data['buy_url'] ?? $this->data['product_url'] ?? null,
                'product_url' => $this->data['product_url'] ?? $this->data['buy_url'] ?? null,
            ]);
    }
}
