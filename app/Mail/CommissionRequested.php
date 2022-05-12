<?php

namespace App\Mail;

use App\Models\Commission\Commission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommissionRequested extends Mailable
{
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
    public function __construct(Commission $commission)
    {
        //
        $this->commission = $commission;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('New Commission Request')
            ->view('mail.commission_requested');
    }
}
