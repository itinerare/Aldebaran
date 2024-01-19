<?php

namespace App\Mail;

use App\Models\MailingList\MailingListSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyMailingListSubscription extends Mailable {
    use Queueable, SerializesModels;

    /**
     * The subscriber instance.
     *
     * @var MailingListSubscriber
     */
    public $subscriber;

    /**
     * Create a new message instance.
     */
    public function __construct(MailingListSubscriber $subscriber) {
        $this->subscriber = $subscriber;
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope() {
        return new Envelope(
            subject: 'Verify Mailing List Subscription',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content() {
        return new Content(
            markdown: 'mail.mailing_lists.verify_subscription',
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
