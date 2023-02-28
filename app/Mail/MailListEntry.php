<?php

namespace App\Mail;

use App\Models\MailingList\MailingListEntry;
use App\Models\MailingList\MailingListSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailListEntry extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /**
     * The entry instance.
     *
     * @var \App\Models\MailingList\MailingListEntry
     */
    public $entry;

    /**
     * The entry instance.
     *
     * @var \App\Models\MailingList\MailingListSubscriber
     */
    public $subscriber;

    /**
     * Create a new message instance.
     */
    public function __construct(MailingListEntry $entry, MailingListSubscriber $subscriber) {
        $this->afterCommit();
        $this->entry = $entry->withoutRelations();
        $this->subscriber = $subscriber->withoutRelations();
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope() {
        return new Envelope(
            subject: $this->entry->subject,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content() {
        return new Content(
            markdown: 'mail.markdown.mailing_list_entry',
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
