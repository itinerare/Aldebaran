<?php

namespace App\Mail;

use App\Models\Commission\Commission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommissionRequestAccepted extends Mailable {
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
     * @return Envelope
     */
    public function envelope() {
        return new Envelope(
            subject: 'Commission Request Accepted (#'.$this->commission->id.')',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content() {
        return new Content(
            markdown: 'mail.commissions.commission-request-accepted',
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
