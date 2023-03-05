<?php

namespace App\Mail;

use App\Models\Commission\Commission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommissionRequested extends Mailable {
    use Queueable, SerializesModels;

    /**
     * The commission instance.
     *
     * @var \App\Models\Commissions\Commission
     */
    public $commission;

    /**
     * Create a new message instance.
     */
    public function __construct(Commission $commission) {
        $this->afterCommit();
        $this->commission = $commission;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope() {
        return new Envelope(
            subject: 'New Commission Request',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content() {
        return new Content(
            markdown: 'mail.markdown.commission_requested',
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
