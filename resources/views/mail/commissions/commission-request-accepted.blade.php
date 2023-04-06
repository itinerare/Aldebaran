<x-mail::message>

Thank you for requesting a {{ $commission->type->name }} commission! I am pleased to inform you that your request has been accepted and placed in the active queue. You may view the status of your commission, its position in the queue, and any additional information at any time via its page, accessible by clicking the button below.
Note that you will be contacted separately if any further information is required and if/when payment is due. You may also be notified as your commission progresses at my discretion.

<x-mail::button :url="$commission->url" color="success">
    View Commission
</x-mail::button>

<hr /><br />

@include('mail.commissions._footer', ['object' => $commission, 'subject' => 'commission', 'subjectTitle' => 'Commission'])

</x-mail::message>
