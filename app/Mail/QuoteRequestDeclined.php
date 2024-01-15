<?php

namespace App\Mail;

use App\Models\Commission\CommissionQuote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteRequestDeclined extends Mailable {
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
            subject: 'Quote Request Declined (#'.$this->quote->id.')',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content() {
        return new Content(
            markdown: 'mail.commissions.quote-request-declined',
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
