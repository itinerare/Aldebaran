<?php

namespace App\Mail;

use App\Models\Commission\CommissionQuote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteRequested extends Mailable {
    use Queueable, SerializesModels;

    /**
     * The quote instance.
     *
     * @var \App\Models\Commissions\CommissionQuote
     */
    public $quote;

    /**
     * Create a new message instance.
     */
    public function __construct(CommissionQuote $quote) {
        $this->afterCommit();
        $this->quote = $quote;
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope() {
        return new Envelope(
            subject: 'New Quote Request',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content() {
        return new Content(
            markdown: 'mail.commissions.quote_requested',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments() {
        return [];
    }
}
