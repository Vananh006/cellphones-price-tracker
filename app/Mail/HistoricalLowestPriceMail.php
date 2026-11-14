<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HistoricalLowestPriceMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Sản phẩm bạn theo dõi vừa có giá tốt')
            ->view('emails.historical-lowest-price')
            ->with([
                'data' => $this->data,
                'buy_url' => $this->data['buy_url'] ?? $this->data['product_url'] ?? null,
                'product_url' => $this->data['product_url'] ?? $this->data['buy_url'] ?? null,
            ]);
    }
}
