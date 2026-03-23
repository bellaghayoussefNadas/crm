<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $quote;

    public function __construct(Quote $quote)
    {
        $this->quote = $quote->load(['contact', 'account', 'user']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nouveau Devis : " . $this->quote->reference . " - " . $this->quote->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.quotes.sent',
        );
    }
}
