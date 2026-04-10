<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;
    public $pdf;

    public function __construct($transaction, $pdf)
    {
        $this->transaction = $transaction;
        $this->pdf = $pdf;
    }

    public function build()
    {
        return $this->subject('Invoice Transaksi #' . $this->transaction->id)
                    ->view('emails.invoice')
                    ->attachData($this->pdf->output(), 'invoice_' . $this->transaction->id . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}